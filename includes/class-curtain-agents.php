<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('curtain_agents')) {
    class curtain_agents {
        /**
         * Class constructor
         */
        public function __construct() {
            $this->create_tables();
        }

        public function list_curtain_agents() {
            global $wpdb;
            $curtain_service = new curtain_service();

            if( isset($_SESSION['line_user_id']) ) {
                $_option_page = 'Agents';
                $permission = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}user_permissions WHERE line_user_id = %s AND service_option_id= %d", $_SESSION['line_user_id'], $curtain_service->get_id($_option_page) ), OBJECT );            
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

            if( isset($_POST['_create']) ) {
                $data=array();
                $data['agent_number']=$_POST['_agent_number'];
                $data['agent_name']=$_POST['_agent_name'];
                $data['agent_address']=$_POST['_agent_address'];
                $data['contact1']=$_POST['_contact1'];
                $data['phone1']=$_POST['_phone1'];
                $data['contact2']=$_POST['_contact2'];
                $data['phone2']=$_POST['_phone2'];
                $this->insert_curtain_agent($data);
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
                $this->update_curtain_agents($data, $where);
                ?><script>window.location.replace("?_update=");</script><?php
            }

            if( isset($_GET['_delete']) ) {
                $where=array();
                $where['curtain_agent_id']=$_GET['_delete'];
                $this->delete_curtain_agents($where);
            }

            if( isset($_POST['_where']) ) {
                $where='"%'.$_POST['_where'].'%"';
                $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}curtain_agents WHERE agent_name LIKE {$where}", OBJECT );
                unset($_POST['_where']);
            } else {
                $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}curtain_agents", OBJECT );
            }
            $output  = '<h2>Curtain Agents</h2>';
            $output .= '<div style="display: flex; justify-content: space-between; margin: 5px;">';
            $output .= '<div>';
            $output .= '<form method="post">';
            $output .= '<input class="wp-block-button__link" type="submit" value="Create" name="_add">';
            $output .= '</form>';
            $output .= '</div>';
            $output .= '<div style="text-align: right">';
            $output .= '<form method="post">';
            $output .= '<input style="display:inline" type="text" name="_where" placeholder="Search...">';
            $output .= '<input style="display:inline" type="submit" value="Search" name="submit_action">';
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
            $output .= '<th>update_time</th>';
            $output .= '<th></th>';
            $output .= '</tr></thead>';
            $output .= '<tbody>';
            foreach ( $results as $index=>$result ) {
                $output .= '<tr>';
                $output .= '<td style="text-align: center;">';
                $output .= '<span id="edit-btn-'.$result->curtain_agent_id.'"><i class="fa-regular fa-pen-to-square"></i></span>';
                $output .= '</td>';
                $output .= '<td style="text-align: center;">'.$result->agent_number.'</td>';
                $output .= '<td>'.$result->agent_name.'</td>';
                $output .= '<td style="text-align: center;">'.$result->contact1.'</td>';
                $output .= '<td style="text-align: center;">'.$result->phone1.'</td>';
                $output .= '<td>'.wp_date( get_option('date_format'), $result->update_timestamp ).' '.wp_date( get_option('time_format'), $result->update_timestamp ).'</td>';
                $output .= '<td style="text-align: center;">';
                $output .= '<span id="qrcode-btn-'.$result->qr_code_agent_no.'"><i class="fa-solid fa-qrcode"></i></span>';
                $output .= '<span> </span>';
                $output .= '<span id="del-btn-'.$result->curtain_agent_id.'"><i class="fa-regular fa-trash-can"></i></span>';
                $output .= '</td>';
            $output .= '</tr>';
            }
            $output .= '</tbody></table></div>';

            if( isset($_GET['_qrcode']) ) {
                $_id = $_GET['_qrcode'];
                $output .= '<div id="dialog" title="QR Code">';
                $output .= '<div id="qrcode">';
                $output .= '<div id="qrcode_content">';
                //$output .= get_site_url().'/'.get_option('_service_page').'/?serial_no='.$_id;
                $output .= get_site_url().'/'.$curtain_service->get_link('Agents').'/?agent_no='.$_id;
                $output .= '</div>';
                $output .= '</div>';
                $output .= '<div style="display: flex;">';
                $print_me = do_shortcode('[print-me target=".print-me-'.$_id.'"/]');
                $output .= $print_me;
                $output .= '<span> </span>';
                $output .= '<span>'.$_id.'</span>';
                $output .= '</div>';
                $output .= '</div>';
                
                $output .= '<br><br><br><br><br>';                
                $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}curtain_agents WHERE qr_code_agent_no = %s", $_id ), OBJECT );            
                $output .= '<div class="print-me-'.$_id.'">';
                //$output .= '<div id="qrcode1" style="display: inline-block; margin-left: 100px;">';
                $output .= '<div id="qrcode1">';
                $output .= '<div id="qrcode_content">';
                //$output .= get_site_url().'/'.get_option('_service_page').'/?serial_no='.$_id;
                $output .= get_site_url().'/'.$curtain_service->get_link('Agents').'/?agent_no='.$_id;
                $output .= '</div>';
                $output .= '</div>';
                $output .= '<p><h1 style="margin-left: 25px;">'.wp_date( get_option('date_format'), $row->create_timestamp ).'</h1></p><br><br><br>';
                //$output .= '<div id="qrcode2" style="display: inline-block;; margin-left: 200px;">';
                $output .= '<div id="qrcode2" style="margin-top: 100px;">';
                $output .= '<div id="qrcode_content">';
                //$output .= get_site_url().'/'.get_option('_service_page').'/?serial_no='.$_id;
                $output .= get_site_url().'/'.$curtain_service->get_link('Agents').'/?agent_no='.$_id;
                $output .= '</div>';
                $output .= '</div>';
                $output .= '<p><h1 style="margin-left: 25px;">'.wp_date( get_option('date_format'), $row->create_timestamp ).'</h1></p>';
                $output .= '</div>';                
            }

            if( isset($_GET['_edit']) ) {
                $_id = $_GET['_edit'];
                $row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}curtain_agents WHERE curtain_agent_id={$_id}", OBJECT );
                $output .= '<div id="dialog" title="Curtain agent update">';
                $output .= '<form method="post">';
                $output .= '<fieldset>';
                $output .= '<input type="hidden" value="'.$row->curtain_agent_id.'" name="_curtain_agent_id">';
                $output .= '<label for="agent_number">Agent Number</label>';
                $output .= '<input type="text" name="_agent_number" id="agent_number" class="text ui-widget-content ui-corner-all" value="'.$row->agent_number.'">';
                $output .= '<label for="agent_name">Agent Name</label>';
                $output .= '<input type="text" name="_agent_name" id="agent_name" class="text ui-widget-content ui-corner-all" value="'.$row->agent_name.'">';
                $output .= '<label for="contact1">Contact</label>';
                $output .= '<input type="text" name="_contact1" id="contact1" class="text ui-widget-content ui-corner-all" value="'.$row->contact1.'">';
                $output .= '<label for="phone1">Phone</label>';
                $output .= '<input type="text" name="_phone1" id="phone1" class="text ui-widget-content ui-corner-all" value="'.$row->phone1.'">';
                $output .= '</fieldset>';
                $output .= '<input class="wp-block-button__link" type="submit" value="Update" name="_update">';
                $output .= '</form>';
                $output .= '</div>';
            }

            if( isset($_POST['_add']) ) {
                $output .= '<div id="dialog" title="Create new agent">';
                $output .= '<form method="post">';
                $output .= '<fieldset>';
                $output .= '<label for="agent_number">Agent Number</label>';
                $output .= '<input type="text" name="_agent_number" id="agent_number" class="text ui-widget-content ui-corner-all">';
                $output .= '<label for="agent_name">Agent Name</label>';
                $output .= '<input type="text" name="_agent_name" id="agent_name" class="text ui-widget-content ui-corner-all"';
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

        public function insert_curtain_agent($data=[]) {
            global $wpdb;
            $table = $wpdb->prefix.'curtain_agents';
            $qr_code_agent_no = $data['agent_number'] . time();
            $data['qr_code_agent_no'] = $qr_code_agent_no;
            $data['create_timestamp'] = time();
            $data['update_timestamp'] = time();
            $wpdb->insert($table, $data);        
            return $wpdb->insert_id;
        }

        public function update_curtain_agents($data=[], $where=[]) {
            global $wpdb;
            $table = $wpdb->prefix.'curtain_agents';
            $qr_code_agent_no = $data['agent_number'] . time();
            $data['qr_code_agent_no'] = $qr_code_agent_no;
            $data['update_timestamp'] = time();
            $wpdb->update($table, $data, $where);
        }

        public function delete_curtain_agents($where=[]) {
            global $wpdb;
            $table = $wpdb->prefix.'curtain_agents';
            $wpdb->delete($table, $where);
        }

        public function get_name( $_id=0 ) {
            global $wpdb;
            $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}curtain_agents WHERE curtain_agent_id = %d", $_id ), OBJECT );
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
                $output .= $result->agent_number.'/'.$result->agent_name;
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
                qr_code_agent_no varchar(50) UNIQUE,
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
    $my_class = new curtain_agents();
    add_shortcode( 'curtain-agent-list', array( $my_class, 'list_curtain_agents' ) );
}