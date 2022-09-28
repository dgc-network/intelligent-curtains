<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}
if (!class_exists('model_number')) {

    class model_number {

        /**
         * Class constructor
         */
        public function __construct() {
            add_shortcode('model-number-list', __CLASS__ . '::list_model_number');
            self::create_tables();
        }

        function list_model_number() {

            if( isset($_POST['_mode']) || isset($_POST['_id']) ) {
                return self::edit_model_number($_POST['_id'], $_POST['_mode']);
            }

            if( ($_GET['action']=='insert-model-number') && (isset($_GET['model_number'])) ) {
                $data=array();
                $data['model_number']=$_GET['model_number'];
                $data['model_description']=$_GET['description'];
                $data['vendor_name']=$_GET['vendor_name'];
                $result = self::insert_model_number($data);
                $output .= $result.'<br>';
            }

            if( ($_GET['action']=='update-model-number') && (isset($_GET['model_number_id'])) ) {
                $data=array();
                if( isset($_GET['model_number']) ) {
                    $data['model_number']=$_GET['model_number'];
                }
                if( isset($_GET['model_description']) ) {
                    $data['model_description']=$_GET['description'];
                }
                if( isset($_GET['vendor_name']) ) {
                    $data['vendor_name']=$_GET['vendor_name'];
                }
                $where=array();
                $where['model_number_id']=$_GET['model_number_id'];
                $result = self::update_curtain_products($data, $where);
                $output .= $result.'<br>';
            }

            if( isset($_POST['create_model_number']) ) {
                $data=array();
                $data['model_number']=$_POST['_model_number'];
                $data['model_description']=$_POST['_model_description'];
                $data['vendor_name']=$_POST['_vendor_name'];
                $result = self::insert_model_number($data);
            }
        
            if( isset($_POST['update_model_number']) ) {
                $data=array();
                $data['model_number']=$_POST['_model_number'];
                $data['model_description']=$_POST['_model_description'];
                $data['vendor_name']=$_POST['_vendor_name'];
                $where=array();
                $where['model_number_id']=$_POST['_model_number_id'];
                $result = self::update_model_number($data, $where);
            }
        
            global $wpdb;
            if( isset($_POST['where_model_number']) ) {
                $where='"%'.$_POST['where_model_number'].'%"';
                $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}model_number WHERE model_description LIKE {$where}", OBJECT );
                unset($_POST['where_model_number']);
            } else {
                $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}model_number", OBJECT );
            }
            $output  = '<h2>Model Number</h2>';
            $output .= '<figure class="wp-block-table"><table><tbody>';
            $output .= '<tr><td colspan=5 style="text-align:right">';
            $output .= '<form method="post">';
            $output .= '<input type="text" name="where_model_number" placeholder="Search...">';
            $output .= '<input type="submit" value="Search" name="submit_action">';
            $output .= '</form>';
            $output .= '</td></tr>';
            $output .= '<tr style="background-color:yellow">';
            $output .= '<td>id</td>';
            $output .= '<td>model</td>';
            $output .= '<td>description</td>';
            $output .= '<td>vendor</td>';
            $output .= '<td>update_time</td>';
            $output .= '</tr>';
            foreach ( $results as $index=>$result ) {
                $output .= '<tr>';
                $output .= '<td>'.$result->model_number_id.'</a></td>';
                $output .= '<td><form method="post">';
                $output .= '<input type="hidden" value="'.$result->model_number_id.'" name="_id">';
                $output .= '<input type="submit" value="'.$result->model_number.'" name="_mode">';
                $output .= '</form></td>';
                $output .= '<td>'.$result->model_description.'</td>';
                $output .= '<td>'.$result->vendor_name.'</td>';
                $output .= '<td>'.wp_date( get_option('date_format'), $result->update_timestamp ).' '.wp_date( get_option('time_format'), $result->update_timestamp ).'</td>';
                $output .= '</tr>';
            }
            $output .= '</tbody></table></figure>';
            $output .= '<form method="post">';
            $output .= '<div class="wp-block-buttons">';
            $output .= '<div class="wp-block-button">';
            $output .= '<input class="wp-block-button__link" type="submit" value="Create" name="_mode">';
            $output .= '</div>';
            $output .= '<div class="wp-block-button">';
            //$output .= '<a class="wp-block-button__link" href="/">Cancel</a>';
            $output .= '</div>';
            $output .= '</div>';
            $output .= '</form>';

            if( isset($_POST['_serial_no']) ) {
                $output .= '<div id="basic-demo" class="example_content"><div id="qrcode"><div id="qrcode_content">';
                $output .= get_site_url().'/service/?serial_no='.$_POST['_serial_no'].'</div></div></div>';
            }
                            
            return $output;
        }

        function edit_model_number( $_id=null, $_mode=null ) {

            global $wpdb;
            $row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}model_number WHERE model_number_id={$_id}", OBJECT );
            if( $_mode=='Create' ) {
                $output  = '<h2>New Model Number</h2>';
            } else {
                $output  = '<h2>Model Number Update</h2>';
            }
            $output .= '<form method="post">';
            $output .= '<figure class="wp-block-table"><table><tbody>';
            if( $_mode=='Create' ) {
                $output .= '<tr><td>'.'Model Number:'.'</td><td><input size="50" type="text" name="_model_number"></td></tr>';
                $output .= '<tr><td>'.'Description :'.'</td><td><input size="50" type="text" name="_model_description"></td></tr>';
                $output .= '<tr><td>'.'Vendor      :'.'</td><td><input size="50" type="text" name="_vendor_name"></td></tr>';            
            } else {
                $output .= '<input type="hidden" value="'.$row->model_number_id.'" name="_model_number_id">';
                $output .= '<tr><td>'.'Model Number:'.'</td><td><input size="50" type="text" name="_model_number" value="'.$row->model_number.'"></td></tr>';
                $output .= '<tr><td>'.'Description :'.'</td><td><input size="50" type="text" name="_model_description" value="'.$row->model_description.'"></td></tr>';
                $output .= '<tr><td>'.'Vendor      :'.'</td><td><input size="50" type="text" name="_vendor_name" value="'.$row->vendor_name.'"></td></tr>';
            }   
            $output .= '</tbody></table></figure>';

            $output .= '<div class="wp-block-buttons">';
            $output .= '<div class="wp-block-button">';
            if( $_mode=='Create' ) {
                $output .= '<input class="wp-block-button__link" type="submit" value="Create" name="create_model_number">';
            } else {
                $output .= '<input class="wp-block-button__link" type="submit" value="Update" name="update_model_number">';
            }
            $output .= '</div>';
            $output .= '<div class="wp-block-button">';
            $output .= '<input class="wp-block-button__link" type="submit" value="Cancel"';
            $output .= '</div>';
            $output .= '</div>';
            $output .= '</form>';
        
            if( !($_mode=='Create') ) {
                $where='model_number_id='.$row->model_number_id;
                $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}serial_number WHERE {$where}", OBJECT );
                $output .= '<figure class="wp-block-table"><table><tbody>';
                $output .= '<tr style="background-color:yellow">';
                $output .= '<td> </td>';
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
                    $model = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}model_number WHERE model_number_id = {$result->model_number_id}", OBJECT );
                    $output .= '<td>'.$model->model_number.'</td>';
                    $spec = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}specifications WHERE specification_id = {$result->specification_id}", OBJECT );
                    $output .= '<td>'.$spec->specification.'</td>';
                    $user = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}curtain_users WHERE curtain_user_id = {$result->curtain_user_id}", OBJECT );
                    $output .= '<td>'.$user->display_name.'</td>';
                    $output .= '<td>'.wp_date( get_option('date_format'), $result->update_timestamp ).' '.wp_date( get_option('time_format'), $result->update_timestamp ).'</td>';
                    $output .= '</tr>';
                }
                $output .= '</tbody></table></figure>';
            }

            return $output;
        }

        function insert_model_number($data=[]) {
            global $wpdb;
            $table = $wpdb->prefix.'model_number';
            $data = array(
                'model_number' => $data['model_number'],
                'model_description' => $data['model_description'],
                'vendor_name' => $data['vendor_name'],
                'create_timestamp' => time(),
                'update_timestamp' => time(),
            );
            $wpdb->insert($table, $data);        
            return $wpdb->insert_id;
        }

        function update_model_number($data=[], $where=[]) {
            global $wpdb;
            $table = $wpdb->prefix.'model_number';
            $data['update_timestamp'] = time();
            $wpdb->update($table, $data, $where);
        }

        function select_options( $default_id=null ) {
            global $wpdb;
            $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}model_number", OBJECT );
            $output = '<option value="no_select">-- Select an option --</option>';
            foreach ($results as $index => $result) {
                if ( $results[$index]->model_number_id == $default_id ) {
                    $output .= '<option value="'.$results[$index]->model_number_id.'" selected>';
                } else {
                    $output .= '<option value="'.$results[$index]->model_number_id.'">';
                }
                $output .= $results[$index]->model_number;
                $output .= '</option>';        
            }
            $output .= '<option value="delete_select">-- Remove this --</option>';
            return $output;    
        }

        function create_tables() {
            global $wpdb;
            $charset_collate = $wpdb->get_charset_collate();
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
            $sql = "CREATE TABLE `{$wpdb->prefix}model_number` (
                model_number_id int NOT NULL AUTO_INCREMENT,
                model_number varchar(5),
                model_description varchar(50),
                vendor_name varchar(50),
                create_timestamp int(10),
                update_timestamp int(10),
                UNIQUE (model_number),
                PRIMARY KEY (model_number_id)
            ) $charset_collate;";
            dbDelta($sql);            
        }
    }
    new model_number();
}