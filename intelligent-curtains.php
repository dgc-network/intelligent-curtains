<?php

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
add_option('_my_plugin_dir', plugin_dir_path( __FILE__ ));
include_once MY_PLUGIN_DIR . '/line-bot-sdk-tiny/LINEBotTiny.php';
include_once dirname( __FILE__ ) . '/includes/class-options-loader.php';
include_once dirname( __FILE__ ) . '/includes/class-line-webhook.php';
include_once dirname( __FILE__ ) . '/includes/class-curtain-service.php';
include_once dirname( __FILE__ ) . '/includes/class-curtain-agents.php';
include_once dirname( __FILE__ ) . '/includes/class-curtain-models.php';
include_once dirname( __FILE__ ) . '/includes/class-curtain-users.php';
include_once dirname( __FILE__ ) . '/includes/class-serial-number.php';
add_option('_service_page', 'service');
add_option('_line_account', 'https://line.me/ti/p/@490tjxdt');

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
*/
?>
