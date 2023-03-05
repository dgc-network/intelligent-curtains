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
            $this->_wp_page_postid = general_helps::create_page($this->_wp_page_title, 'curtain-service', 'system');
            add_shortcode( 'curtain-service', array( $this, 'curtain_service' ) );
            $this->create_tables();
        }

        public function agent_registry_notice($line_user_id) {
            global $wpdb;
            $system_status = new system_status();
            $wp_pages = new wp_pages();
            $json_templates = new json_templates();
            $results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}curtain_users WHERE is_admin = %d", 1 ), OBJECT );
            //foreach ( $results as $index=>$result ) {

                $template = $json_templates->get_json('Restaurant');

                $see_more = $json_templates->get_json('See_More');
                $see_more = wp_unslash($see_more);
                $see_more = json_decode($see_more, true);

                //$link_uri = get_permalink(get_page_by_title('Orders'));
                $link_uri = get_option('Orders');
                $contents = $json_templates->get_json('Apparel');
                $contents = wp_unslash($contents);
                $contents = json_decode($contents, true);

                //$contents["contents"][0]["body"]["contents"][0]["url"] = 'http://aihome.tw/wp-content/uploads/2022/10/客廳5-1.png';
                $contents["contents"][0]["body"]["contents"][0]["url"] = 'https://lh3.googleusercontent.com/m3y0WMGmo6HbkBO_GrgNyVE1jgkQwu1r5qdWV1hoq5dMy8S82j7TLW6CPQlx83xYX0AFzW1A6cIkrV7AL8iyUDaBC-OrFMXjSdScM5HnF4Jxs8NA5IYBIjcNGC2N8GnmnY9nTD-XhRimNbwjrWdtZlBwHzAx8IvuYhWhllglb3ato0JqNT-lMd6Am8N1fcHlNQw7qJp0hIteqmkryy-PzZ9jDV5_hMH51Ck5I-u5v_cqFryEPi6glCpWek7REHOYBoKnaIH7KfJ5zrwy2otAlLuL9pbkL73nDW2O1cIPniUoy_Fzq3i1ve4LW4xWsz0DL_uQuZ5hm2UqyQM-RrvTJSlkPmuBGxuqRiPG99zihgFav6Oo6osO9oASXUU85WjNOX9B4PjeLLh6KFfAaxED3KpnfNge52fd1sQbefIcZo7qORrTTi7Ng1Cloly_9xm31y1dIo9oVYLUoO7iA3G7s1vrtmSmWF6SLF7KcKnflW6NvgdY5iNp9JKMvt2rpGUGzl6d_mmB3xQrUtWsvdz8ml4x3RzkwvaIhdUtTBcYm3RhfF1M-rOAEdLKy3JpUBkBDMFZJPB8Q46T9e0Uv5e_6bao_sxK-PGI1MRUw3UejLTVHFQS38sSqDjVeULazPLIVXfSzupSppQFH4qgqfUBbbpo1-8X1VAmIsMkSAj6oGo2XrPLg7hatQlbMh8X2hKR9BTz2vqh3nj8wqV0RAqseYtrXfb8xpKlu4lNWvNBpcTFQwEwDfNTaMLgR96MjXfdqZ6RuvrnGK8I0aWGxeC8jUsdTsw0lPAq4HVQkFoH2DtuBWPsETqm-tYaFqb93zL4-ofwyAWBnduClxbI_zvuJnhEEIoECtkuvnoWTcKfZOmyyP9aHxjOTqKAZkZdVDYvKw1sX1A2KfytsRi05attA_jdKJ3POAIXJwvvr6X2Dk82dg=w1064-h1418-no?authuser=0';
                $contents["contents"][0]["body"]["contents"][1]["contents"][0]["contents"][0]["text"] = 'Agent Registry';
                $contents["contents"][0]["body"]["contents"][1]["contents"][1]["contents"][0]["text"] = ' ';
                $contents["contents"][0]["body"]["contents"][1]["contents"][1]["contents"][1]["text"] = ' ';
                $contents["contents"][0]["body"]["contents"][1]["contents"][1]["contents"][1]["decoration"] = 'none';
