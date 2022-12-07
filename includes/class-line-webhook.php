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

        function line_rich_menu( $rich_menu_content='' ) {
            $client = new LINEBotTiny();
            $client->richMenu($rich_menu_content);
        }

        function push_imagemap_messages( $_contents=array() ) {
            $client = new LINEBotTiny();
            $client->pushMessage([
                'to' => $_contents['line_user_id'],
                'messages' => [
                    [
                        "type" => "imagemap",
                        //"baseUrl" => "https://example.com/bot/images/rm001",
                        //"baseUrl" => "https://disabused-shop.000webhostapp.com/images/image002",
                        //"altText" => "this is an imagemap",
                        //"altText" => $_contents['body_messages'][0],
                        "baseUrl" => $_contents["base_url"],
                        "altText" => $_contents["alt_text"],
                        "baseSize" => [
                            "width" => 1040,
                            "height" => 1040,
                        ],
                        "actions" => [
                            [
                                "type" => "uri",
                                //"linkUri" => "https://photos.app.goo.gl/o7kmoFQ2ApzDnw7f6",
                                "linkUri" => $_contents["link_uri"],
                                "area" => [
                                    "x" => 0,
                                    "y" => 0,
                                    "width" => 1040,
                                    "height" => 1040
                                ]
                            ],
                        ],
                    ]
                ]
            ]);
        }

        function push_flex_messages( $_contents=array() ) {
            $hero_contents = array();
            foreach ( $_contents['hero_messages'] as $hero_message ) {
                $hero_content = array();
                $hero_content['type'] = 'text';
                $hero_content['text'] = $hero_message;
                $hero_content['margin'] = '20px';
                $hero_content['action']['type'] = 'uri';
                $hero_content['action']['label'] = 'action';
                $hero_content['action']['uri'] = $_contents['link_uri'];
                $hero_contents[] = $hero_content;
            }
            $body_contents = array();
            foreach ( $_contents['body_messages'] as $body_message ) {
                $body_content = array();
                $body_content['type'] = 'text';
                $body_content['text'] = $body_message;
                $body_content['wrap'] = true;
                $body_content['action']['type'] = 'uri';
                $body_content['action']['label'] = 'action';
                $body_content['action']['uri'] = $_contents['link_uri'];
                $body_contents[] = $body_content;
            }

            $client = new LINEBotTiny();
            $client->pushMessage([
                'to' => $_contents['line_user_id'],
                'messages' => [
                    [
                        "type" => "flex",
                        //"altText" => "this is a flex message",
                        "altText" => $_contents['body_messages'][0],
                        "contents" => [
                            "type" => "bubble",
                            "hero" => [
                                "type" => "box",
                                "layout" => "horizontal",
                                "backgroundColor" => "#00b900",
                                "contents" => $hero_contents
                            ],
                            "body" => [
                                "type" => "box",
                                "layout" => "vertical",
                                "contents" => $body_contents
                            ]
                        ]    
                    ]
                ]
            ]);
        }

        public function init() {
            global $wpdb;
            $serial_number = new serial_number();
            $curtain_service = new curtain_service();
            $curtain_users = new curtain_users();
            $client = new LINEBotTiny();

            foreach ((array)$client->parseEvents() as $event) {
                //self::insert_event_log($event);

                $profile = $client->getProfile($event['source']['userId']);
                $line_user_id = $profile['userId'];
                $display_name = $profile['displayName'];

                $data=array();
                $data['line_user_id']=$profile['userId'];
                $data['display_name']=$profile['displayName'];
                $return_id = $curtain_users->insert_curtain_user($data);
/*        
                $user = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}curtain_users WHERE line_user_id = %s", $line_user_id ), OBJECT );            
                if (is_null($row) || !empty($wpdb->last_error)) {
                    $data=array();
                    $data['line_user_id']=$profile['userId'];
                    $data['display_name']=$profile['displayName'];
                    $return_id = $curtain_users->insert_curtain_user($data);
                }
*/
                switch ($event['type']) {
                    case 'message':
                        $message = $event['message'];
                        switch ($message['type']) {
                            case 'text':
                                $six_digit_random_number = $message['text'];
                                if( strlen( $six_digit_random_number ) == 6 ) {
                                    global $wpdb;
                                    $row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}serial_number WHERE one_time_password = {$six_digit_random_number}", OBJECT );
                                    if (!(is_null($row) || !empty($wpdb->last_error))) {
                                        // continue the process if the 6 digit number is correct, register the qr code
                                        $user = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}curtain_users WHERE line_user_id = %s", $line_user_id ), OBJECT );            
                                        if (!(is_null($user) || !empty($wpdb->last_error))) {
                                            $data=array();
                                            $data['curtain_user_id']=$user->curtain_user_id;
                                            $where=array();
                                            $where['one_time_password']=$six_digit_random_number;
                                            $result = $serial_number->update_serial_number($data, $where);
                                            
                                            $body_messages = array();
                                            $body_messages[] = 'Hi, '.$profile['displayName'];
                                            $body_messages[] = 'QR Code 已經完成註冊';
                                            $body_messages[] = '請點擊連結進入售後服務區:';
                                            $_contents = array();
                                            $_contents['line_user_id'] = $line_user_id;
                                            //$option = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}service_options WHERE service_option_page = %s", '_service_page' ), OBJECT );
                                            //$_contents['link_uri'] = get_site_url().'/'.$option->service_option_link;
                                            $_contents['base_url'] = $curtain_service->get_link('_image003');
                                            $_contents['alt_text'] = 'Hi, '.$profile['displayName'];
                                            $_contents['link_uri'] = get_site_url().'/'.$curtain_service->get_link('_service_page');
                                            $_contents['body_messages'] = $body_messages;
                                            //self::push_flex_messages( $_contents );
                                            self::push_imagemap_messages( $_contents );
                                        }
                                    } else {
                                        // continue the process if the 6 digit number is incorrect
                                        $body_messages = array();
                                        $body_messages[] = 'Hi, '.$profile['displayName'];
                                        $body_messages[] = '您輸入的六位數字'.$message['text'].'有錯誤';
                                        $body_messages[] = '請重新輸入正確數字已完成 QR Code 註冊';
                                        $_contents = array();
                                        $_contents['line_user_id'] = $line_user_id;
                                        //$option = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}service_options WHERE service_option_page = %s", '_service_page' ), OBJECT );
                                        //$_contents['link_uri'] = get_site_url().'/'.$option->service_option_link.'/?serial_no=';
                                        $_contents['base_url'] = $curtain_service->get_link('_image002');
                                        $_contents['alt_text'] = 'Hi, '.$profile['displayName'];
                                        $_contents['link_uri'] = get_site_url().'/'.$curtain_service->get_link('_service_page').'/?serial_no=';
                                        $_contents['body_messages'] = $body_messages;
                                        //self::push_flex_messages( $_contents );
                                        self::push_imagemap_messages( $_contents );
                                    }
                                } else {
                                    //send message to line_bot if the message is not the six digit message 
                                    $data=array();
                                    $data['chat_from']=$line_user_id;
                                    $data['chat_to']='line_bot';
                                    $data['chat_message']=$message['text'];
                                    $result = self::insert_chat_message($data);
                                    
                                    //$option = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}service_options WHERE service_option_page = %s", '_service_page' ), OBJECT );
                                    //$results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}user_permissions WHERE service_option_id = $option->service_option_id", OBJECT );
                                    $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}user_permissions WHERE service_option_id = $curtain_service->get_id('_service_page')", OBJECT );
                                    foreach ( $results as $index=>$result ) {
                                        $hero_messages = array();
                                        $hero_messages[] = $profile['displayName'];
                                        $body_messages = array();
                                        $body_messages[] = $message['text'];
                                        $_contents = array();
                                        //$user = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}curtain_users WHERE curtain_user_id = %d", $result->curtain_user_id ), OBJECT );
                                        //$_contents['line_user_id'] = $user->line_user_id;
                                        $_contents['line_user_id'] = $curtain_users->get_id($result->curtain_user_id);
                                        //$option = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}service_options WHERE service_option_page = %s", '_chat_form' ), OBJECT );
                                        //$_contents['link_uri'] = get_site_url().'/'.$option->service_option_link.'/?_id='.$user->line_user_id;
                                        $_contents['link_uri'] = get_site_url().'/'.$curtain_service->get_link('_chat_form').'/?_id='.$user->line_user_id;
                                        $_contents['hero_messages'] = $hero_messages;
                                        $_contents['body_messages'] = $body_messages;
                                        self::push_flex_messages( $_contents );
                                    }
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