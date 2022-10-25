<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}
if (!class_exists('serial_number')) {

    class serial_number {

        /**
         * Class constructor
         */
        public function __construct() {
            add_shortcode('serial-number-list', __CLASS__ . '::list_model_serial');
            self::create_tables();
        }

        function list_serial_number() {
/*
            if( isset($_POST['_mode']) || isset($_POST['_id']) ) {
                //return self::edit_serial_number($_POST['_id'], $_POST['_mode']);
            }

            if( ($_GET['action']=='insert-serial-number') && (isset($_GET['curtain_model_id'])) && (isset($_GET['specification']))  && (isset($_GET['curtain_agent_id']))) {
                $data=array();
                $data['curtain_model_id']=$_GET['curtain_model_id'];
                $data['specification']=$_GET['specification'];
                $data['curtain_agent_id']=$_GET['curtain_agent_id'];
                $result = self::insert_serial_number($data);
                $output .= $result.'<br>';
            }

            if( ($_GET['action']=='update-serial-number') && (isset($_GET['serial_number_id'])) ) {
                $data=array();
                if( isset($_GET['curtain_model_id']) ) {
                    $data['curtain_model_id']=$_GET['curtain_model_id'];
                }
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
//
            global $wpdb;
            if( isset($_POST['_where_serial_number']) ) {
                $where='"%'.$_POST['_where_serial_number'].'%"';
                $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}serial_number WHERE qr_code_serial_no LIKE {$where}", OBJECT );
                unset($_POST['_where_serial_number']);
            } else {
                $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}serial_number", OBJECT );
            }
            $output  = '<h2>Serial Number</h2>';
            $output .= '<figure class="wp-block-table"><table><tbody>';
            $output .= '<tr><td colspan=6 style="text-align:right">';
            $output .= '<form method="post">';
            $output .= '<input type="text" name="_where_serial_number" placeholder="Search...">';
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
                $model = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}curtain_models WHERE curtain_model_id = {$result->curtain_model_id}", OBJECT );
                $output .= '<td>'.$model->curtain_model_name.'</td>';
                $output .= '<td>'.$result->specification.'</td>';
                $user = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}curtain_users WHERE curtain_user_id = {$result->curtain_user_id}", OBJECT );
                $output .= '<td>'.$user->display_name.'</td>';
                $output .= '<td>'.wp_date( get_option('date_format'), $result->update_timestamp ).' '.wp_date( get_option('time_format'), $result->update_timestamp ).'</td>';
                $output .= '</tr>';
            }
            $output .= '</tbody></table></figure>';
*/
            

            if( isset($_POST['_create']) ) {
                //$curtain_service = new curtain_service();
                $data=array();
                $data['curtain_model_id']=$_POST['_curtain_model_id'];
                //$data['specification_id']=$_POST['_specification_id'];
                $data['specification']=$_POST['_specification'];
                $data['curtain_agent_id']=$_POST['_curtain_agent_id'];
                //$result = $curtain_service->insert_serial_number($data);
                $result = insert_serial_number($data);
            }

            global $wpdb;
            if( isset($_POST['_where']) ) {
                $where='"%'.$_POST['_where'].'%"';
                $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}serial_number WHERE qr_code_serial_no LIKE {$where}", OBJECT );
                unset($_POST['_where']);
            } else {
                $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}serial_number", OBJECT );
            }
            $output  = '<h2>Serial Number</h2>';
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
            $output .= '<th>serial_no</th>';
            $output .= '<th>model</th>';
            $output .= '<th>spec</th>';
            $output .= '<th>user</th>';
            $output .= '<th>update_time</th>';
            $output .= '</tr></thead>';
            $output .= '<tbody>';
            foreach ( $results as $index=>$result ) {
                $output .= '<tr>';
                $output .= '<td>'.$result->serial_number_id.'</a></td>';
                $output .= '<td><form method="post">';
                $output .= '<input type="submit" value="'.$result->qr_code_serial_no.'" name="_serial_no">';
                $output .= '</form></td>';
                $model = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}curtain_models WHERE curtain_model_id = {$result->curtain_model_id}", OBJECT );
                $output .= '<td>'.$model->curtain_model_name.'</td>';
                $output .= '<td>'.$result->specification.'</td>';
                $user = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}curtain_users WHERE curtain_user_id = {$result->curtain_user_id}", OBJECT );
                $output .= '<td>'.$user->display_name.'</td>';
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
                $row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}curtain_models WHERE curtain_model_id={$_id}", OBJECT );
                if (count($row) > 0) {
                    $output .= '<div id="dialog" title="Curtain model update">';
                    $output .= '<form method="post">';
                    $output .= '<fieldset>';
                    $output .= '<input type="hidden" value="'.$row->curtain_model_id.'" name="_curtain_model_id">';
                    $output .= '<label for="_curtain_model_name">Model Name</label>';
                    $output .= '<input type="text" name="_curtain_model_name" id="curtain-model-name" class="text ui-widget-content ui-corner-all" value="'.$row->curtain_model_name.'">';
                    $output .= '<label for="_model_description">Description</label>';
                    $output .= '<input type="text" name="_model_description" id="model-description" class="text ui-widget-content ui-corner-all" value="'.$row->model_description.'">';
                    $output .= '<label for="_curtain_vendor_name">Curtain Vendor</label>';
                    $output .= '<input type="text" name="_curtain_vendor_name" id="vendor-name" class="text ui-widget-content ui-corner-all" value="'.$row->curtain_vendor_name.'">';
                    $output .= '</fieldset>';
                    $output .= '<input class="wp-block-button__link" type="submit" value="Update" name="_update">';
                    $output .= '<input class="wp-block-button__link" type="submit" value="Delete" name="_delete">';
                    $output .= '</form>';
                    $output .= '</div>';
                } else {
                    $output .= '<div id="dialog" title="Create new serial_no">';

                    $curtain_models = new curtain_models();
                    $curtain_agents = new curtain_agents();
/*
                    $output .= '<form method="post">';
                    $output .= '<figure class="wp-block-table"><table><tbody>';
                    $output .= '<input type="hidden" value="'.$row->curtain_agent_id.'" name="_curtain_agent_id">';
                    $output .= '<tr><td>'.'Model Number:'.'</td><td><select name="_curtain_model_id">'.$curtain_models->select_options().'</select></td></tr>';
                    $output .= '<tr><td>'.'Specification:'.'</td><td><input size="50" type="text" name="_specification"></td></tr>';
                    $output .= '<tr><td>'.'Agent Name:'.'</td><td><select name="_curtain_agent_id">'.$curtain_agents->select_options().'</select></td></tr>';
                    $output .= '</tbody></table></figure>';
                    $output .= '<input class="wp-block-button__link" type="submit" value="Generate a Serial Number" name="_generate_serial_no">';
                    $output .= '</form>';                
*/
                    $output .= '<form method="post">';
                    $output .= '<fieldset>';
                    $output .= '<label for="_curtain_model_id">Model Name</label>';                    
                    //$output .= '<input type="text" name="_curtain_model_name" id="curtain-model-name" class="text ui-widget-content ui-corner-all">';
                    $output .= '<select name="_curtain_model_id">'.$curtain_models->select_options().'</select>';
                    $output .= '<label for="_specification">Specification</label>';
                    $output .= '<input type="text" name="_specification" id="specification" class="text ui-widget-content ui-corner-all">';
                    $output .= '<label for="_curtain_agent_id">Agent Name</label>';
                    $output .= '<select name="_curtain_agent_id">'.$curtain_agents->select_options().'</select>';
                    $output .= '</fieldset>';
                    $output .= '<input class="wp-block-button__link" type="submit" value="Create" name="_create">';
                    $output .= '<input class="wp-block-button__link" type="submit" value="Cancel"';
                    $output .= '</form>';

                    $output .= '</div>';
                }
            }

            if( isset($_POST['_serial_no']) ) {
                $output .= '<div id="dialog" title="QR Code">';
                $output .= '<div id="qrcode"><div id="qrcode_content">';
                $output .= get_site_url().'/'.get_option('_service_page').'/?serial_no='.$_POST['_serial_no'];
                $output .= '</div></div>';
                $output .= '</div>';
            }

            return $output;
        }

        function insert_serial_number($data=[]) {
            global $wpdb;
            $curtain_model_id = $data['curtain_model_id'];
            $model = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}curtain_models WHERE curtain_model_id = {$curtain_model_id}", OBJECT );
            if ( count($model) > 0 ) {
                $qr_code_serial_no = $model->curtain_model_name . $data['specification'] . time();
                $table = $wpdb->prefix.'serial_number';
                $data = array(
                    'qr_code_serial_no' => $qr_code_serial_no,
                    'curtain_model_id' => $data['curtain_model_id'],
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
            $curtain_model_id = $data['curtain_model_id'];
            $specification = $data['specification'];
            $model = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}curtain_models WHERE curtain_model_id = {$curtain_model_id}", OBJECT );
            if ((count($model) > 0) && (count($spec) > 0)) {
                $qr_code_serial_no = $model->curtain_model_name . $specification . time();
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
                curtain_model_id int(10),
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
    new serial_number();
}