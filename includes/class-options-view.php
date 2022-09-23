<?php
class Options_View {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param    string    $plugin_name     The name of the plugin.
	 * @param    string    $version    		The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	function run() {

		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_scripts' ) );
/*
		add_filter( 'woocommerce_product_tabs', array( __CLASS__, 'custom_product_tab' ) );
		add_action( 'woocommerce_single_product_summary', array( __CLASS__, 'custom_after_single_product_title' ), 6 );
		add_action( 'woocommerce_before_add_to_cart_form', array( __CLASS__, 'custom_before_add_to_cart_button' ), 10, 0 );	

		add_action( 'wp_ajax_woocommerce_ajax_add_to_cart', array( __CLASS__, 'woocommerce_ajax_add_to_cart' ) );
		add_action( 'wp_ajax_nopriv_woocommerce_ajax_add_to_cart', array( __CLASS__, 'woocommerce_ajax_add_to_cart' ) );

		add_filter( 'woocommerce_add_cart_item_data', array( __CLASS__, 'add_custom_cart_item_data' ), 25, 2 );
		add_action( 'woocommerce_before_calculate_totals', array( __CLASS__, 'add_custom_price' ), 9999, 1);
		add_filter( 'woocommerce_get_item_data', array( __CLASS__, 'get_custom_cart_item_data' ), 25, 2 );
		add_action( 'woocommerce_checkout_create_order_line_item', array( __CLASS__, 'custom_checkout_create_order_line_item' ), 20, 4 );

		add_filter( 'woocommerce_email_recipient_new_booking', array( __CLASS__, 'additional_customer_email_recipient' ), 10, 2 ); 
		add_filter( 'woocommerce_email_recipient_new_order', array( __CLASS__, 'additional_customer_email_recipient' ), 10, 2 ); // Optional (testing)
		
		add_action( 'woocommerce_after_shop_loop_item', array( __CLASS__, 'remove_add_to_cart_buttons' ), 1 );

		add_action( 'init', array( __CLASS__, 'custom_wc_product_countdown_html' ) );
*/
	}

	function enqueue_scripts() {
/*		
		wp_enqueue_script( 'jquery-js', plugin_dir_url( __FILE__ ) . 'assets/js/jquery.min.js', array( 'jquery' ), time(), true );
		wp_enqueue_script( 'qrcode-js', plugin_dir_url( __FILE__ ) . 'assets/js/jquery.qrcode.min.js', array( 'jquery' ), time(), true );

		wp_enqueue_script( 'custom-js', plugin_dir_url( __FILE__ ) . 'assets/js/options-view.js', array( 'jquery' ), time(), true );
		wp_enqueue_style( 'style-css', plugin_dir_url( __FILE__ ) . 'assets/css/options-view.css', '', time() );
*/
		wp_enqueue_script( 'jquery-js', 'assets/js/jquery.min.js', array( 'jquery' ), time(), true );
		wp_enqueue_script( 'qrcode-js', 'assets/js/jquery.qrcode.min.js', array( 'jquery' ), time(), true );

		wp_enqueue_script( 'custom-js', 'assets/js/options-view.js', array( 'jquery' ), time(), true );
		wp_enqueue_style( 'style-css', 'assets/css/options-view.css', '', time() );

		// Load the datepicker script (pre-registered in WordPress).
		wp_enqueue_script( 'jquery-ui-datepicker' );

		// You need styling for the datepicker. For simplicity I've linked to the jQuery UI CSS on a CDN.
		wp_register_style( 'jquery-ui', 'https://code.jquery.com/ui/1.12.1/themes/smoothness/jquery-ui.css' );
		wp_enqueue_style( 'jquery-ui' );  
		
	}

