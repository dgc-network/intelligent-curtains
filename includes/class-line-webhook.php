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
            $this->create_tables();
        }

        public function create_rich_menu( $_content=array() ) {
            $client = new LINEBotTiny();
            $rick_menu_id = $client->createRichMenu([
                "size" => [
                    "width" => 2500,
                    "height" => 1686    
                ],
                "selected" => false,
                "name" => "richmenu-a",
                "chatBarText" => $_contents["chat_bar_text"],
                "areas" => [
                    [
                        "bounds" => [
                            "x" => 0,
                            "y" => 0,
                            "width" => 1250,
                            "height" => 1686    
                        ],
                        "action" => [
                            "type" => "uri",
                            "uri" => "https://developers.line.biz/"    
                        ]
                    ],
                    [
                        "bounds" => [
                            "x" => 1251,
                            "y" => 0,
                            "width" => 1250,
                            "height" => 1686    
                        ],
                        "action" => [
                            "type" => "richmenuswitch",
                            "richMenuAliasId" => "richmenu-alias-b",
                            "data" => "richmenu-changed-to-b"
                        ]
                    ]
                ]
            ]);

            $image_path = '/path/to/image.jpeg';
            $client->uploadImageToRichMenu($rick_menu_id, $image_path);
        }

        public function push_imagemap_messages( $_contents=array() ) {
            $client = new LINEBotTiny();
            $client->pushMessage([
                'to' => $_contents['line_user_id'],
                'messages' => [
                    [
                        "type" => "imagemap",
                        "baseUrl" => $_contents["base_url"],
                        "altText" => $_contents["alt_text"],
                        "baseSize" => [
                            "width" => 1040,
                            "height" => 1040,
                        ],
                        "actions" => [
                            [
                                "type" => "uri",
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

        public function push_flex_messages( $_contents=array() ) {
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
            $curtain_agents = new curtain_agents();
            $client = new LINEBotTiny();
            $open_ai = new open_ai();

            foreach ((array)$client->parseEvents() as $event) {

                $profile = $client->getProfile($event['source']['userId']);

                $data=array();
                $data['line_user_id']=$profile['userId'];
                $data['display_name']=$profile['displayName'];
                $curtain_users->insert_curtain_user($data);

                switch ($event['type']) {
                    case 'message':
                        $message = $event['message'];
                        switch ($message['type']) {
                            case 'text':
                                $six_digit_random_number = $message['text'];
                                if( strlen( $six_digit_random_number ) == 6 ) {
                                    $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}serial_number WHERE one_time_password = %s", $six_digit_random_number ), OBJECT );            
                                    if (!(is_null($row) || !empty($wpdb->last_error))) {
                                        //** continue the process if the 6 digit number is correct, register the qr code */
                                        $user = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}curtain_users WHERE line_user_id = %s", $profile['userId'] ), OBJECT );            
                                        if (!(is_null($user) || !empty($wpdb->last_error))) {
                                            $data=array();
                                            $data['curtain_user_id']=$user->curtain_user_id;
                                            $where=array();
                                            $where['one_time_password']=$six_digit_random_number;
                                            $serial_number->update_serial_number($data, $where);
                                            
                                            $body_messages = array();
                                            $body_messages[] = 'Hi, '.$profile['displayName'];
                                            $body_messages[] = 'QR Code 已經完成註冊';
                                            $body_messages[] = '請點擊連結進入售後服務區';

                                            $_contents = array();
                                            $_contents['line_user_id'] = $profile['userId'];
                                            $_contents['base_url'] = $curtain_service->get_link('User registry');
                                            $_contents['alt_text'] = 'Hi, '.$profile['displayName'].'QR Code 已經完成註冊'.'請點擊連結進入售後服務區';
                                            $_contents['link_uri'] = get_site_url().'/'.$curtain_service->get_link('Service').'/?_id='.$profile['userId'];
                                            $_contents['body_messages'] = $body_messages;
                                            $this->push_imagemap_messages( $_contents );
                                        }
                                    } else {
                                        //** continue the process if the 6 digit number is incorrect */
                                        $body_messages = array();
                                        $body_messages[] = 'Hi, '.$profile['displayName'];
                                        $body_messages[] = '您輸入的六位數字'.$message['text'].'有錯誤';
                                        $body_messages[] = '請重新輸入正確數字已完成 QR Code 註冊';

                                        $_contents = array();
                                        $_contents['line_user_id'] = $profile['userId'];
                                        $_contents['base_url'] = $curtain_service->get_link('Registry error');
                                        $_contents['alt_text'] = 'Hi, '.$profile['displayName'].'您輸入的六位數字'.$message['text'].'有誤'.'請重新輸入正確數字已完成 QR Code 註冊';
                                        $_contents['link_uri'] = get_site_url().'/'.$curtain_service->get_link('Service').'/?_id='.$profile['userId'].'&serial_no=';
                                        $_contents['body_messages'] = $body_messages;
                                        $this->push_imagemap_messages( $_contents );

                                    }
                                } else {
                                    //** if the message is not the six digit message */
                                    $agent = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}curtain_agents WHERE agent_number = %s", $message['text'] ), OBJECT );            
                                    if (is_null($agent) || !empty($wpdb->last_error)) {
                                        //** send message to line_bot */
                                        $data=array();
                                        $data['chat_from']=$profile['userId'];
                                        $data['chat_to']='line_bot';
                                        $data['chat_message']=$message['text'];
                                        $this->insert_chat_message($data);

                                        $param=array();
                                        $param["model"]="text-davinci-003";
                                        $param["prompt"]=$message['text'];
                                        $param["max_tokens"]=100;
                                        $param["temperature"]=0;
                                        $param["top_p"]=1;
                                        $param["n"]=1;
                                        $param["stream"]=false;
                                        $param["logprobs"]=null;
                                        $param["stop"]="\n";
                                        $response = $open_ai->createCompletion($param);
                                                                
                                        $results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}user_permissions WHERE service_option_id = %d", $curtain_service->get_id('Messages') ), OBJECT );            
                                        foreach ( $results as $index=>$result ) {
                                            $hero_messages = array();
                                            $hero_messages[] = $profile['displayName'];
                                            $body_messages = array();
                                            //$body_messages[] = $message['text'];
                                            $body_messages[] = $response;
                                            $_contents = array();
                                            $_contents['line_user_id'] = $result->line_user_id;
                                            $_contents['link_uri'] = get_site_url().'/'.$curtain_service->get_link('Users').'/?_id='.$result->line_user_id;
                                            $_contents['hero_messages'] = $hero_messages;
                                            $_contents['body_messages'] = $body_messages;
                                            $this->push_flex_messages( $_contents );
                                        }
                                    } else {
                                        //** Agent registration */
                                        $data=array();
                                        $data['curtain_agent_id']=$curtain_agents->get_id($message['text']);
                                        $where=array();
                                        $where['line_user_id']=$profile['userId'];
                                        $curtain_users->update_curtain_users($data, $where);

                                        $_contents = array();
                                        $_contents['line_user_id'] = $profile['userId'];
                                        $_contents['base_url'] = $curtain_service->get_link('Agent registry');
                                        $_contents['alt_text'] = 'Hi, '.$profile['displayName'].', 您已經完成經銷商註冊, 請點擊連結進入訂貨服務區';
                                        $_contents['link_uri'] = get_site_url().'/'.$curtain_service->get_link('Orders').'/?_id='.$profile['userId'];
                                        $this->push_imagemap_messages( $_contents );
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

        public function insert_chat_message($data=[]) {
            global $wpdb;
            $table = $wpdb->prefix.'chat_messages';
            $data['create_timestamp'] = time();
            $wpdb->insert($table, $data);
            return $wpdb->insert_id;
        }

        public function create_tables() {
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
        }        
    }
}
?>