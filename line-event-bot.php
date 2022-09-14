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


?>
