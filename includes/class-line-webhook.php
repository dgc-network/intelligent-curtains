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
            //add_shortcode('event-list', __CLASS__ . '::list_mode');
        }

        public function init() {
            $client = line_bot_sdk();
            foreach ($client->parseEvents() as $event) {

                $profile = $client->getProfile($event['source']['userId']);
                $line_user_id = $profile['userId'];
            
                switch ($event['type']) {
                    case 'message':
                        $message = $event['message'];
                        switch ($message['type']) {
                            case 'text':
                                // start my codes from here
                                $six_digit_random_number = $message['text'];
                                if( strlen( $six_digit_random_number ) == 6 ) {
                                    global $wpdb;
                                    $row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}serial_number WHERE curtain_user_id = {$six_digit_random_number}", OBJECT );
                                    if (count($row) > 0) {
                                        $otp_service = new otp_service();
                                        $return_id = 0;
                                        $user = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}curtain_users WHERE line_user_id = {$line_user_id}", OBJECT );
                                        if (count($user) > 0) {
                                            $return_id = $user->curtain_user_id;
                                        } else {
                                            $data=array();
                                            $data['line_user_id']=$profile['userId'];
                                            $data['display_name']=$profile['displayName'];                
                                            $data['last_otp']=$six_digit_random_number;        
                                            $return_id = $otp_service->insert_curtain_users($data);
                                        }
                                        
                                        $data=array();
                                        $data['curtain_user_id']=$return_id;
                                        $where=array();
                                        $where['curtain_user_id']=$six_digit_random_number;
                                        $result = self::update_serial_number($data, $where);
/*
                                        global $wpdb;
                                        $table = $wpdb->prefix.'serial_number';
                                        $data = array(
                                            'curtain_user_id' => intval($return_id),
                                            'update_timestamp' => time(),
                                        );
                                        $where = array('curtain_user_id' => $six_digit_random_number);
                                        $wpdb->update($table, $data, $where);
*/                        
                                        $client->replyMessage([
                                            'replyToken' => $event['replyToken'],
                                            'messages' => [
                                                [
                                                    'type' => 'text',
                                                    'text' => 'Hi, '.$profile['displayName'],
                                                ],
                                                [
                                                    'type' => 'text',
                                                    'text' => '恭喜您完成註冊手續',
                                                ]
                                            ]
                                        ]);
                                    }
                                } else {
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
            }            
        }
    }
}
?>