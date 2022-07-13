<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly.

if (!class_exists('courses')) {

    class courses {

        /**
         * Class constructor
         */
        public function __construct() {
            add_shortcode('course-list', __CLASS__ . '::list_mode');
            self::create_tables();
        }

        function course_settings_page_callback(){
            echo do_shortcode('[course-list]');
        }

        function profit_sharing( $_id=0 ) {

            if ($_id==0) return '<div>learning ID is required</div>';

            if( isset($_POST['submit_action']) ) {
        
                if( $_POST['submit_action']=='Cancel' ) {
                    unset($_GET['edit_mode']);
                    unset($_POST['edit_mode']);
                    return self::list_mode();
                }

                global $wpdb;
                $current_user_id = get_current_user_id();
                $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}learning_profit_sharing WHERE learning_id = {$_id}", OBJECT );
                foreach ($results as $index => $result) {
                    if (( $_POST['_sharing_id_'.$index]=='delete_select' )){
                        $table = $wpdb->prefix.'learning_profit_sharing';
                        $where = array(
                            'l_p_s_id' => $results[$index]->l_p_s_id
                        );
                        $wpdb->delete( $table, $where );    
                    } else {
                        $table = $wpdb->prefix.'learning_profit_sharing';
                        $data = array(
                            'sharing_title' => $_POST['_sharing_title_'.$index],
                            'sharing_id' => $_POST['_sharing_id_'.$index],
                            'sharing_profit' => $_POST['_sharing_profit_'.$index],
                        );
                        $where = array(
                            'l_p_s_id' => $results[$index]->l_p_s_id
                        );
                        $wpdb->update( $table, $data, $where );    
                    }
                }
                if ( !($_POST['_sharing_title']=='') ){
                    $table = $wpdb->prefix.'learning_profit_sharing';
                    $data = array(
                        'learning_id' => $_id,
                        'sharing_id' => $_POST['_sharing_id'],
                        'sharing_title' => $_POST['_sharing_title'],
                        'sharing_profit' => $_POST['_sharing_profit'],
                    );
                    $format = array('%d', '%d', '%s', '%f');
                    $wpdb->insert($table, $data, $format);
                }
            }

            /** 
             * profit_sharing header
             */
            global $wpdb;
            $row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}course_learnings WHERE learning_id = {$_id}", OBJECT );
            $product = wc_get_product( $row->course_id );

            $output  = '<h2>課程成本結構設定</h2>';
            $output .= '<form method="post">';
            $output .= '<figure class="wp-block-table"><table><tbody>';
            $output .= '<tr><td>'.'Course:'.'</td><td>'.$product->get_name().'</td></tr>';
            $output .= '<tr><td>'.'Learnings:'.'</td><td><a href="'.$row->learning_link.'">'.$row->learning_title.'</a></td></tr>';
            $output .= '</tbody></table></figure>';

            /** 
             * profit sharing relationship with learning
             */
            $output .= '<figure class="wp-block-table"><table><tbody>';
            $output .= '<tr><td>'.'#'.'</td><td>Titles</td><td>Sharing</td><td>Profit</td></tr>';
            $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}learning_profit_sharing WHERE learning_id = {$_id}", OBJECT );
            foreach ($results as $index => $result) {
                $output .= '<tr><td>'.($index+1).'</td>';
                $output .= '<td><input size="20" type="text" name="_sharing_title_'.$index.'" value="'.$results[$index]->sharing_title.'"></td>';
                $output .= '<td>'.'<select name="_sharing_id_'.$index.'">'.self::select_users($results[$index]->sharing_id).'</select></td>';
                $output .= '<td><input size="5" type="text" name="_sharing_profit_'.$index.'" value="'.$results[$index]->sharing_profit.'"></td>';
                $output .= '</tr>';
            }
            $output .= '<tr><td>'.'#'.'</td>';
            $output .= '<td><input size="20" type="text" name="_sharing_title"></td>';
            $output .= '<td>'.'<select name="_sharing_id">'.self::select_users().'</select>'.'</td>';
            $output .= '<td><input size="5" type="text" name="_sharing_profit"></td>';
            $output .= '</tr></tbody></table></figure>';
            
            /** 
             * profit sharing footer
             */
            $output .= '<div class="wp-block-buttons">';
            $output .= '<div class="wp-block-button">';
            $output .= '<input class="wp-block-button__link" type="submit" value="Submit" name="submit_action">';
            $output .= '</div>';
            //$output .= '</form>';
            //$output .= '<form method="get">';
            $output .= '<div class="wp-block-button">';
            //$output .= '<input class="wp-block-button__link" type="submit" value="Cancel"';
            $output .= '<input class="wp-block-button__link" type="submit" value="Cancel" name="submit_action">';
            $output .= '</div>';
            $output .= '</div>';
            $output .= '</form>';

            return $output;
        }

        function course_learnings( $_id=0 ) {

            if ($_id==0) return '<div>course ID is required</div>';

            if( isset($_POST['submit_action']) ) {
        
                //if ($_POST['view_mode']=='profit_sharing') return self::profit_sharing($_POST['_id']);

                if( $_POST['submit_action']=='Sharing' ) {
                    //unset($_GET['edit_mode']);
                    //unset($_POST['edit_mode']);
                    return self::profit_sharing($_POST['_learning_id']);
                }
/*
                if( $_POST['submit_action']=='Cancel' ) {
                    //unset($_GET['edit_mode']);
                    unset($_POST['edit_mode']);
                    return self::list_mode();
                }
*/
                if ( !($_POST['_learning_id']=='') ){

                    global $wpdb;
                    $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}course_learnings WHERE course_id = {$_id}", OBJECT );
                    foreach ($results as $index => $result) {
                        if (( $_POST['_learning_title_'.$index]=='delete' ) || ( $_POST['_learning_link_'.$index]=='delete' ) ){
                            $table = $wpdb->prefix.'course_learnings';
                            $where = array(
                                'learning_id' => $results[$index]->learning_id
                            );
                            $wpdb->delete( $table, $where );    
                        } else {
                            $table = $wpdb->prefix.'course_learnings';
                            $data = array(
                                'learning_title' => $_POST['_learning_title_'.$index],
                                'learning_hours' => $_POST['_learning_hours_'.$index],
                                'learning_link' => $_POST['_learning_link_'.$index],
                                'teaching_id' => $_POST['_teaching_id_'.$index],
                                'is_witness' => rest_sanitize_boolean($_POST['_is_witness_'.$index]),
                            );
                            $where = array(
                                'learning_id' => $results[$index]->learning_id
                            );
                            $wpdb->update( $table, $data, $where );    
                        }
                    }    
                }

                if ( !($_POST['_learning_title']=='') ){
                    $table = $wpdb->prefix.'course_learnings';
                    $data = array(
                        //'course_id' => intval($_GET['_id']),
                        'course_id' => intval($_id),
                        'learning_title' => sanitize_text_field($_POST['_learning_title']),
                        'learning_hours' => floatval($_POST['_learning_hours']),
                        'learning_link' => sanitize_text_field($_POST['_learning_link']),
                        'teaching_id' => intval($_POST['_teaching_id']),
                        'is_witness' => rest_sanitize_boolean($_POST['_is_witness']),
                    );
                    $format = array('%d', '%s', '%f', '%s', '%d', '%d');
                    $wpdb->insert($table, $data, $format);
                }
            }

            /** 
             * course_learnings header
             */
            $product = wc_get_product( $_id );
            $output  = '<h2>課程vs學習項目設定</h2>';
            $output .= '<figure class="wp-block-table"><table><tbody>';
            $output .= '<tr><td>'.'Title:'.'</td><td>'.$product->get_name().'</td></tr>';
            $output .= '<tr><td>'.'Created:'.'</td><td>'.$product->get_date_created().'</td></tr>';
            $output .= '<tr><td>'.'List Price:'.'</td><td>'.$product->get_regular_price().'</td></tr>';
            $output .= '<tr><td>'.'Sale Price:'.'</td><td>'.$product->get_sale_price().'</td></tr>';
            $output .= '</tbody></table></figure>';

            /** 
             * course_learnings details
             */
            $TotalHours=0;
            global $wpdb;
            $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}course_learnings WHERE course_id = {$_id}", OBJECT );
            $output .= '<figure class="wp-block-table"><table><tbody>';
            //$output .= '<tr><td>#</td><td>Titles</td><td>Hours</td><td>Link</td><td>Mentor</td><td>Witness</td><td>Profit</td></tr>';
            $output .= '<tr><td>#</td><td>Titles</td><td>Hours</td><td>Link</td></tr>';
            $output .= '<form method="post">';
            foreach ($results as $index => $result) {
                
                //$output .= '<form method="post">';
                $output .= '<input type="hidden" name="view_mode" value="Learnings">';
                $output .= '<input type="hidden" name="_id" value="'.$_id.'">';
                $output .= '<input type="hidden" name="_learning_id" value="'.$results[$index]->learning_id.'">';
                $output .= '<tr><td>'.($index+1).'</td>';
                $output .= '<td><input size="20" type="text" name="_learning_title_'.$index.'" value="'.$results[$index]->learning_title.'"></td>';
                $output .= '<td><input size="2" type="text" name="_learning_hours_'.$index.'" value="'.$results[$index]->learning_hours.'"></td>';
                $output .= '<td><input size="60" type="text" name="_learning_link_'.$index.'" value="'.$results[$index]->learning_link.'"></td>';
/*
                $output .= '<td><select name="_teaching_id_'.$index.'" style="max-width:80px;">'.self::select_teachings($results[$index]->teaching_id).'</select></td>';
                $output .= '<td><input type="checkbox" name="_is_witness_'.$index.'"';
                if ($results[$index]->is_witness) {$output .= ' value="true" checked';}
                $output .= '></td>';
                $output .= '<td><input type="submit" name="submit_action" value="Sharing"></td>';
*/                
                $output .= '</tr>';
                //$output .= '</form>';                
                $TotalHours += floatval($results[$index]->learning_hours);
            }
            /** 
             * course_learnings footer
             */
            //$output .= '<form method="post">';
            $output .= '<input type="hidden" name="view_mode" value="Learnings">';
            $output .= '<input type="hidden" name="_id" value="'.$_id.'">';
            $output .= '<tr><td>'.'#'.'</td>';
            $output .= '<td><input size="20" type="text" name="_learning_title"></td>';
            $output .= '<td><input size="2" type="text" name="_learning_hours"></td>';
            $output .= '<td><input size="60" type="text" name="_learning_link"></td>';
