<?php

/**
 * Plugin Name: electric-curtains
 * Plugin URI: https://wordpress.org/plugins/electric-curtains/
 * Description: The leading web api plugin for pig system by shortcode
 * Author: dgc.network
 * Author URI: https://dgc.network/
 * Version: 1.0.0
 * Requires at least: 6.0
 * Tested up to: 6.0.2
 * 
 * Text Domain: electric-curtains
 * Domain Path: /languages/
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

include_once dirname( __FILE__ ) . '/line-bot-sdk-tiny/LINEBotTiny.php';
//include_once dirname( __FILE__ ) . '/includes/class-event-bot.php';
include_once dirname( __FILE__ ) . '/includes/class-line-webhook.php';
include_once dirname( __FILE__ ) . '/includes/class-otp-service.php';

$line_webhook = new line_webhook();
$line_webhook->init();

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
/*
add_action( 'init', 'frontporch_enqueue_scripts' );
function frontporch_enqueue_scripts() {
    if (!is_admin() ) {
        wp_enqueue_script( 'jquery' );
        wp_register_script( 'google-jquery-ui', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.12/jquery-ui.min.js', array( 'jquery' ) );
        wp_register_script( 'jquery-template', get_bloginfo('template_directory').'/js/jquery.template.js',array('jquery'),version_cache(), true);
        wp_register_style( 'jquery-style', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.1/themes/smoothness/jquery-ui.css', true);
        wp_register_style( 'template-style', 'http://www.frontporchdeals.com/wordpress/wp-includes/js/jqueryui/css/ui-lightness/jquery-ui-1.8.12.custom.css', true); 
        wp_enqueue_style( 'jquery-style' );
        wp_enqueue_style( ' jquery-template' );
        wp_enqueue_script( 'google-jquery-ui' );
        wp_enqueue_script( 'jquery-template' );
    }       
}
*/
?>
