<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}
if (!class_exists('specifications')) {

    class specifications {

        /**
         * Class constructor
         */
        public function __construct() {
            add_shortcode('specification-list', __CLASS__ . '::list_specifications');
            self::create_tables();
        }

        function list_specifications() {

            if( isset($_POST['_mode']) || isset($_POST['_id']) ) {
                return self::edit_specification($_POST['_id'], $_POST['_mode']);
            }

            if( isset($_POST['_create_spec']) ) {
                $data=array();
                $data['specification']=$_POST['_specification'];
                $data['spec_description']=$_POST['_spec_description'];
                $result = self::insert_specification($data);
            }
        
            if( isset($_POST['_update_spec']) ) {
                $data=array();
                $data['specification']=$_POST['_specification'];
                $data['spec_description']=$_POST['_spec_description'];
                $where=array();
                $where['specification_id']=$_POST['_specification_id'];
                $result = self::update_specifications($data, $where);
            }
        
            global $wpdb;
            if( isset($_POST['where_specification']) ) {
                $where='"%'.$_POST['where_specification'].'%"';
                $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}specifications WHERE spec_description LIKE {$where}", OBJECT );
                unset($_POST['where_specification']);
            } else {
                $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}specifications", OBJECT );
            }
            $output  = '<h2>Specification</h2>';
            $output .= '<figure class="wp-block-table"><table><tbody>';
            $output .= '<tr><td colspan=5 style="text-align:right">';
            $output .= '<form method="post">';
            $output .= '<input type="text" name="where_specification" placeholder="Search...">';
            $output .= '<input type="submit" value="Search" name="submit_action">';
            $output .= '</form>';
            $output .= '</td></tr>';
            $output .= '<tr style="background-color:yellow">';
            $output .= '<td>id</td>';
            $output .= '<td>spec</td>';
            $output .= '<td>description</td>';
            $output .= '<td>update_time</td>';
            $output .= '</tr>';
            foreach ( $results as $index=>$result ) {
                $output .= '<tr>';
                $output .= '<td>'.$result->specification_id.'</a></td>';
                $output .= '<td><form method="post">';
                $output .= '<input type="hidden" value="'.$result->specification_id.'" name="_id">';
                $output .= '<input type="submit" value="'.$result->specification.'" name="_mode">';
                $output .= '</form></td>';
                $output .= '<td>'.$result->spec_description.'</td>';
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

        function edit_specification( $_id=null, $_mode=null ) {

            global $wpdb;
            $row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}specifications WHERE specification_id={$_id}", OBJECT );
            if( $_mode=='Create' ) {
                $output  = '<h2>New Specification</h2>';
            } else {
                $output  = '<h2>Specification Update</h2>';
            }
            $output .= '<form method="post">';
            $output .= '<figure class="wp-block-table"><table><tbody>';
            if( $_mode=='Create' ) {
                $output .= '<tr><td>'.'specification:'.'</td><td><input size="50" type="text" name="_specification"></td></tr>';
                $output .= '<tr><td>'.'Description :'.'</td><td><input size="50" type="text" name="_spec_description"></td></tr>';
            } else {
                $output .= '<input type="hidden" value="'.$row->specification_id.'" name="_specification_id">';
                $output .= '<tr><td>'.'specification:'.'</td><td><input size="50" type="text" name="_specification" value="'.$row->specification.'"></td></tr>';
                $output .= '<tr><td>'.'Description :'.'</td><td><input size="50" type="text" name="_spec_description" value="'.$row->spec_description.'"></td></tr>';
            }   
            $output .= '</tbody></table></figure>';

            $output .= '<div class="wp-block-buttons">';
            $output .= '<div class="wp-block-button">';
            if( $_mode=='Create' ) {
                $output .= '<input class="wp-block-button__link" type="submit" value="Create" name="_create_spec">';
            } else {
                $output .= '<input class="wp-block-button__link" type="submit" value="Update" name="_update_spec">';
            }
            $output .= '</div>';
            $output .= '<div class="wp-block-button">';
            $output .= '<input class="wp-block-button__link" type="submit" value="Cancel"';
            $output .= '</div>';
            $output .= '</div>';
            $output .= '</form>';
        
            if( !($_mode=='Create') ) {
                $where='specification_id='.$row->specification_id;
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
                    $spec = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}specification WHERE specification_id = {$result->specification_id}", OBJECT );
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

        function insert_specification($data=[]) {
            global $wpdb;
            $table = $wpdb->prefix.'specifications';
            $data = array(
                'specification' => $data['specification'],
                'spec_description' => $data['spec_description'],
                'create_timestamp' => time(),
                'update_timestamp' => time(),
            );
            $wpdb->insert($table, $data);        
            return $wpdb->insert_id;
        }

        function update_specifications($data=[], $where=[]) {
            global $wpdb;
            $table = $wpdb->prefix.'specifications';
            $data['update_timestamp'] = time();
            $wpdb->update($table, $data, $where);
        }

        function select_options( $default_id=null ) {
            global $wpdb;
            $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}specifications", OBJECT );
            $output = '<option value="no_select">-- Select an option --</option>';
            foreach ($results as $index => $result) {
                if ( $results[$index]->specification_id == $default_id ) {
                    $output .= '<option value="'.$results[$index]->specification_id.'" selected>';
                } else {
                    $output .= '<option value="'.$results[$index]->specification_id.'">';
                }
                $output .= $results[$index]->specification;
                $output .= '</option>';        
            }
            $output .= '<option value="delete_select">-- Remove this --</option>';
            return $output;    
        }

        function create_tables() {
            global $wpdb;
            $charset_collate = $wpdb->get_charset_collate();
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
            $sql = "CREATE TABLE `{$wpdb->prefix}specifications` (
                specification_id int NOT NULL AUTO_INCREMENT,
                specification varchar(5),
                spec_description varchar(50),
                create_timestamp int(10),
                update_timestamp int(10),
                UNIQUE (specification),
                PRIMARY KEY (specification_id)
            ) $charset_collate;";
            dbDelta($sql);            
        }
    }
    new specifications();
}