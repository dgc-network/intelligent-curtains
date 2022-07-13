<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}
if (!class_exists('timeslots')) {

    class timeslots {

        /**
         * Class constructor
         */
        public function __construct() {
            add_shortcode('timeslot-list', __CLASS__ . '::list_mode');
            self::create_tables();
        }

        function edit_mode( $_id=0, $_mode='' ) {

            if ($_id==0){
                $_mode='Create';
            }

            if( isset($_POST['submit_action']) ) {

                if( $_POST['submit_action']=='Create' ) {
        
                    global $wpdb;
                    $table = $wpdb->prefix.'timeslots';
                    $data = array(
                        'timeslot_begin' => $_POST['_timeslot_begin'],
                        'timeslot_end' => $_POST['_timeslot_end'],
                        'timeslot_session' => $_POST['_timeslot_session'],
                    );
                    $format = array('%s', '%s', '%d');
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
                        $table = $wpdb->prefix.'timeslots';
                        $data = array(
                            'timeslot_begin' => $_POST['_timeslot_begin'],
                            'timeslot_end' => $_POST['_timeslot_end'],
                            'timeslot_session' => intval($_POST['_timeslot_session']),
                        );
                        $where = array('timeslot_id' => $_id);
                        $wpdb->update( $table, $data, $where );
                    }
                }
            
                if( $_POST['submit_action']=='Delete' ) {
            
                    global $wpdb;
                    $table = $wpdb->prefix.'timeslots';
                    $where = array('timeslot_id' => $_id);
                    $deleted = $wpdb->delete( $table, $where );
                }

                //unset($_GET['edit_mode']);
                //unset($_POST['edit_mode']);
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
            $row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}timeslots WHERE timeslot_id = {$_id}", OBJECT );
            $output  = '<form method="post">';
            $output .= '<figure class="wp-block-table"><table><tbody>';
            $output .= '<tr><td>'.'Begin:'.'</td><td><input style="width: 100%" type="text" name="_timeslot_begin" value="'.$row->timeslot_begin.'"></td></tr>';
            $output .= '<tr><td>'.'End:'.'</td><td><input style="width: 100%" type="text" name="_timeslot_end" value="'.$row->timeslot_end.'"></td></tr>';
            $output .= '<tr><td>'.'Session:'.'</td><td><input style="width: 100%" type="text" name="_timeslot_session" value="'.$row->timeslot_session.'"></td></tr>';
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
                //return 'I am here';
            }            

            /**
             * List Mode
             */
            global $wpdb;
            $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}timeslots ORDER BY timeslot_begin", OBJECT );
            $output  = '<h2>Timeslots</h2>';
            $output .= '<figure class="wp-block-table"><table><tbody>';
            $output .= '<tr><td>Begin</td><td>End</td><td>Session</td></tr>';
            foreach ( $results as $index=>$result ) {
                $output .= '<tr>';
                $output .= '<td><a href="?edit_mode=Edit&_id='.$result->timeslot_id.'">'.$result->timeslot_begin.'</a></td>';
                $output .= '<td>'.$result->timeslot_end.'</td>';
                if ($result->timeslot_session==0) $session_display='midnight';
                if ($result->timeslot_session==1) $session_display='morning';
                if ($result->timeslot_session==2) $session_display='afternoon';
                if ($result->timeslot_session==3) $session_display='night';
                $output .= '<td>'.$session_display.'</td>';
                $output .= '</tr>';
            }
            $output .= '</tbody></table></figure>';

            $output .= '<form method="get">';
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

            $sql = "CREATE TABLE `{$wpdb->prefix}timeslots` (
                timeslot_id int NOT NULL AUTO_INCREMENT,
                timeslot_begin varchar(10) NOT NULL,
                timeslot_end varchar(10),
                timeslot_session int(1),
                PRIMARY KEY  (timeslot_id)
            ) $charset_collate;";        
            dbDelta($sql);

        }
        
    }
    new timeslots();
}
?>