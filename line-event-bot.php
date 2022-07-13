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
        'event_source' => $event['source'],
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

/*
{
    "destination": "xxxxxxxxxx",
    "events": [
      {
        "type": "message",
        "message": {
          "type": "text",
          "id": "14353798921116",
          "text": "Hello, world"
        },
        "timestamp": 1625665242211,
        "source": {
          "type": "user",
          "userId": "U80696558e1aa831..."
        },
        "replyToken": "757913772c4646b784d4b7ce46d12671",
        "mode": "active",
        "webhookEventId": "01FZ74A0TDDPYRVKNK77XKC3ZR",
        "deliveryContext": {
          "isRedelivery": false
        }
      },
      {
        "type": "follow",
        "timestamp": 1625665242214,
        "source": {
          "type": "user",
          "userId": "Ufc729a925b3abef..."
        },
        "replyToken": "bb173f4d9cf64aed9d408ab4e36339ad",
        "mode": "active",
        "webhookEventId": "01FZ74ASS536FW97EX38NKCZQK",
        "deliveryContext": {
          "isRedelivery": false
        }
      },
      {
        "type": "unfollow",
        "timestamp": 1625665242215,
        "source": {
          "type": "user",
          "userId": "Ubbd4f124aee5113..."
        },
        "mode": "active",
        "webhookEventId": "01FZ74B5Y0F4TNKA5SCAVKPEDM",
        "deliveryContext": {
          "isRedelivery": false
        }
      }
    ]
  }
*/  
?>
