<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('curtain_users')) {
    class curtain_users {
        /**
         * Class constructor
         */
        public function __construct() {
            //add_shortcode('curtain-user-list', array( __CLASS__, 'list_curtain_users' ));
            //add_shortcode('curtain-chat-form', array( __CLASS__, 'curtain_chat_form' ));
            //add_shortcode('chat-message-list', array( __CLASS__, 'list_chat_messages' ));
            //add_action( 'wp_ajax_sendChat', array( __CLASS__, 'sendChat' ) );
            //add_action( 'wp_ajax_nopriv_sendChat', array( __CLASS__, 'sendChat' ) );
            //add_action( 'wp_ajax_chatHeartbeat', array( __CLASS__, 'chatHeartbeat' ) );
            //add_action( 'wp_ajax_nopriv_chatHeartbeat', array( __CLASS__, 'chatHeartbeat' ) );
            //add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_scripts' ) );
            self::create_tables();
        }

        function enqueue_scripts() {
            wp_enqueue_script( 'custom-curtain-users', plugin_dir_url( __DIR__ ) . 'assets/js/custom-curtain-users.js', array( 'jquery' ), time(), true );
        }

        function list_chat_messages() {
            global $wpdb;
            $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}chat_messages", OBJECT );
            //$to = 'Uc12a5ff53a702d188e609709d6ef3edf';
            //$results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}chat_messages WHERE `chat_from` = %s OR `chat_to` = %s", $to, $to ), OBJECT );            
            return var_dump($results);
        }

        function send_chat() {
            $from = $_SESSION['username'];
            $to = $_POST['to'];
            $message = $_POST['message'];

            $data=array();
            $data['chat_from']= esc_sql($from);
            $data['chat_to']= esc_sql($to);
            $data['chat_message']= esc_sql($message);
            $line_webhook = new line_webhook();
            $result = $line_webhook->insert_chat_message($data);

            $response = array();
            $response['currenttime'] = wp_date( get_option('time_format'), time() );
            echo json_encode( $response );
            wp_die();
        }

        function chatHeartbeat() {
            $items = array();
            $items['item']['t']=wp_date( get_option('time_format'), time() );
            $response = array();
            $response['items'] = $items;
            echo json_encode( $response );
            wp_die();
        }

        function chatHeartbeat_backup() {
            
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
                    //setcookie('openChatBoxes',  array());
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
            $response = array();
            $response['items'] = $items;
            echo json_encode( $response );
            wp_die();
        }
        
        

        public function list_curtain_users() {
            
            global $wpdb;
            if( isset($_SESSION['username']) ) {
                $option = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}service_options WHERE service_option_page = %s", '_users_page' ), OBJECT );
                $user = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}curtain_users WHERE line_user_id = %s", $_SESSION['username'] ), OBJECT );
                $permission = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}user_permissions WHERE curtain_user_id = %d AND service_option_id= %d", $user->curtain_user_id, $option->service_option_id ), OBJECT );            
                if (is_null($permission) || !empty($wpdb->last_error)) {
                    if ( $_GET['_check_permission'] != 'false' ) {
                        return 'You have not permission to access this page. Please check to the administrators.';
                    }
                }
            } else {
                if ( $_GET['_check_permission'] != 'false' ) {
                    return 'You have not permission to access this page. Please check to the administrators.';
                }
            }
