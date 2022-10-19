<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}
if (!class_exists('curtain_service')) {

    class curtain_service {

        /**
         * Class constructor
         */
        public function __construct() {
            add_shortcode('registration', __CLASS__ . '::registration');
            add_shortcode('service-option-list', __CLASS__ . '::list_service_options');
            self::create_tables();
        }

        function push_text_message($text_message='', $line_user_id='') {
            $client = line_bot_sdk();
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

        function push_OTP_to($line_user_id='') {
            $six_digit_random_number = random_int(100000, 999999);
            $text_message = 'OTP code : '.$six_digit_random_number;
            self::push_text_message($text_message, $line_user_id);
        }

        function registration() {

            if ( isset($_POST['_link_submit']) ) {
                if ( $_POST['_link_submit']=='Agents' ) {
                    $curtain_agents = new curtain_agents();
                    return $curtain_agents->list_curtain_agents();
                }
                if ( $_POST['_link_submit']=='Models' ) {
                    $curtain_models = new curtain_models();
                    return $curtain_models->list_curtain_models();
                }
                if ( $_POST['_link_submit']=='Users' ) {
                    $curtain_users = new curtain_users();
                    return $curtain_users->list_curtain_users();
                }
            }

            if( isset($_POST['_submit_action']) ) {
                if( $_POST['_submit_action']=='Resend' ) {

                    $line_user_id = $_POST['_line_user_id'];
                    self::push_OTP_to($line_user_id);

                    global $wpdb;
                    $table = $wpdb->prefix.'curtain_users';
                    $data = array(
                        'last_otp' => $six_digit_random_number,
                    );
                    $where = array(
                        'curtain_user_id' => $curtain_user_id,
                    );
                    $wpdb->update( $table, $data, $where );                
                }
                unset($_POST['submit_action']);
            }

            $qr_code_serial_no = $_GET['serial_no'];
            
            $output = '<div style="text-align:center;">';
            global $wpdb;
            //$row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}serial_number WHERE qr_code_serial_no = {$qr_code_serial_no}", OBJECT );
            $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}serial_number WHERE qr_code_serial_no = %s", $qr_code_serial_no ), OBJECT );            
            if (count($row) > 0) {

                $curtain_user_id=$row->curtain_user_id;
                $user = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}curtain_users WHERE curtain_user_id = {$curtain_user_id}", OBJECT );
                if (count($user) > 0) {
                    $output .= 'Hi, '.$user->display_name.'<br>';
                }
                $output .= '感謝您選購我們的電動窗簾<br>';
                $model = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}curtain_models WHERE curtain_model_id = {$row->curtain_model_id}", OBJECT );
                if ( count($model) > 0 ) {
                    $output .= '型號:'.$model->curtain_model_name.' 規格: '.$row->specification.'<br>';
                }

                $six_digit_random_number = random_int(100000, 999999);
                $output .= '請利用手機按 '.'<a href="https://line.me/ti/p/@490tjxdt">';
                $output .= '<img src="https://scdn.line-apps.com/n/line_add_friends/btn/zh-Hant.png" alt="加入好友" height="36" border="0"></a>';
                $output .= '<br>在我們的Line官方帳號聊天室中輸入六位數字密碼: <p style="color:blue">'.$six_digit_random_number.'</p>';
                $output .= '密碼確認後, 請按下我們提供的連結來繼續後續的作業<br>';
    
                if (count($user) > 0) {
                    // login
/*
                    $output .= '如果您忘記密碼, 請按下後方重送的按鍵: ';
                    $output .= '<form method="post">';
                    $output .= '<input type="hidden" value="'.$user->line_user_id.'" name="_line_user_id">';
                    $output .= '<div class="wp-block-button">';
                    $output .= '<input class="wp-block-button__link" type="submit" value="Resend" name="_submit_action">';
                    $output .= '</div>';
                    $output .= '</form>';
*/
                    $data=array();
                    $data['last_otp']=$six_digit_random_number;
                    $where=array();
                    $where['curtain_user_id']=$user->curtain_user_id;
                    $curtain_users = new curtain_users();
                    $result = $curtain_users->update_curtain_users($data, $where);
                    
                } else {
                    // registration
                    $data=array();
                    $data['curtain_user_id']=$six_digit_random_number;
                    $where=array();
                    $where['qr_code_serial_no']=$qr_code_serial_no;
                    //$result = self::update_serial_number($data, $where);
                    $serial_number = new serial_number();
                    $result = $serial_number->update_serial_number($data, $where);
                }

            } else {
                // Display curtain service menu OR curtain administration menu
                if (($_GET['_mode']=='admin') ){
                    $output .= '<h2>Admin Options</h2>';
                    $output .= '<div class="wp-block-buttons">';
                    $output .= '<form method="post">';
                    $output .= '<div class="wp-block-button">';
                    $output .= '<input class="wp-block-button__link" type="submit" value="Agents" name="_link_submit">';
                    $output .= '</div>';
                    $output .= '</form>';
                    $output .= '<form method="post">';
                    $output .= '<div class="wp-block-button">';
                    $output .= '<input class="wp-block-button__link" type="submit" value="Models" name="_link_submit">';
                    $output .= '</div>';
                    $output .= '</form>';
                    $output .= '<form method="post">';
                    $output .= '<div class="wp-block-button">';
                    $output .= '<input class="wp-block-button__link" type="submit" value="Users" name="_link_submit">';
                    $output .= '</div>';
                    $output .= '</form>';
                    $output .= '</div>';

                } else {
                    $output .= '<h2>Service Options</h2>';
                    global $wpdb;
                    $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}service_options", OBJECT );
                    $output .= '<div class="wp-block-buttons">';
                    foreach ( $results as $index=>$result ) {
                        $output .= '<form action="'.$result->service_option_link.'">';
                        $output .= '<div class="wp-block-button">';
                        $output .= '<input class="wp-block-button__link" type="submit" value="'.$result->service_option_title.'">';
                        $output .= '</div>';
                        $output .= '</form>';
                    }
                    $output .= '</div>';
                }
            }
            $output .= '</div>';
            return $output;
        }

        function list_service_options() {

            if( isset($_POST['_mode']) || isset($_POST['_id']) ) {
                return self::edit_service_options($_POST['_id'], $_POST['_mode']);
            }

            if( isset($_POST['_create_service_option']) ) {
                $data=array();
                $data['service_option_title']=$_POST['_service_option_title'];
                $data['service_option_link']=$_POST['_service_option_link'];
                $result = self::insert_service_option($data);
            }
        
            if( isset($_POST['_update_service_option']) ) {
                $data=array();
                $data['service_option_title']=$_POST['_service_option_title'];
                $data['service_option_link']=$_POST['_service_option_link'];
                $where=array();
                $where['service_option_id']=$_POST['_service_option_id'];
                $result = self::update_service_options($data, $where);
            }
        
            global $wpdb;
            if( isset($_POST['_where_service_options']) ) {
                $where='"%'.$_POST['_where_service_options'].'%"';
                $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}service_options WHERE service_option_title LIKE {$where}", OBJECT );
                unset($_POST['_where_service_options']);
            } else {
                $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}service_options", OBJECT );
            }
            $output  = '<h2>Service Options</h2>';
            $output .= '<figure class="wp-block-table"><table><tbody>';
            $output .= '<tr><td colspan=5 style="text-align:right">';
            $output .= '<form method="post">';
            $output .= '<input type="text" name="_where_service_options" placeholder="Search...">';
            $output .= '<input type="submit" value="Search" name="submit_action">';
            $output .= '</form>';
            $output .= '</td></tr>';
            $output .= '<tr style="background-color:yellow">';
            $output .= '<td>id</td>';
            $output .= '<td>title</td>';
            $output .= '<td>link</td>';
            $output .= '<td>update_time</td>';
            $output .= '</tr>';
            foreach ( $results as $index=>$result ) {
                $output .= '<tr>';
                $output .= '<td>'.$result->service_option_id.'</a></td>';
                $output .= '<td><form method="post">';
                $output .= '<input type="hidden" value="'.$result->service_option_id.'" name="_id">';
                $output .= '<input type="submit" value="'.$result->service_option_title.'" name="_mode">';
                $output .= '</form></td>';
                $output .= '<td>'.$result->service_option_link.'</td>';
                $output .= '<td>'.wp_date( get_option('date_format'), $result->update_timestamp ).' '.wp_date( get_option('time_format'), $result->update_timestamp ).'</td>';
                $output .= '</tr>';
            }
            $output .= '</tbody></table></figure>';
            $output .= '<form method="post">';
            $output .= '<div class="wp-block-buttons">';
            $output .= '<div class="wp-block-button">';
            $output .= '<input class="wp-block-button__link" type="submit" value="Create" name="_mode">';
            $output .= '</div>';
            $output .= '</div>';
            $output .= '</form>';

            return $output;
        }

        function edit_service_options( $_id=null, $_mode=null ) {

            global $wpdb;
            $row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}service_options WHERE service_option_id={$_id}", OBJECT );
            if( $_mode=='Create' ) {
                $output  = '<h2>New Service Option</h2>';
            } else {
                $output  = '<h2>Service Option Update</h2>';
            }
            $output .= '<form method="post">';
            $output .= '<figure class="wp-block-table"><table><tbody>';
            if( $_mode=='Create' ) {
                $output .= '<tr><td>'.'Title:'.'</td><td><input size="50" type="text" name="_service_option_title"></td></tr>';
                $output .= '<tr><td>'.'Link:'.'</td><td><input size="50" type="text" name="_service_option_link"></td></tr>';
            } else {
                $output .= '<input type="hidden" value="'.$row->service_option_id.'" name="_service_option_id">';
                $output .= '<tr><td>'.'Title:'.'</td><td><input size="50" type="text" name="_service_option_title" value="'.$row->service_option_title.'"></td></tr>';
                $output .= '<tr><td>'.'Link:'.'</td><td><input size="50" type="text" name="_service_option_link" value="'.$row->service_option_link.'"></td></tr>';
            }   
            $output .= '</tbody></table></figure>';

            $output .= '<div class="wp-block-buttons">';
            $output .= '<div class="wp-block-button">';
            if( $_mode=='Create' ) {
                $output .= '<input class="wp-block-button__link" type="submit" value="Create" name="_create_service_option">';
            } else {
                $output .= '<input class="wp-block-button__link" type="submit" value="Update" name="_update_service_option">';
            }
            $output .= '</div>';
            $output .= '<div class="wp-block-button">';
            $output .= '<input class="wp-block-button__link" type="submit" value="Cancel"';
            $output .= '</div>';
            $output .= '</div>';
            $output .= '</form>';
        
            return $output;
        }

        function insert_service_option($data=[]) {
            global $wpdb;
            $table = $wpdb->prefix.'service_options';
            $data = array(
                'service_option_title' => $data['service_option_title'],
                'service_option_link' => $data['service_option_link'],
                'create_timestamp' => time(),
                'update_timestamp' => time(),
            );
            $wpdb->insert($table, $data);        
            return $wpdb->insert_id;
        }

        function update_service_options($data=[], $where=[]) {
            global $wpdb;
            $table = $wpdb->prefix.'service_options';
            $data['update_timestamp'] = time();
            $wpdb->update($table, $data, $where);
        }

        function create_tables() {
            global $wpdb;
            $charset_collate = $wpdb->get_charset_collate();
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
            $sql = "CREATE TABLE `{$wpdb->prefix}service_options` (
                service_option_id int NOT NULL AUTO_INCREMENT,
                service_option_title varchar(20),
                service_option_link varchar(255),
                service_option_category int(2),
                create_timestamp int(10),
                update_timestamp int(10),
                PRIMARY KEY (service_option_id)
            ) $charset_collate;";
            dbDelta($sql);            
        }
    }
    new curtain_service();
}
?>