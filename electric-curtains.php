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
include_once dirname( __FILE__ ) . '/includes/class-options-loader.php';

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

define('temp_file', ABSPATH.'/_temp_out.txt' );

add_action("activated_plugin", "activation_handler1");
function activation_handler1(){
    $cont = ob_get_contents();
    if(!empty($cont)) file_put_contents(temp_file, $cont );
}

add_action( "pre_current_active_plugins", "pre_output1" );
function pre_output1($action){
    if(is_admin() && file_exists(temp_file))
    {
        $cont= file_get_contents(temp_file);
        if(!empty($cont))
        {
            echo '<div class="error"> Error Message:' . $cont . '</div>';
            @unlink(temp_file);
        }
    }
}
?>
