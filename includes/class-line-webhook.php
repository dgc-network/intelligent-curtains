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
            $service_options = new service_options();
            $service_options->create_page('Service', '[curtain-service]');            
        }

        public function curtain_service() {
            global $wpdb;
            $service_options = new service_options();
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
                    $results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}user_permissions WHERE line_user_id = %s", $_SESSION['line_user_id'] ), OBJECT );
                    $output .= '<div class="wp-block-buttons">';
                    foreach ( $results as $index=>$result ) {
                        if ($service_options->get_category($result->service_option_id)=='admin') {
                            $output .= '<div class="wp-block-button" style="margin: 10px;">';
                            $output .= '<a class="wp-block-button__link" href="'.$service_options->get_link($result->service_option_id).'">'.$service_options->get_name($result->service_option_id).'</a>';
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
                        //$_SESSION['line_user_id'] = $user->line_user_id;
                    }
                    $output .= '感謝您選購我們的電動窗簾<br>';
                    $model = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}curtain_models WHERE curtain_model_id = {$row->curtain_model_id}", OBJECT );
                    if (!(is_null($model) || !empty($wpdb->last_error))) {
                        $output .= '型號:'.$model->curtain_model_name.' 規格: '.$row->specification.'<br>';
                    }
                    $six_digit_random_number = random_int(100000, 999999);
                    $output .= '請利用手機<i class="fa-solid fa-mobile-screen"></i>按'.'<a href="'.get_option('_line_account').'">這裡</a>, 加入我們的Line官方帳號,<br>';
                    //$output .= '使用電腦<i class="fa-solid fa-desktop"></i>上的Line, 在我們的官方帳號聊天室中輸入六位數字密碼,<br>';
                    $output .= '在我們的官方帳號聊天室中輸入六位數字密碼,<br>'.'<span style="font-size:24px;color:blue;">'.$six_digit_random_number;
                    $output .= '</span>'.'完成註冊程序<br>';

                    //$output .= '請利用手機按<br>'.'<a href="'.get_option('_line_account').'">';
                    //$output .= '<img src="https://scdn.line-apps.com/n/line_add_friends/btn/zh-Hant.png" alt="加入好友" height="16px" border="0"></a>';
                    //$output .= '<br>在我們的Line官方帳號聊天室中輸入六位數字密碼: <span style="font-size:24px;color:blue;">'.$six_digit_random_number.'</span>';
                    //$output .= ' 完成註冊程序<br>';
                    $data=array();
                    $data['one_time_password']=$six_digit_random_number;
                    $where=array();
                    $where['qr_code_serial_no']=$qr_code_serial_no;
                    $result = $serial_number->update_serial_number($data, $where);    
                }
    
            } else {

                //$where='"%view%"';
                //$results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}service_options WHERE service_option_category LIKE {$where}", OBJECT );
                $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}service_links", OBJECT );
                $output .= '<div class="wp-block-buttons">';
                foreach ( $results as $index=>$result ) {
                    $output .= '<div class="wp-block-button" style="margin: 10px;">';
                    $output .= '<a class="wp-block-button__link" href="'.$result->service_option_link.'">'.$result->service_option_title.'</a>';
                    $output .= '</div>';
                }
                $output .= '</div>';
            }
            $output .= '</div>';
            return $output;
        }

        public function create_rich_menu( $_content=array() ) {
            $client = new line_bot_api();
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
            $client = new line_bot_api();
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

            $client = new line_bot_api();
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

        public function init_webhook() {
            global $wpdb;
            $serial_number = new serial_number();
            $service_options = new service_options();
            $curtain_users = new curtain_users();
            $curtain_agents = new curtain_agents();
            $client = new line_bot_api();
            $open_ai = new open_ai();
            $business_central = new business_central();

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
                                            $_contents['base_url'] = $service_options->get_link('User registry');
                                            $_contents['alt_text'] = 'Hi, '.$profile['displayName'].'QR Code 已經完成註冊'.'請點擊連結進入售後服務區';
                                            $_contents['link_uri'] = get_site_url().'/'.$service_options->get_link('Service').'/?_id='.$profile['userId'];
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
                                        $_contents['base_url'] = $service_options->get_link('Registry error');
                                        $_contents['alt_text'] = 'Hi, '.$profile['displayName'].'您輸入的六位數字'.$message['text'].'有誤'.'請重新輸入正確數字已完成 QR Code 註冊';
                                        $_contents['link_uri'] = get_site_url().'/'.$service_options->get_link('Service').'/?_id='.$profile['userId'].'&serial_no=';
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

                                        $results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}user_permissions WHERE service_option_id = %d", $service_options->get_id('Messages') ), OBJECT );            
                                        foreach ( $results as $index=>$result ) {
                                            $hero_messages = array();
                                            $hero_messages[] = $profile['displayName'];
                                            $body_messages = array();
                                            $body_messages[] = $message['text'];
                                            $_contents = array();
                                            $_contents['line_user_id'] = $result->line_user_id;
                                            $_contents['link_uri'] = get_site_url().'/'.$service_options->get_link('Users').'/?_id='.$result->line_user_id;
                                            $_contents['hero_messages'] = $hero_messages;
                                            $_contents['body_messages'] = $body_messages;
                                            $this->push_flex_messages( $_contents );
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
                                                                
                                        $client->pushMessage([
                                            'to' => $_contents['line_user_id'],
                                            'messages' => [
                                                [
                                                    'type' => 'text',
                                                    //'text' => $response['text']
                                                    //'text' => $response
                                                    'text' => $string
                                                ]                                                                    
                                            ]
                                        ]);

                                        //** send auto-reply message to line_bot */
                                        $data=array();
                                        $data['chat_from']='line_bot';
                                        $data['chat_to']=$profile['userId'];
                                        $data['chat_message']=$string;
                                        $this->insert_chat_message($data);

                                        $results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}user_permissions WHERE service_option_id = %d", $service_options->get_id('Messages') ), OBJECT );            
                                        foreach ( $results as $index=>$result ) {
                                            $hero_messages = array();
                                            //$hero_messages[] = $profile['displayName'];
                                            $hero_messages[] = 'Aihome';
                                            $body_messages = array();
                                            //$body_messages[] = $message['text'];
                                            $body_messages[] = $string;
                                            $_contents = array();
                                            $_contents['line_user_id'] = $result->line_user_id;
                                            $_contents['link_uri'] = get_site_url().'/'.$service_options->get_link('Users').'/?_id='.$result->line_user_id;
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
                                        $_contents['base_url'] = $service_options->get_link('Agent registry');
                                        $_contents['alt_text'] = 'Hi, '.$profile['displayName'].', 您已經完成經銷商註冊, 請點擊連結進入訂貨服務區';
                                        $_contents['link_uri'] = get_site_url().'/'.$service_options->get_link('Orders').'/?_id='.$profile['userId'];
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
    $my_class = new line_webhook();
    add_shortcode( 'curtain-service', array( $my_class, 'curtain_service' ) );
}
?>