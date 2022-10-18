<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}
if (!class_exists('otp_service')) {

    class otp_service {

        /**
         * Class constructor
         */
        public function __construct() {
            add_shortcode('registration', __CLASS__ . '::registration');
            add_shortcode('serial-number-list', __CLASS__ . '::list_serial_number');
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

            if( isset($_POST['submit_action']) ) {

                $line_user_id = $_POST['line_user_id'];

                if( $_POST['submit_action']=='Login' ) {
                    // check the $_POST['otp_input'] to match the last_otp field in curtain_users table
                    if ( $last_otp==$_POST['otp_input'] ) {
                        wp_redirect( home_url().'' ); 
                        exit;
                    } else {
                        $text_message = 'The '.$_POST['otp_input'].' is a wrong OTP code.';
                        self::push_text_message($text_message, $line_user_id);
                    }
                }

                if( $_POST['submit_action']=='Resend' ) {

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
            
            $output = '<div>';
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
                $model = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}model_number WHERE model_number_id = {$row->model_number_id}", OBJECT );
                //$spec = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}specifications WHERE specification_id = {$row->specification_id}", OBJECT );
                //if ((count($model) > 0) && (count($spec) > 0)) {
                if ( count($model) > 0 ) {
                    //$output .= '型號:'.$model->model_number.' 規格: '.$spec->specification.' '.$spec->spec_description.'<br>';
                    $output .= '型號:'.$model->model_number.' 規格: '.$row->specification.'<br>';
                }

                $six_digit_random_number = random_int(100000, 999999);
                $output .= '請利用手機按 '.'<a href="https://line.me/ti/p/@490tjxdt">';
                $output .= '<img src="https://scdn.line-apps.com/n/line_add_friends/btn/zh-Hant.png" alt="加入好友" height="36" border="0"></a>';
                $output .= '<br>在我們的Line官方帳號聊天室中輸入六位數字密碼: <p style="color:blue">'.$six_digit_random_number.'</p>';
                $output .= '並按下我們提供的連結來繼續後續的作業<br>';
    
                if (count($user) > 0) {
                    // login
                    //$output .= '請輸入我們送到您Line帳號的OTP(一次性密碼):';
                    $output .= '忘記密碼:';
                    $output .= '<form method="post">';
                    //$output .= '<input type="text" name="otp_input">';
                    $output .= '<div class="wp-block-button">';
                    $output .= '<input type="hidden" value="'.$user->line_user_id.'" name="line_user_id">';
                    //$output .= '<input class="wp-block-button__link" type="submit" value="Login" name="submit_action">';
                    $output .= '<input class="wp-block-button__link" type="submit" value="Resend" name="submit_action">';
                    $output .= '</div>';
                    $output .= '</form>';
                } else {
                    // registration
                    //$six_digit_random_number = random_int(100000, 999999);
                    $data=array();
                    $data['curtain_user_id']=$six_digit_random_number;
                    $where=array();
                    $where['qr_code_serial_no']=$qr_code_serial_no;
                    $result = self::update_serial_number($data, $where);
    
                    //$output .= '請利用手機按 '.'<a href="https://line.me/ti/p/@490tjxdt">';
                    //$output .= '<img src="https://scdn.line-apps.com/n/line_add_friends/btn/zh-Hant.png" alt="加入好友" height="36" border="0"></a>';
                    //$output .= ' 加入我們的官方帳號, 讓我們成為您的好友,<br> 並在Line聊天室中輸入六位數字註冊密碼: <p style="color:blue">'.$six_digit_random_number.' </p>完成註冊程序<br>';
                }

            } else {
                // send invitation link by URL for the Line@ account
                // https://line.me/ti/p/@490tjxdt
                // <a href="https://lin.ee/LPnyoeD">
                $output .= '請利用手機按 '.'<a href="https://line.me/ti/p/@490tjxdt">';
                $output .= '<img src="https://scdn.line-apps.com/n/line_add_friends/btn/zh-Hant.png" alt="加入好友" height="36" border="0"></a>';
                $output .= ' 加入我們的官方帳號, 讓我們成為您的好友,<br>';

                if( isset($_GET['serial_no']) ) {
                    $output .= 'qr_code_serial_no='.$_GET['serial_no'].'<br>';
                }

                if( isset($_GET['action']) ) {

                    //if( ($_GET['action']=='insert-serial-number') && (isset($_GET['model_number_id'])) && (isset($_GET['specification_id'])) ) {
                    if( ($_GET['action']=='insert-serial-number') && (isset($_GET['model_number_id'])) && (isset($_GET['specification'])) ) {
                        $data=array();
                        $data['model_number_id']=$_GET['model_number_id'];
                        //$data['specification_id']=$_GET['specification_id'];
                        $data['specification']=$_GET['specification'];
                        $result = self::insert_serial_number($data);
                        $output .= $result.'<br>';
                    }

                    if( ($_GET['action']=='update-serial-number') && (isset($_GET['serial_number_id'])) ) {
                        $data=array();
                        if( isset($_GET['model_number_id']) ) {
                            $data['model_number_id']=$_GET['model_number_id'];
                        }
                        //if( isset($_GET['specification_id']) ) {
                        //    $data['specification_id']=$_GET['specification_id'];
                        //}
                        if( isset($_GET['specification']) ) {
                            $data['specification']=$_GET['specification'];
                        }
                        if( isset($_GET['curtain_agent_id']) ) {
                            $data['curtain_agent_id']=$_GET['curtain_agent_id'];
                        }
                        if( isset($_GET['curtain_user_id']) ) {
                            $data['curtain_user_id']=$_GET['curtain_user_id'];
                        }
                        $where=array();
                        $where['serial_number_id']=$_GET['serial_number_id'];
                        $result = self::update_serial_number($data, $where);
                        $output .= $result.'<br>';
                    }
                }
            }
            $output .= '</div>';
            return $output;
        }

        function list_serial_number() {
            global $wpdb;
            if( isset($_POST['where_serial_number']) ) {
                $where='"%'.$_POST['where_serial_number'].'%"';
                $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}serial_number WHERE qr_code_serial_no LIKE {$where}", OBJECT );
                unset($_POST['where_serial_number']);
            } else {
                $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}serial_number", OBJECT );
            }
            $output  = '<h2>Serial Number</h2>';
            $output .= '<figure class="wp-block-table"><table><tbody>';
            $output .= '<tr><td colspan=6 style="text-align:right">';
            $output .= '<form method="post">';
            $output .= '<input type="text" name="where_serial_number" placeholder="Search...">';
            $output .= '<input type="submit" value="Search" name="submit_action">';
            $output .= '</form>';
            $output .= '</td></tr>';
            $output .= '<tr style="background-color:yellow">';
            $output .= '<td></td>';
            $output .= '<td>serial_no</td>';
            $output .= '<td>model</td>';
            $output .= '<td>spec</td>';
            $output .= '<td>user</td>';
            $output .= '<td>update_time</td>';
            $output .= '</tr>';
            foreach ( $results as $index=>$result ) {
                $output .= '<tr>';
                $output .= '<td></td>';
                $output .= '<td><form method="post">';
                $output .= '<input type="submit" value="'.$result->qr_code_serial_no.'" name="_serial_no">';
                $output .= '</form></td>';
                $model = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}model_number WHERE model_number_id = {$result->model_number_id}", OBJECT );
                $output .= '<td>'.$model->model_number.'</td>';
                //$spec = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}specifications WHERE specification_id = {$result->specification_id}", OBJECT );
                //$output .= '<td>'.$spec->specification.'</td>';
                $output .= '<td>'.$result->specification.'</td>';
                $user = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}curtain_users WHERE curtain_user_id = {$result->curtain_user_id}", OBJECT );
                $output .= '<td>'.$user->display_name.'</td>';
                $output .= '<td>'.wp_date( get_option('date_format'), $result->update_timestamp ).' '.wp_date( get_option('time_format'), $result->update_timestamp ).'</td>';
                $output .= '</tr>';
            }
            $output .= '</tbody></table></figure>';

            if( isset($_POST['_serial_no']) ) {
                $output .= '<div id="basic-demo" class="example_content"><div id="qrcode"><div id="qrcode_content">';
                $output .= get_site_url().'/service/?serial_no='.$_POST['_serial_no'].'</div></div></div>';
            }
                
            return $output;
        }

        function insert_serial_number($data=[]) {
            global $wpdb;
            $model_number_id = $data['model_number_id'];
            //$specification_id = $data['specification_id'];
            $model = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}model_number WHERE model_number_id = {$model_number_id}", OBJECT );
            //$spec = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}specifications WHERE specification_id = {$specification_id}", OBJECT );
            //if ((count($model) > 0) && (count($spec) > 0)) {
            if ( count($model) > 0 ) {
                //$qr_code_serial_no = $model->model_number . $spec->specification . time();
                $qr_code_serial_no = $model->model_number . $data['specification'] . time();
                $table = $wpdb->prefix.'serial_number';
                $data = array(
                    'qr_code_serial_no' => $qr_code_serial_no,
                    'model_number_id' => $data['model_number_id'],
                    //'specification_id' => $data['specification_id'],
                    'specification' => $data['specification'],
                    'curtain_agent_id' => $data['curtain_agent_id'],
                    'curtain_user_id' => $data['curtain_user_id'],
                    'create_timestamp' => time(),
                    'update_timestamp' => time(),
                );
                $wpdb->insert($table, $data);
                return $wpdb->insert_id;
            }
        }

        function update_serial_number($data=[], $where=[]) {
            global $wpdb;
            $model_number_id = $data['model_number_id'];
            //$specification_id = $data['specification_id'];
            $specification = $data['specification'];
            $model = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}model_number WHERE model_number_id = {$model_number_id}", OBJECT );
            //$spec = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}specifications WHERE specification_id = {$specification_id}", OBJECT );
            if ((count($model) > 0) && (count($spec) > 0)) {
                //$qr_code_serial_no = $model->model_number . $spec->specification . time();
                $qr_code_serial_no = $model->model_number . $specification . time();
                $data['qr_code_serial_no'] = $qr_code_serial_no;
            }
            $data['update_timestamp'] = time();
            $table = $wpdb->prefix.'serial_number';
            $wpdb->update($table, $data, $where);
        }

        function create_tables() {
            global $wpdb;
            $charset_collate = $wpdb->get_charset_collate();
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
            $sql = "CREATE TABLE `{$wpdb->prefix}serial_number` (
                serial_number_id int NOT NULL AUTO_INCREMENT,
                model_number_id int(10),
                specification_id int(10),
                specification varchar(10),
                curtain_user_id int(10),
                curtain_agent_id int(10),
                qr_code_serial_no varchar(50),
                create_timestamp int(10),
                update_timestamp int(10),
                UNIQUE (qr_code_serial_no),
                PRIMARY KEY (serial_number_id)
            ) $charset_collate;";
            dbDelta($sql);
        }
    }
    new otp_service();
}
?>