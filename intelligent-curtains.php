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
require_once MY_PLUGIN_DIR . 'includes/class-options-loader.php';
require_once MY_PLUGIN_DIR . 'line-bot-sdk-tiny/LINEBotTiny.php';
require_once MY_PLUGIN_DIR . 'includes/class-line-webhook.php';
require_once MY_PLUGIN_DIR . 'includes/class-curtain-service.php';
require_once MY_PLUGIN_DIR . 'includes/class-curtain-agents.php';
require_once MY_PLUGIN_DIR . 'includes/class-curtain-models.php';
require_once MY_PLUGIN_DIR . 'includes/class-curtain-users.php';
require_once MY_PLUGIN_DIR . 'includes/class-serial-number.php';
add_option('_service_page', 'service');
add_option('_line_account', 'https://line.me/ti/p/@490tjxdt');
add_option('_chat_from', 'line-bot');

$line_webhook = new line_webhook();
$line_webhook->init();

?>
