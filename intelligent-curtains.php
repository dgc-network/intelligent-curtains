<?php
/**
 * Plugin Name: intelligent-curtains
 * Plugin URI: https://wordpress.org/plugins/intelligent-curtains/
 * Description: The leading web api plugin for pig system by shortcode
 * Author: dgc.network
 * Author URI: https://dgc.network/
 * Version: 1.0.2
 * Requires at least: 6.0
 * Tested up to: 6.5.2
 * 
 * Text Domain: intelligent-curtains
 * Domain Path: /languages/
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( headers_sent( $file, $line ) ) {
    error_log( "Headers already sent in $file on line $line" );
}

function is_rest_request() {
    return defined( 'REST_REQUEST' ) && REST_REQUEST;
}

function register_session() {
    if ( ! session_id() && ! is_rest_request() ) {
        //session_start();
    }
}
add_action( 'init', 'register_session', 1 );
/*
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
*/
function remove_admin_bar() {
    // Check if the current user is an administrator or editor
    if (!current_user_can('administrator') && !current_user_can('editor') && !is_admin()) {
        show_admin_bar(false);
    }
}
add_action('after_setup_theme', 'remove_admin_bar');

function redirect_subscribers_after_login($redirect_to, $request, $user) {
    // Check if the user has the subscriber role
    if (isset($user->roles) && is_array($user->roles) && in_array('subscriber', $user->roles)) {
        // Redirect to the root URL
        return home_url('/');
    }

    // Return the original redirect URL for other roles
    return $redirect_to;
}
add_filter('login_redirect', 'redirect_subscribers_after_login', 10, 3);

function enqueue_scripts() {		
    wp_enqueue_script( 'qrcode-js', plugins_url( 'assets/js/jquery.qrcode.min.js' , __FILE__ ), array( 'jquery' ), time() );
    wp_enqueue_script( 'jquery-ui-js', 'https://code.jquery.com/ui/1.13.2/jquery-ui.js' );
    wp_enqueue_script( 'jquery-ui-datepicker' );
    wp_enqueue_script( 'jquery-ui-dialog' );

    wp_enqueue_style( 'custom-options-view', plugins_url( 'assets/css/custom-options-view.css' , __FILE__ ), '', time() );
    wp_enqueue_style( 'jquery-ui-css', 'https://code.jquery.com/ui/1.13.2/themes/smoothness/jquery-ui.css' );

    wp_enqueue_script( 'custom-script', plugins_url( 'assets/js/custom-options-view.js' , __FILE__ ), array( 'jquery' ), time() );
    wp_enqueue_script( 'curtain-orders', plugins_url( 'assets/js/curtain-orders.js' , __FILE__ ), array( 'jquery' ), time() );
    wp_enqueue_script( 'curtain-misc', plugins_url( 'assets/js/curtain-misc.js' , __FILE__ ), array( 'jquery' ), time() );
    wp_localize_script( 'custom-script', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ), ) );
    wp_localize_script( 'curtain-orders', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ), ) );
    wp_localize_script( 'curtain-misc', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ), ) );
}
add_action( 'wp_enqueue_scripts', 'enqueue_scripts' );

function init_webhook_events() {
    $line_bot_api = new line_bot_api();
    $open_ai_api = new open_ai_api();

    $entityBody = file_get_contents('php://input');
    $data = json_decode($entityBody, true);
    $events = $data['events'] ?? [];

    foreach ((array)$events as $event) {
        $line_user_id = $event['source']['userId'];
        $profile = $line_bot_api->getProfile($line_user_id);
        $display_name = str_replace(' ', '', $profile['displayName']);

        // Regular webhook response
        switch ($event['type']) {
            case 'message':
                $message = $event['message'];
                switch ($message['type']) {
                    case 'text':
                        $curtain_faq = new curtain_faq();
                        $query = $curtain_faq->retrieve_curtain_faq_data(0, $message['text']);
                        if ( $query->have_posts() ) {
                            $body_contents = array();
                            while ( $query->have_posts() ) {
                                $query->the_post(); // Setup post data
                                $toolbox_uri = get_post_meta(get_the_ID(), 'toolbox_uri', true);
                                // Create a body content array for each post
                                $body_content = array(
                                    'type' => 'text',
                                    'text' => 'Q: '.get_the_title(),  // Get the current post's title
                                    'weight' => 'bold',
                                    'wrap' => true,
                                );
                                $body_contents[] = $body_content;
                                $body_content = array(
                                    'type' => 'text',
                                    'text' => 'A: '.get_the_content(),  // Get the current post's title
                                    'wrap' => true,
                                );
                                $body_contents[] = $body_content;
                                if ($toolbox_uri) {
                                    $body_content = array(
                                        'type' => 'button',
                                        'action' => array(
                                            'type' => 'uri',
                                            'label' => '工具箱',
                                            'uri' => $toolbox_uri,
                                        ),
                                        'style' => 'primary',
                                        'margin' => 'sm',
                                    );
                                    $body_contents[] = $body_content;    
                                }
                            } 
                            // Reset post data after custom loop
                            wp_reset_postdata();

                            // Generate the Flex Message
                            $flexMessage = $line_bot_api->set_bubble_message([
                                'body_contents' => $body_contents,
                            ]);
                            // Send the Flex Message via LINE API
                            $line_bot_api->replyMessage(array(
                                'replyToken' => $event['replyToken'],
                                'messages' => array($flexMessage),
                            ));
                        } else {
                            // handle_iot_command
                            $command = $message['text'];
                            // 對應表：指令 -> IoT 控制 URL & 動作

                            // Open-AI auto reply
                            $response = $open_ai_api->createChatCompletion($message['text']);
                            $line_bot_api->replyMessage([
                                'replyToken' => $event['replyToken'],
                                'messages' => [
                                    [
                                        'type' => 'text',
                                        'text' => $response
                                    ]                                                                    
                                ]
                            ]);
                        }
                        break;
                    default:
                        error_log('Unsupported message type: ' . $message['type']);
                        break;
                }
                break;
            default:
                error_log('Unsupported event type: ' . $event['type']);
                break;
        }
    }
}
add_action( 'parse_request', 'init_webhook_events' );

