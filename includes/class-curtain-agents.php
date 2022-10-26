<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}
if (!class_exists('curtain_agents')) {

    class curtain_agents {

        /**
         * Class constructor
         */
        public function __construct() {
            add_shortcode('curtain-agent-list', __CLASS__ . '::list_curtain_agents');
            add_action( 'init', array( __CLASS__, 'wpse16119876_init_session' ) );
            self::create_tables();
        }

        function wpse16119876_init_session() {
            if ( ! session_id() ) {
                session_start();
            }
        }

        public function list_curtain_agents() {

            if( isset($_SESSION['line_user_id']) ) {
                $line_user_id = $_SESSION['line_user_id'];
                global $wpdb;
                $user = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}curtain_users WHERE line_user_id = %s AND user_role= %s", $line_user_id, 'admin' ), OBJECT );            
                if (count($user) == 0) {
                    return 'You are not validated to read this page. Please check to the administrators.';
                }
            } else {
                if ( $_GET['_check_permission'] != 'false' ) {
                    return 'You are not validated to read this page. Please check to the administrators.'.get_option('_check_permission');
                }
            }

            if( isset($_POST['_create']) ) {
                $data=array();
                $data['agent_number']=$_POST['_agent_number'];
                $data['agent_name']=$_POST['_agent_name'];
                $data['agent_address']=$_POST['_agent_address'];
                $data['contact1']=$_POST['_contact1'];
                $data['phone1']=$_POST['_phone1'];
                $data['contact2']=$_POST['_contact2'];
                $data['phone2']=$_POST['_phone2'];
                $result = self::insert_curtain_agent($data);
            }
        
            if( isset($_POST['_update']) ) {
                $data=array();
                $data['agent_number']=$_POST['_agent_number'];
                $data['agent_name']=$_POST['_agent_name'];
                $data['agent_address']=$_POST['_agent_address'];
                $data['contact1']=$_POST['_contact1'];
                $data['phone1']=$_POST['_phone1'];
                $data['contact2']=$_POST['_contact2'];
                $data['phone2']=$_POST['_phone2'];
                $where=array();
                $where['curtain_agent_id']=$_POST['_curtain_agent_id'];
                $result = self::update_curtain_agents($data, $where);
            }

            global $wpdb;
            if( isset($_POST['_where']) ) {
                $where='"%'.$_POST['_where'].'%"';
                $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}curtain_agents WHERE agent_name LIKE {$where}", OBJECT );
                unset($_POST['_where']);
            } else {
                $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}curtain_agents", OBJECT );
            }
            $output  = '<h2>Curtain Agents</h2>';
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
            $output .= '<th>agent</th>';
            $output .= '<th>name</th>';
            $output .= '<th>contact</th>';
            $output .= '<th>phone</th>';
            $output .= '<th>update_time</th>';
            $output .= '</tr></thead>';
            $output .= '<tbody>';
            foreach ( $results as $index=>$result ) {
                $output .= '<tr>';
                $output .= '<td>'.$result->curtain_agent_id.'</a></td>';
                $output .= '<td><form method="post">';
                $output .= '<input type="hidden" value="'.$result->curtain_agent_id.'" name="_id">';
                $output .= '<input type="submit" value="'.$result->agent_number.'">';
                $output .= '</form></td>';
                $output .= '<td>'.$result->agent_name.'</td>';
                $output .= '<td>'.$result->contact1.'</td>';
                $output .= '<td>'.$result->phone1.'</td>';
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
                $row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}curtain_agents WHERE curtain_agent_id={$_id}", OBJECT );
                if (count($row) > 0) {
                    $output .= '<div id="dialog" title="Curtain agent update">';
                    $output .= '<form method="post">';
                    $output .= '<fieldset>';
                    $output .= '<input type="hidden" value="'.$row->curtain_agent_id.'" name="_curtain_agent_id">';
                    $output .= '<label for="_agent_number">Agent Number</label>';
                    $output .= '<input type="text" name="_agent_number" id="agent_number" class="text ui-widget-content ui-corner-all" value="'.$row->agent_number.'">';
                    $output .= '<label for="_agent_name">Agent Name</label>';
                    $output .= '<input type="text" name="_agent_name" id="agent_name" class="text ui-widget-content ui-corner-all" value="'.$row->agent_name.'">';
                    $output .= '<label for="_contact1">Contact</label>';
                    $output .= '<input type="text" name="_contact1" id="contact1" class="text ui-widget-content ui-corner-all" value="'.$row->contact1.'">';
                    $output .= '<label for="_phone1">Phone</label>';
                    $output .= '<input type="text" name="_phone1" id="phone1" class="text ui-widget-content ui-corner-all" value="'.$row->phone1.'">';
                    $output .= '</fieldset>';
                    $output .= '<input class="wp-block-button__link" type="submit" value="Update" name="_update">';
                    $output .= '<input class="wp-block-button__link" type="submit" value="Delete" name="_delete">';
                    $output .= '</form>';
                    $output .= '</div>';
                } else {
                    $output .= '<div id="dialog" title="Create new agent">';
                    $output .= '<form method="post">';
                    $output .= '<fieldset>';
                    $output .= '<label for="_agent_number">Agent Number</label>';
                    $output .= '<input type="text" name="_agent_number" id="agent_number" class="text ui-widget-content ui-corner-all">';
                    $output .= '<label for="_agent_name">Agent Name</label>';
                    $output .= '<input type="text" name="_agent_name" id="agent_name" class="text ui-widget-content ui-corner-all"';
                    $output .= '<label for="_contact1">Contact</label>';
                    $output .= '<input type="text" name="_contact1" id="contact1" class="text ui-widget-content ui-corner-all"';
                    $output .= '<label for="_phone1">Phone</label>';
                    $output .= '<input type="text" name="_phone1" id="phone1" class="text ui-widget-content ui-corner-all"';
                    $output .= '</fieldset>';
                    $output .= '<input class="wp-block-button__link" type="submit" value="Create" name="_create">';
                    $output .= '<input class="wp-block-button__link" type="submit" value="Cancel"';
                    $output .= '</form>';
                    $output .= '</div>';
                }
            }

            return $output;
        }

        function insert_curtain_agent($data=[]) {
            global $wpdb;
            $table = $wpdb->prefix.'curtain_agents';
            $data = array(
                'agent_number' => $data['agent_number'],
                'agent_name' => $data['agent_name'],
                'agent_address' => $data['agent_address'],
                'contact1' => $data['contact1'],
                'phone1' => $data['phone1'],
                'contact2' => $data['contact2'],
                'phone2' => $data['phone2'],
                'create_timestamp' => time(),
                'update_timestamp' => time(),
            );
            $wpdb->insert($table, $data);        
            return $wpdb->insert_id;
        }

        function update_curtain_agents($data=[], $where=[]) {
            global $wpdb;
            $table = $wpdb->prefix.'curtain_agents';
            $data['update_timestamp'] = time();
            $wpdb->update($table, $data, $where);
        }

        function select_options( $default_id=null ) {
            global $wpdb;
            $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}curtain_agents", OBJECT );
            $output = '<option value="no_select">-- Select an option --</option>';
            foreach ($results as $index => $result) {
                if ( $results[$index]->curtain_agent_id == $default_id ) {
                    $output .= '<option value="'.$results[$index]->curtain_agent_id.'" selected>';
                } else {
                    $output .= '<option value="'.$results[$index]->curtain_agent_id.'">';
                }
                $output .= $results[$index]->agent_number.'/'.$results[$index]->agent_name;
                $output .= '</option>';        
            }
            $output .= '<option value="delete_select">-- Remove this --</option>';
            return $output;    
        }

        function create_tables() {
            global $wpdb;
            $charset_collate = $wpdb->get_charset_collate();
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
            $sql = "CREATE TABLE `{$wpdb->prefix}curtain_agents` (
                curtain_agent_id int NOT NULL AUTO_INCREMENT,
                agent_number varchar(5),
                agent_name varchar(50),
                agent_address varchar(250),
                contact1 varchar(20),
                phone1 varchar(20),
                contact2 varchar(20),
                phone2 varchar(20),
                create_timestamp int(10),
                update_timestamp int(10),
                PRIMARY KEY (curtain_agent_id)
            ) $charset_collate;";
            dbDelta($sql);            
        }
    }
    new curtain_agents();
}