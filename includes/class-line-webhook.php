<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}
if (!class_exists('line_webhook')) {

    class line_webhook {

        /**
         * Class constructor
         */
        public function __construct() {
            //require_once MY_PLUGIN_DIR . 'line-bot-sdk-tiny/LINEBotTiny.php';
            //add_shortcode('event-list', __CLASS__ . '::list_mode');
            self::create_tables();
        }

        public function init() {

            $client = new LINEBotTiny();
            foreach ((array)$client->parseEvents() as $event) {
                //self::insert_event_log($event);

                $profile = $client->getProfile($event['source']['userId']);
                $line_user_id = $profile['userId'];
            
                switch ($event['type']) {
                    case 'message':
                        $message = $event['message'];
                        switch ($message['type']) {
                            case 'text':
                                $six_digit_random_number = $message['text'];
                                if( strlen( $six_digit_random_number ) == 6 ) {
                                    global $wpdb;
                                    $row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}serial_number WHERE curtain_user_id = {$six_digit_random_number}", OBJECT );
                                    if (count($row) > 0) {
                                        // continue the process if the 6 digit number is correct
                                        $serial_number = new serial_number();
                                        $curtain_users = new curtain_users();
                                        $return_id = 0;
                                        $user = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}curtain_users WHERE line_user_id = %s", $line_user_id ), OBJECT );            
                                        if (count($user) > 0) {
                                            // The user has registered recently
                                            $return_id = $user->curtain_user_id;
                                        } else {
                                            $data=array();
                                            $data['line_user_id']=$profile['userId'];
                                            $data['display_name']=$profile['displayName'];                
                                            $data['last_otp']=$six_digit_random_number;        
                                            $return_id = $curtain_users->insert_curtain_user($data);
                                        }
                                        
                                        $data=array();
                                        $data['curtain_user_id']=$return_id;
                                        $where=array();
                                        $where['curtain_user_id']=$six_digit_random_number;
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
                                                    'text' => '請點擊下方連結進入售後服務區:',
                                                ],
                                                [
                                                    'type' => 'text',
                                                    'text' => get_site_url().'/'.get_option('_service_page'),
                                                ]
                                            ]
                                        ]);
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
                                                ]
                                            ]
                                        ]);    
/*
                                        $user = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}curtain_users WHERE line_user_id = %s", $line_user_id ), OBJECT );            
                                        if (count($user) > 0) {
                                            $client->replyMessage([
                                                'replyToken' => $event['replyToken'],
                                                'messages' => [
                                                    [
                                                        'type' => 'text',
                                                        'text' => 'Hi, '.$profile['displayName'],
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
                                        } else {
                                            // return message for wrong 6 digit number
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
                                                    ]
                                                ]
                                            ]);    
                                        }
*/                                        
                                    }
                                } else {
                                    //send message to line_bot if the message is not six digit 
                                    $data=array();
                                    $data['from']=$line_user_id;
                                    $data['to']='line_bot';
                                    $data['message']=$message;
                                    $result = self::insert_chat_message($data);
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
            $data = array(
                'chat_from' => $data['from'],
                'chat_to' => $data['to'],
                'chat_message' => $data['message'],
                'create_timestamp' => time(),
            );
            $wpdb->insert($table, $data);
            return $wpdb->insert_id;
        }

        public function update_chat_messages($data=[], $where=[]) {
            global $wpdb;
            $table = $wpdb->prefix.'chat_messages';
            //$data['update_timestamp'] = time();
            $wpdb->update($table, $data, $where);
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