function is_user_not_an_agent($user_id=false) {
    if (empty($user_id)) $user_id=get_current_user_id();
    $user = get_userdata($user_id);
    // Get the curtain_agent_id meta for the user
    $curtain_agent_id = get_user_meta($user_id, 'curtain_agent_id', true);
    
    // Check if curtain_agent_id does not exist or is empty
    if (empty($curtain_agent_id)) {
        return true;
    }
    return false;
}

function user_is_not_logged_in() {
    $line_login_api = new line_login_api();
    $line_login_api->display_line_login_button();
}

function get_post_type_meta_keys($post_type) {
    global $wpdb;
    $query = $wpdb->prepare("
        SELECT DISTINCT(meta_key)
        FROM $wpdb->postmeta
        INNER JOIN $wpdb->posts ON $wpdb->posts.ID = $wpdb->postmeta.post_id
        WHERE $wpdb->posts.post_type = %s
    ", $post_type);

    return $wpdb->get_col($query);
}

require_once plugin_dir_path( __FILE__ ) . 'services/services.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-curtain-agents.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-curtain-categories.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-curtain-orders.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-curtain-serials.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-order-status.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-product-items.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-curtain-faq.php';

function set_flex_message($display_name, $link_uri, $text_message) {
    // Flex Message JSON structure with a button
    return $flexMessage = [
        'type' => 'flex',
        'altText' => $text_message,
        'contents' => [
            'type' => 'bubble',
            'body' => [
                'type' => 'box',
                'layout' => 'vertical',
                'contents' => [
                    [
                        'type' => 'text',
                        'text' => 'Hello, '.$display_name,
                        'size' => 'lg',
                        'weight' => 'bold',
                    ],
                    [
                        'type' => 'text',
                        'text' => $text_message,
                        'wrap' => true,
                    ],
                ],
            ],
            'footer' => [
                'type' => 'box',
                'layout' => 'vertical',
                'contents' => [
                    [
                        'type' => 'button',
                        'action' => [
                            'type' => 'uri',
                            'label' => 'Click me!',
                            'uri' => $link_uri, // Replace with your desired URI
                        ],
                    ],
                ],
            ],
        ],
    ];
}

function get_keyword_matched($keyword) {
    // Check if $keyword is contained within '我要註冊登入登錄'
    if (strpos($keyword, '註冊') !== false) return true;
    if (strpos($keyword, '登入') !== false) return true;
    if (strpos($keyword, '登錄') !== false) return true;
    if (strpos($keyword, 'login') !== false) return true;
    if (strpos($keyword, 'Login') !== false) return true;
        
    return false;
}

function wp_login_submit() {
    $response = array('success' => false, 'error' => 'Invalid data format');

    if (isset($_POST['_display_name']) && isset($_POST['_user_email']) && isset($_POST['_log']) && isset($_POST['_pwd'])) {
        $user_login = sanitize_text_field($_POST['_log']);
        $user_password = sanitize_text_field($_POST['_pwd']);
        $display_name = sanitize_text_field($_POST['_display_name']);
        $user_email = sanitize_text_field($_POST['_user_email']);

        $credentials = array(
            'user_login'    => $user_login,
            'user_password' => $user_password,
            'remember'      => true,
        );
        $user = wp_signon($credentials, false);

        if (!is_wp_error($user)) {
            wp_set_current_user($user->ID);
            wp_set_auth_cookie($user->ID);
            do_action('wp_login', $user->user_login);

            wp_update_user(array(
                'ID' => $user->ID,
                'display_name' => $display_name,
                'user_email' => $user_email,
            ));
            $response = array('success' => true);
        } else {
            $response = array('error' => $user->get_error_message());
        }
    }
    wp_send_json($response);
}
add_action('wp_ajax_wp_login_submit', 'wp_login_submit');
add_action('wp_ajax_nopriv_wp_login_submit', 'wp_login_submit');

// handle_iot_command
add_action('rest_api_init', function () {
    register_rest_route('iot/v1', '/control', array(
        'methods' => 'POST',
        'callback' => 'handle_iot_command',
        'permission_callback' => '__return_true',
    ));
});

function handle_iot_command(WP_REST_Request $request) {
    global $wpdb;

    $command = sanitize_text_field($request->get_param('command'));

    $device_mappings = array(
        '開燈'   => ['device' => 'light',   'location' => 'living_room', 'action' => 'on'],
        '關燈'   => ['device' => 'light',   'location' => 'living_room', 'action' => 'off'],
        '開窗簾' => ['device' => 'curtain', 'location' => 'living_room', 'action' => 'open'],
        '關窗簾' => ['device' => 'curtain', 'location' => 'living_room', 'action' => 'close'],
    );

    if (!array_key_exists($command, $device_mappings)) {
        return new WP_REST_Response(['status' => 'error', 'message' => '未知的指令：' . $command], 400);
    }

    $info = $device_mappings[$command];
    $json_cmd = json_encode([
        'device' => $info['device'],
        'location' => $info['location'],
        'action' => $info['action']
    ]);

    $device_id = $info['location'] . '-' . $info['device']; // e.g., living_room-light
    $table = $wpdb->prefix . 'iot_devices';

    $wpdb->replace($table, [
        'device_id'         => $device_id,
        'pending_command'   => $json_cmd,
        'command_set_time'  => current_time('mysql')
    ]);

    return new WP_REST_Response([
        'status' => 'ok',
        'device' => $device_id,
        'command' => $json_cmd
    ], 200);
}

// map device IDs to tokens
const IOT_AUTH_TOKENS = [
    'kitchen-01'     => 'abc123',
    'livingroom-02'  => 'xyz789',
];

add_action('rest_api_init', function () {
    register_rest_route('iot/v1', '/get-command', [
        'methods'             => 'GET',
        'callback'            => 'iot_get_command_handler',
        'permission_callback' => '__return_true',
    ]);
});

/**
 * GET handler: /wp-json/iot/v1/get-command
 */
function iot_get_command_handler( WP_REST_Request $req ) {
    global $wpdb;
    $device_id  = sanitize_text_field( $req->get_param('device_id') );
    $auth_token = sanitize_text_field( $req->get_param('auth_token') );

    // 1) validate
    if ( ! $device_id || ! $auth_token ) {
        return new WP_Error('missing_params', 'device_id and auth_token are required', ['status'=>400]);
    }
    if ( ! array_key_exists($device_id, IOT_AUTH_TOKENS)
         || IOT_AUTH_TOKENS[$device_id] !== $auth_token ) {
        return new WP_Error('unauthorized', 'Invalid auth_token', ['status'=>403]);
    }

    // 2) fetch pending command
    $table = $wpdb->prefix . 'iot_devices'; 
    $row = $wpdb->get_row( $wpdb->prepare(
        "SELECT pending_command, command_set_time 
           FROM {$table} 
          WHERE device_id = %s",
        $device_id
    ) );

    if ( ! $row || empty($row->pending_command) ) {
        // no command
        return rest_ensure_response( (object)['action'=>null,'device'=>null,'location'=>null] );
    }

    // 3) auto-expire after 120 s
    $now     = time();
    $set_ts  = strtotime( $row->command_set_time );
    if ( ($now - $set_ts) > 120 ) {
        // clear expired command
        $wpdb->update( $table,
            ['pending_command' => null, 'command_set_time'=>null],
            ['device_id' => $device_id]
        );
        return rest_ensure_response( (object)['action'=>null,'device'=>null,'location'=>null] );
    }

    // 4) return the JSON command
    //    pending_command is stored as JSON string {"action":"...","device":"...","location":"..."}
    $cmd = json_decode( $row->pending_command );
    if ( json_last_error() !== JSON_ERROR_NONE ) {
        return new WP_Error('bad_json','Stored command is not valid JSON',['status'=>500]);
    }

    return rest_ensure_response( $cmd );
}
