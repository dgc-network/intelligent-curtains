<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}
if (!class_exists('learnings')) {

    class learnings {

        /**
         * Class constructor
         */
        public function __construct() {
            add_shortcode('learning_list', __CLASS__ . '::list_mode');
            self::create_tables();
        }

        function list_mode() {

            if( isset($_POST['submit_action']) ) {
        
                global $wpdb;
                $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}learning_courses WHERE learning_id = {$_GET['_id']}", OBJECT );
                foreach ($results as $index => $result) {
                    if ( $_POST['_course_id_'.$index]=='delete_select' ){
                        $table = $wpdb->prefix.'learning_courses';
                        $where = array(
                            't_c_id' => $results[$index]->t_c_id
                        );
                        $wpdb->delete( $table, $where );    
                    } else {
                        $table = $wpdb->prefix.'learning_courses';
                        $data = array(
                            //'learning_date' => strtotime($_POST['_learning_date']),
                            'course_id' => $_POST['_course_id_'.$index]
                        );
                        $where = array(
                            't_c_id' => $results[$index]->t_c_id
                        );
                        $updated = $wpdb->update( $table, $data, $where );
                    }
                }
                if (( $_POST['_course_id']=='no_select' ) || ( $_POST['_course_id']=='delete_select' ) ){
                } else {
                    $table = $wpdb->prefix.'learning_courses';
                    $data = array(
                        //'created_date' => strtotime($_POST['_created_date']), 
                        'learning_id' => $_GET['_id'],
                        'course_id' => $_POST['_course_id']
                    );
                    $format = array('%d', '%d');
                    $wpdb->insert($table, $data, $format);    
                }
            }
            
            if( isset($_GET['view_mode']) ) {
                global $wpdb;
                $row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}learnings WHERE learning_id = {$_GET['_id']}", OBJECT );
                $learningDate = wp_date( get_option( 'date_format' ), $row->learning_date );
                $output  = '<form method="post">';
                $output .= '<figure class="wp-block-table"><table><tbody>';
                $output .= '<tr><td>'.'Title:'.'</td><td>'.$row->learning_title.'</td></tr>';
                $output .= '<tr><td>'.'Date:'.'</td><td>'.$learningDate.'</td></tr>';
                $output .= '</tbody></table></figure>';

                $output .= '<figure class="wp-block-table"><table><tbody>';
                $output .= '<tr><td>'.'#'.'</td><td>'.'Courses'.'</td></tr>';
                $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}learning_courses WHERE learning_id = {$_GET['_id']}", OBJECT );
                foreach ($results as $index => $result) {
                    $output .= '<tr><td>'.$index.'</td><td>'.'<select name="_course_id_'.$index.'">'.Courses::select_options($results[$index]->course_id).'</td></tr>';
                }
                $output .= '<tr><td>'.($index+1).'</td><td>'.'<select name="_course_id">'.Courses::select_options().'</select>'.'</td></tr>';
                $output .= '</tbody></table></figure>';
                
                $output .= '<div class="wp-block-buttons">';
                $output .= '<div class="wp-block-button">';
                $output .= '<input class="wp-block-button__link" type="submit" value="Submit" name="submit_action">';
                $output .= '</div>';
                $output .= '</form>';
                $output .= '<form method="get">';
                $output .= '<div class="wp-block-button">';
                $output .= '<input class="wp-block-button__link" type="submit" value="Cancel"';
                $output .= '</div>';
                $output .= '</div>';
                $output .= '</form>';

                return $output;
            }        
            
            if( isset($_POST['edit_mode']) ) {
        
                global $wpdb;
                $row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}learnings WHERE learning_id = {$_POST['_id']}", OBJECT );
                $CreatedDate = wp_date( get_option( 'date_format' ), $row->created_date );
                $output  = '<form method="post">';
                $output .= '<figure class="wp-block-table"><table><tbody>';
                if( $_POST['edit_mode']=='Create' ) {
                    $output .= '<tr><td>'.'Title:'.'</td><td><input style="width: 100%" type="text" name="_learning_title" value=""></td></tr>';
                    $output .= '<tr><td>'.'Date:'.'</td><td><input style="width: 100%" type="text" name="_created_date" value="'.date(get_option('date_format')).'" disabled></td></tr>';
                }
                if( $_POST['edit_mode']=='Update' ) {
                    $output .= '<tr><td>'.'ID:'.'</td><td style="width: 100%"><input style="width: 100%" type="text" name="_learning_id" value="'.$row->learning_id.'"></td></tr>';
                    $output .= '<tr><td>'.'Title:'.'</td><td><input style="width: 100%" type="text" name="_learning_title" value="'.$row->learning_title.'"></td></tr>';
                    $output .= '<tr><td>'.'Date:'.'</td><td><input style="width: 100%" type="text" name="_created_date" value="'.$CreatedDate.'" disabled></td></tr>';
                }
                if( $_POST['edit_mode']=='Delete' ) {
                    $output .= '<tr><td>'.'ID:'.'</td><td style="width: 100%"><input style="width: 100%" type="text" name="_learning_id" value="'.$row->learning_id.'"></td></tr>';
                    $output .= '<tr><td>'.'Title:'.'</td><td><input style="width: 100%" type="text" name="_learning_title" value="'.$row->learning_title.'" disabled></td></tr>';
                    $output .= '<tr><td>'.'Date:'.'</td><td><input style="width: 100%" type="text" name="_created_date" value="'.$CreatedDate.'" disabled></td></tr>';
                }
                $output .= '</tbody></table></figure>';
        
                $output .= '<div class="wp-block-buttons">';
                $output .= '<div class="wp-block-button">';
                if( $_POST['edit_mode']=='Create' ) {
                    $output .= '<input class="wp-block-button__link" type="submit" value="Create" name="create_action">';
                }
                if( $_POST['edit_mode']=='Update' ) {
                    $output .= '<input class="wp-block-button__link" type="submit" value="Update" name="update_action">';
                }
                if( $_POST['edit_mode']=='Delete' ) {
                    $output .= '<input class="wp-block-button__link" type="submit" value="Delete" name="delete_action">';
                }
                $output .= '</div>';
                $output .= '<div class="wp-block-button">';
                $output .= '<input class="wp-block-button__link" type="submit" value="Cancel"';
                $output .= '</div>';
                $output .= '</div>';
                $output .= '</form>';
            
                return $output;
        
            }
            
            if( isset($_POST['create_action']) ) {
        
                global $wpdb;
                $table = $wpdb->prefix.'learnings';
                $data = array(
                    //'created_date' => strtotime($_POST['_created_date']), 
                    'created_date' => current_time('timestamp'), 
                    'learning_title' => $_POST['_learning_title']
                );
                $format = array('%d', '%s');
                $wpdb->insert($table, $data, $format);
            }
        
            if( isset($_POST['update_action']) ) {
        
                global $wpdb;
                $table = $wpdb->prefix.'learnings';
                $data = array(
                    'learning_title' => $_POST['_learning_title'],
                    //'created_date' => strtotime($_POST['_created_date'])
                );
                $where = array('learning_id' => $_POST['_learning_id']);
                $wpdb->update( $table, $data, $where );
            }
        
            if( isset($_POST['delete_action']) ) {
                global $wpdb;
                $table = $wpdb->prefix.'learnings';
                $where = array('learning_id' => $_POST['_learning_id']);
                $wpdb->delete( $table, $where );
            }

            /**
             * List Mode
             */                    
            $output  = '<figure class="wp-block-table"><table><tbody>';
            $output .= '<tr><td>Title</td><td>Date</td><td>--</td><td>--</td></tr>';
        
            global $wpdb;
            $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}learnings", OBJECT );
            foreach ($results as $index => $result) {
                $learningId = $results[$index]->learning_id;
                $learningTitle = $results[$index]->learning_title;
                $CreatedDate = wp_date( get_option( 'date_format' ), $results[$index]->created_date );
        
                $output .= '<form method="post">';
                $output .= '<tr>';
                $output .= '<td><a href="?view_mode=true&_id='.$learningId.'">'.$learningTitle.'</a></td>';
                $output .= '<td>'.$CreatedDate.'</td>';
                $output .= '<input type="hidden" value="'.$learningId.'" name="_id">';
                $output .= '<td><input class="wp-block-button__link" type="submit" value="Update" name="edit_mode"></td>';
                $output .= '<td><input class="wp-block-button__link" type="submit" value="Delete" name="edit_mode"></td>';
                $output .= '</tr>';
                $output .= '</form>';
            }        
            $output .= '</tbody></table></figure>';
        
            $output .= '<form method="post">';
            $output .= '<div class="wp-block-buttons">';
            $output .= '<div class="wp-block-button">';
            $output .= '<input class="wp-block-button__link" type="submit" value="Create" name="edit_mode">';
            $output .= '</div>';
            $output .= '<div class="wp-block-button">';
            $output .= '<a class="wp-block-button__link" href="/">Cancel</a>';
            $output .= '</div>';
            $output .= '</div>';
            $output .= '</form>';
        
            return $output;    
        }
        
        function select_options( $default_id=null ) {

            global $wpdb;
            $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}learnings", OBJECT );
            $output = '<option value="no_select">-- Select an option --</option>';
            foreach ($results as $index => $result) {
                if ( $results[$index]->learning_id == $default_id ) {
                    $output .= '<option value="'.$results[$index]->learning_id.'" selected>';
                } else {
                    $output .= '<option value="'.$results[$index]->learning_id.'">';
                }
                $output .= $results[$index]->learning_title;
                $output .= '</option>';        
            }
            $output .= '<option value="delete_select">-- Remove this --</option>';
            return $output;    
        }

        function create_tables() {
        
            global $wpdb;
            $charset_collate = $wpdb->get_charset_collate();
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
            $sql = "CREATE TABLE `{$wpdb->prefix}learnings` (
                learning_id int NOT NULL AUTO_INCREMENT,
                learning_title varchar(255) NOT NULL,
                created_date int NOT NULL,
                PRIMARY KEY  (learning_id)
            ) $charset_collate;";        
            dbDelta($sql);

            $sql = "CREATE TABLE `{$wpdb->prefix}learning_courses` (
                t_c_id int NOT NULL AUTO_INCREMENT,
                learning_id int NOT NULL,
                course_id int NOT NULL,
                PRIMARY KEY  (t_c_id)
            ) $charset_collate;";        
            dbDelta($sql);
        }
        
    }
    new learnings();
}
?>