<?php

/**
 * Copyright 2016 LINE Corporation
 *
 * LINE Corporation licenses this file to you under the Apache License,
 * version 2.0 (the "License"); you may not use this file except in compliance
 * with the License. You may obtain a copy of the License at:
 *
 *   https://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations
 * under the License.
 */

//require_once('./LINEBotTiny.php');
//include_once dirname( __FILE__ ) . '/LINEBotTiny.php';

$channelAccessToken = 'ongg0SgvMZjDQlO3qHvSvGBU/JyMlz2GBiRi9t7iUBHXqZIZAioD9Im7gJ6MYLBA/Aq5BupS6HEd6U/cxDKvstGnUWPfHcQ9OEpQ3QGK44BUzAkp7s3CXP0G4h2C0/o1UO7xpmiI3RelAJhTWK8khQdB04t89/1O/w1cDnyilFU=';
$channelSecret = '1bd1c2ac3b3a36399de32f5a83f135c0';

$client = new LINEBotTiny($channelAccessToken, $channelSecret);

foreach ($client->parseEvents() as $event) {
    $getsource = $event['source'];
    $usr_id = $getsource['userId'];
/*
    $response = $bot->getProfile('<userId>');
    if ($response->isSucceeded()) {
        $profile = $response->getJSONDecodedBody();
        echo $profile['displayName'];
        echo $profile['pictureUrl'];
        echo $profile['statusMessage'];
    }
*/
/*    
    $response = wp_remote_get( 'https://api.line.me/v2/bot/profile/'.$usr_id, array(
        'header'    => array(
            'Authentication'    => 'Bearer '.$channelAccessToken
        )
    ));
*/
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
