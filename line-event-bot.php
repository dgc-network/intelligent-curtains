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
include_once dirname( __FILE__ ) . '/includes/class-eventLogs.php';

$channelAccessToken = '';
$channelSecret = '';
if (file_exists(dirname( __FILE__ ) . '/line-bot-sdk-tiny/config.ini')) {
    $config = parse_ini_file(dirname( __FILE__ ) . "/line-bot-sdk-tiny/config.ini", true);
//if (file_exists(__DIR__ . '/line-bot-sdk-tiny/config.ini')) {
    //$config = parse_ini_file(__DIR__ . "/line-bot-sdk-tiny/config.ini", true);
    if ($config['Channel']['Token'] == null || $config['Channel']['Secret'] == null) {
        error_log("config.ini 配置檔未設定完全！", 0);
    } else {
        $channelAccessToken = $config['Channel']['Token'];
        $channelSecret = $config['Channel']['Secret'];
    }
}
$client = new LINEBotTiny($channelAccessToken, $channelSecret);

$eventLog = new eventLogs();

foreach ($client->parseEvents() as $event) {
    $eventLog->insertEvent($event);
    $getsource = $event['source'];
    $usr_id = $getsource['userId'];

    switch ($event['type']) {
        case 'message':
            $message = $event['message'];
            switch ($message['type']) {
                case 'text':
                    // start my codes from here
                    $eventLog->insertTextMessage($event);
                    $response = $client->getProfile($event['source']['userId']);

                    $client->replyMessage([
                        'replyToken' => $event['replyToken'],
                        'messages' => [
                            [
                                'type' => 'text',
                                //'text' => $usr_id.':'.$message['text'],
                                'text' => $response['displayName'].':'.$message['text'],
                                //'text' => $message['text']
                            ]
                        ]
                    ]);
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
};
?>
