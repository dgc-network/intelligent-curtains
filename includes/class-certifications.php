<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly.

if (!class_exists('certifications')) {

    class certifications {

        /**
         * Class constructor
         */
        public function __construct() {
            add_shortcode('certification-list', __CLASS__ . '::list_mode');
            self::create_tables();
        }

        function certification_settings_page_callback(){
            echo do_shortcode('[certification-list]');
        }

        function available_timeslots( $dateText='' ) {
            $output = '<div style="display:flex">';
            global $wpdb;
            $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}timeslots WHERE timeslot_session = 1", OBJECT );
            $output .= '<div style="text-align:center; width:100px">';
            $output .= '<div>上午</div>';
            foreach ( $results as $index=>$result ) {
                $output .= '<div class="timepicker" style="margin:5px; border-style:solid; border-width:thin;">'.$result->timeslot_begin.'</div>';
            }
            $output .= '</div>';
            $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}timeslots WHERE timeslot_session = 2", OBJECT );
            $output .= '<div style="text-align:center; width:100px">';
            $output .= '<div>下午</div>';
            foreach ( $results as $index=>$result ) {
                $output .= '<div class="timepicker" style="margin:5px; border-style:solid; border-width:thin;">'.$result->timeslot_begin.'</div>';
            }
            $output .= '</div>';
            $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}timeslots WHERE timeslot_session = 3", OBJECT );
            $output .= '<div style="text-align:center; width:100px">';
            $output .= '<div>晚上</div>';
            foreach ( $results as $index=>$result ) {
                $output .= '<div class="timepicker" style="margin:5px; border-style:solid; border-width:thin;">'.$result->timeslot_begin.'</div>';
            }
            $output .= '</div>';
            $output .= '</div>';
            return $output;
        }

        function booking( $_id=0 ) {

            if ($_id==0){
                return '<div>ID is required</div>';
            }

            if( isset($_POST['submit_action']) ) {
                if( $_POST['submit_action']=='Cancel' ) {
                    unset($_POST['view_mode']);
                    return self::list_mode();
                }
            }

            $user = new WP_User($_id);
            $output  = '<h2>'.$user->display_name.'的服務預約</h2>';
            $output .= '<div id="datepicker"></div>';
            $output .= self::available_timeslots($dateText);
            ?>
            <script>

                jQuery(document).ready(function($) {
                    $("#datepicker").datepicker({
                        onSelect: function(dateText) {
                            //console.log("Selected date: " + dateText + "; input's current value: " + this.value);
                            $(this).change();
                        }
                    }).on("change", function() {
                        //console.log("Got change event from field");
                        var data = {
			                action: 'my_action',
			                whatever: 1234
                        }

                        jQuery.ajax({
                            type : "post",
                            dataType : "json",
                            //url : myAjax.ajaxurl,
                            url : 'admin-ajax.php',
                            data : {action: "my_action", post_id : post_id, nonce: nonce},
                            success: function(response) {
                                if(response.type == "success") {
                                    //jQuery("#like_counter").html(response.like_count);
                                    alert("Your like could be added");
                                } else {
                                    alert("Your like could not be added");
                                }
                            }
                        });
                 
                    });

                    $('.timepicker').on({
                        mouseenter: function(){
                            $(this).css({"border-color":"red","color":"red","cursor":"pointer"});
                        },
                        mouseleave: function(){
                            $(this).css({"border-color":"gray","color":"gray","cursor":"default"});
                        },
                        click: function(){
                            $(this).css({"border-color":"red","color":"red","cursor":"pointer"});
                        }
                    });
                });

            </script>
            <?php
            $output .= '<form method="post">';
            $output .= '<input type="hidden" value="'.$_id.'" name="_id">';
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

        function available_setting( $_id=0 ) {

            if ($_id==0){
                return '<div>ID is required</div>';
            }

            if( isset($_POST['submit_action']) ) {
                if( $_POST['submit_action']=='Submit' ) {
                    global $wpdb;
                    $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}timeslots ORDER BY timeslot_begin", OBJECT );
                    foreach ( $results as $index=>$result ) {
                        if ($_POST['_available_selected_'.$index]=='true') {
                            $row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}available_timeslots WHERE available_host={$_id} AND available_date={$_POST['_available_date']} AND available_time_begin={$result->timeslot_begin}", OBJECT );
                            if (empty($row)) {
                                $table = $wpdb->prefix.'available_timeslots';
                                $data = array(
                                    'available_host' => $_id,
                                    'available_date' => $_POST['_available_date'],
                                    'available_time_begin' => $result->timeslot_begin,
                                );
                                $format = array('%d', '%s', '%s');
                                $insert_id = $wpdb->insert($table, $data, $format);
                            }
                        } else {
                            $row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}available_timeslots WHERE available_host={$_id} AND available_date={$_POST['_available_date']} AND available_time_begin={$result->timeslot_begin}", OBJECT );
                            if (!empty($row)) {
                                $table = $wpdb->prefix.'available_timeslots';
                                $where = array(
                                    'available_host' => $_id, 
                                    'available_date' => $_POST['_available_date'], 
                                    'available_time_begin' => $result->timeslot_begin, 
                                );
                                $deleted = $wpdb->delete( $table, $where );
                            }
                        }
                    }        
                }
                unset($_POST['view_mode']);
                return self::list_mode();
            }

            $user = new WP_User($_id);
            $output  = '<h2>Available time setting</h2>';
            $output  = '<div>'.$user->display_name.'</div>';
            $output .= '<form method="post">';
            global $wpdb;
            $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}timeslots ORDER BY timeslot_begin", OBJECT );
            $output .= '<div><input id="datepicker" type="text" name="_available_date"></div>';
            $output .= '<div>';
            foreach ( $results as $index=>$result ) {
                $output .= '<input type="checkbox" value="true" name="_available_selected_'.$index.'"';
                $output .= '> '.$result->timeslot_begin.' ~ '.$result->timeslot_end.'<br>';
            }
            $output .= '</div>';
            ?>
            <script>
                jQuery(document).ready(function($) {
                    $("#datepicker").datepicker()
                });
            </script>
            <?php

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

        public function list_mode() {

            if( isset($_POST['view_mode']) ) {
                if ($_POST['view_mode']=='Available') return self::available_setting($_POST['_id']);
                if ($_POST['view_mode']=='Booking') return self::booking($_POST['_id']);
                if ($_POST['view_mode']=='More...') return self::see_more($_POST['_id']);
            }

            $args = array(
                'post_type'      => 'product',
                //'product_cat'    => 'Courses',
                //'product_cat'    => 'Certification',
                'product_cat'    => 'Reservation',
                'posts_per_page' => 100,
                'order'          => 'ASC'
            );
            
            $customer_orders = [];
            foreach ( wc_get_is_paid_statuses() as $paid_status ) {
                $customer_orders += wc_get_orders( [
                    'type'        => 'shop_order',
                    'limit'       => - 1,
                    'status'      => $paid_status,
                ] );
            }
            $order_items = [];
            $loop = new WP_Query( $args );
            while ( $loop->have_posts() ) : $loop->the_post();
                global $product;
                foreach ( $customer_orders as $order ) {
                    foreach ( $order->get_items() as $item ) {
                        $item_product = $item->get_product();
                        if ($item_product->get_id()==$product->get_id()){
                            array_push($order_items, $item);
                        }
                    }
                }
            endwhile;
            wp_reset_query();

            $output  = '<h2>認證列表</h2>';
            $output .= '<div style="display:flex">';
            foreach ( $order_items as $item ) {
                $order = $item->get_order();
                $product = $item->get_product();
                $user = $order->get_user();

                $output .= '<div style="display:flex;border:1px solid;padding:10px">';
                $output .= '<div style="display:flex;align-items:center;margin:20px">';
                $output .= '<img src="'.get_avatar_url($order->get_customer_id()).'">';
                $output .= '</div>';
                $output .= '<div>';

                $output .= '<h3>'.$user->display_name.'</h3>';

                $output .= ''.$item->get_name().'';
                $output .= '<form method="post">';
                $output .= '<div class="wp-block-buttons">';
                $output .= '<div class="wp-block-button">';
                $output .= '<input class="wp-block-button__link" type="submit" value="Booking" name="view_mode">';
                $output .= '</div>';
                $output .= '<div class="wp-block-button">';
                //$output .= '<input class="wp-block-button__link" type="submit" value="More..." name="view_mode">';
                $output .= '</div>';
                $output .= '</div>';
                $output .= '<input type="hidden" value="'.$order->get_user_id().'" name="_id">';
                $output .= '</form>';
                $output .= '</div>';
                $output .= '</div>';
            }
            $output .= '</div>';
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

        function create_tables() {
        
            global $wpdb;
            $charset_collate = $wpdb->get_charset_collate();
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

            $sql = "CREATE TABLE `{$wpdb->prefix}available_timeslots` (
                available_id int NOT NULL AUTO_INCREMENT,
                available_host int NOT NULL,
                available_date varchar(10) NOT NULL,
                available_time_begin varchar(10) NOT NULL,
                PRIMARY KEY  (available_id)
            ) $charset_collate;";        
            dbDelta($sql);
        }        
    }
    new certifications();
}

