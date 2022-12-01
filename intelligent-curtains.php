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

function register_session() {
    if ( ! session_id() ) {
        session_start();
    }
}
add_action( 'init', 'register_session' );

define('MY_PLUGIN_DIR', plugin_dir_path( __FILE__ ));
//require_once MY_PLUGIN_DIR . 'includes/class-options-loader.php';
require_once MY_PLUGIN_DIR . 'line-bot-sdk-tiny/LINEBotTiny.php';
require_once MY_PLUGIN_DIR . 'includes/class-line-webhook.php';
require_once MY_PLUGIN_DIR . 'includes/class-curtain-service.php';
require_once MY_PLUGIN_DIR . 'includes/class-curtain-agents.php';
require_once MY_PLUGIN_DIR . 'includes/class-curtain-orders.php';
require_once MY_PLUGIN_DIR . 'includes/class-curtain-models.php';
require_once MY_PLUGIN_DIR . 'includes/class-curtain-users.php';
require_once MY_PLUGIN_DIR . 'includes/class-serial-number.php';
add_option('_service_page', 'service');
add_option('_users_page', 'users');
add_option('_line_account', 'https://line.me/ti/p/@490tjxdt');
add_option('_chat_from', 'line-bot');
/*
$line_webhook = new line_webhook();
$line_webhook->init();
*/
function enqueue_scripts() {
		
    wp_enqueue_script( 'custom-options-view', plugin_dir_url( __DIR__ ) . 'assets/js/custom-options-view.js', array( 'jquery' ), time(), true );
    wp_enqueue_script( 'qrcode-js', plugin_dir_url( __DIR__ ) . 'assets/js/jquery.qrcode.min.js', array( 'jquery' ), time(), true );
    wp_enqueue_script( 'jquery-ui-js', 'https://code.jquery.com/ui/1.13.2/jquery-ui.js' );
    wp_enqueue_script( 'jquery-ui-datepicker' );
    wp_enqueue_script( 'jquery-ui-dialog' );

    wp_enqueue_style( 'custom-options-view', plugin_dir_url( __DIR__ ) . 'assets/css/custom-options-view.css', '', time() );
    wp_enqueue_style( 'chat-css', plugin_dir_url( __DIR__ ) . 'assets/css/chat.css', '', time() );
    wp_enqueue_style( 'jquery-ui-css', 'https://code.jquery.com/ui/1.13.2/themes/smoothness/jquery-ui.css' );
    wp_enqueue_style( 'demos-style-css', 'https://jqueryui.com/resources/demos/style.css' );
/*
    // You need styling for the datepicker. For simplicity I've linked to the jQuery UI CSS on a CDN.
    wp_register_script( 'jquery-ui-js', 'https://code.jquery.com/ui/1.13.2/jquery-ui.js' );
    wp_register_style( 'jquery-ui-css', 'https://code.jquery.com/ui/1.13.2/themes/smoothness/jquery-ui.css' );
    wp_register_style( 'demos-style-css', 'https://jqueryui.com/resources/demos/style.css' );
    wp_enqueue_script( 'jquery-ui-js' );
    wp_enqueue_style( 'jquery-ui-css' );  
    wp_enqueue_style( 'demos-style-css' );
*/    
}
//add_action( 'wp_enqueue_scripts', 'enqueue_scripts' );

?>
