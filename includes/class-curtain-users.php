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
            self::create_tables();
        }

        function list_curtain_users() {
            
            if( isset($_POST['_mode']) || isset($_POST['_id']) ) {
                return self::edit_curtain_user($_POST['_id'], $_POST['_mode']);
            }

            if( isset($_POST['create_user']) ) {
                $data=array();
                $data['line_user_id']=$_POST['_line_user_id'];
                $data['display_name']=$_POST['_display_name'];
                $data['mobile_phone']=$_POST['_mobile_phone'];
                $result = self::insert_curtain_users($data);
            }
        
            if( isset($_POST['update_user']) ) {
                $data=array();
                $data['display_name']=$_POST['_display_name'];
                $data['mobile_phone']=$_POST['_mobile_phone'];
                $where=array();
                $where['curtain_user_id']=$_POST['_user_id'];
                $result = self::update_curtain_users($data, $where);
            }
        
            global $wpdb;
            if( isset($_POST['_where_users']) ) {
                $where='"%'.$_POST['_where_users'].'%"';
                $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}curtain_users WHERE display_name LIKE {$where}", OBJECT );
                unset($_POST['_where_users']);
            } else {
                $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}curtain_users", OBJECT );
            }
            $output  = '<h2>Curtain Users</h2>';
            $output .= '<figure class="wp-block-table"><table><tbody>';
            $output .= '<tr><td colspan=5 style="text-align:right">';
            $output .= '<form method="post">';
            $output .= '<input type="text" name="_where_users" placeholder="Search...">';
            $output .= '<input type="submit" value="Search" name="submit_action">';
            $output .= '</form>';
            $output .= '</td></tr>';
            $output .= '<tr style="background-color:yellow">';
            $output .= '<td>id</td>';
            $output .= '<td>line_user_id</td>';
            $output .= '<td>name</td>';
            $output .= '<td>mobile</td>';
            $output .= '<td>update_time</td>';
            $output .= '</tr>';
            foreach ( $results as $index=>$result ) {
                $output .= '<tr>';
                $output .= '<td>'.$result->curtain_user_id.'</td>';
                $output .= '<td><form method="post">';
                $output .= '<input type="hidden" value="'.$result->curtain_user_id.'" name="_id">';
                $output .= '<input type="submit" value="'.$result->line_user_id.'" name="_mode">';
                $output .= '</form></td>';
                $output .= '<td>'.$result->display_name.'</td>';
                $output .= '<td>'.$result->mobile_phone.'</td>';
                $output .= '<td>'.wp_date( get_option('date_format'), $result->update_timestamp ).' '.wp_date( get_option('time_format'), $result->update_timestamp ).'</td>';
                $output .= '</tr>';
            }
            $output .= '</tbody></table></figure>';
            return $output;
        }

        function edit_curtain_user( $_id=null, $_mode=null ) {
            
            global $wpdb;
            $row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}curtain_users WHERE curtain_user_id={$_id}", OBJECT );
            if( $_mode=='Create' ) {
                $output  = '<h2>New User</h2>';
            } else {
                $output  = '<h2>User Update</h2>';
            }
            $output .= '<form method="post">';
            $output .= '<figure class="wp-block-table"><table><tbody>';
            if( $_mode=='Create' ) {
                $output .= '<tr><td>'.'Line User ID:'.'</td><td><input size="50" type="text" name="_line_user_id"></td></tr>';
                $output .= '<tr><td>'.'Display Name:'.'</td><td><input size="50" type="text" name="_display_name"></td></tr>';
                $output .= '<tr><td>'.'Mobile Phone:'.'</td><td><input size="50" type="text" name="_mobile_phone"></td></tr>';            
            } else {
                $output .= '<input type="hidden" value="'.$row->curtain_user_id.'" name="_user_id">';
                $output .= '<tr><td>'.'Line User ID:'.'</td><td>'.$row->line_user_id.'</td></tr>';
                $output .= '<tr><td>'.'Display Name:'.'</td><td><input size="50" type="text" name="_display_name" value="'.$row->display_name.'"></td></tr>';
                $output .= '<tr><td>'.'Mobile Phone:'.'</td><td><input size="50" type="text" name="_mobile_phone" value="'.$row->mobile_number.'"></td></tr>';
            }   
            $output .= '</tbody></table></figure>';
            $output .= '</form>';

            if( !($_mode=='Create') ) {
                $where='curtain_user_id='.$row->curtain_user_id;
                $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}serial_number WHERE {$where}", OBJECT );
                $output .= '<figure class="wp-block-table"><table><tbody>';
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

            $output .= '<form method="post">';
            $output .= '<div class="wp-block-buttons">';
            $output .= '<div class="wp-block-button">';
            if( $_mode=='Create' ) {
                $output .= '<input class="wp-block-button__link" type="submit" value="Create" name="create_user">';
            } else {
                $output .= '<input class="wp-block-button__link" type="submit" value="Update" name="update_user">';
            }
            $output .= '</div>';
            $output .= '<div class="wp-block-button">';
            $output .= '<input class="wp-block-button__link" type="submit" value="Cancel"';
            $output .= '</div>';
            $output .= '</div>';
            $output .= '</form>';

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
                    'create_timestamp' => time(),
                    'update_timestamp' => time(),
                );
                $wpdb->insert($table, $data);
                return $wpdb->insert_id;
            }
        }

        function update_curtain_users($data=[], $where=[]) {
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
                last_otp varchar(10),
                create_timestamp int(10),
                update_timestamp int(10),
                UNIQUE (line_user_id),
                PRIMARY KEY (curtain_user_id)
            ) $charset_collate;";
            dbDelta($sql);
        }
    }
    new curtain_users();
}