	/*
	 * Added Custom fields on product view page
	 */
	function custom_after_single_product_title() {

		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
		remove_filter( 'woocommerce_single_product_summary', 'show_single_product_countdown', 11 );
		//remove_filter( 'woocommerce_single_product_summary', 'Raise_Prices_With_Time_For_Woocommmerce_Public::show_single_product_countdown', 11 );

		global $post;
		$is_trip_options = get_post_meta( $post->ID, '_trip_options', true );
		$itineraries = get_post_meta( $post->ID, 'wp_travel_trip_itinerary_data', true );

		if ($is_trip_options=='yes') {
			$trip_code = get_trip_code( $post->ID );
			echo '<div align="left"><h4>';
			echo __( 'Trip Code : ', 'text-domain' ) . $trip_code;
			echo '</h4></div>';	
		}

		global $product;
		$rps_prices     = RPT_WC_Meta::get( $product->get_id() );
		$now    = current_time( 'timestamp' );
		$offset = get_option('gmt_offset') * 3600;
		$last_price = '';
		$last_date = '';
		ksort( $rps_prices );
		foreach ( $rps_prices as $date => $price ) {
			$datetime = new DateTime( $date );
			$timestamp = $datetime->getTimestamp();
			$timestamp_offset = $timestamp - $offset;
			if ( $timestamp_offset < $now ) {
				$last_date = $datetime->format('Y-m-d');
				$last_price = $price;
			}
		}
		echo '<div class="rpt-countdown-price">' . wc_price( $last_price ) . '</div>';

		$counter = 0;
		foreach ( $rps_prices as $date => $price ) {
			$datetime = new DateTime( $date );
			$timestamp = $datetime->getTimestamp();
			$timestamp_offset = $timestamp - $offset;
			if ( $timestamp_offset < $now ) {
				continue;
			}
			if ( $counter == 0 )
			echo '<div class="rpt-countdown-price">'  . __( 'The price will be changed to ', 'text-domain' ) . '</div>';
			echo '<div class="rpt-countdown-price">' . wc_price( $price ) . __( ' on ', 'text-domain' ) . $datetime->format('Y-m-d') . '</div>';
			//echo __( 'The price will be changed to ', 'text-domain' );
			//echo '<div class="rpt-countdown-price">' . wc_price( $price ) . '</div>' . __( ' on ', 'text-domain' ) . $datetime->format('Y-m-d');
			$counter = $counter + 1;
		}

		if ( 'yes' === $is_trip_options ) {
			$itinerary_date_array = array();
			if ( is_array( $itineraries ) && count( $itineraries ) > 0 ) {
				foreach ( $itineraries as $x=>$itinerary ) {
					if( !empty( $itineraries[$x]['date'] ) ) {
						array_push( $itinerary_date_array, $itineraries[$x]['date'] );
					}
				}
			}

			echo '<div class="rpt-countdown-price">';
			if ( is_array( $itinerary_date_array ) && count( $itinerary_date_array ) > 0 ) {
				echo __( 'Itinerary : ', 'text-domain' );
				echo '<ul>';
				foreach ( $itineraries as $x=>$itinerary ) {
					echo '<li>' . $itineraries[$x]['date'] . ': ' . $itineraries[$x]['title'] . '</li>';
				}
				echo '</ul>';
			} else {
				echo __( 'Start Date : ', 'text-domain' );
				echo '<div class="start_date"></div>';
			}
			echo '</div>';
		} else {
			echo '<div class="rpt-countdown-price">';
			?>
			<table>
			<tr>
			<td>
			<label for="start_date_input">From</label>
			</td>
			<td>
			<input type="text" id="from" name="start_date_input" style="color:blue; width:fit-content">
			</td>
			</tr>
			<tr>
			<td>
			<label for="end_date_input">To</label>
			</td>
			<td>
			<input type="text" id="to" name="end_date_input" style="color:blue; width:fit-content">
			</td>
			</tr>
			</table>
			<script>
			jQuery(document).ready(function($) {
    			//var dateFormat = "mm/dd/yy",
    			//var dateFormat = "Y-m-d",
  			} );
  			</script>
			<?php
			echo '</div>';
		}
	}
	
	function custom_before_add_to_cart_button() {

	}
         
