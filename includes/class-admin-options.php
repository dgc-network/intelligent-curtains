<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}
if (!class_exists('admin_options')) {

    class admin_options {

        /**
         * Class constructor
         */
        public function __construct() {
            add_shortcode('admin-option-list', __CLASS__ . '::list_admin_options');
            self::create_tables();
        }

        function list_admin_options() {

            if( isset($_POST['_mode']) || isset($_POST['_id']) ) {
                return self::edit_admin_options($_POST['_id'], $_POST['_mode']);
            }

            if( isset($_POST['_create_admin_option']) ) {
                $data=array();
                $data['option_title']=$_POST['_option_title'];
                $data['option_link']=$_POST['_option_link'];
                $result = self::insert_admin_option($data);
            }
        
            if( isset($_POST['_update_admin_option']) ) {
                $data=array();
                $data['option_title']=$_POST['_option_title'];
                $data['option_link']=$_POST['_option_link'];
                $where=array();
                $where['admin_option_id']=$_POST['_admin_option_id'];
                $result = self::update_admin_options($data, $where);
            }
        
            global $wpdb;
            if( isset($_POST['_where_admin_options']) ) {
                $where='"%'.$_POST['_where_admin_options'].'%"';
                $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}admin_options WHERE option_title LIKE {$where}", OBJECT );
                unset($_POST['_where_admin_options']);
            } else {
                $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}admin_options", OBJECT );
            }
            $output  = '<h2>Admin Options</h2>';
            $output .= '<figure class="wp-block-table"><table><tbody>';
            $output .= '<tr><td colspan=5 style="text-align:right">';
            $output .= '<form method="post">';
            $output .= '<input type="text" name="_where_admin_options" placeholder="Search...">';
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
                $output .= '<td>'.$result->admin_option_id.'</a></td>';
                $output .= '<td><form method="post">';
                $output .= '<input type="hidden" value="'.$result->admin_option_id.'" name="_id">';
                $output .= '<input type="submit" value="'.$result->option_title.'" name="_mode">';
                $output .= '</form></td>';
                $output .= '<td>'.$result->option_link.'</td>';
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
            $output .= '</div>';
            $output .= '</div>';
            $output .= '</form>';

            return $output;
        }

        function edit_admin_options( $_id=null, $_mode=null ) {

            global $wpdb;
            $row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}admin_options WHERE admin_option_id={$_id}", OBJECT );
            if( $_mode=='Create' ) {
                $output  = '<h2>New Admin Option</h2>';
            } else {
                $output  = '<h2>Admin Option Update</h2>';
            }
            $output .= '<form method="post">';
            $output .= '<figure class="wp-block-table"><table><tbody>';
            if( $_mode=='Create' ) {
                $output .= '<tr><td>'.'Option Title:'.'</td><td><input size="50" type="text" name="_option_title"></td></tr>';
                $output .= '<tr><td>'.'Option Litle:'.'</td><td><input size="50" type="text" name="_option_link"></td></tr>';
            } else {
                $output .= '<input type="hidden" value="'.$row->admin_option_id.'" name="_admin_option_id">';
                $output .= '<tr><td>'.'Option Title:'.'</td><td><input size="50" type="text" name="_option_title" value="'.$row->option_title.'"></td></tr>';
                $output .= '<tr><td>'.'Option Link:'.'</td><td><input size="50" type="text" name="_option_link" value="'.$row->option_link.'"></td></tr>';
            }   
            $output .= '</tbody></table></figure>';

            $output .= '<div class="wp-block-buttons">';
            $output .= '<div class="wp-block-button">';
            if( $_mode=='Create' ) {
                $output .= '<input class="wp-block-button__link" type="submit" value="Create" name="_create_admin_option">';
            } else {
                $output .= '<input class="wp-block-button__link" type="submit" value="Update" name="_update_admin_option">';
            }
            $output .= '</div>';
            $output .= '<div class="wp-block-button">';
            $output .= '<input class="wp-block-button__link" type="submit" value="Cancel"';
            $output .= '</div>';
            $output .= '</div>';
            $output .= '</form>';
        
            return $output;
        }

        function insert_admin_option($data=[]) {
            global $wpdb;
            $table = $wpdb->prefix.'admin_options';
            $data = array(
                'option_title' => $data['option_title'],
                'option_link' => $data['option_link'],
                'create_timestamp' => time(),
                'update_timestamp' => time(),
            );
            $wpdb->insert($table, $data);        
            return $wpdb->insert_id;
        }

        function update_admin_options($data=[], $where=[]) {
            global $wpdb;
            $table = $wpdb->prefix.'admin_options';
            $data['update_timestamp'] = time();
            $wpdb->update($table, $data, $where);
        }

        function create_tables() {
            global $wpdb;
            $charset_collate = $wpdb->get_charset_collate();
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
            $sql = "CREATE TABLE `{$wpdb->prefix}admin_options` (
                admin_option_id int NOT NULL AUTO_INCREMENT,
                option_title varchar(20),
                option_link varchar(50),
                create_timestamp int(10),
                update_timestamp int(10),
                UNIQUE (option_title),
                PRIMARY KEY (admin_option_id)
            ) $charset_collate;";
            dbDelta($sql);            
        }
    }
    new admin_options();
}