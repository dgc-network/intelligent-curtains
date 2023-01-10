<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('curtain_service')) {
    class curtain_service {
        private $_wp_page_title;
        private $_wp_page_postid;
        /**
         * Class constructor
         */
        public function __construct() {
            $this->_wp_page_title = 'Service';
            $this->_wp_page_postid = get_page_by_title($this->_wp_page_title)->ID;
            $wp_pages = new wp_pages();
            $wp_pages->create_page($this->_wp_page_title, '[curtain-service]', 'system');
            add_shortcode( 'curtain-service', array( $this, 'curtain_service' ) );
            $this->create_tables();
        }

        public function curtain_service() {
            global $wpdb;
            $wp_pages = new wp_pages();
            $serial_number = new serial_number();

            if( isset($_GET['_id']) ) {
                $_SESSION['line_user_id'] = $_GET['_id'];
            }

            $output = '<div style="text-align:center;">';
            if( isset($_GET['serial_no']) ) {
                $qr_code_serial_no = $_GET['serial_no'];
                $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}serial_number WHERE qr_code_serial_no = %s", $qr_code_serial_no ), OBJECT );            
                if (is_null($row) || !empty($wpdb->last_error)) {
                    /** incorrect QR-code then display the admin link */
                    $output .= '<div style="font-weight:700; font-size:xx-large;">售後服務管理系統</div>';
                    $results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}user_permissions WHERE line_user_id = %s", $_SESSION['line_user_id'] ), OBJECT );
                    $output .= '<div class="wp-block-buttons">';
                    foreach ( $results as $index=>$result ) {
                        if ($wp_pages->get_category($result->wp_page_postid)=='admin') {
                            $output .= '<div class="wp-block-button" style="margin: 10px;">';
                            $output .= '<a class="wp-block-button__link" href="'.get_permalink($result->wp_page_postid).'">'.get_the_title($result->wp_page_postid).'</a>';
                            $output .= '</div>';    
                        }
                    }
                    $output .= '</div>';                    

                } else {
                    /** registration for QR-code */
                    $curtain_user_id=$row->curtain_user_id;
                    $user = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}curtain_users WHERE curtain_user_id = %d", $row->curtain_user_id ), OBJECT );            
                    if (!(is_null($user) || !empty($wpdb->last_error))) {
                        $output .= 'Hi, '.$user->display_name.'<br>';
                    }
                    $output .= '感謝您選購我們的電動窗簾<br>';
                    $model = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}curtain_models WHERE curtain_model_id = {$row->curtain_model_id}", OBJECT );
                    if (!(is_null($model) || !empty($wpdb->last_error))) {
                        $output .= '型號:'.$model->curtain_model_name.' 規格: '.$row->specification.'<br>';
                    }
                    $six_digit_random_number = random_int(100000, 999999);
                    $output .= '請利用手機<i class="fa-solid fa-mobile-screen"></i>按'.'<a href="'.get_option('_line_account').'">這裡</a>, 加入我們的Line官方帳號,<br>';
                    $output .= '在我們的官方帳號聊天室中輸入六位數字密碼,<br>'.'<span style="font-size:24px;color:blue;">'.$six_digit_random_number;
                    $output .= '</span>'.'完成註冊程序<br>';

                    $result = $serial_number->update_serial_number(
                        array('one_time_password'=>$six_digit_random_number),
                        array('qr_code_serial_no'=>$qr_code_serial_no)
                    );
                }
    
            } else {

                $output .= '<div style="font-weight:700; font-size:xxx-large;">售後服務/使用說明</div>';
                $output .= '<div style="font-weight:700; font-size:xx-large; color:firebrick;">簡單三步驟，開啟Siri語音控制窗簾。</div>';
                $output .= '<div class="wp-block-buttons">';
                $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}service_links WHERE service_link_category='view'", OBJECT );
                foreach ( $results as $index=>$result ) {
                    $output .= '<div class="wp-block-button" style="margin: 10px;">';
                    $output .= '<a class="wp-block-button__link" href="'.$result->service_link_uri.'">'.$result->service_link_title.'</a>';
                    $output .= '</div>';
                }
                $output .= '</div>';
            }
            $output .= '</div>';
            return $output;
        }

        public function create_rich_menu( $_content=array() ) {
            $line_bot_api = new line_bot_api();
            $rick_menu_id = $line_bot_api->createRichMenu([
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
            $line_bot_api->uploadImageToRichMenu($rick_menu_id, $image_path);
        }

        public function push_imagemap_messages( $_contents=array() ) {
            $line_bot_api = new line_bot_api();
            $line_bot_api->pushMessage([
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
            $header_contents = $this->header_contents($_contents);
            $header_contents = $this->box_contents($_contents['header_messages'], $_contents['link_uri']);
/*            
            $header_contents = array();
            if ( is_array($_contents['header_messages']) ) {
                foreach ( $_contents['header_messages'] as $header_message ) {
                    if ( is_array($header_message) ) {
                        $header_contents[] = $header_message;
                    } else {
                        $header_contents[] = $this->text_content($header_message,$_contents['link_uri']);
                    }
                }    
            } else {
                $header_contents[] = $this->text_content($_contents['header_messages'],$_contents['link_uri']);
            }
*/
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
/*
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
*/
            $body_contents = array();
            if ( is_array($_contents['body_messages']) ) {
                foreach ( $_contents['body_messages'] as $body_message ) {
                    $body_contents[] = $this->text_content($body_message,$_contents['link_uri']);
                }    
            } else {
                $body_contents[] = $this->text_content($_contents['body_messages'],$_contents['link_uri']);
            }

            $line_bot_api = new line_bot_api();
            $line_bot_api->pushMessage([
                'to' => $_contents['line_user_id'],
                'messages' => [
                    [
                        "type" => "flex",
                        //"altText" => "this is a flex message",
                        "altText" => $_contents['body_messages'][0],
                        "contents" => [
                            "type" => "bubble",
                            "header" => $header_contents,
/*                            
                            "header" => [
                                "type" => "box",
                                "layout" => "vertical",
                                "contents" => $header_contents
                            ],
*/
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

        public function bubble_contents( $_bubble_contents=array() ) {
            if ($_bubble_contents!=array()) {
                $_bubble_contents['type'] = 'bubble';
                $_bubble_contents['contents'] = $_bubble_contents;
            }
            return $_bubble_contents;
        }

        public function text_content( $_text_message, $_link_uri ) {
            return array(
                'type' => 'text',
                'text' => $_text_message,
                'wrap' => true,
                'action' => array(
                    'type' => 'uri',
                    'label' => 'action',
                    'uri' => $_link_uri
                )
            );
        }

        public function header_contents( $_contents=array() ) {
            $header_contents = array();
            if ( is_array($_contents['header_messages']) ) {
                foreach ( $_contents['header_messages'] as $header_message ) {
                    if ( is_array($header_message) ) {
                        $header_contents[] = $header_message;
                    } else {
                        $header_contents[] = $this->text_content($header_message,$_contents['link_uri']);
                    }
                }    
            } else {
                $header_contents[] = $this->text_content($_contents['header_messages'],$_contents['link_uri']);
            }
            return $header_contents;
        }

        public function box_contents( $_box_contents=array(), $_link_uri ) {
            $_contents = array();

            if ($_box_contents!=array()) {

                $header_contents = array();
                if ( is_array($_box_contents) ) {
                    foreach ( $_box_contents as $header_message ) {
                        if ( is_array($header_message) ) {
                            $header_contents[] = $header_message;
                        } else {
                            $header_contents[] = $this->text_content($header_message,$_link_uri);
                        }
                    }    
                } else {
                    $header_contents[] = $this->text_content($_box_contents,$_link_uri);
                }
                $_content['type'] = 'box';
                $_content['layout'] = 'vertical';
                $_content['contents'] = $header_contents;
            }
            
            return $_contents;
        }

        public function push_bubble_messages( $_contents=array() ) {
            $line_bot_api = new line_bot_api();
            $line_bot_api->pushMessage([
                'to' => $_contents['line_user_id'],
                'messages' => [
                    [
                        "type" => "flex",
                        "altText" => "this is a flex message",
                        //"altText" => $this->box_contents($_contents['body_messages'])[0],
                        "contents" => [
                            "type"  => "bubble",
                            "header"=> $this->box_contents($_contents['header_messages'],$_contents['link_uri']),
                            "hero"  => $this->box_contents($_contents['hero_messages'],$_contents['link_uri']),
                            "body"  => $this->box_contents($_contents['body_messages'],$_contents['link_uri']),
                            "footer"=> $this->box_contents($_contents['footer_messages'],$_contents['link_uri']),
                        ]    
                    ]
                ]
            ]);
        }

        public function push_carousel_messages( $_contents=array() ) {
            $line_bot_api = new line_bot_api();
            $line_bot_api->pushMessage([
                'to' => $_contents['line_user_id'],
                'messages' => [
                    [
                        "type" => "flex",
                        "altText" => $_contents['body_messages'][0],
                        "contents" => [
                            "type" => "carousel",
                            "contents"=>$this->bubble_contents($_contents['bubble_contents']),
                        ]    
                    ]
                ]
            ]);
        }

        public function init_webhook() {
            global $wpdb;
            $serial_number = new serial_number();
            $wp_pages = new wp_pages();
            $service_links = new service_links();
            $curtain_users = new curtain_users();
            $curtain_agents = new curtain_agents();
            $line_bot_api = new line_bot_api();
            $open_ai = new open_ai();
            $business_central = new business_central();

            foreach ((array)$line_bot_api->parseEvents() as $event) {

                $profile = $line_bot_api->getProfile($event['source']['userId']);

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

                                            $serial_number->update_serial_number(
                                                array('curtain_user_id'=>$user->curtain_user_id),
                                                array('one_time_password'=>$six_digit_random_number)
                                            );
                                            
                                            $body_messages = array();
                                            $body_messages[] = 'Hi, '.$profile['displayName'];
                                            $body_messages[] = 'QR Code 已經完成註冊';
                                            $body_messages[] = '請點擊連結進入售後服務區';

                                            $this->push_imagemap_messages(
                                                array(
                                                    'line_user_id' => $profile['userId'],
                                                    'base_url' => $service_links->get_link('user_registry'),
                                                    'alt_text' => 'Hi, '.$profile['displayName'].'QR Code 已經完成註冊'.'請點擊連結進入售後服務區',
                                                    'link_uri' => get_permalink(get_page_by_title('Service')).'/?_id='.$profile['userId'],
                                                    'body_messages' => $body_messages
                                                )
                                            );
                                        }
                                    } else {
                                        //** continue the process if the 6 digit number is incorrect */
                                        $body_messages = array();
                                        $body_messages[] = 'Hi, '.$profile['displayName'];
                                        $body_messages[] = '您輸入的六位數字'.$message['text'].'有錯誤';
                                        $body_messages[] = '請重新輸入正確數字已完成 QR Code 註冊';

                                        $this->push_imagemap_messages(
                                            array(
                                                'line_user_id' => $profile['userId'],
                                                'base_url' => $service_links->get_link('registry_error'),
                                                'alt_text' => 'Hi, '.$profile['displayName'].'您輸入的六位數字'.$message['text'].'有誤'.'請重新輸入正確數字已完成 QR Code 註冊',
                                                'link_uri' => get_permalink(get_page_by_title('Service')).'/?_id='.$profile['userId'].'&serial_no=',
                                                'body_messages' => $body_messages
                                            )
                                        );

                                    }
                                } else {
                                    //** if the message is not the six digit message */
                                    $agent = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}curtain_agents WHERE agent_number = %s", $message['text'] ), OBJECT );            
                                    if (is_null($agent) || !empty($wpdb->last_error)) {
                                        //** send message to line_bot */
                                        $this->insert_chat_message(
                                            array(
                                                'chat_from' =>$profile['userId'],
                                                'chat_to'   =>'line_bot',
                                                'chat_message'=>$message['text']
                                            )
                                        );

                                        $results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}curtain_users WHERE is_admin = %d", 1 ), OBJECT );
                                        foreach ( $results as $index=>$result ) {
                                            $header_messages = array();
                                            $header_messages[] = $profile['displayName'];
                                            //$hero_messages = array();
                                            //$hero_messages[] = $profile['displayName'];
                                            $body_messages = array();
                                            $body_messages[] = $message['text'];
                                            //$this->push_flex_messages(
                                            $this->push_bubble_messages(
                                                array(
                                                    'line_user_id' => $result->line_user_id,
                                                    'link_uri' => get_permalink(get_page_by_title('Users')).'/?_id='.$result->line_user_id,
                                                    'header_messages' => $hero_messages,
                                                    //'hero_messages' => $hero_messages,
                                                    'body_messages' => $body_messages
                                                )
                                            );
                                        }

                                        //** Open-AI auto reply */
                                        $param=array();
                                        $param["model"]="text-davinci-003";
                                        $param["prompt"]=$message['text'];
                                        $param["max_tokens"]=300;
                                        //$param["temperature"]=0;
                                        //$param["top_p"]=1;
                                        //$param["n"]=1;
                                        //$param["stream"]=false;
                                        //$param["logprobs"]=null;
                                        //$param["stop"]="\n";
                                        $response = $open_ai->createCompletion($param);
                                        $string = preg_replace("/\n\r|\r\n|\n|\r/", '', $response['text']);
                                        //$response = $business_central->getItems();
                                                                
                                        $line_bot_api->pushMessage([
                                            'to' => $_contents['line_user_id'],
                                            'messages' => [
                                                [
                                                    'type' => 'text',
                                                    //'text' => $response
                                                    'text' => $string
                                                ]                                                                    
                                            ]
                                        ]);

                                        //** send auto-reply message to line_bot */
                                        $this->insert_chat_message(
                                            array(
                                                'chat_from' =>'line_bot',
                                                'chat_to'   =>$profile['userId'],
                                                'chat_message'=>$string
                                            )
                                        );

                                        $results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}curtain_users WHERE is_admin = %d", 1 ), OBJECT );
                                        foreach ( $results as $index=>$result ) {
                                            $header_messages = array();
                                            $header_messages[] = 'Aihome';
                                            //$hero_messages = array();
                                            //$hero_messages[] = 'Aihome';
                                            $body_messages = array();
                                            $body_messages[] = $string;
                                            //$this->push_flex_messages(
                                            $this->push_bubble_messages(
                                                array(
                                                    'line_user_id' => $result->line_user_id,
                                                    'link_uri' => get_permalink(get_page_by_title('Users')).'/?_id='.$result->line_user_id,
                                                    'header_messages' => $header_messages,
                                                    //'hero_messages' => $hero_messages,
                                                    'body_messages' => $body_messages
                                                )
                                            );
                                        }

                                    } else {
                                        //** Agent registration */
                                        $curtain_users->update_curtain_users(
                                            array('curtain_agent_id'=>$curtain_agents->get_id($message['text'])),
                                            array('line_user_id'=>$profile['userId'])
                                        );

                                        $this->push_imagemap_messages(
                                            array(
                                                'line_user_id' => $profile['userId'],
                                                'base_url' => $service_links->get_link('agent_registry'),
                                                'alt_text' => 'Hi, '.$profile['displayName'].', 您已經完成經銷商註冊, 請點擊連結進入訂貨服務區',
                                                'link_uri' => get_permalink(get_page_by_title('Orders')).'/?_id='.$profile['userId']
                                                )
                                        );
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
    $my_class = new curtain_service();
}
?>