	/*
	 * Clicked the Add-to-Card button
	 */
	function woocommerce_ajax_add_to_cart() {
	
		$product_id = apply_filters('woocommerce_add_to_cart_product_id', absint($_POST['product_id']));
		$quantity = empty($_POST['quantity']) ? 1 : wc_stock_amount($_POST['quantity']);
		$variation_id = absint($_POST['variation_id']);
		$passed_validation = apply_filters('woocommerce_add_to_cart_validation', true, $product_id, $quantity);
		$product_status = get_post_status($product_id);

		$post_id = $product_id;
		$is_trip_options = get_post_meta( $post_id, '_trip_options', true );
		$itineraries = get_post_meta( $post_id, 'wp_travel_trip_itinerary_data', true );

		if ( 'yes' === $is_trip_options ) {
			if( !empty( $_POST['itinerary_date_array'] ) ) {
				foreach( $_POST['itinerary_date_array'] as $x => $itinerary_date ) {
					$itineraries[$x]['itinerary_date'] = $itinerary_date;
				}
			} else {
				foreach( $itineraries as $x => $itinerary ) {
					$itineraries[$x]['itinerary_date'] = $itineraries[$x]['date'];
				}
			} 
		} else {
			if( !empty( $_POST['start_date_input'] ) ) {
				$itineraries[0]['itinerary_date'] = $_POST['start_date_input'];
			}
			if( !empty( $_POST['end_date_input'] ) ) {
				$itineraries[1]['itinerary_date'] = $_POST['end_date_input'];
			}	
		}
		
		update_post_meta( $post_id, 'wp_travel_trip_itinerary_data', $itineraries );

		if ($passed_validation && WC()->cart->add_to_cart($product_id, $quantity, $variation_id) && 'publish' === $product_status) {
	
			do_action('woocommerce_ajax_added_to_cart', $product_id);
	
			if ('yes' === get_option('woocommerce_cart_redirect_after_add')) {
				wc_add_to_cart_message(array($product_id => $quantity), true);
			}
	
			WC_AJAX :: get_refreshed_fragments();

		} else {
	
			$data = array(
				'error' => true,
				'product_url' => apply_filters('woocommerce_cart_redirect_after_error', get_permalink($product_id), $product_id)
			);
	
			echo wp_send_json($data);
		}

		wp_die();
	}
	
	/*
	 * Add data to cart item
	 */
	function add_custom_cart_item_data( $cart_item_data, $product_id ) {
		$post_id = $product_id;
		$is_trip_options = get_post_meta( $post_id, '_trip_options', true );
		$itineraries = get_post_meta( $post_id, 'wp_travel_trip_itinerary_data', true );

    	// Set the data for the cart item in cart object
    	$data = array() ;
		$cart_item_data['custom_data']['_trip_options'] = $data['_trip_options'] = $is_trip_options;
		$cart_item_data['custom_data']['itineraries'] = $data['itineraries'] = $itineraries;

		// Add the data to session and generate a unique ID
    	if( count( $data > 0 ) ){
        	$cart_item_data['custom_data']['unique_key'] = md5( microtime().rand() );
        	WC()->session->set( 'custom_data', $data );
    	}
    	return $cart_item_data;
	}

	function get_date_price( $product_id, $input_datetime ) {
		
		$offset = get_option('gmt_offset') * 3600;
		$input_timestamp = $input_datetime->getTimestamp();

		$rps_prices     = RPT_WC_Meta::get( $product_id );
		$last_price = '';
		ksort( $rps_prices );
		foreach ( $rps_prices as $date => $price ) {
			$datetime = new DateTime( $date );
			$timestamp = $datetime->getTimestamp();
			$timestamp_offset = $timestamp - $offset;
			if ( $timestamp_offset < $input_timestamp ) {
				$last_price = $price;
			}
		}
		return $last_price;
	}

	function add_custom_price( $cart ) {

		// This is necessary for WC 3.0+
		if ( is_admin() && ! defined( 'DOING_AJAX' ) )
			return;
	
		// Avoiding hook repetition (when using price calculations for example)
		if ( did_action( 'woocommerce_before_calculate_totals' ) >= 2 )
			return;
	
		// Loop through cart items
		foreach ( $cart->get_cart() as $cart_item ) {
			$product_id = $cart_item['product_id'];
			if ($product_id == get_payment_rechargeable_product()->get_id()) {
				return;
			}
			$post_id = $product_id;
			$is_trip_options = get_post_meta( $post_id, '_trip_options', true );
			$itineraries = get_post_meta( $post_id, 'wp_travel_trip_itinerary_data', true );
			if ( 'yes' === $is_trip_options ) {
				$start_date = $itineraries[0]['itinerary_date'];
				$start_datetime = new DateTime( $start_date );
				$last_price = self::get_date_price( $product_id, $start_datetime );
				$cart_item['data']->set_price( $last_price );
			} else {
				$start_date = $itineraries[0]['itinerary_date'];
				$start_datetime = new DateTime( $start_date );
				$end_date = $itineraries[1]['itinerary_date'];
				$end_datetime = new DateTime( $end_date );
				
				$interval = date_diff($start_datetime, $end_datetime);   
				$interval_days = $interval->format('%a');
				$one_day = new DateInterval('P1D');
				$sub_total_price = 0;

				for ($x = 0; $x < $interval_days; $x++) {
					$date_price = self::get_date_price( $product_id, $start_datetime );
					$sub_total_price += $date_price;
					$start_datetime->add($one_day);
				}
				$cart_item['data']->set_price( $sub_total_price );
			}
		}
	}
	