/*
            if( isset($_GET['_delete']) ) {
                $where=array();
                $where['curtain_user_id']=$_GET['_delete'];
                $result = self::delete_curtain_users($where);
                $result = self::delete_user_permissions($where);
            }
*/
            if( isset($_POST['_update']) ) {
                $data=array();
                $data['display_name']=$_POST['_display_name'];
                $data['mobile_phone']=$_POST['_mobile_phone'];
                $data['curtain_agent_id']=$_POST['_curtain_agent_id'];
                $where=array();
                $where['curtain_user_id']=$_POST['_curtain_user_id'];
                $result = self::update_curtain_users($data, $where);

                $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}service_options WHERE service_option_category LIKE '%admin%' OR service_option_category LIKE '%system%'", OBJECT );
                foreach ($results as $index => $result) {
                    $_checkbox = '_checkbox'.$index;
                    if (isset($_POST[$_checkbox])) {
                        $permission = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}user_permissions WHERE curtain_user_id = %d AND service_option_id= %d", $_POST['_curtain_user_id'], $result->service_option_id ), OBJECT );            
                        if (is_null($permission) || !empty($wpdb->last_error)) {
                            $data=array();
                            $data['curtain_user_id']=$_POST['_curtain_user_id'];
                            $data['service_option_id']=$result->service_option_id;
                            self::insert_user_permission($data);
                        }    
                    } else {
                        $permission = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}user_permissions WHERE curtain_user_id = %d AND service_option_id= %d", $_POST['_curtain_user_id'], $result->service_option_id ), OBJECT );            
                        if (!(is_null($permission) || !empty($wpdb->last_error))) {
                            $where=array();
                            $where['curtain_user_id']=$_POST['_curtain_user_id'];
                            $where['service_option_id']=$result->service_option_id;
                            self::delete_user_permissions($where);
                        }    
                    }
                }
                ?><script>window.location.replace("?_update=");</script><?php
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
            $output .= '<div style="text-align: right; margin: 5px;">';
            $output .= '<form method="post">';
            $output .= '<input style="display:inline" type="text" name="_where" placeholder="Search...">';
            $output .= '<input style="display:inline" type="submit" value="Search" name="submit_action">';
            $output .= '</form>';
            $output .= '</div>';

            $output .= '<div class="ui-widget">';
            $output .= '<table id="users" class="ui-widget ui-widget-content">';
            $output .= '<thead><tr class="ui-widget-header ">';
            $output .= '<th></th>';
            $output .= '<th>line_user_id</th>';
            $output .= '<th>name</th>';
            $output .= '<th>mobile</th>';
            $output .= '<th>update_time</th>';
            $output .= '<th></th>';
            $output .= '</tr></thead>';
            $output .= '<tbody>';
            foreach ( $results as $index=>$result ) {
                $output .= '<tr>';
                $output .= '<td style="text-align: center;">';
                $output .= '<span id="edit-btn-'.$result->curtain_user_id.'"><i class="fa-regular fa-pen-to-square"></i></span>';
                $output .= '</td>';
                $output .= '<td>'.$result->line_user_id.'</td>';
                $output .= '<td>'.$result->display_name.'</td>';
                $output .= '<td>'.$result->mobile_phone.'</td>';
                $output .= '<td>'.wp_date( get_option('date_format'), $result->update_timestamp ).' '.wp_date( get_option('time_format'), $result->update_timestamp ).'</td>';
                $output .= '<td style="text-align: center;">';
                $output .= '<span id="edit-btn-'.$result->curtain_user_id.'"><i class="fa-solid fa-user-tie"></i></span>';
                $output .= '<span>  </span>';
                $output .= '<span id="del-btn-'.$result->curtain_user_id.'"><i class="fa-regular fa-trash-can"></i></span>';
                $output .= '</td>';
                $output .= '</tr>';
            }
            $output .= '</tbody></table></div>';

            if( isset($_GET['_edit']) ) {
                $_id = $_GET['_edit'];
                $curtain_agents = new curtain_agents();
                $row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}curtain_users WHERE curtain_user_id={$_id}", OBJECT );
                $output .= '<div id="dialog" title="Curtain user update">';
                $output .= '<form method="post">';
                $output .= '<fieldset>';
                $output .= '<input type="hidden" value="'.$row->curtain_user_id.'" name="_curtain_user_id">';
                $output .= '<label for="display_name">Display Name</label>';
                $output .= '<input type="text" name="_display_name" id="display_name" class="text ui-widget-content ui-corner-all" value="'.$row->display_name.'">';
                $output .= '<label for="mobile_phone">Mobile Phone</label>';
                $output .= '<input type="text" name="_mobile_phone" id="mobile_phone" class="text ui-widget-content ui-corner-all" value="'.$row->mobile_phone.'">';
                $output .= '<label for="curtain_agent_id">Agent</label>';
                $output .= '<select name="_curtain_agent_id">'.$curtain_agents->select_options($row->curtain_agent_id).'</select>';
                $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}service_options WHERE service_option_category LIKE '%admin%' OR service_option_category LIKE '%system%' ", OBJECT );
                $output .= '<label for="user_permissions">Permissions</label>';
                $output .= '<div style="border: 1px solid; padding: 10px;">';
                foreach ($results as $index => $result) {
                    $output .= '<input style="display: inline-block;" type="checkbox" id="checkbox'.$index.'" name="_checkbox'.$index.'" value="'.$result->service_option_id.'"';
                    $permission = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}user_permissions WHERE curtain_user_id = %d AND service_option_id= %d", $row->curtain_user_id, $result->service_option_id ), OBJECT );            
                    if (is_null($permission) || !empty($wpdb->last_error)) {
                        $output .= '>';
                    } else {
                        $output .= ' checked>';
                    }
                    $output .= '<label style="display: inline-block; margin-left: 8px;" for="checkbox'.$index.'"> '.$result->service_option_title;
                    $output .= '('.$result->service_option_category.')</label><br>';
                }
                $output .= '</div>';        

                $output .= '</fieldset>';
                $output .= '<input class="wp-block-button__link" type="submit" value="Update" name="_update">';
                $output .= '</form>';
                $output .= '</div>';
            }

            if( isset($_POST['_chat_user']) && isset($_POST['_id']) ) {
                $_id = $_POST['_id'];
                $row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}curtain_users WHERE curtain_user_id={$_id}", OBJECT );
                if (!(is_null($row) || !empty($wpdb->last_error))) {
                    $output .= '<div id="dialog" title="Chat with '.$row->display_name.'">';
                    $output .= '<input type="hidden" value="'.$row->line_user_id.'" class="chatboxtitle">';

                    $output .= '<div class="chatboxcontent">';
                    global $wpdb;
                    $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}chat_messages", OBJECT );
                    foreach ( $results as $index=>$result ) {
                        if ($result->chat_to==$row->line_user_id && $result->chat_from==$_SESSION['username']) {
                            $output .= '<div class="chatboxmessage" style="float: right;"><div class="chatboxmessagetime">'.wp_date( get_option('time_format'), $result->create_timestamp ).'</div><div class="chatboxinfo">'.$result->chat_message.'</div></div><div style="clear: right;"></div>';
                        }
                        if ($result->chat_from==$row->line_user_id && $result->chat_to!=$_SESSION['username']) {
                            $output .= '<div class="chatboxmessage"><div class="chatboxmessagefrom">'.$row->display_name.':&nbsp;&nbsp;</div><div class="chatboxmessagecontent">'.$result->chat_message.'</div><div class="chatboxmessagetime">'.wp_date( get_option('time_format'), $result->create_timestamp ).'</div></div>';
                        }
                    }
                    $output .= '</div>';
        
                    $output .= '<div class="chatboxinput"><textarea class="chatboxtextarea"></textarea></div>';
                    $output .= '</div>';
                }
            }

            return $output;
        }

        public function curtain_chat_form() {

            global $wpdb;
            $curtain_users = new curtain_users();

            if( isset($_SESSION['username']) ) {
                //$option = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}service_options WHERE service_option_page = %s", '_chat_form' ), OBJECT );
                //$user = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}curtain_users WHERE line_user_id = %s", $_SESSION['username'] ), OBJECT );
                //$permission = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}user_permissions WHERE curtain_user_id = %d AND service_option_id= %d", $user->curtain_user_id, $option->service_option_id ), OBJECT );            
                $params = array();
                $params['username'] = $_SESSION['username'];
                $params['service_option_page'] = '_chat_form';
                $permission = $curtain_users->check_user_permissions($params);
                if (is_null($permission) || !empty($wpdb->last_error)) {
                    if ( $_GET['_check_permission'] != 'false' ) {
                        return 'You have not permission to access this page. Please check to the administrators.';
                    }
                }
            } else {
                if ( $_GET['_check_permission'] != 'false' ) {
                    return 'You have not permission to access this page. Please check to the administrators.';
                }
            }

            if( isset($_GET['_id']) ) {
                $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}curtain_users WHERE line_user_id = %s", $_GET['_id'] ), OBJECT );
                if (is_null($row) || !empty($wpdb->last_error)) {
                    $output = 'LINE USER ID cannot be found!';
                } else {
                    //$output = '<div id="dialog" title="Chat with '.$row->display_name.'">';
                    $output .= '<input type="hidden" value="'.$row->line_user_id.'" class="chatboxtitle">';

                    $output .= '<div class="chatboxcontent">';
                    $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}chat_messages", OBJECT );
                    foreach ( $results as $index=>$result ) {
                        if ($result->chat_to==$row->line_user_id && $result->chat_from==$_SESSION['username']) {
                            $output .= '<div class="chatboxmessage" style="float: right;"><div class="chatboxmessagetime">'.wp_date( get_option('time_format'), $result->create_timestamp ).'</div><div class="chatboxinfo">'.$result->chat_message.'</div></div><div style="clear: right;"></div>';
                        }
                        if ($result->chat_from==$row->line_user_id && $result->chat_to!=$_SESSION['username']) {
                            $output .= '<div class="chatboxmessage"><div class="chatboxmessagefrom">'.$row->display_name.':&nbsp;&nbsp;</div><div class="chatboxmessagecontent">'.$result->chat_message.'</div><div class="chatboxmessagetime">'.wp_date( get_option('time_format'), $result->create_timestamp ).'</div></div>';
                        }
                    }
                    $output .= '</div>';
        
                    $output .= '<div class="chatboxinput"><textarea class="chatboxtextarea"></textarea></div>';
                    //$output .= '</div>';
                }
            } else {
                $output = 'LINE USER ID cannot be found!';
            }
            return $output;
        }

        public function insert_curtain_user($data=[]) {
            global $wpdb;
            $line_user_id = $data['line_user_id'];
            $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}curtain_users WHERE line_user_id = %s", $line_user_id ), OBJECT );            
            if ( is_null($row) || !empty($wpdb->last_error) ) {
                $table = $wpdb->prefix.'curtain_users';
                $data['create_timestamp'] = time();
                $data['update_timestamp'] = time();
                $wpdb->insert($table, $data);
                return $wpdb->insert_id;
            } else {
                return $row->curtain_user_id;
            }
        }

        public function update_curtain_users($data=[], $where=[]) {
            global $wpdb;
            $table = $wpdb->prefix.'curtain_users';
            $data['update_timestamp'] = time();
            $wpdb->update($table, $data, $where);
        }

        public function delete_curtain_users($where=[]) {
            global $wpdb;
            $table = $wpdb->prefix.'curtain_users';
            $wpdb->delete($table, $where);
        }

        public function insert_user_permission($data=[]) {
            global $wpdb;
            $table = $wpdb->prefix.'user_permissions';
            $data['create_timestamp'] = time();
            $wpdb->insert($table, $data);
        }

        public function delete_user_permissions($where=[]) {
            global $wpdb;
            $table = $wpdb->prefix.'user_permissions';
            $wpdb->delete($table, $where);
        }

        public function check_user_permissions($params=[]) {
            global $wpdb;
            if (!isset($params['username'])) {
                $params['username'] = $_SESSION['username'];
            }
            $option = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}service_options WHERE service_option_page = %s", $params['service_option_page'] ), OBJECT );
            $user = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}curtain_users WHERE line_user_id = %s", $params['username'] ), OBJECT );
            $permission = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}user_permissions WHERE curtain_user_id = %d AND service_option_id= %d", $user->curtain_user_id, $option->service_option_id ), OBJECT );            
            if (is_null($permission) || !empty($wpdb->last_error)) {
                return null;
            } else {
                return true;
            }
        }

        public function get_id( $_id=0 ) {
            global $wpdb;
            $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}curtain_users WHERE curtain_user_id = %d", $_id ), OBJECT );
            return $row->line_user_id;
        }

        public function get_name( $_id=0 ) {
            global $wpdb;
            $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}curtain_users WHERE curtain_user_id = %d", $_id ), OBJECT );
            return $row->display_name;
        }

        function create_tables() {
            global $wpdb;
            $charset_collate = $wpdb->get_charset_collate();
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

            $sql = "CREATE TABLE {$wpdb->prefix}curtain_users (
                curtain_user_id int NOT NULL AUTO_INCREMENT,
                line_user_id varchar(50) UNIQUE,
                display_name varchar(50),
                mobile_phone varchar(20),
                curtain_agent_id int(10),
                user_role varchar(20),
                create_timestamp int(10),
                update_timestamp int(10),
                PRIMARY KEY (curtain_user_id)
            ) $charset_collate;";
            dbDelta($sql);

            $sql = "CREATE TABLE {$wpdb->prefix}user_permissions (
                user_permission_id int NOT NULL AUTO_INCREMENT,
                curtain_user_id int NOT NULL,
                service_option_id int NOT NULL,
                create_timestamp int(10),
                PRIMARY KEY (user_permission_id)
            ) $charset_collate;";
            dbDelta($sql);
        }
    }
    $curtain_users = new curtain_users();
    add_shortcode( 'curtain-user-list', array( $curtain_users, 'list_curtain_users' ) );
    add_shortcode( 'curtain-chat-form', array( $curtain_users, 'curtain_chat_form' ) );
    add_action( 'wp_ajax_send_chat', array( $curtain_users, 'send_chat' ) );
    add_action( 'wp_ajax_nopriv_send_chat', array( $curtain_users, 'send_chat' ) );
}