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
            add_shortcode('issue-otp', __CLASS__ . '::issue_otp');
            add_shortcode('serial-number-list', __CLASS__ . '::list_serial_number');
            add_shortcode('curtain-product-list', __CLASS__ . '::list_curtain_product');
            add_shortcode('curtain-user-list', __CLASS__ . '::list_curtain_user');
            self::create_tables();
            //self::delete_records();
        }

        public function line_bot_sdk() {
            $channelAccessToken = '';
            $channelSecret = '';
            $plugin_dir = WP_PLUGIN_DIR . '/line-event-bot';
            if (file_exists($plugin_dir . '/line-bot-sdk-tiny/config.ini')) {
                $config = parse_ini_file($plugin_dir . "/line-bot-sdk-tiny/config.ini", true);
                if ($config['Channel']['Token'] == null || $config['Channel']['Secret'] == null) {
                    error_log("config.ini 配置檔未設定完全！", 0);
                } else {
                    $channelAccessToken = $config['Channel']['Token'];
                    $channelSecret = $config['Channel']['Secret'];
                }
            }
            $client = new LINEBotTiny($channelAccessToken, $channelSecret);
            return $client;
        }

        function product_info( $curtain_qr_code='001' ) {

            if( isset($_POST['submit_action']) && isset($_POST['opt_input']) ) {

                if( $_POST['submit_action']=='確認' ) {

                    // check the $_POST['opt_input'] to match the last_otp field in curtain_users table
                    $row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}curtain_users WHERE curtain_user_id = {$curtain_qr_code}", OBJECT );

                    $client = self::line_bot_sdk();
                    //$client = line_bot_sdk();
                    $client->pushMessage([
                        //'to' => $user_id,
                        'to' => 'U1b08294900a36077765643d8ae14a402',
                        'messages' => [
                            [
                                'type' => 'text',
                                'text' => 'OTP code : '.$_POST['opt_input']
                            ]
                        ]
                    ]);                
                }
                unset($_POST['submit_action']);
            }

            global $wpdb;
            $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}serial_number WHERE curtain_qr_code = {$curtain_qr_code}", OBJECT );
            $output = '<div>';
//            if (count($results) > 0) {
                $output .= '感謝您選購我們的電動窗簾<br>';
                
                foreach ( $results as $index=>$result ) {
                    // find the product information
                    $products = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}curtain_products WHERE curtain_product_id = {$result->curtain_product_id}", OBJECT );
                    foreach ( $products as $index=>$product ) {
                        $output .= '型號:'.$product->product_name.'<br>';
                    }
                }
                $output .= '請輸入我們送到您Line帳號的OTP(一次性密碼):';
                $output .= '<form method="post">';
                $output .= '<input type="text" name="otp_input">';
                $output .= '<div class="wp-block-button">';
                $output .= '<input class="wp-block-button__link" type="submit" value="確認" name="submit_action">';
                $output .= '</div>';
                $output .= '<div class="wp-block-button">';
                $output .= '<input class="wp-block-button__link" type="submit" value="再傳一次" name="submit_action">';
                $output .= '</div>';
                $output .= '</form>';