	/*
	 * Display custom data on cart and checkout page.
	 */
	function get_custom_cart_item_data ( $cart_data, $cart_item ) {

		if( ! empty( $cart_item['custom_data'] ) ){
			$values = '<span>';
			$counter = 0; 
			$itineraries = $cart_item['custom_data']['itineraries'];
        	foreach( $itineraries as $x => $itinerary ) {
				$itinerary_date = $cart_item['custom_data']['itineraries'][$x]['itinerary_date'];
				if( $counter == 0 )
				$values .= $itinerary_date;
				if( $counter == count( $itineraries ) - 1)
				$values .= ' ~ ' . $itinerary_date;
				$counter = $counter + 1;
			}
			$values .= '</span>';

			if( 'yes' === $cart_item['custom_data']['_trip_options'] ){
				$values .= '<ul>';
	        	foreach( $cart_item['custom_data']['itineraries'] as $x => $itinerary ) {
					$label = $cart_item['custom_data']['itineraries'][$x]['label'];
					$title = $cart_item['custom_data']['itineraries'][$x]['title'];
					$assignments = $cart_item['custom_data']['itineraries'][$x]['assignment'];
					$itinerary_date = $cart_item['custom_data']['itineraries'][$x]['itinerary_date'];
					if( ! empty( $assignments ) ){
						foreach( $assignments as $y => $assignment ) {
							$category = $assignments[$y]['category'];
							$product_id = $assignments[$y]['resource'];
							$product_title = get_the_title( $assignments[$y]['resource'] );
							$values .= '<li>'.$itinerary_date.', '.$category.', '.$product_title.'</li>';
						}
					}
				}
				$values .= '</ul>';
			}

			$product_id = $cart_item['product_id'];
			if ($product_id == get_payment_rechargeable_product()->get_id()) {
				return;
			}

			$cart_data[] = array(
				'name'    => __( 'Date', 'text-domain' ),
				'display' => $values				
			);
    	}

    	return $cart_data;
	}

	/* 
	 * woocommerce_checkout_create_order_line_item It has 4 available arguments and available after version WooCommerce 3.3+.
	 * $item is an instance of WC_Order_Item_Product new introduced Class
	 * $cart_item_key is the cart item unique hash key
	 * $values is the cart item
	 * $order an instance of the WC_Order object (This is a very useful additional argument in some specific cases)
	 */
	//add_action( 'woocommerce_checkout_create_order_line_item', 'custom_checkout_create_order_line_item', 20, 4 );
	function custom_checkout_create_order_line_item( $item, $cart_item_key, $cart_item, $order ) {

    	if( isset( $cart_item['custom_data'] ) ) {
			
			//$customer_id = $order->get_customer_id();
			$product_id = $cart_item['product_id']; // Product ID
			$product_qty = $cart_item['quantity']; // Product quantity
			$vendor_id = get_post_field( 'post_author', $product_id );
			  
			foreach( $cart_item['custom_data']['itineraries'] as $x => $itinerary ) {
				$assignments = $cart_item['custom_data']['itineraries'][$x]['assignment'];
				$itinerary_date = $cart_item['custom_data']['itineraries'][$x]['itinerary_date'];
				if( ! empty( $assignments ) ){
					foreach( $assignments as $y => $assignment ) {
						$product_id_resource = $assignments[$y]['resource'];
						self::create_purchase_order($vendor_id, $product_id_resource, $product_qty, $itinerary_date);
					}
				}
			}

			$values = '<span>';
			$counter = 0; 
			$itineraries = $cart_item['custom_data']['itineraries'];
        	foreach( $itineraries as $x => $itinerary ) {
				$itinerary_date = $cart_item['custom_data']['itineraries'][$x]['itinerary_date'];
				if( $counter == 0 )
				$values .= $itinerary_date;
				if( $counter == count( $itineraries ) - 1)
				$values .= ' ~ ' . $itinerary_date;
				$counter = $counter + 1;
			}
			$values .= '</span>';

			$product_id = $cart_item['product_id'];
			if ($product_id == get_payment_rechargeable_product()->get_id()) {
				return;
			}

			$item->update_meta_data( __( 'Date', 'text-domain' ), $values );
			
		}
	}