/*            
            $output .= '<td><select name="_teaching_id" style="max-width:80px;">'.self::select_teachings().'</select>'.'</td>';
            $output .= '<td><input type="checkbox" name="_is_witness"></td>';
*/            
            $output .= '</tr>';
            $output .= '<tr><td colspan=2>'.'Total Hours:'.'</td>';
            $output .= '<td>'.$TotalHours.'</td><td></td>';
            $output .= '</tr></tbody></table></figure>';            

            $output .= '<div class="wp-block-buttons" style="display:flex">';
            $output .= '<div class="wp-block-button">';
            $output .= '<input class="wp-block-button__link" type="submit" value="Submit" name="submit_action">';
            $output .= '</div>';
            $output .= '</form>';

            $output .= '<form method="post">';
            $output .= '<div class="wp-block-button">';
            $output .= '<input class="wp-block-button__link" type="submit" value="Cancel" name="submit_action">';
            $output .= '</div>';
            $output .= '</div>';
            $output .= '</form>';

            return $output;
        }

        function item_orders( $_id=0 ) {

            if ($_id==0) return '<div>course ID is required</div>';

            if( isset($_POST['submit_action']) ) {
/*        
                if( $_POST['submit_action']=='Cancel' ) {
                    //unset($_GET['edit_mode']);
                    unset($_POST['edit_mode']);
                    return self::list_mode();
                }

                global $wpdb;
                $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}course_learnings WHERE course_id = {$_id}", OBJECT );
                foreach ($results as $index => $result) {
                    if (( $_POST['_learning_title_'.$index]=='delete' ) || ( $_POST['_learning_link_'.$index]=='delete' ) ){
                        $table = $wpdb->prefix.'course_learnings';
                        $where = array(
                            'learning_id' => $results[$index]->learning_id
                        );
                        $wpdb->delete( $table, $where );    
                    } else {
                        $table = $wpdb->prefix.'course_learnings';
                        $data = array(
                            'learning_title' => $_POST['_learning_title_'.$index],
                            'learning_hours' => $_POST['_learning_hours_'.$index],
                            'learning_link' => $_POST['_learning_link_'.$index],
                            'teaching_id' => $_POST['_teaching_id_'.$index],
                            'is_witness' => rest_sanitize_boolean($_POST['_is_witness_'.$index]),
                        );
                        $where = array(
                            'learning_id' => $results[$index]->learning_id
                        );
                        $wpdb->update( $table, $data, $where );    
                    }
                }

                if ( !($_POST['_learning_title']=='') ){
                    $table = $wpdb->prefix.'course_learnings';
                    $data = array(
                        'course_id' => intval($_GET['_id']),
                        'learning_title' => sanitize_text_field($_POST['_learning_title']),
                        'learning_hours' => floatval($_POST['_learning_hours']),
                        'learning_link' => sanitize_text_field($_POST['_learning_link']),
                        'teaching_id' => intval($_POST['_teaching_id']),
                        'is_witness' => rest_sanitize_boolean($_POST['_is_witness']),
                    );
                    $format = array('%d', '%s', '%f', '%s', '%d', '%d');
                    $wpdb->insert($table, $data, $format);
                }
*/                
            }

            /** 
             * product_item_orders header
             */
            $product = wc_get_product( $_id );
            $output  = '<h2>課程註冊列表</h2>';
            $output .= '<form method="post">';
            $output .= '<figure class="wp-block-table"><table><tbody>';
            $output .= '<tr><td>Title: </td><td>'.$product->get_name().'</td></tr>';
            $output .= '<tr><td>Created:'.'</td><td>'.$product->get_date_created().'</td></tr>';
            $output .= '<tr><td>List Price:'.'</td><td>'.$product->get_regular_price().'</td></tr>';
            $output .= '<tr><td>Sale Price:'.'</td><td>'.$product->get_sale_price().'</td></tr>';
            $output .= '</tbody></table></figure>';

            /** 
             * product_item_orders detail
             */
            $customer_orders = [];
            foreach ( wc_get_is_paid_statuses() as $paid_status ) {
                $customer_orders += wc_get_orders( [
                    'type'        => 'shop_order',
                    'limit'       => - 1,
                    //'customer_id' => $user_id,
                    'status'      => $paid_status,
                ] );
            }
            $order_items = [];
            foreach ( $customer_orders as $order ) {
                foreach ( $order->get_items() as $item ) {
                    $product = $item->get_product();
                    if ($product->get_id()==$_id){
                        array_push($order_items, $item);
                    }
                }
            }

            $output .= '<figure class="wp-block-table"><table><tbody>';
            $output .= '<tr><td>#</td><td>Name</td><td>Email</td></tr>';
            $index=0;
            foreach ( $order_items as $item ) {
                $order = $item->get_order();
                $user = $order->get_user();
                $output .= '<tr>';
                $output .= '<td>'.($index+1).'</td>';
                $output .= '<td>'.$user->display_name.'</td>';
                $output .= '<td>'.$user->user_email.'</td>';
                $output .= '</tr>';
            }
            $output .= '</tbody></table></figure>';            

            /** 
             * product_item_orders footer
             */
            $output .= '<div class="wp-block-buttons">';
            $output .= '<div class="wp-block-button">';
            //$output .= '<input class="wp-block-button__link" type="submit" value="Submit" name="submit_action">';
            $output .= '</div>';
            $output .= '<div class="wp-block-button">';
            //$output .= '<input class="wp-block-button__link" type="submit" value="Cancel" name="submit_action">';
            $output .= '<input class="wp-block-button__link" type="submit" value="Ok" name="submit_action">';
            $output .= '</div>';
            $output .= '</div>';
            $output .= '</form>';

            return $output;
        }

        static function list_mode() {

            if( isset($_POST['view_mode']) ) {
                //if ($_POST['view_mode']=='profit_sharing') return self::profit_sharing($_POST['_id']);
                //if ($_POST['view_mode']=='course_learnings') return self::course_learnings($_POST['_id']);
                //if ($_POST['view_mode']=='item_orders') return self::item_orders($_POST['_id']);
                if ($_POST['view_mode']=='Learnings') return self::course_learnings($_POST['_id']);
                if ($_POST['view_mode']=='Orders') return self::item_orders($_POST['_id']);
                if ($_POST['view_mode']=='Sharing') return self::learnings_list($_POST['_id']);
            }

            $args = array(
                'post_type'      => 'product',
                'product_cat'    => 'Courses',
                'posts_per_page' => 100,
                'order'          => 'ASC'
            );
                
            $output  = '<h2>課程列表</h2>';
            $output .= '<figure class="wp-block-table"><table><tbody>';
            $output .= '<tr><td>#</td><td>Title</td><td>Course</td><td>Item</td><td>Profit</td></tr>';
            $loop = new WP_Query( $args );
            while ( $loop->have_posts() ) : $loop->the_post();
                global $product;
                $output .= '<tr>';
                $output .= '<form method="post">';
                $output .= '<input type="hidden" value="'.$product->get_id().'" name="_id">';
                $output .= '<td>'.$product->get_id().'</td>';
                $output .= '<td>'.$product->get_name().'</td>';
                $output .= '<td><input class="wp-block-button__link" type="submit" value="Learnings" name="view_mode"></td>';
                $output .= '<td><input class="wp-block-button__link" type="submit" value="Orders" name="view_mode"></td>';
                $output .= '<td><input class="wp-block-button__link" type="submit" value="Sharing" name="view_mode"></td>';
                $output .= '</form>';
                $output .= '</tr>';
            endwhile;
            wp_reset_query();
            $output .= '</tbody></table></figure>';

            $output .= '<form method="post">';
            $output .= '<div class="wp-block-buttons">';
            $output .= '<div class="wp-block-button">';
            $output .= '<a class="wp-block-button__link" href="/wp-admin/post-new.php?post_type=product">Create</a>';
            //$output .= '<input class="wp-block-button__link" type="submit" value="Create" name="view_mode">';
            $output .= '</div>';
            $output .= '<div class="wp-block-button">';
            //$output .= '<a class="wp-block-button__link" href="/">Cancel</a>';
            $output .= '</div>';
            $output .= '</div>';
            $output .= '</form>';
            return $output;
        }
        
        function select_options( $default_id=null ) {

            $args = array(
                'post_type'      => 'product',
                'product_cat'    => 'Courses',
                'posts_per_page' => 100,
                'order'         => 'ASC'
            );       
            $loop = new WP_Query( $args );
        
            $output = '<option value="no_select">-- Select an option --</option>';
            while ( $loop->have_posts() ) : $loop->the_post();
                global $product;
                if ( $product->get_id() == $default_id ) {
                    $output .= '<option value="'.$product->get_id().'" selected>';
                } else {
                    $output .= '<option value="'.$product->get_id().'">';
                }
                $output .= $product->get_name();
                $output .= '</option>';        
            endwhile;
            $output .= '<option value="delete_select">-- Remove this --</option>';

            wp_reset_query();
            return $output;
        }

        function select_learnings( $course_id=null, $default_id=null ) {

            if ($course_id==null){
                $output = '<option value="no_select">-- course_id is required --</option>';
                return $output;    
            }
            global $wpdb;
            $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}course_learnings WHERE course_id={$course_id}", OBJECT );
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

        function select_teachings( $default_id=null ) {

            global $wpdb;
            $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}course_learnings", OBJECT );
            $output = '<option value="no_select">-- Select an option --</option>';
            foreach ($results as $index => $result) {
                if ( $results[$index]->learning_id == $default_id ) {
                    $output .= '<option value="'.$results[$index]->learning_id.'" selected>';
                } else {
                    $output .= '<option value="'.$results[$index]->learning_id.'">';
                }
                $product = wc_get_product( $results[$index]->course_id );
                $output .= $results[$index]->learning_title . '('. $product->get_name() . ')';
                $output .= '</option>';        
            }
            $output .= '<option value="delete_select">-- Remove this --</option>';
            return $output;    
        }

        function select_users( $default_id=null ) {

            $results = get_users();
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

            $sql = "CREATE TABLE `{$wpdb->prefix}course_learnings` (
                learning_id int NOT NULL AUTO_INCREMENT,
                course_id int NOT NULL,
                learning_hours float DEFAULT 1.0,
                learning_title varchar(255),
                learning_link varchar(255),
                teaching_id int DEFAULT 0,
                is_witness boolean,
                txid varchar(255),
                is_deleted boolean,
                PRIMARY KEY  (learning_id)
            ) $charset_collate;";        
            dbDelta($sql);

            $sql = "CREATE TABLE `{$wpdb->prefix}learning_profit_sharing` (
                l_p_s_id int NOT NULL AUTO_INCREMENT,
                learning_id int NOT NULL,
                sharing_title varchar(255),
                sharing_id int,
                sharing_profit float,
                txid varchar(255),
                is_deleted boolean,
                PRIMARY KEY  (l_p_s_id)
            ) $charset_collate;";        
            dbDelta($sql);
        }        
    }
    new courses();
}
?>
