<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('line_webhook')) {
    class line_webhook {
        /**
         * Class constructor
         */
        public function __construct() {
            self::create_tables();
        }

        function push_text_message($text_message='', $line_user_id='') {
            $client = new LINEBotTiny();
            $client->pushMessage([
                'to' => $line_user_id,
                'messages' => [
                    [
                        'type' => 'text',
                        'text' => $text_message
                    ]
                ]
            ]);
        }
/*
        function push_OTP_to($line_user_id='') {
            $six_digit_random_number = random_int(100000, 999999);
            $text_message = 'OTP code : '.$six_digit_random_number;
            self::push_text_message($text_message, $line_user_id);
        }
*/
        public function init() {
            $serial_number = new serial_number();
            $curtain_users = new curtain_users();
            $client = new LINEBotTiny();
            foreach ((array)$client->parseEvents() as $event) {
                //self::insert_event_log($event);

                $profile = $client->getProfile($event['source']['userId']);
                $line_user_id = $profile['userId'];
                $display_name = $profile['displayName'];
            
                global $wpdb;
                $user = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}curtain_users WHERE line_user_id = %s", $line_user_id ), OBJECT );            
                if (is_null($row) || !empty($wpdb->last_error)) {
                    $data=array();
                    $data['line_user_id']=$profile['userId'];
                    $data['display_name']=$profile['displayName'];
                    $return_id = $curtain_users->insert_curtain_user($data);
                }

                switch ($event['type']) {
                    case 'message':
                        $message = $event['message'];
                        switch ($message['type']) {
                            case 'text':
                                $six_digit_random_number = $message['text'];
                                if( strlen( $six_digit_random_number ) == 6 ) {
                                    global $wpdb;
                                    //$row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}serial_number WHERE curtain_user_id = {$six_digit_random_number}", OBJECT );
                                    $row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}serial_number WHERE one_time_password = {$six_digit_random_number}", OBJECT );
                                    //if (count($row) > 0) {
                                    if (!(is_null($row) || !empty($wpdb->last_error))) {
                                        // continue the process if the 6 digit number is correct, register the qr code
                                        $user = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}curtain_users WHERE line_user_id = %s", $line_user_id ), OBJECT );            
                                        //if (count($user) > 0) {
                                        if (!(is_null($user) || !empty($wpdb->last_error))) {
                                            $data=array();
                                            $data['curtain_user_id']=$user->curtain_user_id;
                                            $where=array();
                                            $where['one_time_password']=$six_digit_random_number;
                                            $result = $serial_number->update_serial_number($data, $where);
    
                                            $client->replyMessage([
                                                'replyToken' => $event['replyToken'],
                                                'messages' => [
                                                    [
                                                        'type' => 'text',
                                                        'text' => 'Hi, '.$profile['displayName'],
                                                    ],
                                                    [
                                                        'type' => 'text',
                                                        'text' => 'This QR Code has been registered.',
                                                    ],
                                                    [
                                                        'type' => 'text',
                                                        'text' => '請點擊下方連結進入售後服務區:',
                                                    ],
                                                    [
                                                        'type' => 'text',
                                                        'text' => get_site_url().'/'.get_option('_service_page'),
                                                    ]
                                                ]
                                            ]);
                                        }
                                    } else {
                                        // continue the process if the 6 digit number is incorrect
                                        $client->replyMessage([
                                            'replyToken' => $event['replyToken'],
                                            'messages' => [
                                                [
                                                    'type' => 'text',
                                                    'text' => 'Hi, '.$profile['displayName'],
                                                ],
                                                [
                                                    'type' => 'text',
                                                    'text' => 'message '.$message['text'].' is wrong.',
                                                ],
                                                [
                                                    'type' => 'text',
                                                    'text' => '請點擊下方連結進入售後服務區:',
                                                ],
                                                [
                                                    'type' => 'text',
                                                    'text' => get_site_url().'/'.get_option('_service_page'),
                                                ]
                                            ]
                                        ]);    
                                    }
                                } else {
                                    //send message to line_bot if the message is not six digit 
                                    $data=array();
                                    $data['chat_from']=$line_user_id;
                                    $data['chat_to']='line_bot';
                                    $data['chat_message']=$message['text'];
                                    $result = self::insert_chat_message($data);
                                    $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}curtain_users WHERE user_role = 'admin'", OBJECT );
                                    foreach ( $results as $index=>$result ) {
                                        $text_message = '['.$display_name.']:'.$message['text'];
                                        $text_message = '[{
                                            "type": "bubble",
                                            "body": {
                                              "type": "box",
                                              "layout": "vertical",
                                              "contents": [
                                                {
                                                  "type": "image",
                                                  "url": "https://scdn.line-apps.com/n/channel_devcenter/img/flexsnapshot/clip/clip3.jpg",
                                                  "size": "full",
                                                  "aspectMode": "cover",
                                                  "aspectRatio": "1:1",
                                                  "gravity": "center"
                                                },
                                                {
                                                  "type": "box",
                                                  "layout": "vertical",
                                                  "contents": [],
                                                  "position": "absolute",
                                                  "background": {
                                                    "type": "linearGradient",
                                                    "angle": "0deg",
                                                    "endColor": "#00000000",
                                                    "startColor": "#00000099"
                                                  },
                                                  "width": "100%",
                                                  "height": "40%",
                                                  "offsetBottom": "0px",
                                                  "offsetStart": "0px",
                                                  "offsetEnd": "0px"
                                                },
                                                {
                                                  "type": "box",
                                                  "layout": "horizontal",
                                                  "contents": [
                                                    {
                                                      "type": "box",
                                                      "layout": "vertical",
                                                      "contents": [
                                                        {
                                                          "type": "box",
                                                          "layout": "horizontal",
                                                          "contents": [
                                                            {
                                                              "type": "text",
                                                              "text": "Brown Grand Hotel",
                                                              "size": "xl",
                                                              "color": "#ffffff"
                                                            }
                                                          ]
                                                        },
                                                        {
                                                          "type": "box",
                                                          "layout": "baseline",
                                                          "contents": [
                                                            {
                                                              "type": "icon",
                                                              "url": "https://scdn.line-apps.com/n/channel_devcenter/img/fx/review_gold_star_28.png"
                                                            },
                                                            {
                                                              "type": "icon",
                                                              "url": "https://scdn.line-apps.com/n/channel_devcenter/img/fx/review_gold_star_28.png"
                                                            },
                                                            {
                                                              "type": "icon",
                                                              "url": "https://scdn.line-apps.com/n/channel_devcenter/img/fx/review_gold_star_28.png"
                                                            },
                                                            {
                                                              "type": "icon",
                                                              "url": "https://scdn.line-apps.com/n/channel_devcenter/img/fx/review_gold_star_28.png"
                                                            },
                                                            {
                                                              "type": "icon",
                                                              "url": "https://scdn.line-apps.com/n/channel_devcenter/img/fx/review_gray_star_28.png"
                                                            },
                                                            {
                                                              "type": "text",
                                                              "text": "4.0",
                                                              "color": "#a9a9a9"
                                                            }
                                                          ],
                                                          "spacing": "xs"
                                                        },
                                                        {
                                                          "type": "box",
                                                          "layout": "horizontal",
                                                          "contents": [
                                                            {
                                                              "type": "box",
                                                              "layout": "baseline",
                                                              "contents": [
                                                                {
                                                                  "type": "text",
                                                                  "text": "¥62,000",
                                                                  "color": "#ffffff",
                                                                  "size": "md",
                                                                  "flex": 0,
                                                                  "align": "end"
                                                                },
                                                                {
                                                                  "type": "text",
                                                                  "text": "¥82,000",
                                                                  "color": "#a9a9a9",
                                                                  "decoration": "line-through",
                                                                  "size": "sm",
                                                                  "align": "end"
                                                                }
                                                              ],
                                                              "flex": 0,
                                                              "spacing": "lg"
                                                            }
                                                          ]
                                                        }
                                                      ],
                                                      "spacing": "xs"
                                                    }
                                                  ],
                                                  "position": "absolute",
                                                  "offsetBottom": "0px",
                                                  "offsetStart": "0px",
                                                  "offsetEnd": "0px",
                                                  "paddingAll": "20px"
                                                }
                                              ],
                                              "paddingAll": "0px"
                                            }
                                          }]';                                        
                                        self::push_text_message($text_message, $result->line_user_id);
                                    }
                                }
                                break;
                            default:
                                //send notification to administrators
                                error_log('Unsupported message type: ' . $message['type']);
                                break;
                        }
                        break;
                    default:
                        error_log('Unsupported event type: ' . $event['type']);
                        break;
                }    
            }            
        }

        function insert_event_log($event) {

            switch ($event['type']) {
                case 'message':
                    $event_object = $event['message'];
                    break;
                case 'unsend':
                    $event_object = $event['unsend'];
                    break;
                case 'memberJoined':
                    $event_object = $event['joined'];
                    break;
                case 'memberLeft':
                    $event_object = $event['left'];
                    break;
                case 'postback':
                    $event_object = $event['postback'];
                    break;
                case 'videoPlayComplete':
                    $event_object = $event['videoPlayComplete'];
                    break;
                case 'beacon':
                    $event_object = $event['beacon'];
                    break;
                case 'accountLink':
                    $event_object = $event['link'];
                    break;
                case 'things':
                    $event_object = $event['things'];
                    break;
            }

            switch ($event['source']['type']) {
                case 'user':
                    $source_type = $event['source']['type'];
                    $user_id = $event['source']['userId'];
                    $group_id = $event['source']['userId'];
                    break;
                case 'group':
                    $source_type = $event['source']['type'];
                    $user_id = $event['source']['userId'];
                    $group_id = $event['source']['groupId'];
                    break;
                case 'room':
                    $source_type = $event['source']['type'];
                    $user_id = $event['source']['userId'];
                    $group_id = $event['source']['roomId'];
                    break;
            }

            global $wpdb;
            $table = $wpdb->prefix.'eventLogs';
            $data = array(
                'event_type' => $event['type'],
                'event_timestamp' => time(),
                'source_type' => $source_type,
                'source_user_id' => $user_id,
                'source_group_id' => $group_id,
                'event_replyToken' => $event['replyToken'],
                'event_object' => json_encode($event_object),
            );
            $insert_id = $wpdb->insert($table, $data);        
        }
    
        public function insert_chat_message($data=[]) {
            global $wpdb;
            $table = $wpdb->prefix.'chat_messages';
            $data['create_timestamp'] = time();
            $wpdb->insert($table, $data);
            return $wpdb->insert_id;
        }

        function create_tables() {
            global $wpdb;
            $charset_collate = $wpdb->get_charset_collate();
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

            $sql = "CREATE TABLE {$wpdb->prefix}chat_messages (
                message_id int NOT NULL AUTO_INCREMENT,
                chat_from varchar(255) NOT NULL DEFAULT '',
                chat_to varchar(255) NOT NULL DEFAULT '',
                chat_message TEXT NOT NULL,
                create_timestamp int(10),
                PRIMARY KEY (message_id)
            ) $charset_collate;";
            dbDelta($sql);
        
            $sql = "CREATE TABLE `{$wpdb->prefix}eventLogs` (
                event_id int NOT NULL AUTO_INCREMENT,
                event_type varchar(20),
                event_timestamp int(10),
                source_type varchar(10),
                source_user_id varchar(50),
                source_group_id varchar(50),
                event_replyToken varchar(50),
                event_object varchar(1000),
                PRIMARY KEY  (event_id)
            ) $charset_collate;";
            dbDelta($sql);
        }        
    }
}
?>