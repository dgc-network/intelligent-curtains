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
define('MY_PLUGIN_DIR', trailingslashit( plugin_dir_url( __FILE__ )));

//require_once MY_PLUGIN_DIR . '/includes/class-options-loader.php';
//require_once MY_PLUGIN_DIR . '/line-bot-sdk-tiny/LINEBotTiny.php';
//require_once MY_PLUGIN_DIR . '/includes/class-line-webhook.php';
//require_once MY_PLUGIN_DIR . '/includes/class-curtain-service.php';
//require_once MY_PLUGIN_DIR . '/includes/class-curtain-agents.php';
require_once MY_PLUGIN_DIR . '/includes/class-curtain-models.php';
//require_once MY_PLUGIN_DIR . '/includes/class-curtain-users.php';
//require_once MY_PLUGIN_DIR . '/includes/class-serial-number.php';
//require_once MY_PLUGIN_DIR . '/chat/chat.php';
//require_once MY_PLUGIN_DIR . '/chat/samplea.php';
add_option('_service_page', 'service');
add_option('_line_account', 'https://line.me/ti/p/@490tjxdt');

//$line_webhook = new line_webhook();
//$line_webhook->init();

add_action( 'wp_enqueue_scripts', 'my_plugin_assets' );
function my_plugin_assets() {
    wp_enqueue_script("jquery");
    wp_enqueue_script( 'custom-js', 'custom-options-view.js', array( 'jquery' ), time() );
    wp_enqueue_script( 'qrcode-js', 'jquery.qrcode.min.js', array( 'jquery' ), time() );
    //wp_enqueue_script( 'popup-js',  'popupwindow.min.js', array( 'jquery' ), time() );
    //wp_enqueue_script( 'chat-js',  plugins_url( '/chat/js/chat.js' , __FILE__ ), array( 'jquery' ), time() );
    //wp_enqueue_script( 'jquery-js',  plugins_url( '/chat/js/jquery.js' , __FILE__ ), array(), time() );
    
    wp_enqueue_style( 'custom-css', 'custom-options-view.css', '', time() );
    //wp_enqueue_style( 'popup-css', 'popupwindow.min.css', '', time() );
    //wp_enqueue_style( 'chat-css', MY_PLUGIN_DIR . '/chat/css/chat.css', '', time() );
    //wp_enqueue_style( 'screen-css', MY_PLUGIN_DIR . '/chat/css/screen.css', '', time() );
    //wp_head();
}

add_shortcode('chat','chat_sample_a');
add_shortcode('test','test_mode');
function test_mode(){

    $output = '<div>';
    if( isset($_POST['_serial_no']) ) {
        //$output .= '<div id="basic-demo" class="example_content"><div id="qrcode"><div id="qrcode_content">';
        $output .= '<div id="qrcode"><div id="qrcode_content">';
        $output .= get_site_url().'/'.get_option('_service_page').'/?serial_no='.$_POST['_serial_no'];
        $output .= '</div></div>';
    }
    $output .= '<form method="post">';
    $output .= '<input type="submit" value="123456789" name="_serial_no">';
    $output .= '</form>';
    $output .= '</div>';

    return $output;    
}



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
