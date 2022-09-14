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
            add_shortcode('product-info', __CLASS__ . '::product_info');
            add_shortcode('serial-number-list', __CLASS__ . '::list_serial_number');
            add_shortcode('curtain-product-list', __CLASS__ . '::list_curtain_products');
            add_shortcode('curtain-user-list', __CLASS__ . '::list_curtain_users');
            self::create_tables();
            //self::delete_records();
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

        function product_info() {

            $last_otp = '';
            $line_user_id = '';
            $curtain_user_id = 0;
            $qr_code_serial_no = $_GET['id'];

            if( isset($_POST['submit_action']) ) {

                if( $_POST['submit_action']=='Confirm' ) {
                    // check the $_POST['otp_input'] to match the last_otp field in curtain_users table
                    if ( $last_otp==$_POST['otp_input'] ) {

                    } else {
                        //$line_user_id = 'U1b08294900a36077765643d8ae14a402';
                        $text_message = 'The '.$_POST['otp_input'].' is a wrong OTP code.';
                        self::push_text_message($text_message, $line_user_id);
                    }
                }

                if( $_POST['submit_action']=='Resend' ) {

                    //$line_user_id = 'U1b08294900a36077765643d8ae14a402';
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

            global $wpdb;
            $row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}serial_number WHERE qr_code_serial_no = {$qr_code_serial_no}", OBJECT );
            $output = '<div>';
            if (count($row) > 0) {

                $curtain_user_id=$row->curtain_user_id;
                $user = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}curtain_users WHERE curtain_user_id = {$curtain_user_id}", OBJECT );
                if (count($user) > 0) {
                    $output .= 'Hi, '.$user->display_name.'<br>';
                }
                $output .= '感謝您選購我們的電動窗簾<br>';
                $curtain_product_id=$row->curtain_product_id;
                $product = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}curtain_products WHERE curtain_product_id = {$curtain_product_id}", OBJECT );
                if (count($product) > 0) {
                    $output .= '型號:'.$product->model_number.' 規格: '.$product->specification.' '.$product->product_name.'<br>';
                }

                if (count($user) > 0) {
                    $last_otp = $user->last_otp;
                    $line_user_id = $user->line_user_id;
                    $output .= '請輸入我們送到您Line帳號的OTP(一次性密碼):';
                    $output .= '<form method="post">';
                    $output .= '<input type="text" name="otp_input">';
                    $output .= '<div class="wp-block-button">';
                    $output .= '<input class="wp-block-button__link" type="submit" value="Confirm" name="submit_action">';
                    $output .= '<input class="wp-block-button__link" type="submit" value="Resend" name="submit_action">';
                    $output .= '</div>';
                    $output .= '</form>';
                } else {
                    // send invitation link by URL for the Line@ account
                    $six_digit_random_number = random_int(100000, 999999);
                    $output .= '請利用手機按 '.'<a href="https://line.me/ti/p/@490tjxdt">';
                    $output .= '<img src="https://scdn.line-apps.com/n/line_add_friends/btn/zh-Hant.png" alt="加入好友" height="36" border="0"></a>';
                    $output .= ' 加入我們的官方帳號, 讓我們成為您的好友,<br> 並在Line聊天室中輸入六位數字註冊密碼: <p style="color:blue">'.$six_digit_random_number.' </p>完成註冊程序<br>';
                    global $wpdb;
                    $table = $wpdb->prefix.'serial_number';
                    $data = array(
                        'curtain_user_id' => intval($six_digit_random_number),
                    );
                    $where = array(
                        'qr_code_serial_no' => $qr_code_serial_no,
                    );
                    $wpdb->update( $table, $data, $where );                
                }

            } else {
                // send invitation link by URL for the Line@ account
                // https://line.me/ti/p/@490tjxdt
                // <a href="https://lin.ee/LPnyoeD">
                $output .= '請利用手機按 '.'<a href="https://line.me/ti/p/@490tjxdt">';
                $output .= '<img src="https://scdn.line-apps.com/n/line_add_friends/btn/zh-Hant.png" alt="加入好友" height="36" border="0"></a>';
                $output .= ' 加入我們的官方帳號, 讓我們成為您的好友,<br>';

                if( isset($_GET['id']) ) {
                    $output .= 'qr_code_serial_no='.$_GET['id'].'<br>';
                }

                if( isset($_GET['action']) ) {

                    if( ($_GET['action']=='insert-curtain-product') && (isset($_GET['model_number'])) && (isset($_GET['specification'])) && (isset($_GET['product_name'])) ) {
                        $data=array();
                        $data['model_number']=$_GET['model_number'];
                        $data['specification']=$_GET['specification'];
                        $data['product_name']=$_GET['product_name'];
                        $result = self::insert_curtain_products($data);
                        $output .= $result.'<br>';
                    }

                    if( ($_GET['action']=='insert-serial-number') && (isset($_GET['curtain_product_id'])) ) {
                        $data=array();
                        $data['curtain_product_id']=$_GET['curtain_product_id'];
                        $result = self::insert_serial_number($data);
                        $output .= $result.'<br>';
                    }

                    if( ($_GET['action']=='insert-curtain-user') && (isset($_GET['line_user_id'])) && (isset($_GET['display_name'])) ) {
                        $data=array();
                        $data['line_user_id']=$_GET['line_user_id'];
                        $data['display_name']=$_GET['display_name'];
                        $result = self::insert_curtain_users($data);
                        $output .= $result.'<br>';
                    }
                }
            }
            $output .= '</div>';
            return $output;
        }

        function list_curtain_products() {
            global $wpdb;
            $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}curtain_products", OBJECT );
            $output  = '<h2>Curtain Products</h2>';
            $output .= '<figure class="wp-block-table"><table><tbody>';
            $output .= '<tr style="background-color:yellow">';
            $output .= '<td>id</td>';
            $output .= '<td>model_number</td>';
            $output .= '<td>specification</td>';
            $output .= '<td>update_time</td>';
            $output .= '</tr>';
            foreach ( $results as $index=>$result ) {
                $output .= '<tr>';
                $output .= '<td>'.$result->curtain_product_id.'</td>';
                $output .= '<td>'.$result->model_number.'</td>';
                $output .= '<td>'.$result->specification.'</td>';
                $output .= '<td>'.wp_date( get_option('time_format'), $result->update_timestamp ).'</td>';
                $output .= '</tr>';
            }
            $output .= '</tbody></table></figure>';
            return $output;
        }

        function list_serial_number() {
            global $wpdb;
            $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}serial_number", OBJECT );
            $output  = '<h2>Serial Number</h2>';
            $output .= '<figure class="wp-block-table"><table><tbody>';
            $output .= '<tr style="background-color:yellow">';
            $output .= '<td>qr_code_serial_no</td>';
            $output .= '<td>model</td>';
            $output .= '<td>spec</td>';
            $output .= '<td>user</td>';
            $output .= '<td>update_time</td>';
            $output .= '</tr>';
            foreach ( $results as $index=>$result ) {
                $output .= '<tr>';
                $output .= '<td>'.$result->qr_code_serial_no.'</td>';
                //$output .= '<td>'.$result->curtain_product_id.'</td>';
                $product = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}curtain_products WHERE curtain_product_id = {$result->curtain_product_id}", OBJECT );
                $output .= '<td>'.$product->model_number.'</td>';
                $output .= '<td>'.$product->specification.'</td>';
                $output .= '<td>'.$result->curtain_user_id.'</td>';
                $output .= '<td>'.wp_date( 'Y/m/d', $result->update_timestamp ).'</td>';
                $output .= '</tr>';
            }
            $output .= '</tbody></table></figure>';
            return $output;
        }

        function list_curtain_users() {
            global $wpdb;
            $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}curtain_users", OBJECT );
            $output  = '<h2>Curtain Users</h2>';
            $output .= '<figure class="wp-block-table"><table><tbody>';
            $output .= '<tr style="background-color:yellow">';
            $output .= '<td>id</td>';
            $output .= '<td>line_user_id</td>';
            $output .= '<td>display_name</td>';
            $output .= '<td>update_time</td>';
            $output .= '</tr>';
            foreach ( $results as $index=>$result ) {
                $output .= '<tr>';
                $output .= '<td>'.$result->curtain_user_id.'</td>';
                $output .= '<td>'.$result->line_user_id.'</td>';
                $output .= '<td>'.$result->display_name.'</td>';
                $output .= '<td>'.wp_date( get_option('date_format'), $result->update_timestamp ).'</td>';
                $output .= '</tr>';
            }
            $output .= '</tbody></table></figure>';
            return $output;
        }

        function insert_curtain_products($data=[]) {
            global $wpdb;
            $table = $wpdb->prefix.'curtain_products';
            $data = array(
                'product_code' => time(),
                'model_number' => $data['model_number'],
                'specification' => $data['specification'],
                'product_name' => $data['product_name'],
                'create_timestamp' => time(),
                'update_timestamp' => time(),
            );
            $wpdb->insert($table, $data);        
        }

        function insert_serial_number($data=[]) {
            $qr_code_serial_no = '';
            $curtain_product_id = intval($data['curtain_product_id']);
            global $wpdb;
            $product = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}curtain_products WHERE curtain_product_id = {$curtain_product_id}", OBJECT );
            if (count($product) > 0) {
                $qr_code_serial_no = $product->model_number . $product->specification . time();
                $table = $wpdb->prefix.'serial_number';
                $data = array(
                    'qr_code_serial_no' => $qr_code_serial_no,
                    'curtain_product_id' => $data['curtain_product_id'],
                    'curtain_user_id' => $data['curtain_user_id'],
                    'create_timestamp' => time(),
                    'update_timestamp' => time(),
                );
                $wpdb->insert($table, $data);
            }
        }

        public function insert_curtain_users($data=[]) {
            $line_user_id = $data['line_user_id'];
            global $wpdb;
            $row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}curtain_users WHERE line_user_id = {$line_user_id}", OBJECT );
            //$results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}curtain_users WHERE line_user_id = {$line_user_id}", OBJECT );
            if (count($row) > 0) {
            //if (count($results) > 0) {
                $table = $wpdb->prefix.'curtain_users';
                $data = array(
                    'line_user_id' => $data['line_user_id'],
                    'display_name' => $data['display_name'],
                    'last_otp' => $data['last_otp'],
                    'update_timestamp' => time(),
                );
                $where = array('line_user_id' => $line_user_id);
                $wpdb->update($table, $data, $where);
                return $row->curtain_user_id;
                //return $results[0]->curtain_user_id;
            } else {
                $table = $wpdb->prefix.'curtain_users';
                $data = array(
                    'line_user_id' => $data['line_user_id'],
                    'display_name' => $data['display_name'],
                    'last_otp' => $data['last_otp'],
                    'create_timestamp' => time(),
                    'update_timestamp' => time(),
                );
                $wpdb->insert($table, $data);
                return $wpdb->insert_id;
            }
        }

        function delete_records() {

            global $wpdb;
            $table = $wpdb->prefix.'eventLogs';
            $where = array('event_timestamp' => 2147483647);
            $deleted = $wpdb->delete( $table, $where );
        }

        function create_tables() {

            global $wpdb;
            $charset_collate = $wpdb->get_charset_collate();
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
            $sql = "CREATE TABLE `{$wpdb->prefix}serial_number` (
                serial_number_id int NOT NULL AUTO_INCREMENT,
                curtain_product_id int(10),
                curtain_user_id int(10),
                qr_code_serial_no varchar(50),
                create_timestamp int(10),
                update_timestamp int(10),
                PRIMARY KEY (serial_number_id)
            ) $charset_collate;";
            dbDelta($sql);
            
            $sql = "CREATE TABLE `{$wpdb->prefix}curtain_products` (
                curtain_product_id int NOT NULL AUTO_INCREMENT,
                product_code varchar(50),
                model_number varchar(5),
                specification varchar(4),
                product_name varchar(50),
                create_timestamp int(10),
                update_timestamp int(10),
                PRIMARY KEY (curtain_product_id)
            ) $charset_collate;";
            dbDelta($sql);
            
            $sql = "CREATE TABLE `{$wpdb->prefix}curtain_users` (
                curtain_user_id int NOT NULL AUTO_INCREMENT,
                line_user_id varchar(50),
                display_name varchar(50),
                last_otp varchar(50),
                create_timestamp int(10),
                update_timestamp int(10),
                PRIMARY KEY (curtain_user_id)
            ) $charset_collate;";
            dbDelta($sql);
        }        
    }

    new otp_service();

}
?>