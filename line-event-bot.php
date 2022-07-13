<?php

/**
 * Plugin Name: line-event-bot
 * Plugin URI: https://wordpress.org/plugins/line-event-bot/
 * Description: The leading web api plugin for pig system by shortcode
 * Author: dgc.network
 * Author URI: https://dgc.network/
 * Version: 1.0.0
 * Requires at least: 4.4
 * Tested up to: 5.2
 * 
 * Text Domain: line-event-bot
 * Domain Path: /languages/
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

include_once dirname( __FILE__ ) . '/line-bot-sdk-tiny/LINEBotTiny.php';

?>
