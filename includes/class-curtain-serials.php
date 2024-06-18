<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('serial_number')) {
    class serial_number {
        private $_wp_page_title;
        private $_wp_page_postid;
        /**
         * Class constructor
         */
        public function __construct() {
            $this->_wp_page_title = 'Serials';
            $this->_wp_page_postid = general_helps::create_page($this->_wp_page_title, 'serial-number-list');
            add_shortcode( 'serial-number-list', array( $this, 'list_serial_number' ) );
            $this->create_tables();
        }

        public function list_serial_number() {
            global $wpdb;
            $curtain_models = new curtain_models();
            $curtain_agents = new curtain_agents();
            /** Check the permission */
            if ( !is_user_logged_in() ) return '<div style="text-align:center;"><h3>You did not login the system. Please login first.</h3></div>';
            $user = wp_get_current_user();
            //if ( !$user->has_cap('manage_options') ) return '<div style="text-align:center;"><h3>You did not have the cpability to access this system.<br>Please contact the administrator.</h3></div>';

            /** Post the result */
            if( isset($_POST['_create']) ) {
                $this->insert_serial_number(
                    array(
                        'customer_order_number'=>$_POST['_customer_order_number'],
                        'curtain_model_id'=>$_POST['_curtain_model_id'],
                        'specification'=>$_POST['_specification'],
                        'curtain_agent_id'=>$_POST['_curtain_agent_id']
                    )
                );
            }

            if( isset($_POST['_update']) ) {
                $this->update_serial_number(
                    array(
                        'customer_order_number'=>$_POST['_customer_order_number'],
                        'order_item_id'=>$_POST['_order_item_id'],
                        //'curtain_model_id'=>$_POST['_curtain_model_id'],
                        //'curtain_agent_id'=>$_POST['_curtain_agent_id']
                    ),
                    array(
                        'serial_number_id'=>$_POST['_serial_number_id']
                    )
                );
            }

            if( isset($_GET['_delete']) ) {
                $this->delete_serial_number(
                    array(
                        'serial_number_id'=>$_GET['_delete']
                    )
                );
            }

            /** List */
            $output  = '<h2>Serial Number</h2>';
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
            $output .= '<table class="ui-widget ui-widget-content">';
            $output .= '<thead><tr class="ui-widget-header ">';
            $output .= '<th></th>';
            $output .= '<th>serial_no</th>';
            $output .= '<th>model</th>';
            $output .= '<th>spec</th>';
            $output .= '<th>agent</th>';
            $output .= '<th>user</th>';
            $output .= '<th>update_time</th>';
            $output .= '<th></th>';
            $output .= '</tr></thead>';
            
            $output .= '<tbody>';
            
            //if( isset($_GET['_customer_order_number']) ) {
            //    $customer_order_number = $_GET['_customer_order_number'];
            //    $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}serial_number WHERE customer_order_number={$customer_order_number}", OBJECT );
            
            if( isset($_GET['_order_item_id']) ) {
                $order_item_id = $_GET['_order_item_id'];
                $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}serial_number WHERE order_item_id={$order_item_id}", OBJECT );
            } else {
                $results = general_helps::get_search_results($wpdb->prefix.'serial_number', $_POST['_where']);
            }
/*
            if( isset($_GET['_curtain_agent_id']) ) {
                $curtain_agent_id = $_GET['_curtain_agent_id'];
                $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}serial_number WHERE curtain_agent_id={$curtain_agent_id}", OBJECT );
            } else {
                $results = general_helps::get_search_results($wpdb->prefix.'serial_number', $_POST['_where']);
            }
*/            
            foreach ( $results as $index=>$result ) {
                $output .= '<tr>';
                $output .= '<td style="text-align: center;">';
                $output .= '<span id="btn-qrcode-'.$result->qr_code_serial_no.'"><i class="fa-solid fa-qrcode"></i></span>';
                $output .= '</td>';
                $output .= '<td>'.$result->qr_code_serial_no.'</td>';
                //$output .= '<td>'.$curtain_models->get_name($result->curtain_model_id).'</td>';
                $output .= '<td>'.get_the_title($result->curtain_model_id).'</td>';
                $output .= '<td>'.$result->specification.'</td>';
                //$output .= '<td>'.$curtain_agents->get_name($result->curtain_agent_id).'</td>';
                $curtain_agent_name = get_post_meta($result->curtain_agent_id, 'curtain_agent_name', true);
                $output .= '<td>'.$curtain_agent_name.'</td>';
                $user = get_userdata( $result->curtain_user_id );
                $output .= '<td>'.$user->display_name.'</td>';
                $output .= '<td>'.wp_date( get_option('date_format'), $result->update_timestamp ).' '.wp_date( get_option('time_format'), $result->update_timestamp ).'</td>';
                if(wp_get_current_user()->has_cap('manage_options')){
                    $output .= '<td style="text-align: center;">';
                    $output .= '<span id="btn-del-'.$result->serial_number_id.'"><i class="fa-regular fa-trash-can"></i></span>';
                    $output .= '<span style="margin-left:5px;" id="btn-edit-'.$result->serial_number_id.'"><i class="fa-regular fa-pen-to-square"></i></span>';
                    $output .= '</td>';
                }
                $output .= '</tr>';
            }
            $output .= '</tbody></table></div>';

            if( isset($_POST['_add']) ) {
                $output .= '<div id="dialog" title="Create new serial_no">';
                $output .= '<form method="post">';
                $output .= '<fieldset>';
                $output .= '<label for="curtain_model_id">Model</label>';                    
                //$output .= '<select name="_curtain_model_id" id="curtain_model_id">'.$curtain_models->select_options().'</select>';
                $output .= '<select name="_curtain_model_id" id="curtain_model_id">'.$curtain_models->select_curtain_model_options().'</select>';
                $output .= '<label for="specification">Specification</label>';
                $output .= '<input type="text" name="_specification" id="specification" class="text ui-widget-content ui-corner-all">';
                $output .= '<label for="curtain_agent_id">Agent</label>';
                //$output .= '<select name="_curtain_agent_id" id="curtain_agent_id">'.$curtain_agents->select_options().'</select>';
                $output .= '<select name="_curtain_agent_id" id="curtain_agent_id">'.$curtain_agents->select_curtain_agent_options().'</select>';
                $output .= '</fieldset>';
                $output .= '<input class="wp-block-button__link" type="submit" value="Create" name="_create">';
                $output .= '</form>';
                $output .= '</div>';
            }

            if( isset($_GET['_edit']) ) {
                $_id = $_GET['_edit'];
                $row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}serial_number WHERE serial_number_id={$_id}", OBJECT );
                $output .= '<div id="dialog" title="Serial_no update">';
                $output .= '<form method="post">';
                $output .= '<fieldset>';
                $output .= '<input type="hidden" name="_serial_number_id" value="'.$row->serial_number_id.'">';
                $output .= '<label for="customer_order_number">Customer Order Number</label>';
                $output .= '<input type="text" name="_customer_order_number" id="customer_order_number" value="'.$row->customer_order_number.'" class="text ui-widget-content ui-corner-all">';
                $output .= '<label for="order_item_id">Order Item ID</label>';
                $output .= '<input type="text" name="_order_item_id" id="order_item_id" value="'.$row->order_item_id.'" class="text ui-widget-content ui-corner-all">';
/*                
                $output .= '<label for="curtain_model_id">Model</label>';
                $output .= '<select name="_curtain_model_id" id="curtain_model_id">'.$curtain_models->select_options($row->curtain_model_id).'</select>';
                $output .= '<label for="curtain_agent_id">Agent</label>';
                $output .= '<select name="_curtain_agent_id" id="curtain_agent_id">'.$curtain_agents->select_options($row->curtain_agent_id).'</select>';
*/
                $output .= '</fieldset>';
                $output .= '<input class="wp-block-button__link" type="submit" value="Update" name="_update">';
                $output .= '</form>';
                $output .= '</div>';
            }

            if( isset($_GET['_qrcode']) ) {
                $_id = $_GET['_qrcode'];
                $output .= '<div id="dialog" title="QR Code">';
                $output .= '<div id="qrcode">';
                $output .= '<div id="qrcode_content">';
                $output .= get_option('Service').'?serial_no='.$_id;
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
                $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}serial_number WHERE qr_code_serial_no = %s", $_id ), OBJECT );            
                $output .= '<div class="print-me-'.$_id.'">';
                //$output .= '<div id="qrcode1" style="display: inline-block; margin-left: 100px;">';
                $output .= '<div id="qrcode1">';
                $output .= '<div id="qrcode_content">';
                $output .= get_option('Service').'?serial_no='.$_id;
                $output .= '</div>';
                $output .= '</div>';
                $output .= '<p><h1 style="margin-left: 25px;">'.wp_date( get_option('date_format'), $row->create_timestamp ).'</h1></p><br><br><br>';
                //$output .= '<div id="qrcode2" style="display: inline-block;; margin-left: 200px;">';
                $output .= '<div id="qrcode2" style="margin-top: 100px;">';
                $output .= '<div id="qrcode_content">';
                $output .= get_option('Service').'?serial_no='.$_id;
                $output .= '</div>';
                $output .= '</div>';
                $output .= '<p><h1 style="margin-left: 25px;">'.wp_date( get_option('date_format'), $row->create_timestamp ).'</h1></p>';
                $output .= '</div>';                
            }
            return $output;
        }

        public function insert_serial_number($data=[], $_x='') {
            global $wpdb;
            $curtain_models = new curtain_models();
            $curtain_model_id = $data['curtain_model_id'];
            $model = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}curtain_models WHERE curtain_model_id = {$curtain_model_id}", OBJECT );
            if (!(is_null($model) || !empty($wpdb->last_error))) {
                $qr_code_serial_no = $model->curtain_model_name . $data['specification'] . time() . $_x;
                $data['qr_code_serial_no'] = $qr_code_serial_no;
                $data['create_timestamp'] = time();
                $data['update_timestamp'] = time();
                $table = $wpdb->prefix.'serial_number';
                $wpdb->insert($table, $data);
                return $wpdb->insert_id;
            }
        }

        public function update_serial_number($data=[], $where=[]) {
            global $wpdb;
            $table = $wpdb->prefix.'serial_number';
            $data['update_timestamp'] = time();
            $wpdb->update($table, $data, $where);
        }

        public function delete_serial_number($where=[]) {
            global $wpdb;
            $table = $wpdb->prefix.'serial_number';
            $wpdb->delete($table, $where);
        }

        public function create_tables() {
            global $wpdb;
            $charset_collate = $wpdb->get_charset_collate();
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
            $sql = "CREATE TABLE `{$wpdb->prefix}serial_number` (
                serial_number_id int NOT NULL AUTO_INCREMENT,
                qr_code_serial_no varchar(50) UNIQUE,
                customer_order_number varchar(20),
                order_item_id int,
                curtain_model_id int,
                specification varchar(10),
                curtain_agent_id int,
                curtain_user_id int,
                one_time_password int,
                create_timestamp int(10),
                update_timestamp int(10),
                PRIMARY KEY (serial_number_id)
            ) $charset_collate;";
            dbDelta($sql);
        }
    }
    $my_class = new serial_number();
}