	function create_purchase_order( $customer_id, $product_id, $quantity, $itinerary_date ) {
	
		//global $woocommerce;
  
	  	// Get an instance of the WC_Customer Object
	  	$customer = new WC_Customer( $customer_id );

		$args = array( 
		  	'variation' => array( __( 'Date', 'text-domain' ) => $itinerary_date ),
	  	); 
	  
	  	$order = wc_create_order( array( 'customer_id' => $customer_id ) );
	  	$order->add_product( wc_get_product( $product_id ), $quantity, $args );
		$order->set_address( $customer->get_billing(), 'billing' );
		$order->calculate_totals();

		update_post_meta( $order->id, '_payment_method', 'dgc-payment' );
		update_post_meta( $order->id, '_payment_method_title', 'dgcPay' );

		// Store Order ID in session so it can be re-used after payment failure
		WC()->session->order_awaiting_payment = $order->id;
	
		// Process Payment
		$available_gateways = WC()->payment_gateways->get_available_payment_gateways();
		$result = $available_gateways[ 'dgc-payment' ]->process_payment( $order->id );
	
		if ( $result['result'] == 'success' ) {	
			$result = apply_filters( 'woocommerce_payment_successful_result', $result, $order->id );
		}
	}
/*  
	//add_action('woocommerce_checkout_process', 'custom_checkout_process');
	function custom_checkout_process() {
	
	  	global $woocommerce;
		foreach( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			$product_id = $cart_item['product_id']; // Product ID
			$product_obj = wc_get_product($product_id); // Product Object
			$product_qty = $cart_item['quantity']; // Product quantity
			$product_price = $cart_item['data']->price; // Product price
			$product_total_stock = $cart_item['data']->total_stock; // Product stock
			$product_type = $cart_item['data']->product_type; // Product type
			$product_name = $cart_item['data']->post->post_title; // Product Title (Name)
			$product_slug = $cart_item['data']->post->post_name; // Product Slug
			$product_description = $cart_item['data']->post->post_content; // Product description
			$product_excerpt = $cart_item['data']->post->post_excerpt; // Product short description
			$product_post_type = $cart_item['data']->post->post_type; // Product post type
		
			$cart_line_total = $cart_item['line_total']; // Cart item line total
			$cart_line_tax = $cart_item['line_tax']; // Cart item line tax total
			$cart_line_subtotal = $cart_item['line_subtotal']; // Cart item line subtotal
			$cart_line_subtotal_tax = $cart_item['line_subtotal_tax']; // Cart item line tax subtotal
		
			// variable products
			$variation_id = $cart_item['variation_id']; // Product Variation ID
			if($variation_id != 0){
				$product_variation_obj = wc_get_product($variation_id); // Product variation Object
				$variation_array = $cart_item['variation']; // variation attributes + values
			}
		}
	}
*/	

	//add_action( 'woocommerce_thankyou', 'wc_auto_complete_paid_order', 20, 1 );
	function wc_auto_complete_paid_order( $order_id ) {
		if ( ! $order_id )
			return;
		
		// Get an instance of the WC_Product object
		$order = wc_get_order( $order_id );
		
		// No updated status for orders delivered with Bank wire, Cash on delivery and Cheque payment methods.
		if ( in_array( $order->get_payment_method(), array( 'bacs', 'cod', 'cheque', '' ) ) ) {
			return;
		} 
		// For paid Orders with all others payment methods (paid order status "processing")
		elseif( $order->has_status('processing') ) {
			$order->update_status( 'completed' );
		}
	}	

