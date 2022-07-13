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

$channelAccessToken = 'ongg0SgvMZjDQlO3qHvSvGBU/JyMlz2GBiRi9t7iUBHXqZIZAioD9Im7gJ6MYLBA/Aq5BupS6HEd6U/cxDKvstGnUWPfHcQ9OEpQ3QGK44BUzAkp7s3CXP0G4h2C0/o1UO7xpmiI3RelAJhTWK8khQdB04t89/1O/w1cDnyilFU=';
$channelSecret = '1bd1c2ac3b3a36399de32f5a83f135c0';

$client = new LINEBotTiny($channelAccessToken, $channelSecret);

foreach ($client->parseEvents() as $event) {
    $getsource = $event['source'];
    $usr_id = $getsource['userId'];

    global $wpdb;
    $table = $wpdb->prefix.'eventLogs';
    $data = array(
        'event_type' => $event['type'],
        'event_timestamp' => $event['timestamp'],
        'event_source' => json_encode($event['source']),
        'event_replyToken' => $event['replyToken'],
        'event_mode' => $event['mode'],
        'webhookEventId' => $event['webhookEventId'],
        'isRedelivery' => $event['deliveryContext']['isRedelivery'],
        //'event_object' => $event['message'],
    );
    //$format = array('%s', '%d', '%s', '%s');
    //$insert_id = $wpdb->insert($table, $data, $format);
    $insert_id = $wpdb->insert($table, $data);

    switch ($event['type']) {
        case 'message':
            $message = $event['message'];
            switch ($message['type']) {
                case 'text':
                    // start my codes from here

                    $client->replyMessage([
                        'replyToken' => $event['replyToken'],
                        'messages' => [
                            [
                                'type' => 'text',
                                'text' => $usr_id.':'.$message['text']
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
