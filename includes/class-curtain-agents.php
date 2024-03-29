<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('curtain_agents')) {
    class curtain_agents {
        private $_wp_page_title;
        private $_wp_page_postid;
        /**
         * Class constructor
         */
        public function __construct() {
            $this->create_tables();
            $this->_wp_page_title = 'Agents';
            $this->_wp_page_postid = general_helps::create_page($this->_wp_page_title, 'curtain-agent-list');
            add_shortcode( 'curtain-agent-list', array( $this, 'list_curtain_agents' ) );
            add_action( 'wp_ajax_agent_dialog_get_data', array( $this, 'agent_dialog_get_data' ) );
            add_action( 'wp_ajax_nopriv_agent_dialog_get_data', array( $this, 'agent_dialog_get_data' ) );
            add_action( 'wp_ajax_agent_dialog_save_data', array( $this, 'agent_dialog_save_data' ) );
            add_action( 'wp_ajax_nopriv_agent_dialog_save_data', array( $this, 'agent_dialog_save_data' ) );
        }

        public function list_curtain_agents() {
            global $wpdb;

            /** Check the permission */
            if ( !is_user_logged_in() ) return '<div style="text-align:center;"><h3>You did not login the system. Please login first.</h3></div>';
            $user = wp_get_current_user();
            if ( !$user->has_cap('manage_options') ) return '<div style="text-align:center;"><h3>You did not have the cpability to access this system.<br>Please contact the administrator.</h3></div>';

            /** Post the result */
            if( isset($_POST['_create']) ) {
                $this->insert_curtain_agent(
                    array(
                        'agent_number'=>$_POST['_agent_number'],
                        'agent_password'=>$_POST['_agent_password'],
                        'agent_name'=>$_POST['_agent_name'],
                        'agent_address'=>$_POST['_agent_address'],
                        'contact1'=>$_POST['_contact1'],
                        'phone1'=>$_POST['_phone1'],
                        'contact2'=>$_POST['_contact2'],
                        'phone2'=>$_POST['_phone2']
                    )
                );
            }
        
            if( isset($_POST['_update']) ) {
                $this->update_curtain_agents(
                    array(
                        'agent_number'=>$_POST['_agent_number'],
                        'agent_password'=>$_POST['_agent_password'],
                        'agent_name'=>$_POST['_agent_name'],
                        'agent_address'=>$_POST['_agent_address'],
                        'contact1'=>$_POST['_contact1'],
                        'phone1'=>$_POST['_phone1'],
                        'contact2'=>$_POST['_contact2'],
                        'phone2'=>$_POST['_phone2']
                    ),
                    array(
                        'curtain_agent_id'=>$_POST['_curtain_agent_id']
                    )
                );
                ?><script>window.location.replace("?_update=");</script><?php
            }

            if( isset($_GET['_delete']) ) {
                $this->delete_curtain_agents(
                    array(
                        'curtain_agent_id'=>$_GET['_delete']
                    )
                );
            }

            /** List */
            $output  = '<h2>Curtain Agents</h2>';
            $output .= '<div style="display: flex; justify-content: space-between; margin: 5px;">';
            $output .= '<div>';
            $output .= '<form method="post">';
            //$output .= '<input class="wp-block-button__link" type="submit" value="Create" name="_add">';
            $output .= '</form>';
            $output .= '</div>';
            $output .= '<div style="text-align: right">';
            $output .= '<form method="post">';
            $output .= '<input style="display:inline" type="text" name="_where" placeholder="Search...">';
            $output .= '<input class="wp-block-button__link" type="submit" value="Search" name="submit_action">';
            $output .= '</form>';
            $output .= '</div>';
            $output .= '</div>';

            $output .= '<div class="ui-widget">';
            $output .= '<table id="agents" class="ui-widget ui-widget-content">';
            $output .= '<thead><tr class="ui-widget-header ">';
            $output .= '<th></th>';
            $output .= '<th>agent</th>';
            $output .= '<th>name</th>';
            $output .= '<th>contact</th>';
            $output .= '<th>phone</th>';
            $output .= '<th>address</th>';
            //$output .= '<th>update_time</th>';
            $output .= '<th></th>';
            $output .= '</tr></thead>';

            $output .= '<tbody>';
            $results = general_helps::get_search_results($wpdb->prefix.'curtain_agents', $_POST['_where']);
            foreach ( $results as $index=>$result ) {
                $output .= '<tr>';
                $output .= '<td style="text-align: center;">';
                //$output .= '<span id="btn-edit-'.$result->curtain_agent_id.'"><i class="fa-regular fa-pen-to-square"></i></span>';
                $output .= '<span id="btn-agent-'.$result->curtain_agent_id.'"><i class="fa-regular fa-pen-to-square"></i></span>';
                $output .= '</td>';
                $output .= '<td style="text-align: center;">'.$result->agent_number.'</td>';
                $output .= '<td>'.$result->agent_name.'</td>';
                $output .= '<td style="text-align: center;">'.$result->contact1.'</td>';
                $output .= '<td style="text-align: center;">'.$result->phone1.'</td>';
                $output .= '<td>'.$result->agent_address.'</td>';
                //$output .= '<td>'.wp_date( get_option('date_format'), $result->update_timestamp ).' '.wp_date( get_option('time_format'), $result->update_timestamp ).'</td>';
                $output .= '<td style="text-align: center;">';
                $output .= '<span id="btn-del-'.$result->curtain_agent_id.'"><i class="fa-regular fa-trash-can"></i></span>';
                $output .= '</td>';
                $output .= '</tr>';
            }
            $output .= '<tr><td colspan="7"><div id="btn-agent" style="border:solid; margin:3px; text-align:center; border-radius:5px">+</div></td></tr>';
            $output .= '</tbody></table></div>';

            /** Agent Dialog */
            $output .= '<div id="agent-dialog" title="Agent dialog">';
            $output .= '<fieldset>';
            $output .= '<input type="hidden" id="curtain-agent-id" />';
            $output .= '<div id="agent-zone1">';
            $output .= '<div id="agent-zone1-left" style="display:inline-block; width:45%; margin-right:10px;">';
            $output .= '<label for="curtain-agent-number">Agent Number</label>';
            $output .= '<input type="text" id="curtain-agent-number" />';
            $output .= '</div>';
            $output .= '<div id="agent-zone1-right" style="display:inline-block; width:45%;">';
            $output .= '<label for="curtain-agent-password">Agent Password</label>';
            $output .= '<input type="text" id="curtain-agent-password" />';
            $output .= '</div>';
            $output .= '</div>';

            $output .= '<label for="curtain-agent-name">Agent Name</label>';
            $output .= '<input type="text" id="curtain-agent-name" size="38" />';

            $output .= '<div id="agent-zone2">';
            $output .= '<div id="agent-zone2-left" style="display:inline-block; width:45%; margin-right:10px;">';
            $output .= '<label for="curtain-agent-contact1">Contact</label>';
            $output .= '<input type="text" id="curtain-agent-contact1" />';
            $output .= '</div>';
            $output .= '<div id="agent-zone2-right" style="display:inline-block; width:45%;">';
            $output .= '<label for="curtain-agent-phone1">Phone</label>';
            $output .= '<input type="text" id="curtain-agent-phone1" />';
            $output .= '</div>';
            $output .= '</div>';

            $output .= '<label for="curtain-agent-address">Agent Address</label>';
            $output .= '<input type="text" id="curtain-agent-address" size="38" />';
            $output .= '</fieldset>';
            $output .= '</div>';

            if( isset($_GET['_edit']) ) {
                $_id = $_GET['_edit'];
                $row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}curtain_agents WHERE curtain_agent_id={$_id}", OBJECT );
                $output .= '<div id="dialog" title="Curtain agent update">';
                $output .= '<form method="post">';
                $output .= '<fieldset>';
                $output .= '<input type="hidden" value="'.$row->curtain_agent_id.'" name="_curtain_agent_id">';
                $output .= '<label for="agent-number">Agent Number</label>';
                $output .= '<input type="text" name="_agent_number" value="'.$row->agent_number.'" id="agent-number" class="text ui-widget-content ui-corner-all">';
                $output .= '<label for="agent-password">Agent Password</label>';
                $output .= '<input type="text" name="_agent_password" value="'.$row->agent_password.'" id="agent-password" class="text ui-widget-content ui-corner-all">';
                $output .= '<label for="agent-name">Agent Name</label>';
                $output .= '<input type="text" name="_agent_name" value="'.$row->agent_name.'" id="agent-name" class="text ui-widget-content ui-corner-all">';
                $output .= '<label for="contact1">Contact</label>';
                $output .= '<input type="text" name="_contact1" value="'.$row->contact1.'" id="contact1" class="text ui-widget-content ui-corner-all">';
                $output .= '<label for="phone1">Phone</label>';
                $output .= '<input type="text" name="_phone1" value="'.$row->phone1.'" id="phone1" class="text ui-widget-content ui-corner-all">';
                $output .= '</fieldset>';
                $output .= '<input class="wp-block-button__link" type="submit" value="Update" name="_update">';
                $output .= '</form>';
                $output .= '</div>';
            }

            if( isset($_POST['_add']) ) {
                $output .= '<div id="dialog" title="Create new agent">';
                $output .= '<form method="post">';
                $output .= '<fieldset>';
                $output .= '<label for="agent-number">Agent Number</label>';
                $output .= '<input type="text" name="_agent_number" id="agent-number" class="text ui-widget-content ui-corner-all">';
                $output .= '<label for="agent-password">Agent Password</label>';
                $output .= '<input type="text" name="_agent_password" id="agent-password" class="text ui-widget-content ui-corner-all">';
                $output .= '<label for="agent-name">Agent Name</label>';
                $output .= '<input type="text" name="_agent_name" id="agent-name" class="text ui-widget-content ui-corner-all"';
                $output .= '<label for="contact1">Contact</label>';
                $output .= '<input type="text" name="_contact1" id="contact1" class="text ui-widget-content ui-corner-all"';
                $output .= '<label for="phone1">Phone</label>';
                $output .= '<input type="text" name="_phone1" id="phone1" class="text ui-widget-content ui-corner-all"';
                $output .= '</fieldset>';
                $output .= '<input class="wp-block-button__link" type="submit" value="Create" name="_create">';
                $output .= '</form>';
                $output .= '</div>';
            }
            return $output;
        }

        function agent_dialog_get_data() {
            global $wpdb;
            $_id = $_POST['_id'];
            $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}curtain_agents WHERE curtain_agent_id = %d", $_id ), OBJECT );
            $response = array();
            $response["curtain_agent_number"] = $row->agent_number;
            $response["curtain_agent_password"] = $row->agent_password;
            $response["curtain_agent_name"] = $row->agent_name;
            $response["curtain_agent_contact1"] = $row->contact1;
            $response["curtain_agent_phone1"] = $row->phone1;
            $response["curtain_agent_address"] = $row->agent_address;
            echo json_encode( $response );
            wp_die();
        }

        function agent_dialog_save_data() {
            if( $_POST['_curtain_agent_id']=='' ) {
                $this->insert_curtain_agent(
                    array(
                        'agent_number'=>$_POST['_curtain_agent_number'],
                        'agent_password'=>$_POST['_curtain_agent_password'],
                        'agent_name'=>$_POST['_curtain_agent_name'],
                        'contact1'=>$_POST['_curtain_agent_contact1'],
                        'phone1'=>$_POST['_curtain_agent_phone1'],
                        'agent_address'=>$_POST['_curtain_agent_address'],
                    )
                );
            } else {
                $this->update_curtain_agents(
                    array(
                        'agent_number'=>$_POST['_curtain_agent_number'],
                        'agent_password'=>$_POST['_curtain_agent_password'],
                        'agent_name'=>$_POST['_curtain_agent_name'],
                        'contact1'=>$_POST['_curtain_agent_contact1'],
                        'phone1'=>$_POST['_curtain_agent_phone1'],
                        'agent_address'=>$_POST['_curtain_agent_address'],
                    ),
                    array(
                        'curtain_agent_id'=>$_POST['_curtain_agent_id']
                    )
                );
            }

            $response = array();
            echo json_encode( $response );
            wp_die();
        }

        public function insert_curtain_agent($data=[]) {
            global $wpdb;
            $table = $wpdb->prefix.'curtain_agents';
            $data['create_timestamp'] = time();
            $data['update_timestamp'] = time();
            $wpdb->insert($table, $data);        
            return $wpdb->insert_id;
        }

        public function update_curtain_agents($data=[], $where=[]) {
            global $wpdb;
            $table = $wpdb->prefix.'curtain_agents';
            $data['update_timestamp'] = time();
            $wpdb->update($table, $data, $where);
        }

        public function delete_curtain_agents($where=[]) {
            global $wpdb;
            $table = $wpdb->prefix.'curtain_agents';
            $wpdb->delete($table, $where);
        }

        public function insert_agent_operator($data=[]) {
            global $wpdb;
            $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}agent_operators WHERE curtain_agent_id = %d AND curtain_user_id = %d", $data['curtain_agent_id'], $data['curtain_user_id'] ), OBJECT );
            if (is_null($row) || !empty($wpdb->last_error)) {
                $table = $wpdb->prefix.'agent_operators';
                $data['create_timestamp'] = time();
                $data['update_timestamp'] = time();
                $wpdb->insert($table, $data);        
                return $wpdb->insert_id;    
            } else {
                return $row->agent_operator_id;
            }
        }

        public function delete_agent_operators($where=[]) {
            global $wpdb;
            $table = $wpdb->prefix.'agent_operators';
            $wpdb->delete($table, $where);
        }

        public function get_id( $_name='' ) {
            global $wpdb;
            $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}curtain_agents WHERE agent_number = %s OR agent_name = %s", $_name, $_name ), OBJECT );
            return $row->curtain_agent_id;
        }

        public function get_agent_by_user( $_id=0 ) {
            global $wpdb;
            $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}agent_operators WHERE curtain_user_id = %d", $_id ), OBJECT );
            return $row->curtain_agent_id;
        }

        public function get_name( $_id=0 ) {
            global $wpdb;
            $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}curtain_agents WHERE curtain_agent_id = %d", $_id ), OBJECT );
            return $row->agent_name.'('.$row->agent_number.')';
        }

        public function get_contact( $_id=0 ) {
            global $wpdb;
            $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}curtain_agents WHERE curtain_agent_id = %d", $_id ), OBJECT );
            return $row->contact1;
        }

        public function get_phone( $_id=0 ) {
            global $wpdb;
            $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}curtain_agents WHERE curtain_agent_id = %d", $_id ), OBJECT );
            return $row->phone1;
        }

        public function get_address( $_id=0 ) {
            global $wpdb;
            $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}curtain_agents WHERE curtain_agent_id = %d", $_id ), OBJECT );
            return $row->agent_address;
        }

        public function get_name_by_no( $_no='' ) {
            global $wpdb;
            $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}curtain_agents WHERE agent_number = %s", $_no ), OBJECT );
            return $row->agent_name.'('.$row->agent_number.')';
        }

        public function select_options( $_id=0 ) {
            global $wpdb;
            $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}curtain_agents", OBJECT );
            $output = '<option value="0">-- Select an option --</option>';
            foreach ($results as $result) {
                if ( $result->curtain_agent_id == $_id ) {
                    $output .= '<option value="'.$result->curtain_agent_id.'" selected>';
                } else {
                    $output .= '<option value="'.$result->curtain_agent_id.'">';
                }
                $output .= $result->agent_name.'('.$result->agent_number.')';
                $output .= '</option>';        
            }
            $output .= '<option value="0">-- Remove this --</option>';
            return $output;    
        }

        public function create_tables() {
            global $wpdb;
            $charset_collate = $wpdb->get_charset_collate();
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
            $sql = "CREATE TABLE `{$wpdb->prefix}curtain_agents` (
                curtain_agent_id int NOT NULL AUTO_INCREMENT,
                agent_number varchar(5) UNIQUE,
                agent_password varchar(20),
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

            $sql = "CREATE TABLE `{$wpdb->prefix}agent_operators` (
                agent_operator_id int NOT NULL AUTO_INCREMENT,
                curtain_agent_id int NOT NULL,
                curtain_user_id int NOT NULL,
                create_timestamp int(10),
                update_timestamp int(10),
                PRIMARY KEY (agent_operator_id)
            ) $charset_collate;";
            dbDelta($sql);            
        }
    }
    $my_class = new curtain_agents();
}