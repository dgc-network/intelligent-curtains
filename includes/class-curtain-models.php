<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('curtain_models')) {
    class curtain_models {
        /**
         * Class constructor
         */
        public function __construct() {
            add_shortcode('curtain-model-list', array( __CLASS__, 'list_curtain_models' ));
            self::create_tables();
        }

        function enqueue_scripts() {		
            wp_enqueue_script( 'custom-curtain-models', plugin_dir_url( __DIR__ ) . 'assets/js/custom-curtain-models.js', array( 'jquery' ), time(), true );
        }    

        public function list_curtain_models() {

            if( isset($_SESSION['username']) ) {
                global $wpdb;
                $user = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}curtain_users WHERE line_user_id = %s AND user_role= %s", $_SESSION['username'], 'admin' ), OBJECT );            
                if (count($user) == 0 && $_GET['_check_permission'] != 'false') {
                    return 'You are not validated to read this page. Please check to the administrators.';
                }
            } else {
                if ( $_GET['_check_permission'] != 'false' ) {
                    return 'You are not validated to read this page. Please check to the administrators.';
                }
            }

            if( isset($_POST['_create']) ) {
                $data=array();
                $data['curtain_model_name']=$_POST['_curtain_model_name'];
                $data['model_description']=$_POST['_model_description'];
                $data['curtain_vendor_name']=$_POST['_curtain_vendor_name'];
                $result = self::insert_curtain_model($data);
            }
            
            if( isset($_POST['_update']) ) {
                $data=array();
                $data['curtain_model_name']=$_POST['_curtain_model_name'];
                $data['model_description']=$_POST['_model_description'];
                $data['curtain_vendor_name']=$_POST['_curtain_vendor_name'];
                $where=array();
                $where['curtain_model_id']=$_POST['_curtain_model_id'];
                $result = self::update_curtain_models($data, $where);
            }

            global $wpdb;
            if( isset($_POST['_where']) ) {
                $where='"%'.$_POST['_where'].'%"';
                $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}curtain_models WHERE model_description LIKE {$where}", OBJECT );
                unset($_POST['_where']);
            } else {
                $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}curtain_models", OBJECT );
            }
            $output  = '<h2>Curtain Models</h2>';
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
            $output .= '<th>model</th>';
            $output .= '<th>description</th>';
            $output .= '<th>vendor</th>';
            $output .= '<th>update_time</th>';
            $output .= '</tr></thead>';
            $output .= '<tbody>';
            foreach ( $results as $index=>$result ) {
                $output .= '<tr>';
                $output .= '<td>'.$result->curtain_model_id.'</a></td>';
                $output .= '<td><form method="post">';
                $output .= '<input type="hidden" value="'.$result->curtain_model_id.'" name="_id">';
                $output .= '<input type="submit" value="'.$result->curtain_model_name.'">';
                $output .= '</form></td>';
                $output .= '<td>'.$result->model_description.'</td>';
                $output .= '<td>'.$result->curtain_vendor_name.'</td>';
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
                if (is_null($row) || !empty($wpdb->last_error)) {
                    $output .= '<div id="dialog" title="Create new model">';
                    $output .= '<form method="post">';
                    $output .= '<fieldset>';
                    $output .= '<label for="curtain-model-name">Model Name</label>';
                    $output .= '<input type="text" name="_curtain_model_name" id="curtain-model-name" class="text ui-widget-content ui-corner-all">';
                    $output .= '<label for="model-description">Description</label>';
                    $output .= '<input type="text" name="_model_description" id="model-description" class="text ui-widget-content ui-corner-all">';
                    $output .= '<label for="curtain-vendor-name">Curtain Vendor</label>';
                    $output .= '<input type="text" name="_curtain_vendor_name" id="curtain-vendor-name" class="text ui-widget-content ui-corner-all">';
                    $output .= '</fieldset>';
                    $output .= '<input class="wp-block-button__link" type="submit" value="Create" name="_create">';
                    $output .= '<input class="wp-block-button__link" type="submit" value="Cancel"';
                    $output .= '</form>';
                    $output .= '</div>';
                } else {                    
                    $output .= '<div id="dialog" title="Curtain model update">';
                    $output .= '<form method="post">';
                    $output .= '<fieldset>';
                    $output .= '<input type="hidden" value="'.$row->curtain_model_id.'" name="_curtain_model_id">';
                    $output .= '<label for="curtain-model-name">Model Name</label>';
                    $output .= '<input type="text" name="_curtain_model_name" id="curtain-model-name" class="text ui-widget-content ui-corner-all" value="'.$row->curtain_model_name.'">';
                    $output .= '<label for="model-description">Description</label>';
                    $output .= '<input type="text" name="_model_description" id="model-description" class="text ui-widget-content ui-corner-all" value="'.$row->model_description.'">';
                    $output .= '<label for="curtain-vendor-name">Curtain Vendor</label>';
                    $output .= '<input type="text" name="_curtain_vendor_name" id="curtain-vendor-name" class="text ui-widget-content ui-corner-all" value="'.$row->curtain_vendor_name.'">';
                    $output .= '</fieldset>';
                    $output .= '<input class="wp-block-button__link" type="submit" value="Update" name="_update">';
                    $output .= '<input class="wp-block-button__link" type="submit" value="Delete" name="_delete">';
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

        function insert_curtain_model($data=[]) {
            global $wpdb;
            $table = $wpdb->prefix.'curtain_models';
            $data['create_timestamp'] = time();
            $data['update_timestamp'] = time();
            $wpdb->insert($table, $data);        
            return $wpdb->insert_id;
        }

        function update_curtain_models($data=[], $where=[]) {
            global $wpdb;
            $table = $wpdb->prefix.'curtain_models';
            $data['update_timestamp'] = time();
            $wpdb->update($table, $data, $where);
        }

        public function select_options( $default_id=null ) {
            global $wpdb;
            $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}curtain_models", OBJECT );
            $output = '<option value="no_select">-- Select an option --</option>';
            foreach ($results as $index => $result) {
                if ( $results[$index]->curtain_model_id == $default_id ) {
                    $output .= '<option value="'.$results[$index]->curtain_model_id.'" selected>';
                } else {
                    $output .= '<option value="'.$results[$index]->curtain_model_id.'">';
                }
                $output .= $results[$index]->curtain_model_name;
                $output .= '</option>';        
            }
            $output .= '<option value="delete_select">-- Remove this --</option>';
            return $output;    
        }

        function create_tables() {
            global $wpdb;
            $charset_collate = $wpdb->get_charset_collate();
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
            $sql = "CREATE TABLE `{$wpdb->prefix}curtain_models` (
                curtain_model_id int NOT NULL AUTO_INCREMENT,
                curtain_model_name varchar(5) UNIQUE,
                model_description varchar(50),
                curtain_vendor_name varchar(50),
                create_timestamp int(10),
                update_timestamp int(10),
                PRIMARY KEY (curtain_model_id)
            ) $charset_collate;";
            dbDelta($sql);
        }
    }
    new curtain_models();
}