	//add_filter( 'woocommerce_email_recipient_new_booking', 'additional_customer_email_recipient', 10, 2 ); 
	//add_filter( 'woocommerce_email_recipient_new_order', 'additional_customer_email_recipient', 10, 2 ); // Optional (testing)
	function additional_customer_email_recipient( $recipient, $order ) {
		if ( ! is_a( $order, 'WC_Order' ) ) return $recipient;

		$additional_recipients = array(); // Initializing…
	
		// Iterating though each order item
		foreach( $order->get_items() as $item_id => $line_item ){
			// Get the vendor ID
			$vendor_id = get_post_field( 'post_author', $line_item->get_product_id());
			$vendor = get_userdata( $vendor_id );
			$email = $vendor->user_email;
	
			// Avoiding duplicates (if many items with many emails)
			// or an existing email in the recipient
			if( ! in_array( $email, $additional_recipients ) && strpos( $recipient, $email ) === false )
				$additional_recipients[] = $email;
		}
	
		// Convert the array in a coma separated string
		$additional_recipients = implode( ',', $additional_recipients);
	
		// If an additional recipient exist, we add it
		if( count($additional_recipients) > 0 )
			$recipient .= ','.$additional_recipients;
	
		return $recipient;
	}

	/**
 	 * Add a custom product data tab
 	 */
	function custom_product_tab() {

		global $post;
		$is_trip_options = get_post_meta( $post->ID, '_trip_options', true );
		if ( 'yes' == $is_trip_options ) {
			$tabs = array();
			//$trip_tabs = wp_travel_get_admin_trip_tabs( $post->ID );
			$trip_tabs = wp_travel_get_frontend_tabs( $post->ID );
			if ( is_array( $trip_tabs ) && count( $trip_tabs ) > 0 ) {
				foreach ( $trip_tabs as $key=>$value ) {
					if ( 'yes' == $trip_tabs[$key]['show_in_menu'] ) {
						$tabs[$key] = array(
							'title' 	=> __( $trip_tabs[$key]['label'], 'text-domain' ),
							'priority' 	=> 20,
							'callback' 	=> array( __CLASS__, $key . '_tab_content' )
						);		
					}
				}
			}
			return $tabs;
		}
	}
	
	function itinerary_tab_content() {

		global $post;
		$itineraries = get_post_meta( $post->ID, 'wp_travel_trip_itinerary_data', true );
		echo '<h2>' . __( 'Itinerary', 'text-domain' ) . '</h2>';
		if ( is_array( $itineraries ) && count( $itineraries ) > 0 ) {			
			echo '<ul>';
			foreach ( $itineraries as $x=>$itinerary ) {
				echo '<li class="itinerary-li">';
				if ( empty($itineraries[$x]['date']) ) {
					echo '<input type="text" style="color:blue; width:fit-content" name="itinerary-date-'.$x.'" id="itinerary-date-'.$x.'">';
				} else {
					echo $itineraries[$x]['date'] . '<br>';
				}
				echo esc_attr( $itineraries[$x]['label'] ) . ', ' . esc_attr( $itineraries[$x]['title'] );
				echo '<p>' . esc_attr( $itineraries[$x]['desc'] ) . '</p>';
				echo '</li>';
			}
			echo '</ul>';
		} else {
			echo __( 'No Itineraries found.', 'text-domain' );
		}
	}

	function trip_includes_tab_content() {

		global $post;
		$trip_include = get_post_meta( $post->ID, 'wp_travel_trip_include', true );
		echo '<h2>' . __( 'Trip Includes', 'text-domain' ) . '</h2>';
		if (!empty($trip_include)) {
			echo esc_attr( $trip_include );
		} else {
			esc_html_e( 'No Trip Include found.', 'text-domain' );
		}
	}	

	function trip_excludes_tab_content() {

		global $post;
		$trip_exclude = get_post_meta( $post->ID, 'wp_travel_trip_exclude', true );
		echo '<h2>' . __( 'Trip Excludes', 'text-domain' ) . '</h2>';
		if (!empty($trip_exclude)) {
			echo esc_attr( $trip_exclude );
		} else {
			esc_html_e( 'No Trip Exclude found.', 'text-domain' );
		}
	}	

	function faq_tab_content() {

		global $post;
		$faqs = wp_travel_get_faqs( $post->ID );
		echo '<h2>' . __( 'FAQ', 'text-domain' ) . '</h2>';
		if ( is_array( $faqs ) && count( $faqs ) > 0 ) { 
			echo '<ul>';
			foreach ( $faqs as $key=>$value ) {
				echo '<li>';
				echo esc_attr( $faqs[$key]['question'] );
				echo '<br>';
				echo esc_attr( $faqs[$key]['answer'] );
				echo '</li>';
			}
			echo '</ul>';
		} else { 
			echo __( 'No FAQs found.', 'text-domain' );
		}
	}
}