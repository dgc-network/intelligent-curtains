<?php
//update_option( 'home', 'https://aihome.tw' );
//update_option( 'siteurl', 'https://aihome.tw' );
/**
 * Plugin Name: intelligent-curtains
 * Plugin URI: https://wordpress.org/plugins/intelligent-curtains/
 * Description: The leading web api plugin for pig system by shortcode
 * Author: dgc.network
 * Author URI: https://dgc.network/
 * Version: 1.0.1
 * Requires at least: 6.0
 * Tested up to: 6.0.2
 * 
 * Text Domain: intelligent-curtains
 * Domain Path: /languages/
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
/*
function custom_date_format() {
    $date_format = get_option('date_format');
    if (empty($date_format)) {
        $new_date_format = 'Y-m-d'; // Set your desired date format    
        update_option('date_format', $new_date_format);
        $new_time_format = 'h:i'; // Set your desired time format
        update_option('time_format', $new_time_format);
    }
}
add_action( 'init', 'custom_date_format' );
*/
function register_session() {
    if ( ! session_id() ) {
        session_start();
    }
}
add_action( 'init', 'register_session' );

function enqueue_scripts() {		
    wp_enqueue_script( 'qrcode-js', plugins_url( '/assets/js/jquery.qrcode.min.js' , __FILE__ ), array( 'jquery' ), time() );
    wp_enqueue_script( 'jquery-ui-js', 'https://code.jquery.com/ui/1.13.2/jquery-ui.js' );
    wp_enqueue_script( 'jquery-ui-datepicker' );
    wp_enqueue_script( 'jquery-ui-dialog' );

    wp_enqueue_style( 'custom-options-view', plugins_url( '/assets/css/custom-options-view.css' , __FILE__ ), '', time() );
    wp_enqueue_style( 'chat-css', plugins_url( '/assets/css/chat.css' , __FILE__ ), '', time() );
    wp_enqueue_style( 'jquery-ui-css', 'https://code.jquery.com/ui/1.13.2/themes/smoothness/jquery-ui.css' );
    wp_enqueue_style( 'demos-style-css', 'https://jqueryui.com/resources/demos/style.css' );

    wp_enqueue_script( 'custom-script', plugins_url( '/assets/js/custom-options-view.js' , __FILE__ ), array( 'jquery' ), time() );
    wp_enqueue_script( 'curtain-orders', plugins_url( '/assets/js/curtain-orders.js' , __FILE__ ), array( 'jquery' ), time() );
    wp_enqueue_script( 'curtain-categories', plugins_url( '/assets/js/curtain-categories.js' , __FILE__ ), array( 'jquery' ), time() );
    wp_localize_script( 'custom-script', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ), ) );
    wp_localize_script( 'curtain-orders', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ), ) );
    wp_localize_script( 'curtain-categories', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ), ) );
}
add_action( 'wp_enqueue_scripts', 'enqueue_scripts' );

require_once plugin_dir_path( __FILE__ ) . 'includes/general-helps.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-service-links.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-curtain-service.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-curtain-agents.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-curtain-orders.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-curtain-categories.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-curtain-models.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-curtain-specifications.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-curtain-remotes.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-curtain-serials.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-system-status.php';
require_once plugin_dir_path( __FILE__ ) . 'web-services/options-setting.php';
add_option('_line_account', 'https://line.me/ti/p/@490tjxdt');

$curtain_service = new curtain_service();
$curtain_service->init_webhook_events();

function init_webhook_events() {

    $line_bot_api = new line_bot_api();
    $open_ai_api = new open_ai_api();

    $entityBody = file_get_contents('php://input');
    $data = json_decode($entityBody, true);
    $events = $data['events'] ?? [];

    foreach ((array)$events as $event) {

        // Start the User Login/Registration process if got the one time password
        if ((int)$event['message']['text']==(int)get_option('_one_time_password')) {
            $profile = $line_bot_api->getProfile($event['source']['userId']);
            $display_name = str_replace(' ', '', $profile['displayName']);
            // Encode the Chinese characters for inclusion in the URL
            $link_uri = home_url().'/my-jobs/?_id='.$event['source']['userId'].'&_name='.urlencode($display_name);
            // Flex Message JSON structure with a button
            $flexMessage = [
                'type' => 'flex',
                'altText' => 'This is a Flex Message with a Button',
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
                                'text' => 'You have not logged in yet. Please click the button below to go to the Login/Registration system.',
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
            
            $line_bot_api->replyMessage([
                'replyToken' => $event['replyToken'], // Make sure $event['replyToken'] is valid and present
                'messages' => [$flexMessage],
            ]);            
        }

        // Regular webhook response
        switch ($event['type']) {
            case 'message':
                $message = $event['message'];
                switch ($message['type']) {
                    case 'text':
                        // Open-AI auto reply
                        $response = $open_ai_api->createChatCompletion($message['text']);
                        $line_bot_api->replyMessage([
                            'replyToken' => $event['replyToken'],
                            'messages' => [
                                [
                                    'type' => 'text',
                                    //'text' => $response,
                                    'text' => $message['text'],
                                ]                                                                    
                            ]
                        ]);
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
//add_action( 'parse_request', 'init_webhook_events' );

//add_action('parse_request', 'process_line_webhook');
function process_line_webhook() {
    global $wpdb;
    $line_bot_api = new line_bot_api();
    $open_ai_api = new open_ai_api();
    $curtain_agents = new curtain_agents();
/*
    if (file_exists(plugin_dir_path( __DIR__ ).'assets/templates/see_more.json')) {
        $see_more = file_get_contents(plugin_dir_path( __DIR__ ).'assets/templates/see_more.json');
        $see_more = json_decode($see_more, true);
    }
*/
    $entityBody = file_get_contents('php://input');
    $data = json_decode($entityBody, true);
    $events = $data['events'] ?? [];

    foreach ((array)$events as $event) {
/*
        // Start the User Login/Registration process if got the one time password
        if ($event['message']['text']==get_option('_one_time_password')) {
            $link_uri = get_option('Service').'?_id='.$event['source']['userId'];
            $see_more["body"]["contents"][0]["action"]["label"] = 'User Login/Registration';
            $see_more["body"]["contents"][0]["action"]["uri"] = $link_uri;
            $line_bot_api->replyMessage([
                'replyToken' => $event['replyToken'],
                'messages' => [
                    [
                        "type" => "flex",
                        "altText" => 'Welcome message',
                        'contents' => $see_more
                    ]
                ]
            ]);
        }

        // Start the Agent Login/Registration process if got the correct agent number
        $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}curtain_agents WHERE agent_number = %s", $event['message']['text'] ), OBJECT );            
        if (is_null($row) || !empty($wpdb->last_error)) {
        } else {
            $link_uri = get_option('Service').'?_id='.$event['source']['userId'].'&_agent_no='.$event['message']['text'];
            $see_more["body"]["contents"][0]["action"]["label"] = 'Agent Login/Registration';
            $see_more["body"]["contents"][0]["action"]["uri"] = $link_uri;
            $line_bot_api->replyMessage([
                'replyToken' => $event['replyToken'],
                'messages' => [
                    [
                        "type" => "flex",
                        "altText" => 'Welcome message',
                        'contents' => $see_more
                    ]
                ]
            ]);
        }
*/
        switch ($event['type']) {
            case 'message':
                $message = $event['message'];
                switch ($message['type']) {
                    case 'text':
                        /** Open-AI auto reply */
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
