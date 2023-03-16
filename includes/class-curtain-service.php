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

        public function curtain_service() {
            global $wpdb;
            $curtain_agents = new curtain_agents();
            $serial_number = new serial_number();
            $line_bot_api = new line_bot_api();

            if (file_exists(plugin_dir_path( __DIR__ ).'assets/templates/see_more.json')) {
                $see_more = file_get_contents(plugin_dir_path( __DIR__ ).'assets/templates/see_more.json');
                $see_more = json_decode($see_more, true);
            }

            if ( is_user_logged_in() ) {

                $user = wp_get_current_user();

                /** Reply the question */
                if( isset($_GET['_chat_message']) ) {
                    if( isset($_POST['_reply_submit']) ) {

                        $output = '<div style="text-align:center;">';
                        //$output .= $curtain_agents->get_name($_POST['_curtain_agent_id']);

                        $message_id = $this->insert_chat_message(
                            array(
                                'chat_from' => $_POST['_reply_from'],
                                'chat_to' => $_POST['_reply_to'],
                                'chat_message'=> $_POST['_reply_message']
                            )
                        );                            
                        $link_uri = 'http://aihome.tw/service/?_chat_message='.$message_id;

                        $see_more["header"]["type"] = 'box';
                        $see_more["header"]["layout"] = 'vertical';
                        $see_more["header"]["contents"][0]["type"] = 'text';
                        $see_more["header"]["contents"][0]["text"] = $user->display_name;
                        $see_more["body"]["contents"][0]["type"] = 'text';
                        $see_more["body"]["contents"][0]["text"] = $_POST['_reply_message'];
                        $see_more["body"]["contents"][1]["type"] = 'button';
                        $see_more["body"]["contents"][1]["action"]["type"] = 'uri';
                        $see_more["body"]["contents"][1]["action"]["label"] = 'Reply message';
                        $see_more["body"]["contents"][1]["action"]["uri"] = $link_uri;

                        $line_bot_api->pushMessage([
                            'to' => $_POST['_reply_to'],
                            'messages' => [
                                [
                                    "type" => "flex",
                                    "altText" => 'Reply message',
                                    'contents' => $see_more
                                ]
                            ]
                        ]);

                        $output .= '<h3>Replied the answer to customer Line chat box already.</h3>';
                        $output .= '</div>';
                        return $output;    
                    }
                        
                    $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}chat_messages WHERE message_id = %d", $_GET['_chat_message'] ), OBJECT );
                    $author_obj = get_user_by('id', $row->chat_from);
                    $output = '<div style="text-align:center;">';
                    $output .= '<h3>reply the question</h3>';
                    $output .= '<form method="post" style="display:inline-block; text-align:-webkit-center;">';
                    $output .= '<fieldset>';
                    $output .= '<label style="text-align:left;" for="_chat_from">From: </label>';
                    $output .= $author_obj->display_name;
                    $output .= '<label style="text-align:left;" for="_question">Question:</label>';
                    $output .= '<p style="text-align:left;">'.$row->chat_message.'</p>';
                    $output .= '<label style="text-align:left;" for="_reply_message">Answer:</label>';
                    $output .= '<textarea name="_reply_message" rows="10" cols="50"></textarea>';
                    $output .= '<input type="hidden" name="_reply_from" value="'.$row->chat_to.'" />';
                    $output .= '<input type="hidden" name="_reply_to" value="'.$row->chat_from.'" />';
                    //$output .= '<input type="hidden" name="_curtain_agent_id" value="'.$row->curtain_agent_id.'" />';
                    $output .= '<input type="submit" name="_reply_submit" style="margin:3px;" value="Submit" />';
                    $output .= '</fieldset>';
                    $output .= '</form>';
                    $output .= '</div>';
                    return $output;    
                }

                /** Assign the User for the specified serial number(QR Code) */
                if( isset($_GET['serial_no']) ) {
                    if( isset($_POST['_chat_submit']) ) {

                        $output = '<div style="text-align:center;">';
                        $output .= $curtain_agents->get_name($_POST['_curtain_agent_id']);
                        $results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}agent_operators WHERE curtain_agent_id = %d", $_POST['_curtain_agent_id'] ), OBJECT );
                        foreach ( $results as $result ) {
                            //$author_obj = get_user_by('id', $result->curtain_user_id);
                            //$link_uri = get_user_meta($_POST['_chat_user_id'], 'line_user_id', TRUE);

                            $message_id = $this->insert_chat_message(
                                array(
                                    'chat_from' => get_user_meta($_POST['_chat_user_id'], 'line_user_id', TRUE),
                                    'chat_to' => get_user_meta($result->curtain_user_id, 'line_user_id', TRUE),
                                    'chat_message'=> $_POST['_chat_message']
                                )
                            );                            
                            $link_uri = 'http://aihome.tw/service/?_chat_message='.$message_id;

                            $see_more["header"]["type"] = 'box';
                            $see_more["header"]["layout"] = 'vertical';
                            $see_more["header"]["contents"][0]["type"] = 'text';
                            $see_more["header"]["contents"][0]["text"] = $user->display_name;
                            $see_more["body"]["contents"][0]["type"] = 'text';
                            $see_more["body"]["contents"][0]["text"] = $_POST['_chat_message'];
                            $see_more["body"]["contents"][1]["type"] = 'button';
                            $see_more["body"]["contents"][1]["action"]["type"] = 'uri';
                            $see_more["body"]["contents"][1]["action"]["label"] = 'Chat message';
                            $see_more["body"]["contents"][1]["action"]["uri"] = $link_uri;

                            $line_bot_api->pushMessage([
                                'to' => get_user_meta($result->curtain_user_id, 'line_user_id', TRUE),
                                'messages' => [
                                    [
                                        "type" => "flex",
                                        "altText" => 'Chat message',
                                        'contents' => $see_more
                                    ]
                                ]
                            ]);

                        }

                        $output .= '<h3>Will reply the question to your Line chat box soon.</h3>';
                        $output .= '</div>';
                        return $output;    
                    }
                        
                    $output = '<div style="text-align:center;">';
                    $qr_code_serial_no = $_GET['serial_no'];
                    $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}serial_number WHERE qr_code_serial_no = %s", $qr_code_serial_no ), OBJECT );            
                    /** incorrect QR-code then display the admin link */
                    if (is_null($row) || !empty($wpdb->last_error)) {                        
                        $output .= '<div style="font-weight:700; font-size:xx-large;">Wrong Code</div>';
    
                    /** registration for QR-code */
                    } else {                        
                        $output .= 'Hi, '.$user->display_name.'<br>';
                        $output .= '感謝您選購我們的電動窗簾<br>';
                        $model = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}curtain_models WHERE curtain_model_id = {$row->curtain_model_id}", OBJECT );
                        if (!(is_null($model) || !empty($wpdb->last_error))) {
                            $output .= '型號:'.$model->curtain_model_name.' 規格: '.$row->specification.'<br>';
                        }
                        $serial_number->update_serial_number(
                            array('curtain_user_id'=>intval($user->ID)),
                            array('qr_code_serial_no'=>$qr_code_serial_no)
                        );

                        $output .= '<form method="post" style="display:inline-block; text-align:-webkit-center;">';
                        $output .= '<fieldset>';
                        $output .= '<label style="text-align:left;" for="_chat_message">Question:</label>';
                        $output .= '<textarea name="_chat_message" rows="10" cols="50"></textarea>';
                        $output .= '<input type="hidden" name="_chat_user_id" value="'.$user->ID.'" />';
                        $output .= '<input type="hidden" name="_curtain_agent_id" value="'.$row->curtain_agent_id.'" />';
                        $output .= '<input type="submit" name="_chat_submit" style="margin:3px;" value="Submit" />';
                        $output .= '</fieldset>';
                        $output .= '</form>';
    
                    }
                    $output .= '</div>';
                    return $output;        
                }
        
                /** Assign the User as the specified Agent Operators */
                if( isset($_GET['_agent_no']) ) {
                    if( isset($_POST['_agent_submit']) ) {
                        $agent = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}curtain_agents WHERE agent_number = %s AND phone1 = %s", $_POST['_agent_number'], $_POST['_agent_code'] ), OBJECT );            
                        if (is_null($agent) || !empty($wpdb->last_error)) {

                            $line_bot_api->pushMessage([
                                'to' => get_user_meta( $user->ID, 'line_user_id', TRUE ),
                                'messages' => [
                                    [
                                        "type" => "text",
                                        "text" => 'Please click the below link to register the system. ',
                                    ]
                                ]
                            ]);
        
                            return 'Wrong Code';
                        } else {
                            $_SESSION['_agent_number'] = $_POST['_agent_number'];
                            $_SESSION['_agent_code'] = $_POST['_agent_code'];

                            $curtain_agents->insert_agent_operator(
                                array(
                                    'curtain_agent_id'=>$curtain_agents->get_id($_POST['_agent_number']),
                                    'curtain_user_id'=>intval($user->ID)
                                ),
                            );
                            ?><script>window.location.replace("https://aihome.tw/toolbox/");</script><?php
                        }
                    }

                    $agent_number = $_GET['_agent_no'];
                    $output  = '<div style="text-align:center;">';
                    $output .= '<p>This is a process to register as the operator for '.$curtain_agents->get_name_by_no($agent_number).'.</p>';
                    $output .= '<p>Please enter the code and click the below Submit button to complete the registration.</p>';
                    $output .= '<form method="post" style="display:inline-block; text-align:-webkit-center;">';
                    $output .= '<input type="text" name="_agent_code" />';
                    $output .= '<input type="hidden" name="_agent_number" value="'.$_GET['_agent_no'].'" />';
                    $output .= '<input type="submit" name="_agent_submit" style="margin:3px;" value="Submit" />';
                    $output .= '</form>';
                    $output .= '</div>';
                    return $output;    
                }

                /** Update the User account information */
                if( isset($_GET['_id']) ) {
                    if( isset($_POST['_user_submit']) ) {
                        $users = get_users(array(
                            'meta_key'     => 'line_user_id',
                            'meta_value'   => $_POST['_line_user_id'],
                            'meta_compare' => '=',
                        ));
                        $user_data = wp_update_user( array( 
                            'ID' => $users[0]->ID, 
                            'display_name' => $_POST['_display_name'], 
                            'user_email' => $_POST['_user_email'], 
                        ) );

                        if ( is_wp_error( $user_data ) ) {
                            // There was an error; possibly this user doesn't exist.
                            echo 'Error.';
                        } else {
                            // Success!
                            echo 'User profile updated.';
                        }
                    }

                    $output  = '<div style="text-align:center;">';
                    $output .= '<h3>User profile</h3>';
                    $output .= '<form method="post" style="display:inline-block; text-align:-webkit-center;">';
                    $output .= '<fieldset>';
                    $output .= '<label style="text-align:left;" for="_display_name">Name:</label>';
                    $output .= '<input type="text" name="_display_name" value="'.$user->display_name.'" />';
                    $output .= '<label style="text-align:left;" for="_user_email">Email:</label>';
                    $output .= '<input type="text" name="_user_email" value="'.$user->user_email.'" />';
                    $output .= '<input type="hidden" name="_line_user_id" value="'.$_GET['_id'].'" />';
                    $output .= '<input type="submit" name="_user_submit" style="margin:3px;" value="Submit" />';
                    $output .= '</fieldset>';
                    $output .= '</form>';
                    $output .= '</div>';
                    return $output;    
                }

            } else {

                /** Line User ID registration and login into the system */
                if( isset($_GET['_id']) ) {
                    //$display_name = str_replace('%20', ' ', $_GET['_name']);    
                    $array = get_users( array( 'meta_value' => $_GET['_id'] ));
                    if (empty($array)) {
                        $user_id = wp_insert_user( array(
                            'user_login' => $_GET['_id'],
                            'user_pass' => $_GET['_id'],
                        ));
                        $user = get_user_by( 'ID', $user_id );
                        add_user_meta( $user_id, 'line_user_id', $_GET['_id']);
                        // To-Do: add_user_meta( $user_id, 'wallet_address', $_GET['_wallet_address']);
                    }

                    $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}curtain_agents WHERE agent_number = %s", $_GET['_agent_no'] ), OBJECT );            
                    if (is_null($row) || !empty($wpdb->last_error)) {
                        $link_uri = get_option('Service').'?_id='.$_GET['_id'];
                    } else {
                        $link_uri = get_option('Service').'?_agent_no='.$row->agent_number;
                    }

                    $output  = '<div style="text-align:center;">';
                    $output .= '<p>This is an automated process to assist you in registering for the system.</p>';
                    $output .= '<p>Please click the Submit button below to complete your registration.</p>';
                    $output .= '<form action="'.esc_url( site_url( 'wp-login.php', 'login_post' ) ).'" method="post" style="display:inline-block;">';
                    $output .= '<input type="hidden" name="log" value="'. $_GET['_id'] .'" />';
                    $output .= '<input type="hidden" name="pwd" value="'. $_GET['_id'] .'" />';
                    $output .= '<input type="hidden" name="rememberme" value="foreverchecked" />';
                    $output .= '<input type="hidden" name="redirect_to" value="'.esc_url( $link_uri ).'" />';
                    $output .= '<input type="submit" name="wp-submit" class="button button-primary" value="Submit" />';
                    $output .= '</form>';
                    $output .= '</div>';
                    return $output;

                } else {
                    $output = '<div style="text-align:center;">';
                    $output .= '感謝您選購我們的電動窗簾<br>';
                    $output .= '請利用手機<i class="fa-solid fa-mobile-screen"></i>按'.'<a href="'.get_option('_line_account').'">這裡</a>, 加入我們的Line官方帳號,<br>';
                    $output .= '</div>';
                    return $output;        
                }
            }
        }

        public function init_webhook_events() {
            global $wpdb;
            $line_bot_api = new line_bot_api();
            $open_ai_api = new open_ai_api();
            $curtain_agents = new curtain_agents();

            if (file_exists(plugin_dir_path( __DIR__ ).'assets/templates/see_more.json')) {
                $see_more = file_get_contents(plugin_dir_path( __DIR__ ).'assets/templates/see_more.json');
                $see_more = json_decode($see_more, true);
            }

            foreach ((array)$line_bot_api->parseEvents() as $event) {

                $profile = $line_bot_api->getProfile($event['source']['userId']);

                switch ($event['type']) {
                    case 'message':
                        $message = $event['message'];
                        switch ($message['type']) {
                            case 'text':

                                /** Agent registration */
                                $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}curtain_agents WHERE agent_number = %s", $message['text'] ), OBJECT );            
                                if (is_null($row) || !empty($wpdb->last_error)) {
                                    //$link_uri = get_option('Service').'?_id='.$event['source']['userId'];
                                } else {
                                    $link_uri = get_option('Service').'?_agent_no='.$row->agent_number;
                                }
                                
                                /** Line User ID registration */
                                $array = get_users( array( 'meta_value' => $event['source']['userId'] ));
                                if (empty($array)) {
                                    if (is_null($row) || !empty($wpdb->last_error)) {
                                        $link_uri = get_option('Service').'?_id='.$event['source']['userId'];
                                    } else {
                                        $link_uri = get_option('Service').'?_id='.$event['source']['userId'].'&_agent_no='.$row->agent_number;
                                    }
                                }

                                if (empty($array) || !(is_null($row) || !empty($wpdb->last_error))) {
                
                                    $see_more["body"]["contents"][0]["action"]["label"] = 'Login/Registration';
                                    $see_more["body"]["contents"][0]["action"]["uri"] = $link_uri;
                                    $line_bot_api->replyMessage([
                                        'replyToken' => $event['replyToken'],
                                        'messages' => [
                                            [
                                                "type" => "flex",
                                                "altText" => 'Welcome message',
                                                'contents' => $see_more
                                            ]
                                        ]
                                    ]);

                                } else {
                                    //** Open-AI auto reply */
                                    $param=array();
                                    $param["model"]="text-davinci-003";
                                    $param["prompt"]=$message['text'];
                                    $param["max_tokens"]=1000;
                                    $response = $open_ai_api->createCompletion($param);
                                    $string = preg_replace("/\n\r|\r\n|\n|\r/", '', $response['text']);
                                                            
                                    $line_bot_api->replyMessage([
                                        'replyToken' => $event['replyToken'],
                                        'messages' => [
                                            [
                                                'type' => 'text',
                                                //'text' => $response
                                                'text' => $string
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