//return var_dump($contents["contents"][0]["body"]["contents"][1]["contents"][2]["contents"][1]["contents"][2]);
                $contents["contents"][0]["body"]["contents"][1]["contents"][2]["contents"][1]["contents"][2]["text"] = 'Go to order';
                $contents["contents"][0]["body"]["contents"][1]["contents"][2]["contents"][1]["contents"][2]["action"]["type"] = 'uri';
                $contents["contents"][0]["body"]["contents"][1]["contents"][2]["contents"][1]["contents"][2]["action"]["label"] = 'action';
                $contents["contents"][0]["body"]["contents"][1]["contents"][2]["contents"][1]["contents"][2]["action"]["uri"] = $link_uri;
                $contents["contents"][1] = $see_more;

                $wp_pages->push_flex_messages(
                    array(
                        'line_user_id' => $line_user_id,
                        'alt_text' => 'Agent Registry',
                        'contents' => $contents
                    )
                );

            //}    
        }

        public function curtain_service() {

            global $wpdb;
            $curtain_agents = new curtain_agents();
            $serial_number = new serial_number();
            $line_bot_api = new line_bot_api();

            /** Line User ID registration and login into the system */
            if( isset($_GET['_id']) ) {
                $display_name = str_replace('%20', ' ', $_GET['_name']);    
                $array = get_users( array( 'meta_value' => $_GET['_id'] ));
                if (empty($array)) {
                    $user_id = wp_insert_user( array(
                        'user_login' => $_GET['_id'],
                        'user_pass' => $_GET['_id'],
                        'display_name' => $display_name,
                    ));
                    $user = get_user_by( 'ID', $user_id );
                    add_user_meta( $user_id, 'line_user_id', $_GET['_id']);
                    // To-Do: add_user_meta( $user_id, 'wallet_address', $_GET['_wallet_address']);
                }

                $args = array(
                    'redirect'        => get_option('Service'),
                    'value_username'  => $_GET['_id'],
                    'value_password'  => $_GET['_id']
                );
                
                $output  = '<div style="text-align:center;">';
                $output .= '<p>This is an automated process to assist you in registering for the system.</p>';
                $output .= '<p>Please click the Submit button below to complete your registration.</p>';
                $output .= '<form action="'.esc_url( site_url( 'wp-login.php', 'login_post' ) ).'" method="post" style="display:inline-block;">';
				$output .= '<input type="submit" name="wp-submit" class="button button-primary" value="Submit" />';
				$output .= '<input type="hidden" name="log" value="'. $args['value_username'] .'" />';
				$output .= '<input type="hidden" name="pwd" value="'. $args['value_password'] .'" />';
				$output .= '<input type="hidden" name="rememberme" value="foreverchecked" />';
				$output .= '<input type="hidden" name="redirect_to" value="'.esc_url( $args['redirect'] ).'" />';
                $output .= '</form>';
                $output .= '</div>';
                return $output;
            }

            if ( is_user_logged_in() ) {

                $user = wp_get_current_user();

                /** Assign the User as the specified Agent Operators */
                if( isset($_GET['_agent_registration']) ) {
                    if( isset($_POST['_agent_submit']) ) {
                        $agent = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}curtain_agents WHERE agent_number = %s AND phone1 = %s", $_POST['_agent_number'], $_POST['_agent_code'] ), OBJECT );            
                        if (is_null($agent) || !empty($wpdb->last_error)) {
                            return 'Wrong Code';
                        } else {
                            $curtain_agents->insert_agent_operator(
                                array(
                                    'curtain_agent_id'=>$curtain_agents->get_id($_POST['_agent_number']),
                                    'curtain_user_id'=>intval($user->ID)
                                ),
                            );

                            //line_bot_api::pushMessage([
                            $line_bot_api->pushMessage([
                                'to' => get_user_meta( $user->ID, 'line_user_id', TRUE ),
                                'messages' => [
                                    [
                                        "type" => "text",
                                        "text" => 'Please click the below link to register the system. ',
                                    ]
                                ]
                            ]);
        
                            return 'Success';
                        }
                    }
                    $agent_number=$_GET['_agent_registration'];
                    $output  = '<div style="text-align:center;">';
                    $output .= '<p>This is a process to register as the operator for '.$curtain_agents->get_name($agent_number).'.</p>';
                    $output .= '<p>Please enter the code and click the below Submit button to complete the registration.</p>';
                    $output .= '<form method="post" style="display:inline-block; text-align:-webkit-center;">';
                    $output .= '<input type="text" name="_agent_code" />';
                    $output .= '<input type="hidden" name="_agent_number" value="'.$_GET['_agent_registration'].'" />';
                    $output .= '<input type="submit" name="_agent_submit" style="margin:3px;" value="Submit" />';
                    $output .= '</form>';
                    $output .= '</div>';
                    return $output;    
                }

                /** Assign the User for the specified serial number(QR Code) */
                if( isset($_GET['serial_no']) ) {
                    $output = '<div style="text-align:center;">';
                    $qr_code_serial_no = $_GET['serial_no'];
                    $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}serial_number WHERE qr_code_serial_no = %s", $qr_code_serial_no ), OBJECT );            
                    /** incorrect QR-code then display the admin link */
                    if (is_null($row) || !empty($wpdb->last_error)) {                        
                        $output .= '<div style="font-weight:700; font-size:xx-large;">售後服務管理系統</div>';
/*                        
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
*/    
                    /** registration for QR-code */
                    } else {                        
                        $output .= 'Hi, '.$user->display_name.'<br>';
                        $output .= '感謝您選購我們的電動窗簾<br>';
                        $model = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}curtain_models WHERE curtain_model_id = {$row->curtain_model_id}", OBJECT );
                        if (!(is_null($model) || !empty($wpdb->last_error))) {
                            $output .= '型號:'.$model->curtain_model_name.' 規格: '.$row->specification.'<br>';
                        }
                        $six_digit_random_number = random_int(100000, 999999);
                        $output .= '請利用手機<i class="fa-solid fa-mobile-screen"></i>按'.'<a href="'.get_option('_line_account').'">這裡</a>, 加入我們的Line官方帳號,<br>';
    
                        $serial_number->update_serial_number(
                            array('curtain_user_id'=>intval($user->ID)),
                            array('qr_code_serial_no'=>$qr_code_serial_no)
                        );
                    }
                    $output .= '</div>';
                    return $output;        
                }
            }
        }

        public function init_webhook_events() {
            $line_bot_api = new line_bot_api();
            $open_ai_api = new open_ai_api();
            $curtain_agents = new curtain_agents();

            foreach ((array)$line_bot_api->parseEvents() as $event) {
            //foreach ((array)line_bot_api::parseEvents() as $event) {

                $profile = $line_bot_api->getProfile($event['source']['userId']);
                //$profile = line_bot_api::getProfile($event['source']['userId']);
                $display_name = str_replace(' ', '%20', $profile['displayName']);
                $link_uri = get_option('Service').'?_id='.$event['source']['userId'].'&_name='.$display_name;

                /** Line User ID registration */
                $array = get_users( array( 'meta_value' => $event['source']['userId'] ));
                if (empty($array)) {

                    if (file_exists(plugin_dir_path( __DIR__ ).'assets/templates/see_more.json')) {
                        $see_more = file_get_contents(plugin_dir_path( __DIR__ ).'assets/templates/see_more.json');
                        $see_more = json_decode($see_more, true);
                    }
                    $see_more["body"]["contents"][0]["action"]["label"] = 'Registration';
                    $see_more["body"]["contents"][0]["action"]["uri"] = $link_uri;
/*
                    $see_more["body"]["contents"][0]["type"] = 'text';
                    $see_more["body"]["contents"][0]["text"] = 'Hi, '.$profile['displayName'].', Please click the below button to register the system.';

                    $see_more["body"]["contents"][1]["type"] = 'button';
                    $see_more["body"]["contents"][1]["action"]["type"] = 'uri';
                    $see_more["body"]["contents"][1]["action"]["label"] = 'Registration';
                    $see_more["body"]["contents"][1]["action"]["uri"] = $link_uri;

                    $context = stream_context_create(
                        array(
                            'http' => array(
                                'method' => 'POST',
                                'header' => array(
                                    'Content-Type: application/json;',
                                    'Authorization: Bearer '.$line_bot_api->channel_access_token
                                ),
                                'content' => json_encode(
                                    array(
                                        'replyToken' => $event['replyToken'],
                                        "messages" => array(
                                            array(
                                                "type" => "flex",
                                                "altText" => 'Welcome message',
                                                'contents' => $see_more
                                            )
                                        )
                                    )
                                )
                            )
                        )
                    );
                    $contents = file_get_contents('https://api.line.me/v2/bot/message/push', false, $context);
*/
                    $line_bot_api->replyMessage([
                    //line_bot_api::replyMessage([
                        'replyToken' => $event['replyToken'],
                        'messages' => [
                            [
                                "type" => "flex",
                                "altText" => 'Welcome message',
                                'contents' => $see_more
                            ]
                        ]
                    ]);
/*                    
                    $line_bot_api->replyMessage([
                        'replyToken' => $event['replyToken'],
                        'messages' => [
                            [
                                "type" => "text",
                                "text" => 'Please click the below link to register the system. '. $link_uri,
                            ]
                        ]
                    ]);
*/                    
                } 

                switch ($event['type']) {
                    case 'message':
                        $message = $event['message'];
                        switch ($message['type']) {
                            case 'text':

                                /** Agent registration */
                                $agent = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}curtain_agents WHERE agent_number = %s", $message['text'] ), OBJECT );            
                                if (!(is_null($agent) || !empty($wpdb->last_error))) {
                                    $link_uri = get_option('Service').'?_agent_registration='.$message['text'].'&_id='.$event['source']['userId'].'&_name='.$display_name;
                                    $line_bot_api->replyMessage([
                                    //line_bot_api::replyMessage([
                                        'replyToken' => $event['replyToken'],
                                        'messages' => [
                                            [
                                                "type" => "text",
                                                "text" => 'Please click the below link to register the system. '. $link_uri,
                                            ]
                                        ]
                                    ]);
    
/*
                                    $array = get_users( array( 'meta_value' => $event['source']['userId'] ));
                                    $curtain_agents->create_agent_operator(
                                        array(
                                            'curtain_agent_id'=>$curtain_agents->get_id($message['text']),
                                            'curtain_user_id'=>$array[0]->ID
                                        ),
                                    );
                                    //$this->agent_registry_notice($profile['userId']);

                                    general_helps::push_imagemap_messages(
                                        array(
                                            'line_user_id' => $profile['userId'],
                                            'base_url' => $service_links->get_link('agent_registry'),
                                            'alt_text' => 'Hi, '.$profile['displayName'].', 您已經完成經銷商註冊, 請點擊連結進入訂貨服務區',
                                            'link_uri' => get_option('Orders').'?_id='.$profile['userId']
                                        )
                                    );
*/
                                }
                                
                                //** Open-AI auto reply */
                                $param=array();
                                $param["model"]="text-davinci-003";
                                $param["prompt"]=$message['text'];
                                $param["max_tokens"]=1000;
                                $response = $open_ai_api->createCompletion($param);
                                $string = preg_replace("/\n\r|\r\n|\n|\r/", '', $response['text']);
                                                        
                                $line_bot_api->replyMessage([
                                //line_bot_api::replyMessage([
                                    'replyToken' => $event['replyToken'],
                                    'messages' => [
                                        [
                                            'type' => 'text',
                                            //'text' => $response
                                            'text' => $string
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
            }
        }

        public function curtain_service_backup() {
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
                $output .= '<div style="font-weight:700; font-size:xx-large; color:firebrick; margin:50px;">簡單三步驟，開啟Siri語音控制窗簾。</div>';
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

        public function init_webhook_backup() {
            global $wpdb;
            $serial_number = new serial_number();
            $wp_pages = new wp_pages();
            $service_links = new service_links();
            $curtain_users = new curtain_users();
            $curtain_agents = new curtain_agents();
            $line_bot_api = new line_bot_api();
            $open_ai = new open_ai();
            //$business_central = new business_central();

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
                                            
                                            $body = array();
                                            $body[] = 'Hi, '.$profile['displayName'];
                                            $body[] = 'QR Code 已經完成註冊';
                                            $body[] = '請點擊連結進入售後服務區';

                                            $wp_pages->push_imagemap_messages(
                                                array(
                                                    'line_user_id' => $profile['userId'],
                                                    'base_url' => $service_links->get_link('user_registry'),
                                                    'alt_text' => 'Hi, '.$profile['displayName'].'QR Code 已經完成註冊'.'請點擊連結進入售後服務區',
                                                    //'link_uri' => get_permalink(get_page_by_title('Service')).'/?_id='.$profile['userId'],
                                                    'link_uri' => get_option('Service').'?_id='.$profile['userId'],
                                                    'body' => $body
                                                )
                                            );
                                        }
                                    } else {
                                        //** continue the process if the 6 digit number is incorrect */
                                        $body = array();
                                        $body[] = 'Hi, '.$profile['displayName'];
                                        $body[] = '您輸入的六位數字'.$message['text'].'有錯誤';
                                        $body[] = '請重新輸入正確數字已完成 QR Code 註冊';

                                        $wp_pages->push_imagemap_messages(
                                            array(
                                                'line_user_id' => $profile['userId'],
                                                'base_url' => $service_links->get_link('registry_error'),
                                                'alt_text' => 'Hi, '.$profile['displayName'].'您輸入的六位數字'.$message['text'].'有誤'.'請重新輸入正確數字已完成 QR Code 註冊',
                                                //'link_uri' => get_permalink(get_page_by_title('Service')).'/?_id='.$profile['userId'].'&serial_no=',
                                                'link_uri' => get_option('Service').'?_id='.$profile['userId'].'&serial_no=',
                                                'body' => $body
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
                                            $wp_pages->push_bubble_messages(
                                                array(
                                                    'line_user_id' => $result->line_user_id,
                                                    //'link_uri' => get_permalink(get_page_by_title('Users')).'/?_id='.$result->line_user_id,
                                                    'link_uri' => get_option('Users').'?_id='.$result->line_user_id,
                                                    'header' => $profile['displayName'],
                                                    'body' => $message['text']
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
                                            $wp_pages->push_bubble_messages(
                                                array(
                                                    'line_user_id' => $result->line_user_id,
                                                    //'link_uri' => get_permalink(get_page_by_title('Users')).'/?_id='.$result->line_user_id,
                                                    'link_uri' => get_option('Users').'?_id='.$result->line_user_id,
                                                    'header' => 'Aihome',
                                                    'body' => $string
                                                )
                                            );
                                        }

                                    } else {
                                        /** Agent registration */
                                        $curtain_users->update_curtain_users(
                                            array('curtain_agent_id'=>$curtain_agents->get_id($message['text'])),
                                            array('line_user_id'=>$profile['userId'])
                                        );
                                        $this->agent_registry_notice($profile['userId']);

                                        $wp_pages->push_imagemap_messages(
                                            array(
                                                'line_user_id' => $profile['userId'],
                                                'base_url' => $service_links->get_link('agent_registry'),
                                                'alt_text' => 'Hi, '.$profile['displayName'].', 您已經完成經銷商註冊, 請點擊連結進入訂貨服務區',
                                                //'link_uri' => get_permalink(get_page_by_title('Orders')).'/?_id='.$profile['userId']
                                                'link_uri' => get_option('Orders').'?_id='.$profile['userId']
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