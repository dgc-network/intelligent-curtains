<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}
if (!class_exists('curtain_users')) {

    class curtain_users {

        /**
         * Class constructor
         */
        public function __construct() {
            add_shortcode('curtain-user-list', __CLASS__ . '::list_curtain_users');
            add_action( 'wp_ajax_startChatSession', array( __CLASS__, 'startChatSession' ) );
            add_action( 'wp_ajax_nopriv_startChatSession', array( __CLASS__, 'startChatSession' ) );
            add_action( 'wp_ajax_sendChat', array( __CLASS__, 'sendChat' ) );
            add_action( 'wp_ajax_nopriv_sendChat', array( __CLASS__, 'sendChat' ) );
            add_action( 'wp_ajax_chatHeartbeat', array( __CLASS__, 'chatHeartbeat' ) );
            add_action( 'wp_ajax_nopriv_chatHeartbeat', array( __CLASS__, 'chatHeartbeat' ) );
            add_action( 'wp_enqueue_scripts', array( __CLASS__, 'my_enqueue' ) );
            add_action( 'init', array( __CLASS__, 'wpse16119876_init_session' ) );
            self::create_tables();

            if (!isset($_SESSION['chatHistory'])) {
                $_SESSION['chatHistory'] = array();	
            }
            
            if (!isset($_SESSION['openChatBoxes'])) {
                $_SESSION['openChatBoxes'] = array();	
            }            
        }

        function my_enqueue() {
            wp_enqueue_script( 'custom-curtain-users', plugin_dir_url( __DIR__ ) . 'assets/js/custom-curtain-users.js', array( 'jquery' ), time(), true );
            wp_localize_script( 'custom-curtain-users', 'my_foobar_client', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
        }

        function wpse16119876_init_session() {
            if ( ! session_id() ) {
                session_start();
            }
        }

        //add_action( 'wp_ajax_startChatSession', 'startChatSession' );
        //add_action( 'wp_ajax_nopriv_startChatSession', 'startChatSession' );
        function startChatSession() {
        
            $items = array();
            if (!empty($_SESSION['openChatBoxes'])) {
                foreach ($_SESSION['openChatBoxes'] as $chatbox => $void) {
                    //array_push($items, chatBoxSession($chatbox));
                    array_push($items, $_SESSION['chatHistory'][$chatbox]);
                }
            }

            $json = array();
            $json['username'] = $_SESSION['username'];
            $json['chatboxtitle'] = $_SESSION['chatboxtitle'];
            $json['items'] = $items;
            $json_encode = json_encode( $json );
            echo $json_encode;
            wp_die();
        
        }
        
        function chatBoxSession($chatbox) {
	
            //$items = '';
            
            $items = array();
            if (isset($_SESSION['chatHistory'][$chatbox])) {
                $items = $_SESSION['chatHistory'][$chatbox];
            }
        
            return $items;
        }
                
        //add_action( 'wp_ajax_chatHeartbeat', 'chatHeartbeat' );
        //add_action( 'wp_ajax_nopriv_chatHeartbeat', 'chatHeartbeat' );
        function chatHeartbeat() {
            
            $sql = "select * from {$wpdb->prefix}chat where ({$wpdb->prefix}chat.to = '".mysql_real_escape_string($_SESSION['username'])."' AND recd = 0) order by id ASC";
            $query = mysql_query($sql);
            //$items = '';
            $items = array();
        
            $chatBoxes = array();
        
            while ($chat = mysql_fetch_array($query)) {
        
                $chat['message'] = sanitize($chat['message']);

                if (!isset($_SESSION['openChatBoxes'][$chat['from']]) && isset($_SESSION['chatHistory'][$chat['from']])) {
                    $items = $_SESSION['chatHistory'][$chat['from']];
                }
                $items['s']=0;
                $items['f']=$chat['from'];
                $items['s']=$chat['message'];
        
        /*
                $items .= <<<EOD
                               {
                    "s": "0",
                    "f": "{$chat['from']}",
                    "m": "{$chat['message']}"
               },
        EOD;
        */
                if (!isset($_SESSION['chatHistory'][$chat['from']])) {
                    //$_SESSION['chatHistory'][$chat['from']] = '';
                    $_SESSION['chatHistory'][$chat['from']] = array();
                }
                $_SESSION['chatHistory'][$chat['from']]['s']=0;
                $_SESSION['chatHistory'][$chat['from']]['f']=$chat['from'];
                $_SESSION['chatHistory'][$chat['from']]['s']=$chat['message'];
            
        /*
            $_SESSION['chatHistory'][$chat['from']] .= <<<EOD
                                   {
                    "s": "0",
                    "f": "{$chat['from']}",
                    "m": "{$chat['message']}"
               },
        EOD;
        */		
                unset($_SESSION['tsChatBoxes'][$chat['from']]);
                $_SESSION['openChatBoxes'][$chat['from']] = $chat['sent'];
            }
        
            if (!empty($_SESSION['openChatBoxes'])) {
                foreach ($_SESSION['openChatBoxes'] as $chatbox => $time) {
                    if (!isset($_SESSION['tsChatBoxes'][$chatbox])) {
                        $now = time()-strtotime($time);
                        $time = date('g:iA M dS', strtotime($time));
        
                        $message = "Sent at $time";
                        if ($now > 180) {
                            $items['s']=2;
                            $items['f']=$chatbox;
                            $items['s']=$message;
        
        /*			
                        $items .= <<<EOD
        {
        "s": "2",
        "f": "$chatbox",
        "m": "{$message}"
        },
        EOD;
        */
                            if (!isset($_SESSION['chatHistory'][$chatbox])) {
                                //$_SESSION['chatHistory'][$chatbox] = '';
                                $_SESSION['chatHistory'][$chatbox] = array();
                            }
                            $_SESSION['chatHistory'][$chatbox]['s']=2;
                            $_SESSION['chatHistory'][$chatbox]['f']=$chatbox;
                            $_SESSION['chatHistory'][$chatbox]['s']=$message;

        /*
            $_SESSION['chatHistory'][$chatbox] .= <<<EOD
                {
        "s": "2",
        "f": "$chatbox",
        "m": "{$message}"
        },
        EOD;
        */
                            $_SESSION['tsChatBoxes'][$chatbox] = 1;
                        }
                    }
                }
            }
        
            $sql = "update {$wpdb->prefix}chat set recd = 1 where {$wpdb->prefix}chat.to = '".mysql_real_escape_string($_SESSION['username'])."' and recd = 0";
            $query = mysql_query($sql);
        /*
            if ($items != '') {
                $items = substr($items, 0, -1);
            }
        
        header('Content-type: application/json');
        ?>
        {
                "items": [
                    <?php echo $items;?>
                ]
        }
        
        <?php
                    exit(0);
        */			
            $json = array();
            $json['items'] = $items;
            echo json_encode( $json );
            wp_die();
        }
        
        

        function sendChat() {

            $from = $_SESSION['username'];
            $to = $_POST['to'];
            $message = $_POST['message'];
        
            $_SESSION['openChatBoxes'][$_POST['to']] = date('Y-m-d H:i:s', time());
            
            $messagesan = sanitize($message);
        
            if (!isset($_SESSION['chatHistory'][$_POST['to']])) {
                //$_SESSION['chatHistory'][$_POST['to']] = '';
                $_SESSION['chatHistory'][$_POST['to']] = array();
            }
            $_SESSION['chatHistory'][$_POST['to']]['s']=1;
            $_SESSION['chatHistory'][$_POST['to']]['f']=$to;
            $_SESSION['chatHistory'][$_POST['to']]['m']=$messagesan;

/*        
            $_SESSION['chatHistory'][$_POST['to']] .= <<<EOD
                               {
                    "s": "1",
                    "f": "{$to}",
                    "m": "{$messagesan}"
               },
        EOD;
*/          

            unset($_SESSION['tsChatBoxes'][$_POST['to']]);
        
            $sql = "insert into {$wpdb->prefix}chat ({$wpdb->prefix}chat.from,{$wpdb->prefix}chat.to,message,sent) values ('".mysql_real_escape_string($from)."', '".mysql_real_escape_string($to)."','".mysql_real_escape_string($message)."',NOW())";
            $query = mysql_query($sql);            
            //echo "1";
          
            wp_die();
        }

        public function list_curtain_users() {
            
            //unset($_SESSION['line_user_id']);
            if( isset($_SESSION['line_user_id']) ) {
                $line_user_id = $_SESSION['line_user_id'];
                global $wpdb;
                $user = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}curtain_users WHERE line_user_id = %s AND user_role= %s", $line_user_id, 'admin' ), OBJECT );            
                if (count($user) == 0 && $_GET['_check_permission'] != 'false') {
                    return 'You are not validated to read this page. Please check to the administrators.';
                }
            } else {
                if ( $_GET['_check_permission'] != 'false' ) {
                    return 'You are not validated to read this page. Please check to the administrators.'.get_option('_check_permission');
                }
            }

            if( isset($_POST['_update']) ) {
                $data=array();
                $data['display_name']=$_POST['_display_name'];
                $data['mobile_phone']=$_POST['_mobile_phone'];
                $data['user_role']=$_POST['_user_role'];
                $where=array();
                $where['curtain_user_id']=$_POST['_curtain_user_id'];
                $result = self::update_curtain_users($data, $where);
            }
        
            global $wpdb;
            if( isset($_POST['_where']) ) {
                $where='"%'.$_POST['_where'].'%"';
                $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}curtain_users WHERE display_name LIKE {$where}", OBJECT );
                unset($_POST['_where']);
            } else {
                $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}curtain_users", OBJECT );
            }
            $output  = '<h2>Curtain Users</h2>';
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
            $output .= '<th>line_user_id</th>';
            $output .= '<th>name</th>';
            $output .= '<th>mobile</th>';
            $output .= '<th>update_time</th>';
            $output .= '</tr></thead>';
            $output .= '<tbody>';
            foreach ( $results as $index=>$result ) {
                $output .= '<tr>';
                $output .= '<td>'.$result->curtain_user_id.'</a></td>';
                $output .= '<td><form method="post">';
                $output .= '<input type="hidden" value="'.$result->curtain_user_id.'" name="_id">';
                $output .= '<input type="submit" value="'.$result->line_user_id.'" name="_update_user">';
                $output .= '</form></td>';
                $output .= '<td><form method="post">';
                $_SESSION['username'] = 'line_bot';
                $_SESSION['chatboxtitle'] = $result->line_user_id;
                $output .= '<input type="hidden" value="'.$result->curtain_user_id.'" name="_id">';
                $output .= '<input type="submit" value="'.$result->display_name.'" name="_chat_user" class="startChatSession">';
                $output .= '</form></td>';
                //$output .= '<td>'.$result->display_name.'</td>';
                $output .= '<td>'.$result->mobile_phone.'</td>';
                $output .= '<td>'.wp_date( get_option('date_format'), $result->update_timestamp ).' '.wp_date( get_option('time_format'), $result->update_timestamp ).'</td>';
                $output .= '</tr>';
            }
            $output .= '</tbody></table></div>';

            if( isset($_POST['_update_user']) && isset($_POST['_id']) ) {
                $_id = $_POST['_id'];
                global $wpdb;
                $row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}curtain_users WHERE curtain_user_id={$_id}", OBJECT );
                if (count($row) > 0) {
                    $output .= '<div id="dialog" title="Curtain user update">';
                    $output .= '<form method="post">';
                    $output .= '<fieldset>';
                    $output .= '<input type="hidden" value="'.$row->curtain_user_id.'" name="_curtain_user_id">';
                    $output .= '<label for="_display_name">Display Name</label>';
                    $output .= '<input type="text" name="_display_name" id="display_name" class="text ui-widget-content ui-corner-all" value="'.$row->display_name.'">';
                    $output .= '<label for="_mobile_phone">Mobile Phone</label>';
                    $output .= '<input type="text" name="_mobile_phone" id="mobile_phone" class="text ui-widget-content ui-corner-all" value="'.$row->mobile_phone.'">';
                    $output .= '<label for="_user_role">User Role</label>';
                    $output .= '<input type="text" name="_user_role" id="user_role" class="text ui-widget-content ui-corner-all" value="'.$row->user_role.'">';
                    $output .= '</fieldset>';
                    $output .= '<input class="wp-block-button__link" type="submit" value="Update" name="_update">';
                    $output .= '<input class="wp-block-button__link" type="submit" value="Delete" name="_delete">';
                    $output .= '</form>';
                    $output .= '</div>';

                }
            }

            if( isset($_POST['_chat_user']) && isset($_POST['_id']) ) {
                $_id = $_POST['_id'];
                global $wpdb;
                $row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}curtain_users WHERE curtain_user_id={$_id}", OBJECT );
                if (count($row) > 0) {
                    $output .= '<div id="dialog" title="Chat with '.$row->display_name.'">';
                    $output .= '<div class="chatboxcontent"></div>';
                    $output .= '<div class="chatboxinput"><textarea class="chatboxtextarea"></textarea></div>';
                    $output .= '</div>';
                }
            }

            return $output;
        }

        public function insert_curtain_user($data=[]) {
            global $wpdb;
            $line_user_id = $data['line_user_id'];
            $row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}curtain_users WHERE line_user_id = {$line_user_id}", OBJECT );
            if (count($row) > 0) {
                return $row->curtain_user_id;
            } else {
                $table = $wpdb->prefix.'curtain_users';
                $data = array(
                    'line_user_id' => $data['line_user_id'],
                    'display_name' => $data['display_name'],
                    'last_otp' => $data['last_otp'],
                    'user_role' => $data['user_role'],
                    'create_timestamp' => time(),
                    'update_timestamp' => time(),
                );
                $wpdb->insert($table, $data);
                return $wpdb->insert_id;
            }
        }

        public function update_curtain_users($data=[], $where=[]) {
            global $wpdb;
            $table = $wpdb->prefix.'curtain_users';
            $data['update_timestamp'] = time();
            $wpdb->update($table, $data, $where);
        }

        function create_tables() {
            global $wpdb;
            $charset_collate = $wpdb->get_charset_collate();
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
            $sql = "CREATE TABLE `{$wpdb->prefix}curtain_users` (
                curtain_user_id int NOT NULL AUTO_INCREMENT,
                line_user_id varchar(50),
                display_name varchar(50),
                mobile_phone varchar(20),
                user_role varchar(20),
                last_otp varchar(10),
                create_timestamp int(10),
                update_timestamp int(10),
                UNIQUE (line_user_id),
                PRIMARY KEY (curtain_user_id)
            ) $charset_collate;";
            dbDelta($sql);

            $sql = "CREATE TABLE `{$wpdb->prefix}chat` (
                `id` int NOT NULL AUTO_INCREMENT,
                `from` varchar(255) NOT NULL DEFAULT '',
                `to` varchar(255) NOT NULL DEFAULT '',
                `message` TEXT NOT NULL,
                `sent` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
                `recd` INTEGER UNSIGNED NOT NULL DEFAULT 0,
                PRIMARY KEY (`id`)
            ) $charset_collate;";
            dbDelta($sql);
        
        }
    }
    new curtain_users();
}