/*
            } else {
                // send invitation link by URL for the Line@ account
                // https://line.me/ti/p/@490tjxdt
                $output .= '請加入Line@帳號 '.'<a href="https://line.me/ti/p/@490tjxdt">';
                $output .= 'https://line.me/ti/p/@490tjxdt</a>'.' 讓我們成為您的好友,<br>';
                $output .= '並在Line聊天室中重新上傳QR-code圖檔, 完成註冊程序';    
            }
*/
            $output .= '</div>';
            return $output;
        }

        function issue_otp( $user_id='U1b08294900a36077765643d8ae14a402' ) {

            if( isset($_POST['submit_action']) ) {

                if( $_POST['submit_action']=='Issue OTP' ) {
                    //$client = $this->line_bot_sdk();
                    $client = self::line_bot_sdk();
                    $client->pushMessage([
                        //'to' => $user_id,
                        'to' => 'U1b08294900a36077765643d8ae14a402',
                        'messages' => [
                            [
                                'type' => 'text',
                                'text' => 'OTP code : 123456'
                            ]
                        ]
                    ]);                
                }
                unset($_POST['submit_action']);
            }

            $output  = '<form method="post">';
            $output .= '<div class="wp-block-button">';
            $output .= '<input class="wp-block-button__link" type="submit" value="Issue OTP" name="submit_action">';
            $output .= '</div>';
            $output .= '</form>';
            return $output;

        }

        /**
         * List Mode
         */
        function list_mode() {
            global $wpdb;
            $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}eventLogs WHERE event_type = 'message' ORDER BY event_timestamp DESC LIMIT 10", OBJECT );
            $output  = '<h2>Message Events</h2>';
            $output .= '<figure class="wp-block-table"><table><tbody>';
            //$output .= '<tr><td>Timestamp</td><td>EventObject</td><td>Source</td><td>UserId</td></tr>';
            $output .= '<tr><td>User</td><td>EventObject</td><td>Source</td></tr>';
            foreach ( $results as $index=>$result ) {
                if ($result->source_type=='user'){
                    $profile = self::line_bot_sdk()->getProfile($result->source_user_id);
                    $group_name = $profile['displayName'];
                    $group_picture_url = $profile['pictureUrl'];
                    $display_name = $profile['displayName'];
                    $user_picture_url = $profile['pictureUrl'];
                } else {
                    $summary = self::line_bot_sdk()->getGroupSummary($result->source_group_id);
                    $group_name = $summary['groupName'];
                    $group_picture_url = $summary['pictureUrl'];
                    $profile = self::line_bot_sdk()->getGroupMemberProfile($result->source_group_id, $result->source_user_id);
                    $display_name = $profile['displayName'];
                    $user_picture_url = $profile['pictureUrl'];
                }
                $display_message = '';
                $message = json_decode($result->event_object, true);
                
                switch ($message['type']) {
                    case 'text':
                        $display_message = $message['text'];
                        break;
                    case 'image':
                        $content = self::line_bot_sdk()->getContent($message['id']);
                        $display_message = $content;
                        //$display_message = $message['id'];
                        //$display_message = $message['contentProvider']['type'];
                        break;
                    default:
                        $display_message = json_encode($message);
                        break;
                }
                
                $output .= '<tr>';
                //$output .= '<td>'.$result->event_timestamp.'</td>';
                $output .= '<td>'.'<img src="'.$user_picture_url.'" width="50" height="50" style="border-radius: 50%">'.$display_name.'</td>';
                $output .= '<td>'.$display_message.'('.$message['type'].')'.'</td>';
                $output .= '<td>'.'<img src="'.$group_picture_url.'" width="50" height="50" style="border-radius: 50%">'.$group_name.'('.$result->source_type.')'.'</td>';
                $output .= '</tr>';
            }
            $output .= '</tbody></table></figure>';

            $output .= '<form method="get">';
            $output .= '<div class="wp-block-buttons">';
            $output .= '<div class="wp-block-button">';
            $output .= '<input class="wp-block-button__link" type="submit" value="Create" name="edit_mode">';
            $output .= '</div>';
            $output .= '<div class="wp-block-button">';
            //$output .= '<a class="wp-block-button__link" href="/">Cancel</a>';
            $output .= '<input class="wp-block-button__link" type="submit" value="Cancel" name="edit_mode">';
            $output .= '</div>';
            $output .= '</div>';
            $output .= '</form>';

            return $output;
        }

        public function insertEvent($event) {

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
                'event_mode' => $event['mode'],
                'webhookEventId' => $event['webhookEventId'],
                'isRedelivery' => $event['deliveryContext']['isRedelivery'],
                'event_object' => json_encode($event_object),
            );
            $insert_id = $wpdb->insert($table, $data);        
        }
    
        public function insertMessageEvent($event) {

            $message = $event['message'];

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
            $table = $wpdb->prefix.'messageEvents';
            $data = array(
                'event_timestamp' => time(),
                'message_type' => $event['message']['type'],
                'source_type' => $source_type,
                'source_user_id' => $user_id,
                'source_group_id' => $group_id,
                'webhookEventId' => $event['webhookEventId'],
                'event_message' => json_encode($message),
            );
            $insert_id = $wpdb->insert($table, $data);        
        }

        public function insertTextMessage($event) {

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
            $table = $wpdb->prefix.'textMessages';
            $data = array(
                'event_timestamp' => time(),
                'source_type' => $source_type,
                'source_user_id' => $user_id,
                'source_group_id' => $group_id,
                'webhookEventId' => $event['webhookEventId'],
                'textMessage_text' => $event['message']['text'],
            );
            $insert_id = $wpdb->insert($table, $data);        
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
                serial_id int NOT NULL AUTO_INCREMENT,
                curtain_product_id int(10),
                curtain_user_id int(10),
                curtain_qr_code varchar(50),
                create_timestamp int(10),
                update_timestamp int(10),
                PRIMARY KEY (serial_id)
            ) $charset_collate;";
            dbDelta($sql);
            
            $sql = "CREATE TABLE `{$wpdb->prefix}curtain_products` (
                curtain_product_id int NOT NULL AUTO_INCREMENT,
                product_code varchar(50),
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
                last_opt varchar(50),
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