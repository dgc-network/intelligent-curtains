<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}
if (!class_exists('event_bot')) {

    class event_bot {

        private $client;

        /**
         * Class constructor
         */
        public function __construct() {
            add_shortcode('event-list', __CLASS__ . '::list_mode');
            add_shortcode('text-message-list', __CLASS__ . '::list_text_message');
            self::create_tables();
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

        function edit_mode( $_id=0, $_mode='' ) {

            if ($_id==0){
                $_mode='Create';
            }

            if( isset($_POST['submit_action']) ) {

                if( $_POST['submit_action']=='Create' ) {
        
                    global $wpdb;
                    $table = $wpdb->prefix.'events';
                    $data = array(
                        //'created_date' => current_time('timestamp'), 
                        'event_title' => $_POST['_event_title'],
                        'event_begin' => $_POST['_event_begin'],
                        'event_end' => $_POST['_event_end'],
                        'event_host' => $_POST['_event_host'],
                    );
                    $format = array('%s', '%d', '%d', '%d');
                    $insert_id = $wpdb->insert($table, $data, $format);
/*    
                    $CreateCourseAction = new CreateCourseAction();                
                    //$CreateCourseAction->setCourseId(intval($_POST['_course_id']));
                    $CreateCourseAction->setCourseId(intval($insert_id));
                    $CreateCourseAction->setCourseTitle($_POST['_course_title']);
                    $CreateCourseAction->setCreatedDate(intval(current_time('timestamp')));
                    //$CreateCourseAction->setListPrice(floatval($_POST['_list_price']));
                    //$CreateCourseAction->setSalePrice(floadval($_POST['_sale_price']));
                    $CreateCourseAction->setPublicKey($_POST['_public_key']);
                    $send_data = $CreateCourseAction->serializeToString();
    
                    $op_result = OP_RETURN_send(OP_RETURN_SEND_ADDRESS, OP_RETURN_SEND_AMOUNT, $send_data);
                
                    if (isset($op_result['error'])) {
    
                        $result_output = 'Error: '.$op_result['error']."\n";
                        return $result_output;
                    } else {
    
                        $table = $wpdb->prefix.'courses';
                        $data = array(
                            'txid' => $op_result['txid'], 
                        );
                        $where = array('course_id' => $insert_id);
                        $wpdb->update( $table, $data, $where );
                    }
*/                    
                }
    
                if( $_POST['submit_action']=='Update' ) {
    /*        
                    $UpdateCourseAction = new UpdateCourseAction();                
                    $UpdateCourseAction->setCourseId(intval($_POST['_course_id']));
                    $UpdateCourseAction->setCourseTitle($_POST['_course_title']);
                    $UpdateCourseAction->setCreatedDate(intval(strtotime($_POST['_created_date'])));
                    //$UpdateCourseAction->setListPrice(floatval($_POST['_list_price']));
                    //$UpdateCourseAction->setSalePrice(floatval($_POST['_sale_price']));
                    $UpdateCourseAction->setPublicKey($_POST['_public_key']);
                    $send_data = $UpdateCourseAction->serializeToString();
    
                    $op_result = OP_RETURN_send(OP_RETURN_SEND_ADDRESS, OP_RETURN_SEND_AMOUNT, $send_data);
    */            
                    if (isset($op_result['error'])) {
                        $result_output = 'Error: '.$op_result['error']."\n";
                        return $result_output;
                    } else {
    
                        global $wpdb;
                        $table = $wpdb->prefix.'events';
                        $data = array(
                            'event_title' => $_POST['_event_title'],
                            'event_begin' => $_POST['_event_begin'],
                            'event_end' => $_POST['_event_end'],
                            'event_host' => $_POST['_event_host'],
                            //'txid' => $op_result['txid'], 
                        );
                        $where = array('event_id' => $_id);
                        $wpdb->update( $table, $data, $where );
                    }
                }
            
                if( $_POST['submit_action']=='Delete' ) {
            
                    global $wpdb;
                    $table = $wpdb->prefix.'events';
                    $where = array('event_id' => $_id);
                    $deleted = $wpdb->delete( $table, $where );
                }

                $_GET['edit_mode']='';
                return self::list_mode();
/*
                ?><script>window.location=window.location.path</script><?php
*/                
            }

            /** 
             * edit_mode
             */
            global $wpdb;
            $row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}events WHERE event_id = {$_id}", OBJECT );
            $output  = '<form method="post">';
            $output .= '<figure class="wp-block-table"><table><tbody>';
            $output .= '<tr><td>'.'Title:'.'</td><td><input style="width: 100%" type="text" name="_event_title" value="'.$row->event_title.'"></td></tr>';
            $output .= '<tr><td>'.'Begin:'.'</td><td><input style="width: 100%" type="text" name="_event_begin" value="'.$row->event_begin.'"></td></tr>';
            $output .= '<tr><td>'.'End:'.'</td><td><input style="width: 100%" type="text" name="_event_end" value="'.$row->event_end.'"></td></tr>';
            $output .= '<tr><td>'.'Host:'.'</td><td><input style="width: 100%" type="text" name="_event_host" value="'.$row->event_host.'"></td></tr>';
            $output .= '</tbody></table></figure>';
    
            if( $_mode=='Create' ) {
                $output .= '<div class="wp-block-buttons">';
                $output .= '<div class="wp-block-button">';
                $output .= '<input class="wp-block-button__link" type="submit" value="Create" name="submit_action">';
                $output .= '</div>';
                $output .= '<div class="wp-block-button">';
                $output .= '<input class="wp-block-button__link" type="submit" value="Cancel" name="submit_action">';
                $output .= '</div>';
                $output .= '</div>';
            } else {
                $output .= '<div class="wp-block-buttons">';
                $output .= '<div class="wp-block-button">';
                $output .= '<input class="wp-block-button__link" type="submit" value="Update" name="submit_action">';
                $output .= '</div>';
                $output .= '<div class="wp-block-button">';
                $output .= '<input class="wp-block-button__link" type="submit" value="Delete" name="submit_action">';
                $output .= '</div>';
                $output .= '</div>';
            }
            $output .= '</form>';
        
            return $output;
        }

        function list_mode() {

            /**
             * List Mode
             */
            global $wpdb;
            $user_id = get_current_user_id();
            //$results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}eventLogs WHERE event_host = {$user_id}", OBJECT );
            $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}eventLogs WHERE event_type = 'message'", OBJECT );
            $output  = '<h2>Message Events</h2>';
            $output .= '<figure class="wp-block-table"><table><tbody>';
            $output .= '<tr><td>Timestamp</td><td>Source</td><td>UserId</td><td>EventObject</td></tr>';
            foreach ( $results as $index=>$result ) {
                $output .= '<tr>';
                //$output .= '<td><a href="?edit_mode=Edit&_id='.$result->event_id.'">'.$result->event_type.'</a></td>';
                $output .= '<td>'.$result->event_timestamp.'</td>';
                $output .= '<td>'.$result->source_type.'</td>';
                $output .= '<td>'.$result->source_user_id.'</td>';
                $output .= '<td>'.$result->event_object.'</td>';
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

        function list_text_message() {

            /**
             * List Mode
             */
            global $wpdb;
            $user_id = get_current_user_id();
            //$results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}eventLogs WHERE event_host = {$user_id}", OBJECT );
            $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}textMessages", OBJECT );
            $output  = '<h2>Text Messages</h2>';
            $output .= '<figure class="wp-block-table"><table><tbody>';
            $output .= '<tr><td>Timestamp</td><td>Message</td><td>Source</td><td>Name</td></tr>';
            foreach ( $results as $index=>$result ) {
                $response = self::line_bot_sdk()->getProfile($result->source_user_id);
                $output .= '<tr>';
                $output .= '<td>'.$result->event_timestamp.'</td>';
                $output .= '<td>'.$result->textMessage_text.'</td>';
                $output .= '<td>'.$result->source_type.'</td>';
                //$output .= '<td>'.$result->webhookEventId.'</td>';
                //$output .= '<td>'.$result->source_user_id.'</td>';
                $output .= '<td>'.$response['displayName'].'</td>';
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
                    break;
                case 'group':
                    $source_type = $event['source']['type'];
                    $user_id = $event['source']['userId'];
                    $group_room_id = $event['source']['groupId'];
                    break;
                case 'group':
                    $source_type = $event['source']['type'];
                    $user_id = $event['source']['userId'];
                    $group_room_id = $event['source']['roomId'];
                    break;
            }

            global $wpdb;
            $table = $wpdb->prefix.'eventLogs';
            $data = array(
                'event_type' => $event['type'],
                'event_timestamp' => time(),
                'source_type' => $source_type,
                'source_user_id' => $user_id,
                'source_group_room_id' => $group_room_id,
                'event_replyToken' => $event['replyToken'],
                'event_mode' => $event['mode'],
                'webhookEventId' => $event['webhookEventId'],
                'isRedelivery' => $event['deliveryContext']['isRedelivery'],
                'event_object' => json_encode($event_object),
            );
            $insert_id = $wpdb->insert($table, $data);        
        }
    
        public function insertTextMessage($event) {

            switch ($event['source']['type']) {
                case 'user':
                    $source_type = $event['source']['type'];
                    $user_id = $event['source']['userId'];
                    break;
                case 'group':
                    $source_type = $event['source']['type'];
                    $user_id = $event['source']['userId'];
                    $group_room_id = $event['source']['groupId'];
                    break;
                case 'group':
                    $source_type = $event['source']['type'];
                    $user_id = $event['source']['userId'];
                    $group_room_id = $event['source']['roomId'];
                    break;
            }

            global $wpdb;
            $table = $wpdb->prefix.'textMessages';
            $data = array(
                'webhookEventId' => $event['webhookEventId'],
                'event_timestamp' => time(),
                'source_type' => $source_type,
                'source_user_id' => $user_id,
                'source_group_room_id' => $group_room_id,
                'textMessage_text' => $event['message']['text'],
            );
            $insert_id = $wpdb->insert($table, $data);        
        }

        function create_tables() {
        
            global $wpdb;
            $charset_collate = $wpdb->get_charset_collate();
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
            $sql = "CREATE TABLE `{$wpdb->prefix}eventLogs` (
                event_id int NOT NULL AUTO_INCREMENT,
                event_type varchar(20),
                event_timestamp int(10),
                source_type varchar(10),
                source_user_id varchar(50),
                source_group_room_id varchar(50),
                event_replyToken varchar(50),
                event_mode varchar(50),
                webhookEventId varchar(50),
                isRedelivery boolean,
                event_object varchar(1000),
                PRIMARY KEY  (event_id)
            ) $charset_collate;";
            dbDelta($sql);
            
            $sql = "CREATE TABLE `{$wpdb->prefix}textMessages` (
                textMessage_id int NOT NULL AUTO_INCREMENT,
                webhookEventId varchar(50),
                event_timestamp int(10),
                source_type varchar(10),
                source_user_id varchar(50),
                source_group_room_id varchar(50),
                event_replyToken varchar(50),
                textMessage_text varchar(255),
                PRIMARY KEY  (textMessage_id)
            ) $charset_collate;";
            dbDelta($sql);
        }        
    }
}
?>