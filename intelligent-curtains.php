<?php
//update_option( 'home', 'https://aihome.tw' );
//update_option( 'siteurl', 'https://aihome.tw' );
/**
 * Plugin Name: intelligent-curtains
 * Plugin URI: https://wordpress.org/plugins/intelligent-curtains/
 * Description: The leading web api plugin for pig system by shortcode
 * Author: dgc.network
 * Author URI: https://dgc.network/
 * Version: 1.0.0
 * Requires at least: 6.0
 * Tested up to: 6.0.2
 * 
 * Text Domain: intelligent-curtains
 * Domain Path: /languages/
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define('MY_PLUGIN_DIR', plugin_dir_path( __FILE__ ));
//require_once MY_PLUGIN_DIR . 'includes/class-options-loader.php';
//require_once MY_PLUGIN_DIR . 'line-bot-sdk-tiny/LINEBotTiny.php';
require_once MY_PLUGIN_DIR . 'includes/class-line-webhook.php';
//require_once MY_PLUGIN_DIR . 'includes/class-curtain-service.php';
//require_once MY_PLUGIN_DIR . 'includes/class-curtain-agents.php';
//require_once MY_PLUGIN_DIR . 'includes/class-curtain-models.php';
//require_once MY_PLUGIN_DIR . 'includes/class-curtain-users.php';
//require_once MY_PLUGIN_DIR . 'includes/class-serial-number.php';
add_option('_service_page', 'service');
add_option('_line_account', 'https://line.me/ti/p/@490tjxdt');
add_option('_chat_from', 'line-bot');

/*
function init_session() {
    if ( ! session_id() ) {
        session_start();
    }
}
add_action( 'init', 'init_session' );
*/
/*
function wpb_cookies_tutorial2() { 
    // Time of user's visit
    $visit_time = date('F j, Y g:i a');
     
    // Check if cookie is already set
    if(isset($_SESSION['wpb_visit_time'])) {
     
        // Do this if cookie is set 
        function visitor_greeting() {
     
            // Use information stored in the cookie 
            $lastvisit = $_SESSION['wpb_visit_time'];
     
            $string .= 'You last visited our website '. $lastvisit .'. Check out whats new'; 
     
            // Delete the old cookie so that we can set it again with updated time
            unset($_SESSION['wpb_visit_time']); 
     
            return $string;
        }   
     
    } else { 

        // Do this if the cookie doesn't exist
        function visitor_greeting() { 
            $string .= 'New here? Check out these resources...' ;
            return $string;
        }   
    }
    add_shortcode('greet_me', 'visitor_greeting');
     
    // Set or Reset the cookie
    setcookie('wpb_visit_time',  $visit_time, time()+31556926);
} 
add_action('init', 'wpb_cookies_tutorial2');
*/
$line_webhook = new line_webhook();
//$line_webhook->init();
/*
function line_bot_sdk() {
    $channelAccessToken = '';
    $channelSecret = '';
    if (file_exists(dirname( __FILE__ ) . '/line-bot-sdk-tiny/config.ini')) {
        $config = parse_ini_file(dirname( __FILE__ ) . '/line-bot-sdk-tiny/config.ini', true);
        if ($config['Channel']['Token'] == null || $config['Channel']['Secret'] == null) {
            error_log("config.ini uncompleted!", 0);
        } else {
            $channelAccessToken = $config['Channel']['Token'];
            $channelSecret = $config['Channel']['Secret'];
        }
    }
    $client = new LINEBotTiny($channelAccessToken, $channelSecret);
    return $client;
}
*/
?>