/*
add_action( 'admin_footer', 'my_action_javascript' ); // Write our JS below here
function my_action_javascript() { ?>
	<script type="text/javascript" >
	jQuery(document).ready(function($) {

        $("#datepicker").datepicker({
            onSelect: function(dateText) {
                //console.log("Selected date: " + dateText + "; input's current value: " + this.value);
                $(this).change();
            }
        }).on("change", function() {
            //console.log("Got change event from field");
            var data = {
			    'action': 'my_action',
			    'whatever': 1234
            }
	    	// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
            jQuery.post(ajaxurl, data, function(response) {
	    		alert('Got this from the server: ' + response);
    		});
        };

        });
        $('.timepicker').on({
            mouseenter: function(){
                $(this).css({"border-color":"red","color":"red","cursor":"pointer"});
            },
            mouseleave: function(){
                $(this).css({"border-color":"gray","color":"gray","cursor":"default"});
            },
            click: function(){
                $(this).css({"border-color":"red","color":"red","cursor":"pointer"});
            }
        });

	});
	</script> <?php
}
*/
add_action( 'wp_ajax_my_action', 'my_action' );
add_action( 'wp_ajax_nopriv_my_action', 'my_action' );
function my_action() {
	global $wpdb; // this is how you get access to the database

	$whatever = intval( $_POST['whatever'] );

	$whatever += 10;

    echo $whatever;

	wp_die(); // this is required to terminate immediately and return a proper response
}
?>