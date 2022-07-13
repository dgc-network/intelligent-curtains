<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}
if (!class_exists('calendars')) {

    class calendars {

        /**
         * Class constructor
         */
        public function __construct() {
            add_shortcode('calendar-list', __CLASS__ . '::list_mode');
            self::create_tables();
        }

        function edit_mode( $_id=0, $_mode='' ) {

            if ($_id==0){
                $_mode='Create';
            }

            if( isset($_POST['submit_action']) ) {

                if( $_POST['submit_action']=='Create' ) {
        
                    global $wpdb;
                    $table = $wpdb->prefix.'events';
                    $data = array(
                        //'created_date' => current_time('timestamp'), 
                        'event_title' => $_POST['_event_title'],
                        'event_begin' => $_POST['_event_begin'],
                        'event_end' => $_POST['_event_end'],
                        'event_host' => $_POST['_event_host'],
                    );
                    $format = array('%s', '%d', '%d', '%d');
                    $insert_id = $wpdb->insert($table, $data, $format);
/*    
                    $CreateCourseAction = new CreateCourseAction();                
                    //$CreateCourseAction->setCourseId(intval($_POST['_course_id']));
                    $CreateCourseAction->setCourseId(intval($insert_id));
                    $CreateCourseAction->setCourseTitle($_POST['_course_title']);
                    $CreateCourseAction->setCreatedDate(intval(current_time('timestamp')));
                    //$CreateCourseAction->setListPrice(floatval($_POST['_list_price']));
                    //$CreateCourseAction->setSalePrice(floadval($_POST['_sale_price']));
                    $CreateCourseAction->setPublicKey($_POST['_public_key']);
                    $send_data = $CreateCourseAction->serializeToString();
    
                    $op_result = OP_RETURN_send(OP_RETURN_SEND_ADDRESS, OP_RETURN_SEND_AMOUNT, $send_data);
                
                    if (isset($op_result['error'])) {
    
                        $result_output = 'Error: '.$op_result['error']."\n";
                        return $result_output;
                    } else {
    
                        $table = $wpdb->prefix.'courses';
                        $data = array(
                            'txid' => $op_result['txid'], 
                        );
                        $where = array('course_id' => $insert_id);
                        $wpdb->update( $table, $data, $where );
                    }
*/                    
                }
    
                if( $_POST['submit_action']=='Update' ) {
    /*        
                    $UpdateCourseAction = new UpdateCourseAction();                
                    $UpdateCourseAction->setCourseId(intval($_POST['_course_id']));
                    $UpdateCourseAction->setCourseTitle($_POST['_course_title']);
                    $UpdateCourseAction->setCreatedDate(intval(strtotime($_POST['_created_date'])));
                    //$UpdateCourseAction->setListPrice(floatval($_POST['_list_price']));
                    //$UpdateCourseAction->setSalePrice(floatval($_POST['_sale_price']));
                    $UpdateCourseAction->setPublicKey($_POST['_public_key']);
                    $send_data = $UpdateCourseAction->serializeToString();
    
                    $op_result = OP_RETURN_send(OP_RETURN_SEND_ADDRESS, OP_RETURN_SEND_AMOUNT, $send_data);
    */            
                    if (isset($op_result['error'])) {
                        $result_output = 'Error: '.$op_result['error']."\n";
                        return $result_output;
                    } else {
    
                        global $wpdb;
                        $table = $wpdb->prefix.'events';
                        $data = array(
                            'event_title' => $_POST['_event_title'],
                            'event_begin' => $_POST['_event_begin'],
                            'event_end' => $_POST['_event_end'],
                            'event_host' => $_POST['_event_host'],
                            //'txid' => $op_result['txid'], 
                        );
                        $where = array('event_id' => $_id);
                        $wpdb->update( $table, $data, $where );
                    }
                }
            
                if( $_POST['submit_action']=='Delete' ) {
            
                    global $wpdb;
                    $table = $wpdb->prefix.'events';
                    $where = array('event_id' => $_id);
                    $deleted = $wpdb->delete( $table, $where );
                }

                $_GET['edit_mode']='';
                return self::list_mode();
/*
                ?><script>window.location=window.location.path</script><?php
*/                
            }

            /** 
             * edit_mode
             */
            global $wpdb;
            $row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}events WHERE event_id = {$_id}", OBJECT );
            $output  = '<form method="post">';
            $output .= '<figure class="wp-block-table"><table><tbody>';
            $output .= '<tr><td>'.'Title:'.'</td><td><input style="width: 100%" type="text" name="_event_title" value="'.$row->event_title.'"></td></tr>';
            $output .= '<tr><td>'.'Begin:'.'</td><td><input style="width: 100%" type="text" name="_event_begin" value="'.$row->event_begin.'"></td></tr>';
            $output .= '<tr><td>'.'End:'.'</td><td><input style="width: 100%" type="text" name="_event_end" value="'.$row->event_end.'"></td></tr>';
            $output .= '<tr><td>'.'Host:'.'</td><td><input style="width: 100%" type="text" name="_event_host" value="'.$row->event_host.'"></td></tr>';
            $output .= '</tbody></table></figure>';
    
            if( $_mode=='Create' ) {
                $output .= '<div class="wp-block-buttons">';
                $output .= '<div class="wp-block-button">';
                $output .= '<input class="wp-block-button__link" type="submit" value="Create" name="submit_action">';
                $output .= '</div>';
                $output .= '<div class="wp-block-button">';
                $output .= '<input class="wp-block-button__link" type="submit" value="Cancel" name="submit_action">';
                $output .= '</div>';
                $output .= '</div>';
            } else {
                $output .= '<div class="wp-block-buttons">';
                $output .= '<div class="wp-block-button">';
                $output .= '<input class="wp-block-button__link" type="submit" value="Update" name="submit_action">';
                $output .= '</div>';
                $output .= '<div class="wp-block-button">';
                $output .= '<input class="wp-block-button__link" type="submit" value="Delete" name="submit_action">';
                $output .= '</div>';
                $output .= '</div>';
            }
            $output .= '</form>';
        
            return $output;
        }

        function list_mode() {
/*
            if( isset($_GET['view_mode']) ) {
                //if ($_GET['view_mode']=='course_learnings') return self::course_learnings($_GET['_id']);
                return self::view_mode($_GET['_id']);
            }
*/
            if( isset($_GET['edit_mode']) ) {
                if ($_GET['edit_mode']=='Create') return self::edit_mode();
                if ($_GET['edit_mode']=='Edit') return self::edit_mode( $_GET['_id'] );
            }            

            /**
             * List Mode
             */
            global $wpdb;
            $user_id = get_current_user_id();
            $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}events WHERE event_host = {$user_id}", OBJECT );
            $output  = '<h2>Events</h2>';
            $output .= '<figure class="wp-block-table"><table><tbody>';
            $output .= '<tr><td>Events</td><td>Begin</td><td>End</td></tr>';
            foreach ( $results as $index=>$result ) {
                $output .= '<tr>';
                $output .= '<td><a href="?edit_mode=Edit&_id='.$result->event_id.'">'.$result->event_title.'</a></td>';
                $output .= '<td>'.$result->event_begin.'</td>';
                $output .= '<td>'.$result->event_end.'</td>';
                $output .= '</tr>';
            }
            $output .= '</tbody></table></figure>';

            $output .= '<form method="get">';
            $output .= '<div class="wp-block-buttons">';
            $output .= '<div class="wp-block-button">';
            $output .= '<input class="wp-block-button__link" type="submit" value="Create" name="edit_mode">';
            $output .= '</div>';
            $output .= '<div class="wp-block-button">';
            //$output .= '<a class="wp-block-button__link" href="/">Cancel</a>';
            $output .= '<input class="wp-block-button__link" type="submit" value="Cancel" name="edit_mode">';
            $output .= '</div>';
            $output .= '</div>';
            $output .= '</form>';

            return $output;
        }
        
        function select_available_time($host=0, $date=0) {
            if ($host==0) return '$host is required';
            if ($date==0) return '$date is required';
            $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}calendars WHERE event_host = {$host}", OBJECT );
            foreach ( $results as $index=>$result ) {
                $output .= $result->event_start . ' - ' . $result->event_end;
            }
            return $output;

            date(get_option('date_format'));
            $output  = '<option value="no_select">-- Select a time --</option>';
            $output .= '<option value="08000900">08:00-09:00</option>';
            $output .= '<option value="09001000">09:00-10:00</option>';
            $output .= '<option value="10001100">10:00-11:00</option>';
            $output .= '<option value="11001200">11:00-12:00</option>';
            return $output;
        }
        
        function select_time() {
            date(get_option('date_format'));
            $output  = '<option value="no_select">-- Select a time --</option>';
            $output .= '<option value="08000900">08:00-09:00</option>';
            $output .= '<option value="09001000">09:00-10:00</option>';
            $output .= '<option value="10001100">10:00-11:00</option>';
            $output .= '<option value="11001200">11:00-12:00</option>';
            return $output;
        }
        
        function select_options( $default_id=null ) {

            $results = get_orders();
            $output = '<option value="no_select">-- Select an option --</option>';
            foreach ($results as $index => $result) {
                if ( $results[$index]->ID == $default_id ) {
                    $output .= '<option value="'.$results[$index]->ID.'" selected>';
                } else {
                    $output .= '<option value="'.$results[$index]->ID.'">';
                }
                $output .= $results[$index]->display_name;
                $output .= '</option>';
            }
            $output .= '<option value="delete_select">-- Remove this --</option>';
            return $output;    
        }

        function create_tables() {
        
            global $wpdb;
            $charset_collate = $wpdb->get_charset_collate();
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

            $sql = "CREATE TABLE `{$wpdb->prefix}events` (
                event_id int NOT NULL AUTO_INCREMENT,
                event_begin int NOT NULL,
                event_end int,
                event_title varchar(255),
                event_auther int,
                event_host int,
                txid varchar(255),
                is_deleted boolean,
                PRIMARY KEY  (event_id)
            ) $charset_collate;";        
            dbDelta($sql);

        }
        
    }
    new calendars();
}
?>