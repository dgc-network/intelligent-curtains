<?php
if (!defined('ABSPATH')) exit;

if (!class_exists('orders')) {

    class orders {

        /**
         * Class constructor
         */
        public function __construct() {
            add_shortcode('order-list', __CLASS__ . '::list_mode');
            self::create_tables();
        }

        function course_learnings( $_id=0 ) {

            if ($_id==0) return '<div>course ID is required</div>';

            if( isset($_POST['submit_action']) ) {
                
                if( $_POST['submit_action']=='Cancel' ) {
                    unset($_GET['edit_mode']);
                    unset($_POST['edit_mode']);
                    return self::list_mode();
                }

                $current_user_id = get_current_user_id();
                global $wpdb;
                $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}user_course_learnings WHERE student_id = {$current_user_id} AND course_id = {$_id}", OBJECT );
                foreach ($results as $index => $result) {
                    if (( $_POST['_learning_id_'.$index]=='delete_select' ) || ( $_POST['_lecturer_witness_id_'.$index]=='delete_select' ) ){
                        $table = $wpdb->prefix.'user_course_learnings';
                        $where = array(
                            'u_c_l_id' => $results[$index]->u_c_l_id
                        );
                        $wpdb->delete( $table, $where );    
                    } else {
                        $table = $wpdb->prefix.'user_course_learnings';
                        $data = array(
                            'learning_id' => $_POST['_learning_id_'.$index],
                            'lecturer_id' => $_POST['_lecturer_id_'.$index],
                            'witness_id' => $_POST['_witness_id_'.$index],
                        );
                        $where = array(
                            'u_c_l_id' => $results[$index]->u_c_l_id
                        );
                        $wpdb->update( $table, $data, $where );    
                    }
                }
/*                
                if ( !($_POST['_learning_id']=='no_select') ){
                    $table = $wpdb->prefix.'user_course_learnings';
                    $data = array(
                        'student_id' => $current_user_id,
                        'learning_id' => $_POST['_learning_id'],
                        'lecturer_id' => $_POST['_lecturer_id'],
                        'witness_id' => $_POST['_witness_id'],
                        'course_id' => $_id,
                    );
                    $format = array('%d', '%d', '%d', '%d', '%d');
                    $wpdb->insert($table, $data, $format);
                }
*/                
            }

            /** 
             * user_course_learnings header
             */
            $current_user_id = get_current_user_id();
            $product = wc_get_product( $_id );

            $output  = '<h2>個人學習項目的輔導與認證</h2>';
            $output .= '<form method="post">';
            $output .= '<figure class="wp-block-table"><table><tbody>';
            $output .= '<tr><td>'.'Name:'.'</td><td>'.get_userdata($current_user_id)->display_name.'</td></tr>';
            $output .= '<tr><td>'.'Email:'.'</td><td>'.get_userdata($current_user_id)->user_email.'</td></tr>';
            $output .= '<tr><td>'.'Title:'.'</td><td>'.$product->get_name().'</td></tr>';
            $output .= '</tbody></table></figure>';

            /** 
             * user_course_learning detail
             */
            global $wpdb;
            $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}user_course_learnings WHERE student_id = {$current_user_id} AND course_id = {$_id}", OBJECT );
            if (empty($results)) {
                $c_results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}course_learnings WHERE course_id = {$_id}", OBJECT );
                foreach ($c_results as $index => $result) {
                    $table = $wpdb->prefix.'user_course_learnings';
                    $data = array(
                        'student_id' => $current_user_id,
                        'learning_id' => $c_results[$index]->learning_id,
                        'course_id' => $_id,
                    );
                    $format = array('%d', '%d', '%d');
                    $wpdb->insert($table, $data, $format);
                }
                $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}user_course_learnings WHERE student_id = {$current_user_id} AND course_id = {$_id}", OBJECT );
            }
            $output .= '<figure class="wp-block-table"><table><tbody>';
            $output .= '<tr><td>'.'#'.'</td><td>Learnings</td><td>Mentors</td><td>Witnesses</td></tr>';
            foreach ($results as $index => $result) {
                $output .= '<tr><td>'.($index+1).'</td>';
                $row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}course_learnings WHERE learning_id = {$results[$index]->learning_id}", OBJECT );
                $output .= '<td><a href="'.$row->learning_link.'">'.$row->learning_title.'</a></td>';
                $output .= '<td>'.'<select name="_lecturer_id_'.$index.'">'.self::select_lecturers($results[$index]->learning_id, $results[$index]->lecturer_id).'</select></td>';
                $output .= '<td>'.'<select name="_witness_id_'.$index.'">'.self::select_witnesses($results[$index]->learning_id, $results[$index]->witness_id).'</select></td>';
                $output .= '</tr>';
            }
            $output .= '</tbody></table></figure>';
            
            /** 
             * user_course_learnings footer
             */
            $output .= '<div class="wp-block-buttons">';
            $output .= '<div class="wp-block-button">';
            $output .= '<input class="wp-block-button__link" type="submit" value="Submit" name="submit_action">';
            $output .= '</div>';
            $output .= '<div class="wp-block-button">';
            $output .= '<input class="wp-block-button__link" type="submit" value="Cancel" name="submit_action">';
            $output .= '</div>';
            $output .= '</div>';
            $output .= '</form>';

            return $output;
        }

        function list_mode() {

            if( isset($_GET['view_mode']) ) {
                if ($_GET['view_mode']=='course_learnings') return self::course_learnings($_GET['_id']);
            }

            $user_id = get_current_user_id();
            $customer_orders = [];
            foreach ( wc_get_is_paid_statuses() as $paid_status ) {
                $customer_orders += wc_get_orders( [
                    'type'        => 'shop_order',
                    'limit'       => - 1,
                    'customer_id' => $user_id,
                    'status'      => $paid_status,
                ] );
            }

            $output  = '<h2>註冊課程列表</h2>';
            $output .= '<figure class="wp-block-table"><table><tbody>';
            $output .= '<tr><td>Courses</td><td>Date</td><td>Status</td></tr>';
            foreach ( $customer_orders as $order ) {

                $items = $order->get_items();
                foreach ( $order->get_items() as $item ) {
                    $product = $item->get_product();
                    if (strpos($product->get_categories(),'Courses')!==false) {
                        $output .= '<tr>';
                        $output .= '<td><a href="?view_mode=course_learnings&_id='.$product->get_id().'">'.$product->get_name().'</a></td>';
                        $output .= '<td>'.$order->get_date_created().'</td>';
                        $output .= '<td>'.$order->get_status().'</td>';
                        $output .= '</tr>';
                    }
                }
            }
            $output .= '</tbody></table></figure>';
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

        function select_lecturers( $learning_id=null, $default_id=null ) {

            if ($learning_id==null){
                $output = '<option value="no_select">-- learning id is required --</option>';
                return $output;    
            }
            global $wpdb;
            $t_results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}course_learnings WHERE teaching_id={$learning_id}", OBJECT );
            $output = '<option value="no_select">-- Select an option --</option>';
            foreach ($t_results as $t_index => $t_result) {
                $t_learning_id = $t_results[$t_index]->learning_id;
                $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}user_course_learnings WHERE learning_id={$t_learning_id}", OBJECT );
                foreach ($results as $index => $result) {
                    if ( $results[$index]->student_id == $default_id ) {
                        $output .= '<option value="'.$results[$index]->student_id.'" selected>';
                    } else {
                        $output .= '<option value="'.$results[$index]->student_id.'">';
                    }
                    $output .= get_userdata($results[$index]->student_id)->display_name;
                    $output .= '</option>';        
                }
                $output .= '<option value="delete_select">-- Remove this --</option>';
            }
            return $output;    
        }

        function select_witnesses( $learning_id=null, $default_id=null ) {

            if ($learning_id==null){
                $output = '<option value="no_select">-- learning id is required --</option>';
                return $output;    
            }
            global $wpdb;
            $t_results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}course_learnings WHERE teaching_id={$learning_id} AND is_witness", OBJECT );
            $output = '<option value="no_select">-- Select an option --</option>';
            foreach ($t_results as $t_index => $t_result) {
                $t_learning_id = $t_results[$t_index]->learning_id;
                $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}user_course_learnings WHERE learning_id={$t_learning_id}", OBJECT );
                foreach ($results as $index => $result) {
                    if ( $results[$index]->student_id == $default_id ) {
                        $output .= '<option value="'.$results[$index]->student_id.'" selected>';
                    } else {
                        $output .= '<option value="'.$results[$index]->student_id.'">';
                    }
                    $output .= get_userdata($results[$index]->student_id)->display_name;
                    $output .= '</option>';        
                }
                $output .= '<option value="delete_select">-- Remove this --</option>';
            }
            return $output;    
        }

        function create_tables() {
        
            global $wpdb;
            $charset_collate = $wpdb->get_charset_collate();
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

            $sql = "CREATE TABLE `{$wpdb->prefix}user_course_learnings` (
                u_c_l_id int NOT NULL AUTO_INCREMENT,
                student_id int NOT NULL,
                learning_id int NOT NULL,
                course_id int NOT NULL,
                lecturer_id int,
                lecture_date int,
                witness_id int,
                certified_date int,
                txid varchar(255),
                is_deleted boolean,
                teaching_id int,
                PRIMARY KEY  (u_c_l_id)
            ) $charset_collate;";        
            dbDelta($sql);            
        }        
    }
    new orders();
}
?>