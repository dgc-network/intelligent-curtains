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
/*
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
*/
function register_session() {
    if ( ! session_id() ) {
        session_start();
    }
}
add_action( 'init', 'register_session' );

function remove_admin_bar() {
    // Check if the current user is an administrator or editor
    if (!current_user_can('administrator') && !current_user_can('editor') && !is_admin()) {
        show_admin_bar(false);
    }
}
add_action('after_setup_theme', 'remove_admin_bar');
/*
function remove_admin_bar() {
    if (!current_user_can('administrator') && !is_admin()) {
      show_admin_bar(false);
    }
}
add_action('after_setup_theme', 'remove_admin_bar');  
*/  
function enqueue_scripts() {		
    wp_enqueue_script( 'qrcode-js', plugins_url( 'assets/js/jquery.qrcode.min.js' , __FILE__ ), array( 'jquery' ), time() );
    wp_enqueue_script( 'jquery-ui-js', 'https://code.jquery.com/ui/1.13.2/jquery-ui.js' );
    wp_enqueue_script( 'jquery-ui-datepicker' );
    wp_enqueue_script( 'jquery-ui-dialog' );

    wp_enqueue_style( 'custom-options-view', plugins_url( 'assets/css/custom-options-view.css' , __FILE__ ), '', time() );
    wp_enqueue_style( 'chat-css', plugins_url( 'assets/css/chat.css' , __FILE__ ), '', time() );
    wp_enqueue_style( 'jquery-ui-css', 'https://code.jquery.com/ui/1.13.2/themes/smoothness/jquery-ui.css' );
    wp_enqueue_style( 'demos-style-css', 'https://jqueryui.com/resources/demos/style.css' );

    wp_enqueue_script( 'custom-script', plugins_url( 'assets/js/custom-options-view.js' , __FILE__ ), array( 'jquery' ), time() );
    wp_enqueue_script( 'curtain-orders', plugins_url( 'assets/js/curtain-orders.js' , __FILE__ ), array( 'jquery' ), time() );
    wp_enqueue_script( 'curtain-misc', plugins_url( 'assets/js/curtain-misc.js' , __FILE__ ), array( 'jquery' ), time() );
    wp_localize_script( 'custom-script', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ), ) );
    wp_localize_script( 'curtain-orders', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ), ) );
    wp_localize_script( 'curtain-misc', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ), ) );
}
add_action( 'wp_enqueue_scripts', 'enqueue_scripts' );

require_once plugin_dir_path( __FILE__ ) . 'services/default-settings.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-curtain-orders.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-curtain-categories.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-curtain-models.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-curtain-specifications.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-curtain-agents.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-order-status.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-login-users.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-curtain-serials.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-product-items.php';

require_once plugin_dir_path( __FILE__ ) . 'includes/general-helps.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-service-links.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-curtain-service.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-curtain-remotes.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-system-status.php';

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
                        $result = get_keyword_matched($message['text']);
                        if ($result) {
                            $text_message = 'You have not logged in yet. Please click the button below to go to the Login/Registration system.';
                            $text_message = '您尚未登入系統！請點擊下方按鍵登入或註冊本系統。';
                            // Encode the Chinese characters for inclusion in the URL
                            $link_uri = home_url().'/service/?_id='.$line_user_id.'&_name='.urlencode($display_name);
                            $flexMessage = set_flex_message($display_name, $link_uri, $text_message);
                            $line_bot_api->replyMessage([
                                'replyToken' => $event['replyToken'],
                                'messages' => [$flexMessage],
                            ]);

                        } else {
                            // Open-AI auto reply
                            $response = $open_ai_api->createChatCompletion($message['text']);
                            //$response = $open_ai_api->generate_openai_proposal($message['text']);
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

function get_keyword_matched($keyword) {
    // Check if $keyword is contained within '我要註冊登入登錄'
    if (strpos($keyword, '註冊') !== false) return true;
    if (strpos($keyword, '登入') !== false) return true;
    if (strpos($keyword, '登錄') !== false) return true;
    if (strpos($keyword, 'login') !== false) return true;
    if (strpos($keyword, 'Login') !== false) return true;
        
    return false;
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
        }
        $current_user = get_userdata( $user_id );
        ?>
        <div class="ui-widget">
            <h2>User registration/login</h2>
            <fieldset>
                <label for="display-name">Name:</label>
                <input type="text" id="display-name" value="<?php echo esc_attr($_GET['_name']);?>" class="text ui-widget-content ui-corner-all" />
                <label for="user-email">Email:</label>
                <input type="text" id="user-email" value="<?php echo esc_attr($current_user->user_email);?>" class="text ui-widget-content ui-corner-all" />
                <input type="hidden" id="log" value="<?php echo esc_attr($_GET['_id']);?>" />
                <input type="hidden" id="pwd" value="<?php echo esc_attr($_GET['_id']);?>" />
                <hr>
                <input type="submit" id="wp-login-submit" class="button button-primary" value="Submit" />
            </fieldset>
        </div>
        <?php        
    } else {

        ?><script>window.location.replace("https://aihome.tw/wp-login.php");</script><?php
        //wp_redirect( $current_url );
        //exit();                

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

