<?php
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

function register_session() {
    if ( ! session_id() ) {
        session_start();
    }
}
add_action( 'init', 'register_session' );

function remove_admin_bar() {
    if (!current_user_can('administrator') && !is_admin()) {
      show_admin_bar(false);
    }
}
add_action('after_setup_theme', 'remove_admin_bar');  
  
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

function init_webhook_events() {
    global $wpdb;
    $line_bot_api = new line_bot_api();
    $open_ai_api = new open_ai_api();

    $entityBody = file_get_contents('php://input');
    $data = json_decode($entityBody, true);
    $events = $data['events'] ?? [];

    foreach ((array)$events as $event) {
        // Start the session to access stored OTP and expiration
        session_start();
        // Get stored OTP and expiration timestamp from session
        $one_time_password = isset($_SESSION['one_time_password']) ? intval($_SESSION['one_time_password']) : 0;

        // Start the User Login/Registration process if got the one time password
        if ((int)$event['message']['text']===$one_time_password) {
        //}
        //if ((int)$event['message']['text']==(int)get_option('_one_time_password')) {
            $profile = $line_bot_api->getProfile($event['source']['userId']);
            $display_name = str_replace(' ', '', $profile['displayName']);
            // Encode the Chinese characters for inclusion in the URL
            $link_uri = home_url().'/service/?_id='.$event['source']['userId'].'&_name='.urlencode($display_name);
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
                if (!is_user_logged_in()) {
                    $line_user_id = $event['source']['userId'];
                    proceed_to_registration_login($line_user_id);
                }
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
                                    'text' => $response,
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
add_action( 'parse_request', 'init_webhook_events' );

function proceed_to_registration_login($line_user_id) {
    // Using Line User ID to register and login into the system
    $array = get_users( array( 'meta_value' => $line_user_id ));
    if (empty($array)) {
        $user_id = wp_insert_user( array(
            'user_login' => $line_user_id,
            'user_pass' => $line_user_id,
        ));
        $user = get_user_by( 'ID', $user_id );
        add_user_meta( $user_id, 'line_user_id', $line_user_id );
    }

    $link_uri = home_url().'/support/after_service/';

    $output  = '<div style="text-align:center;">';
    $output .= '<p>This is an automated process that helps you register for the system. ';
    $output .= 'Please click the Submit button below to complete your registration.</p>';
    $output .= '<form action="'.esc_url( site_url( 'wp-login.php', 'login_post' ) ).'" method="post" style="display:inline-block;">';
    $output .= '<fieldset>';
    $output .= '<input type="hidden" name="log" value="'. $line_user_id .'" />';
    $output .= '<input type="hidden" name="pwd" value="'. $line_user_id .'" />';
    $output .= '<input type="hidden" name="rememberme" value="foreverchecked" />';
    $output .= '<input type="hidden" name="redirect_to" value="'.esc_url( $link_uri ).'" />';
    $output .= '<input type="submit" name="wp-submit" class="button button-primary" value="Submit" />';
    $output .= '</fieldset>';
    $output .= '</form>';
    $output .= '</div>';
    return $output;

}

function user_did_not_login_yet() {
    
    if( isset($_GET['_id']) && isset($_GET['_name']) ) {
        // Using Line User ID to register and login into the system
        $array = get_users( array( 'meta_value' => $_GET['_id'] ));
        if (empty($array)) {
            $user_id = wp_insert_user( array(
                'user_login' => $_GET['_id'],
                'user_pass' => $_GET['_id'],
            ));
            add_user_meta( $user_id, 'line_user_id', $_GET['_id']);
        } else {
            // Get user by 'line_user_id' meta
            global $wpdb;
            $user_id = $wpdb->get_var($wpdb->prepare(
                "SELECT user_id FROM $wpdb->usermeta WHERE meta_key = 'line_user_id' AND meta_value = %s",
                $_GET['_id']
            ));
            $site_id = get_user_meta( $user_id, 'site_id', true);
            $site_title = get_the_title($site_id);
        }
        //$user = get_user_by( 'ID', $user_id );
        $user_data = get_userdata( $user_id );
        ?>
        <div class="ui-widget">
            <h2>User registration/login</h2>
            <fieldset>
                <label for="display-name">Name:</label>
                <input type="text" id="display-name" value="<?php echo esc_attr($_GET['_name']);?>" class="text ui-widget-content ui-corner-all" />
                <label for="user-email">Email:</label>
                <input type="text" id="user-email" value="<?php echo esc_attr($user_data->user_email);?>" class="text ui-widget-content ui-corner-all" />
                <label for="site-id">Site:</label>
                <input type="text" id="site-title" value="<?php echo esc_attr($site_title);?>" class="text ui-widget-content ui-corner-all" />
                <div id="site-hint" style="display:none; color:#999;"></div>
                <input type="hidden" id="site-id" value="<?php echo esc_attr($site_id);?>" />
                <input type="hidden" id="log" value="<?php echo esc_attr($_GET['_id']);?>" />
                <input type="hidden" id="pwd" value="<?php echo esc_attr($_GET['_id']);?>" />
                <hr>
                <input type="submit" id="wp-login-submit" class="button button-primary" value="Submit" />
            </fieldset>
        </div>
        <?php        
} else {
        // Display a message or redirect to the login/registration page
        $one_time_password = random_int(100000, 999999);
        update_option('_one_time_password', $one_time_password);
        // Store OTP in session for verification
        session_start();
        $_SESSION['one_time_password'] = $one_time_password;

        ?>
        <div class="desktop-content ui-widget" style="text-align:center; display:none;">
            <!-- Content for desktop users -->
            <p>感謝您使用我們的系統</p>
            <p>請輸入您的 Email 帳號</p>
            <input type="text" id="user-email-input" />
            <div id="otp-input-div" style="display:none;">
            <p>請輸入傳送到您 Line 上的六位數字密碼</p>
            <input type="text" id="one-time-password-desktop-input" />
            <input type="hidden" id="line-user-id-input" />
            </div>
        </div>

        <div class="mobile-content ui-widget" style="text-align:center; display:none;">
            <!-- Content for mobile users -->
            <p>感謝您使用我們的系統</p>
            <p>請加入我們的Line官方帳號,</p>
            <p>利用手機按或掃描下方QR code</p>
            <a href="<?php echo get_option('line_official_account');?>">
            <img src="<?php echo get_option('line_official_qr_code');?>">
            </a>
            <p>並請在聊天室中, 輸入六位數字:</p>
            <h3><?php echo get_option('_one_time_password');?></h3>
            <p>完成註冊/登入作業</p>
        </div>
        <?php
    }    
}

