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
            add_shortcode('curtain-service', array( __CLASS__, 'init_curtain_service' ));
            add_shortcode('service-option-list', array( __CLASS__, 'list_service_options' ));
            //add_action( 'init', array( __CLASS__, 'register_session' ) );
            self::create_tables();
        }

        function register_session() {
            if ( ! session_id() ) {
                session_start();
            }
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

        function push_OTP_to($line_user_id='') {
            $six_digit_random_number = random_int(100000, 999999);
            $text_message = 'OTP code : '.$six_digit_random_number;
            self::push_text_message($text_message, $line_user_id);
        }

        function init_curtain_service() {

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

            $output = '<div style="text-align:center;">';
            if( isset($_GET['serial_no']) ) {
                $qr_code_serial_no = $_GET['serial_no'];
                global $wpdb;
                $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}serial_number WHERE qr_code_serial_no = %s", $qr_code_serial_no ), OBJECT );            
                if ((count($row) > 0)) {
                    
                    $curtain_user_id=$row->curtain_user_id;
                    //$user = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}curtain_users WHERE curtain_user_id = {$curtain_user_id}", OBJECT );
                    $user = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}curtain_users WHERE curtain_user_id = %d", $row->curtain_user_id ), OBJECT );            
                    if (count($user) > 0) {
                        $output .= 'Hi, '.$user->display_name.'<br>';
                        $_SESSION['username'] = $user->line_user_id;
                    }
                    $output .= '感謝您選購我們的電動窗簾<br>';
                    $model = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}curtain_models WHERE curtain_model_id = {$row->curtain_model_id}", OBJECT );
                    if ( count($model) > 0 ) {
                        $output .= '型號:'.$model->curtain_model_name.' 規格: '.$row->specification.'<br>';
                    }
    
                    global $wpdb;
                    $where='"%view%"';
                    $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}service_options WHERE service_option_category LIKE {$where}", OBJECT );
                    $output .= '<div class="wp-block-buttons">';
                    foreach ( $results as $index=>$result ) {
                        $output .= '<div class="wp-block-button">';
                        $output .= '<a class="wp-block-button__link" href="'.$result->service_option_link.'">'.$result->service_option_title.'</a>';
                        $output .= '</div>';
                    }
                    $output .= '</div>';
    
                    if (count($user) > 0) {
                        // login
                        $six_digit_random_number = random_int(100000, 999999);
                        $output .= '如需其他服務, 請利用手機按<br>'.'<a href="'.get_option('_line_account').'">';
                        $output .= '<img src="https://scdn.line-apps.com/n/line_add_friends/btn/zh-Hant.png" alt="加入好友" height="16" border="0"></a>';
                        //$output .= '<br>在我們的Line官方帳號聊天室中輸入六位數字密碼: <span style="font-size:24px;color:blue;">'.$six_digit_random_number.'</span>';
                        //$output .= '<br>密碼確認後, 請接著按下我們提供的連結來繼續後續的作業<br>';
                        $output .= '<br>在我們的Line官方帳號聊天室中聯絡我們的客服人員<br>';
/*
                        $data=array();
                        $data['last_otp']=$six_digit_random_number;
                        $where=array();
                        $where['curtain_user_id']=$user->curtain_user_id;
                        $curtain_users = new curtain_users();
                        $result = $curtain_users->update_curtain_users($data, $where);
*/                        
                    } else {

                        $six_digit_random_number = random_int(100000, 999999);
                        $output .= '請利用手機按<br>'.'<a href="'.get_option('_line_account').'">';
                        $output .= '<img src="https://scdn.line-apps.com/n/line_add_friends/btn/zh-Hant.png" alt="加入好友" height="16px" border="0"></a>';
                        $output .= '<br>在我們的Line官方帳號聊天室中輸入六位數字密碼: <span style="font-size:24px;color:blue;">'.$six_digit_random_number.'</span>';
                        $output .= ' 完成註冊程序<br>';
                        // registration
                        $data=array();
                        $data['one_time_password']=$six_digit_random_number;
                        $where=array();
                        $where['qr_code_serial_no']=$qr_code_serial_no;
                        $serial_number = new serial_number();
                        $result = $serial_number->update_serial_number($data, $where);    
                    }
    
                } else {

                    global $wpdb;
                    $where='"%admin%"';
                    $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}service_options WHERE service_option_category LIKE {$where}", OBJECT );
                    $output .= '<div class="wp-block-buttons">';
                    foreach ( $results as $index=>$result ) {
                        $output .= '<div class="wp-block-button">';
                        $output .= '<a class="wp-block-button__link" href="'.$result->service_option_link.'">'.$result->service_option_title.'</a>';
                        $output .= '</div>';
                    }
                    $output .= '</div>';
                }
    
            } else {
                
                global $wpdb;
                $where='"%view%"';
                $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}service_options WHERE service_option_category LIKE {$where}", OBJECT );
                $output .= '<div class="wp-block-buttons">';
                foreach ( $results as $index=>$result ) {
                    $output .= '<div class="wp-block-button">';
                    $output .= '<a class="wp-block-button__link" href="'.$result->service_option_link.'">'.$result->service_option_title.'</a>';
                    $output .= '</div>';
                }
                $output .= '</div>';
            }
            $output .= '</div>';
            return $output;
        }

        function list_service_options() {

            if( isset($_SESSION['username']) ) {
                $line_user_id = $_SESSION['username'];
                global $wpdb;
                $user = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}curtain_users WHERE line_user_id = %s AND user_role= %s", $line_user_id, 'admin' ), OBJECT );            
                if (count($user) == 0 && $_GET['_check_permission'] != 'false') {
                    return 'You are not validated to read this page. Please check to the administrators.';
                }
            } else {
                if ( $_GET['_check_permission'] != 'false' ) {
                    return 'You are not validated to read this page. Please check to the administrators.';
                }
            }

            if( isset($_POST['_create']) ) {
                $data=array();
                $data['service_option_title']=$_POST['_service_option_title'];
                $data['service_option_link']=$_POST['_service_option_link'];
                $data['service_option_category']=$_POST['_service_option_category'];
                $result = self::insert_service_option($data);
            }
        
            if( isset($_POST['_update']) ) {
                $data=array();
                $data['service_option_title']=$_POST['_service_option_title'];
                $data['service_option_link']=$_POST['_service_option_link'];
                $data['service_option_category']=$_POST['_service_option_category'];
                $where=array();
                $where['service_option_id']=$_POST['_service_option_id'];
                $result = self::update_service_options($data, $where);
            }

            global $wpdb;
            if( isset($_POST['_where']) ) {
                $where='"%'.$_POST['_where'].'%"';
                $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}service_options WHERE service_option_title LIKE {$where}", OBJECT );
                unset($_POST['_where']);
            } else {
                $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}service_options", OBJECT );
            }
            $output  = '<h2>Service Options</h2>';
            $output .= '<div style="text-align: right">';
            $output .= '<form method="post">';
            $output .= '<input style="display:inline" type="text" name="_where" placeholder="Search...">';
            $output .= '<input style="display:inline" type="submit" value="Search" name="submit_action">';
            $output .= '</form>';
            $output .= '</div>';
            $output .= '<div class="ui-widget">';
            $output .= '<table id="users" class="ui-widget ui-widget-content">';
            $output .= '<thead><tr class="ui-widget-header ">';
            $output .= '<th>id</th>';
            $output .= '<th>title</th>';
            $output .= '<th>link</th>';
            $output .= '<th>category</th>';
            $output .= '<th>update_time</th>';
            $output .= '</tr></thead>';
            $output .= '<tbody>';
            foreach ( $results as $index=>$result ) {
                $output .= '<tr>';
                $output .= '<td>'.$result->service_option_id.'</a></td>';
                $output .= '<td><form method="post">';
                $output .= '<input type="hidden" value="'.$result->service_option_id.'" name="_id">';
                $output .= '<input type="submit" value="'.$result->service_option_title.'">';
                $output .= '</form></td>';
                $output .= '<td>'.$result->service_option_link.'</td>';
                $output .= '<td>'.$result->service_option_category.'</td>';
                $output .= '<td>'.wp_date( get_option('date_format'), $result->update_timestamp ).' '.wp_date( get_option('time_format'), $result->update_timestamp ).'</td>';
                $output .= '</tr>';
            }
            $output .= '</tbody></table></div>';
            $output .= '<form method="post">';
            $output .= '<input id="create-model" class="wp-block-button__link" type="submit" value="Create" name="_mode">';
            $output .= '</form>';

            if( isset($_POST['_mode']) || isset($_POST['_id']) ) {
                $_id = $_POST['_id'];
                global $wpdb;
                $row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}service_options WHERE service_option_id={$_id}", OBJECT );
                if (is_null($row) || !empty($wpdb->last_error)) {
                    $output .= '<div id="dialog" title="Create new option">';
                    $output .= '<form method="post">';
                    $output .= '<fieldset>';
                    $output .= '<label for="_service_option_title">Option Title</label>';
                    $output .= '<input type="text" name="_service_option_title" id="service_option_title" class="text ui-widget-content ui-corner-all">';
                    $output .= '<label for="_service_option_link">Option Link</label>';
                    $output .= '<input type="text" name="_service_option_link" id="service_option_link" class="text ui-widget-content ui-corner-all">';
                    $output .= '<label for="_service_option_category">Category</label>';
                    $output .= '<input type="text" name="_service_option_category" id="service_option_category" class="text ui-widget-content ui-corner-all">';
                    $output .= '</fieldset>';
                    $output .= '<input class="wp-block-button__link" type="submit" value="Create" name="_create">';
                    $output .= '<input class="wp-block-button__link" type="submit" value="Cancel"';
                    $output .= '</form>';
                    $output .= '</div>';
                } else {                    
                    $output .= '<div id="dialog" title="Service Option update">';
                    $output .= '<form method="post">';
                    $output .= '<fieldset>';
                    $output .= '<input type="hidden" value="'.$row->service_option_id.'" name="_service_option_id">';
                    $output .= '<label for="_service_option_title">Option Title</label>';
                    $output .= '<input type="text" name="_service_option_title" id="service_option_title" class="text ui-widget-content ui-corner-all" value="'.$row->service_option_title.'">';
                    $output .= '<label for="_service_option_link">Option Link</label>';
                    $output .= '<input type="text" name="_service_option_link" id="service_option_link" class="text ui-widget-content ui-corner-all" value="'.$row->service_option_link.'">';
                    $output .= '<label for="_service_option_category">Category</label>';
                    $output .= '<input type="text" name="_service_option_category" id="service_option_category" class="text ui-widget-content ui-corner-all" value="'.$row->service_option_category.'">';
                    $output .= '</fieldset>';
                    $output .= '<input class="wp-block-button__link" type="submit" value="Update" name="_update">';
                    $output .= '<input class="wp-block-button__link" type="submit" value="Delete" name="_delete">';
                    $output .= '</form>';
                    $output .= '</div>';
                }
            }

            return $output;
        }

        function insert_service_option($data=[]) {
            global $wpdb;
            $table = $wpdb->prefix.'service_options';
            $data['create_timestamp'] = time();
            $data['update_timestamp'] = time();
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
                service_option_category varchar(10),
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