<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('curtain_orders')) {
    class curtain_orders {
        private $_wp_page_title;
        private $_wp_page_postid;
        private $see_more;
        private $curtain_agent_id;
        /**
         * Class constructor
         */
        public function __construct() {
            $this->create_tables();
            $this->_wp_page_title = 'Orders';
            $this->_wp_page_postid = general_helps::create_page($this->_wp_page_title, 'shopping-item-list', 'system');
            add_shortcode( 'shopping-item-list', array( $this, 'list_order_items' ) );
            if (file_exists(plugin_dir_path( __DIR__ ).'assets/templates/see_more.json')) {
                $this->see_more = file_get_contents(plugin_dir_path( __DIR__ ).'assets/templates/see_more.json');
                $this->see_more = json_decode($this->see_more, true);
            }
            add_action( 'wp_ajax_order_item_dialog_get_data', array( $this, 'order_item_dialog_get_data' ) );
            add_action( 'wp_ajax_nopriv_order_item_dialog_get_data', array( $this, 'order_item_dialog_get_data' ) );
            add_action( 'wp_ajax_order_item_dialog_save_data', array( $this, 'order_item_dialog_save_data' ) );
            add_action( 'wp_ajax_nopriv_order_item_dialog_save_data', array( $this, 'order_item_dialog_save_data' ) );
            add_action( 'wp_ajax_select_order_status', array( $this, 'select_order_status' ) );
            add_action( 'wp_ajax_nopriv_select_order_status', array( $this, 'select_order_status' ) );
            add_action( 'wp_ajax_select_category_id', array( $this, 'select_category_id' ) );
            add_action( 'wp_ajax_nopriv_select_category_id', array( $this, 'select_category_id' ) );
            add_action( 'wp_ajax_select_order_status', array( $this, 'select_order_status' ) );
            add_action( 'wp_ajax_nopriv_select_order_status', array( $this, 'select_order_status' ) );
            add_action( 'wp_ajax_sub_items_dialog_get_data', array( $this, 'sub_items_dialog_get_data' ) );
            add_action( 'wp_ajax_nopriv_sub_items_dialog_get_data', array( $this, 'sub_items_dialog_get_data' ) );
            add_action( 'wp_ajax_sub_items_dialog_save_data', array( $this, 'sub_items_dialog_save_data' ) );
            add_action( 'wp_ajax_nopriv_sub_items_dialog_save_data', array( $this, 'sub_items_dialog_save_data' ) );
        }

        public function order_status_notice($customer_order_number, $customer_order_status) {
            global $wpdb;
            $system_status = new system_status();
            $line_bot_api = new line_bot_api();

            $link_uri = get_option('Orders').'?_print='.$customer_order_number;
            $order_status = 'Order status has been changed to '.$system_status->get_name($customer_order_status);

            $all_users = get_users();
            foreach($all_users as $user){
                if($user->has_cap('manage_options')){
                    $this->see_more["header"]["type"] = 'box';
                    $this->see_more["header"]["layout"] = 'vertical';
                    $this->see_more["header"]["backgroundColor"] = "#e3dee3";
                    $this->see_more["header"]["contents"][0]["type"] = 'text';
                    $this->see_more["header"]["contents"][0]["text"] = 'Order No.: '.$customer_order_number;

                    $this->see_more["body"]["contents"][0]["type"] = 'text';
                    $this->see_more["body"]["contents"][0]["text"] = $order_status;
                    $this->see_more["body"]["contents"][0]["wrap"] = true;

                    $this->see_more["footer"]["type"] = 'box';
                    $this->see_more["footer"]["layout"] = 'vertical';
                    $this->see_more["footer"]["backgroundColor"] = "#e3dee3";
                    $this->see_more["footer"]["contents"][0]["type"] = 'button';
                    $this->see_more["footer"]["contents"][0]["action"]["type"] = 'uri';
                    $this->see_more["footer"]["contents"][0]["action"]["label"] = 'Go back Order';
                    $this->see_more["footer"]["contents"][0]["action"]["uri"] = $link_uri;

                    $line_bot_api->pushMessage([
                        'to' => get_user_meta($user->ID, 'line_user_id', TRUE),
                        'messages' => [
                            [
                                "type" => "flex",
                                "altText" => 'System Notification',
                                'contents' => $this->see_more
                            ]
                        ]
                    ]);
                }            
            }
        }

        public function list_order_items() {
            global $wpdb;
            $curtain_agents = new curtain_agents();
            $curtain_categories = new curtain_categories();
            $curtain_models = new curtain_models();
            $curtain_remotes = new curtain_remotes();
            $curtain_specifications = new curtain_specifications();
            $serial_number = new serial_number();
            $curtain_service = new curtain_service();
            $system_status = new system_status();

            if ( !is_user_logged_in() ) {
                echo do_shortcode( '[qr-scanner-redirect]' );
            }
            $user = wp_get_current_user();
            $_agent_number = get_user_meta( $user->ID, 'agent_number', TRUE );
            $_agent_code = get_user_meta( $user->ID, 'agent_code', TRUE );

            if( isset($_agent_number) && isset($_agent_code) ) {
                $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}curtain_agents WHERE agent_number = %s AND agent_password = %s", $_agent_number, $_agent_code ), OBJECT );
                if ( is_null($row) || !empty($wpdb->last_error) ) {
                    $output  = '<div style="text-align:center;">';
                    $output .= '<h3>This is a wrong code, please click the Submit button below to re-login the agent order system.</h3>';
                    $output .= '<form method="post" style="display:inline-block; text-align:-webkit-center;">';
                    $output .= '<input type="submit" name="_agent_submit1" style="margin:3px;" value="Submit" />';
                    $output .= '</form>';
                    $output .= '</div>';
                    return $output;                        
                }
                $this->curtain_agent_id = $curtain_agents->get_id($_agent_number);

            } else {
                echo do_shortcode( '[qr-scanner-redirect]' );
            }
/*
            if( isset($_GET['_repack']) ) {
                $this->delete_customer_orders(
                    array(
                        'customer_order_amount'=>$_GET['_repack']
                    )
                );
            }
*/
            if( isset($_GET['_delete_sub_item']) ) {
                $this->delete_sub_items(
                    array(
                        'sub_item_id'=>$_GET['_delete_sub_item']
                    )
                );
            }

            if( isset($_GET['_delete_customer_order']) ) {
                $this->delete_customer_orders(
                    array(
                        'customer_order_number'=>$_GET['_delete_customer_order']
                    )
                );
            }

            //* Print Customer Order */
            if( isset($_POST['_status_submit']) ) {
                $this->update_customer_orders(
                    array(
                        'customer_order_status'=>$_POST['_customer_order_status'],
                    ),
                    array(
                        'customer_order_number'=>$_POST['_customer_order_number'],
                    )
                );
                $this->order_status_notice($_POST['_customer_order_number'], $_POST['_customer_order_status']);
            }

            if( isset($_GET['_print_customer_order']) ) {
                $_id = $_GET['_print_customer_order'];
                $row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}customer_orders WHERE customer_order_number={$_id}", OBJECT );
                $output  = '<div style="text-align:center;"><h2>Customer Order</h2></div>';
                $output .= '<div class="ui-widget">';
                $output .= '<table id="order-header" class="ui-widget ui-widget-content">';
                $output .= '<tr>';
                $output .= '<td>Order Number:</td><td><span id="select-order-number">'.$row->customer_order_number.'</span></td>';
                $output .= '<td>Order Date:</td><td>'.wp_date( get_option('date_format'), $row->create_timestamp ).'</td>';
                $output .= '</tr>';
                $output .= '<tr>';
                $output .= '<td>Agent:</td><td>'.$curtain_agents->get_name($row->curtain_agent_id).'</td>';
                $output .= '<td>Status:</td>';
                if($user->has_cap('manage_options')){
                    $output .= '<td>';
                    $output .= '<form method="post" style="display:flex;">';
                    $output .= '<select id="customer-order-status" name="_customer_order_status">'.$system_status->select_options($row->customer_order_status).'</select>';
                    $output .= '<input type="hidden" id="customer-order-number" name="_customer_order_number" value="'.$row->customer_order_number.'" />';
                    //$output .= '<input type="submit" name="_status_submit" style="margin:3px;" value="Submit" />';
                    $output .= '</form>';
                    $output .= '</td>';
                } else {
                    $output .= '<td>'.$system_status->get_name($row->customer_order_status).'</td>';
                }
                $output .= '</tr>';
                $output .= '<tr>';
                $output .= '<td>Contact:</td><td>'.$curtain_agents->get_contact($row->curtain_agent_id).'</td>';
                $output .= '<td>Phone:</td><td>'.$curtain_agents->get_phone($row->curtain_agent_id).'</td>';
                $output .= '</tr>';
                $output .= '<tr>';
                $output .= '<td>Address:</td><td colspan="3">'.$curtain_agents->get_address($row->curtain_agent_id).'</td>';
                $output .= '</tr>';
                $output .= '</table>';

                $output .= '<table id="orders" class="ui-widget ui-widget-content">';
                $output .= '<thead><tr class="ui-widget-header ">';
                $output .= '<th>#</th>';
                $output .= '<th>Category</th>';
                $output .= '<th>Model</th>';
                $output .= '<th>Specification</th>';
                $output .= '<th>Dimension</th>';
                $output .= '<th>QTY</th>';
                $output .= '<th>Amount</th>';
                $output .= '<th></th>';
                $output .= '</tr></thead>';
                $output .= '<tbody>';

                $x=0;
                $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}order_items WHERE customer_order_number={$row->customer_order_number}", OBJECT );
                foreach ( $results as $index=>$result ) {
                    $output .= '<tr>';
                    $x=$x+1;
                    $output .= '<td style="text-align: center;">'.$x.'</td>';
                    $output .= '<td>'.$curtain_categories->get_name($result->curtain_category_id).'</td>';
                    $output .= '<td>'.$curtain_models->get_description($result->curtain_model_id);
                    $output .= '<br>'.$curtain_remotes->get_name($result->curtain_remote_id).'</td>';
                    if ($curtain_categories->is_specification_hided($result->curtain_category_id)) {
                        $output .= '<td></td>';
                    } else {
                        $output .= '<td>'.$curtain_specifications->get_description($result->curtain_specification_id).'</td>';
                    }

                    $output .= '<td>';
                    if ($curtain_categories->is_width_hided($result->curtain_category_id)) {
                        $output .= '';
                    } else {
                        $output .= 'Width:'.$result->curtain_width;
                    }
                    if ($curtain_categories->is_height_hided($result->curtain_category_id)) {
                        $output .= '';
                    } else {
                        $output .= '<br>Height:'.$result->curtain_height;
                    }
                    $output .= '</td>';

                    $output .= '<td style="text-align:center;">'.$result->order_item_qty.'</td>';
                    $output .= '<td style="text-align:center;">'.number_format_i18n($result->order_item_amount).'</td>';
                    $output .= '<td style="text-align: center;">';
                    $serials_page_url = '/serials/?_order_item_id='.$result->curtain_order_id;
                    $output .= '<a href="'.$serials_page_url.'">'.'<i class="fa-solid fa-qrcode"></i>'.'</a>';
                    $output .= '</td>';
                    $output .= '</tr>';
                }
                $output .= '<tr>';
                $output .= '<td style="text-align:right;" colspan="6">Sub Total: </td>';
                $output .= '<td style="text-align:center;">'.number_format_i18n($row->customer_order_amount).'</td>';
                $output .= '</tr>';
                $output .= '</tbody></table></div>';
                return $output;
            }

            //* Customer Orders List */
            if( isset($_POST['_customer_orders']) ) {
                if($user->has_cap('manage_options')){
                    $output  = '<h2>Customer Orders - All</h2>';
                } else {
                    $output  = '<h2>Customer Orders - '.$curtain_agents->get_name($this->curtain_agent_id).'</h2>';
                }
                $output .= '<div class="ui-widget">';
                $output .= '<table id="orders" class="ui-widget ui-widget-content">';
                $output .= '<thead><tr class="ui-widget-header ">';
                $output .= '<th></th>';
                $output .= '<th>Order No.</th>';
                $output .= '<th>Date</th>';
                $output .= '<th>Agent</th>';
                $output .= '<th>Amount</th>';
                $output .= '<th>Status</th>';
                $output .= '<th></th>';
                $output .= '</tr></thead>';

                $output .= '<tbody>';
                $_addition = array('curtain_agent_id='.$this->curtain_agent_id);
                if($user->has_cap('manage_options')){
                    $results = general_helps::get_search_results($wpdb->prefix.'customer_orders', $_POST['_where']);
                } else {
                    $results = general_helps::get_search_results($wpdb->prefix.'customer_orders', $_POST['_where'], $_addition);
                }
                foreach ( $results as $index=>$result ) {
                    $output .= '<tr>';
                    $output .= '<td style="text-align: center;">';
                    //$output .= '<span id="btn-print-customer-order-'.$result->customer_order_number.'"><i class="fa-solid fa-print"></i></span>';
                    $output .= '<span id="btn-print-customer-order-'.$result->customer_order_number.'"><i class="fa-regular fa-pen-to-square"></i></span>';
                    $output .= '</td>';
                    $output .= '<td style="text-align: center;">'.$result->customer_order_number.'</td>';
                    $output .= '<td style="text-align: center;">'.wp_date( get_option('date_format'), $result->create_timestamp ).'</td>';
                    $output .= '<td>'.$curtain_agents->get_name($result->curtain_agent_id).'</td>';
                    $output .= '<td style="text-align: center;">'.number_format_i18n($result->customer_order_amount).'</td>';
                    $output .= '<td>'.$system_status->get_name($result->customer_order_status).'</td>';
                    if($user->has_cap('manage_options')){
                        $output .= '<td style="text-align: center;">';
                        $output .= '<span id="btn-del-customer-order-'.$result->customer_order_number.'"><i class="fa-regular fa-trash-can"></i></span>';
                        $output .= '</td>';
                    }
                    $output .= '</tr>';
                }
                $output .= '</tbody></table></div>';
                return $output;
            }

            //* Checkout */
            if( isset($_POST['_checkout_submit']) ) {
                $customer_order_number=time();
                $customer_order_amount=0;
                $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}order_items WHERE curtain_agent_id={$this->curtain_agent_id} AND is_checkout=0", OBJECT );                
                foreach ( $results as $index=>$result ) {
                    $_is_checkout = '_is_checkout_'.$index;
                    if ( $_POST[$_is_checkout]==1 ) {
                        $this->update_order_items(
                            array(
                                'customer_order_number'=>$customer_order_number,
                                'is_checkout'=>1
                            ),
                            array(
                                'curtain_order_id'=>$result->curtain_order_id
                            )
                        );

                        $customer_order_amount=$customer_order_amount+$result->order_item_amount;

                        $x = 0;
                        while ($x < $result->order_item_qty) {
                            $serial_number->insert_serial_number(
                                array(
                                    'customer_order_number'=>$customer_order_number,
                                    'order_item_id'=>$result->curtain_order_id,
                                    'curtain_model_id'=>$result->curtain_model_id,
                                    'specification'   =>$curtain_specifications->get_name($result->curtain_specification_id).$result->curtain_width,
                                    'curtain_agent_id'=>$result->curtain_agent_id
                                ),
                                $x
                            );
                            $x = $x + 1;
                        }
                    }
                }

                // Convert the shopping items to customer orders and purchase order
                $this->insert_customer_order(
                    array(
                        'customer_order_number' => $customer_order_number,
                        'curtain_agent_id'      => $this->curtain_agent_id,
                        'customer_order_amount' => $customer_order_amount,
                        'customer_order_status' => 'order01' // order01: Completed the checkout but did not purchase yet
                    )
                );

                // Notice the admin about the order status
                $this->order_status_notice($customer_order_number, 'order01');
            }
            
            /** Shopping Cart Item Create and Editing*/
            if( isset($_POST['_create']) ) {
                $width = 1;
                $height = 1;
                $qty = 1;
                if (is_numeric($_POST['_curtain_width'])) {
                    $width = $_POST['_curtain_width'];
                }
                if (is_numeric($_POST['_curtain_height'])) {
                    $height = $_POST['_curtain_height'];
                }
                if (is_numeric($_POST['_shopping_item_qty'])) {
                    $qty = $_POST['_shopping_item_qty'];
                }
                $m_price = $curtain_models->get_price($_POST['_curtain_model_id']);
                $r_price = $curtain_remotes->get_price($_POST['_curtain_remote_id']);
                $s_price = $curtain_specifications->get_price($_POST['_curtain_specification_id']);
                if ($curtain_specifications->is_length_only($_POST['_curtain_specification_id'])==1){
                    $amount = ($m_price + $r_price + $width/100 * $s_price) * $qty;
                } else {
                    $amount = ($m_price + $r_price + $width/100 * $height/100 * $s_price) * $qty;
                }
                $this->insert_order_item(
                    array(
                        'curtain_agent_id'=>$this->curtain_agent_id,
                        'curtain_category_id'=>$_POST['_curtain_category_id'],
                        'curtain_model_id'=>$_POST['_curtain_model_id'],
                        'curtain_remote_id'=>$_POST['_curtain_remote_id'],
                        'curtain_specification_id'=>$_POST['_curtain_specification_id'],
                        'curtain_width'=>$_POST['_curtain_width'],
                        'curtain_height'=>$_POST['_curtain_height'],
                        'order_item_qty'=>$_POST['_shopping_item_qty'],
                        'order_item_amount'=>$amount,
                        'is_checkout'=>0
                    )
                );
            }

            if( isset($_POST['_update']) ) {
                $width = 1;
                $height = 1;
                $qty = 1;
                if (is_numeric($_POST['_curtain_width'])) {
                    $width = $_POST['_curtain_width'];
                }
                if (is_numeric($_POST['_curtain_height'])) {
                    $height = $_POST['_curtain_height'];
                }
                if (is_numeric($_POST['_shopping_item_qty'])) {
                    $qty = $_POST['_shopping_item_qty'];
                }
                $m_price = $curtain_models->get_price($_POST['_curtain_model_id']);
                $r_price = $curtain_remotes->get_price($_POST['_curtain_remote_id']);
                $s_price = $curtain_specifications->get_price($_POST['_curtain_specification_id']);
                if ($curtain_specifications->is_length_only($_POST['_curtain_specification_id'])==1){
                    $amount = ($m_price + $r_price + $width/100 * $s_price) * $qty;
                } else {
                    $amount = ($m_price + $r_price + $width/100 * $height/100 * $s_price) * $qty;
                }
                $this->update_order_items(
                    array(
                        'curtain_category_id'=>$_POST['_curtain_category_id'],
                        'curtain_model_id'=>$_POST['_curtain_model_id'],
                        'curtain_remote_id'=>$_POST['_curtain_remote_id'],
                        'curtain_specification_id'=>$_POST['_curtain_specification_id'],
                        'curtain_width'=>$_POST['_curtain_width'],
                        'curtain_height'=>$_POST['_curtain_height'],
                        'order_item_qty'=>$_POST['_shopping_item_qty'],
                        'order_item_amount'=>$amount,
                    ),
                    array(
                        'curtain_order_id'=>$_POST['_curtain_order_id'],
                    )
                );
                ?><script>window.location.replace("?_update=");</script><?php
            }

            if( isset($_GET['_delete']) ) {
                $this->delete_order_items(
                    array(
                        'curtain_order_id'=>$_GET['_delete']
                    )
                );
            }

            /** Shopping Cart List */
            $output  = '<h2>Shopping Cart - '.$curtain_agents->get_name($this->curtain_agent_id).'</h2>';
            $output .= '<div style="display: flex; justify-content: space-between; margin: 5px;">';
            $output .= '<div>';
            $output .= '<form method="post">';
            $output .= '<input class="wp-block-button__link" type="submit" value="New Item" name="_add">';
            $output .= '<input class="wp-block-button__link" type="submit" value="My Orders" name="_customer_orders">';
            $output .= '</form>';
            $output .= '</div>';
            $output .= '<div style="text-align: right;">';
            $output .= '<form method="post">';
            $output .= '<input style="display:inline" type="text" name="_where" placeholder="Search...">';
            $output .= '<input class="wp-block-button__link" type="submit" value="Search" name="submit_action">';
            $output .= '</form>';
            $output .= '</div>';
            $output .= '</div>';

            $output .= '<div class="ui-widget">';
            $output .= '<table id="order-items" class="ui-widget ui-widget-content">';
            $output .= '<thead><tr class="ui-widget-header ">';
            $output .= '<th></th>';
            $output .= '<th></th>';
            $output .= '<th>date/time</th>';
            $output .= '<th>category</th>';
            $output .= '<th>model</th>';
            $output .= '<th>parts</th>';
            $output .= '<th>QTY</th>';
            $output .= '<th>amount</th>';
            $output .= '<th></th>';
            $output .= '</tr></thead>';

            $output .= '<form method="post">';
            $output .= '<tbody>';
            $_addition = array('curtain_agent_id='.$this->curtain_agent_id, 'is_checkout=0');
            $results = general_helps::get_search_results($wpdb->prefix.'order_items', $_POST['_where'], $_addition);
            foreach ( $results as $index=>$result ) {
                $output .= '<tr>';
                if ( $result->is_checkout==1 ) {
                    $output .= '<td></td>';
                    $output .= '<td></td>';
                } else {
                    $output .= '<td style="text-align: center;">';
                    $output .= '<input style="display:inline" type="checkbox" value="1" name="_is_checkout_'.$index.'">';
                    $output .= '</td>';
                    $output .= '<td style="text-align: center;">';
                    //$output .= '<span style="margin-left:5px;" id="btn-edit-'.$result->curtain_order_id.'"><i class="fa-regular fa-pen-to-square"></i></span>';
                    $output .= '<span style="margin-left:5px;" id="btn-order-item-'.$result->curtain_order_id.'"><i class="fa-regular fa-pen-to-square"></i></span>';
                    $output .= '</td>';
                }
                $output .= '<td>';
                $output .= wp_date( get_option('date_format'), $result->create_timestamp ).' '.wp_date( get_option('time_format'), $result->create_timestamp );
                $output .= '</td>';
                $output .= '<td>'.$curtain_categories->get_name($result->curtain_category_id).'</td>';
                $output .= '<td style="text-align: center;">'.$curtain_models->get_name($result->curtain_model_id).'</td>';
                $output .= '<td style="text-align: center;">';
                $output .= '<span id="btn-sub-items-'.$result->curtain_order_id.'"><i class="fa-solid fa-gifts"></i></span>';
                $output .= '</td>';
                $output .= '<td style="text-align: center;">'.$result->order_item_qty.'</td>';
                $output .= '<td style="text-align: center;">'.number_format_i18n($result->order_item_amount).'</td>';
                if ( $result->is_checkout==1 ) {
                    $output .= '<td>checkout already</td>';
                } else {
                    $output .= '<td style="text-align: center;">';
                    $output .= '<span id="btn-del-'.$result->curtain_order_id.'"><i class="fa-regular fa-trash-can"></i></span>';
                    $output .= '</td>';
                }
                $output .= '</tr>';
            }
            $output .= '<tr><td colspan="9"><div id="btn-order-item" style="border:solid; margin:3px; text-align:center; border-radius:5px">+</div></td></tr>';
            $output .= '</tbody></table></div>';
            $output .= '<input class="wp-block-button__link" type="submit" value="Checkout" name="_checkout_submit">';
            $output .= '</form>';

            /** Order Item Dialog */
            $output .= '<div id="order-item-dialog" title="Order Item dialog">';
            $output .= '<fieldset>';
            $output .= '<input type="hidden" id="order-item-id">';
            $output .= '<label for="curtain-category-id">Curtain Category</label>';
            $output .= '<select id="curtain-category-id"></select>';
            $output .= '<label id="curtain-model-label" for="curtain-model-id">Curtain Model</label>';
            $output .= '<select id="curtain-model-id"></select>';
            $output .= '<label id="curtain-remote-label" for="curtain-remote-id">Curtain Remote</label>';
            $output .= '<select id="curtain-remote-id"></select>';
            $output .= '<label id="curtain-specification-label" for="curtain-specification-id">Specification</label>';
            $output .= '<select id="curtain-specification-id"></select>';
            $output .= '<label id="curtain-width-label" for="curtain-width">Width</label>';
            $output .= '<input type="text" id="curtain-width" />';
            $output .= '<label id="curtain-height-label" for="curtain-height">Height</label>';
            $output .= '<input type="text" id="curtain-height" />';    
            $output .= '<label for="order-item-qty">QTY</label>';
            $output .= '<input type="text" id="order-item-qty" />';
            $output .= '</fieldset>';
            $output .= '</div>';

            /** Sub Items Dialog */
            $output .= '<div id="sub-items-dialog" title="Sub Items dialog">';
            $output .= '<table id="sub-items" class="ui-widget ui-widget-content">';
            $output .= '<thead><tr class="ui-widget-header ">';
            $output .= '<th></th>';
            $output .= '<th>parts</th>';
            $output .= '<th>QTY</th>';
            $output .= '<th></th>';
            $output .= '</tr></thead>';
            $output .= '<tbody>';
            $x = 0;
            while ($x<10) {
                $output .= '<tr id="sub-item-'.$x.'" style="display:none;">';
                $output .= '<td></td>';
                $output .= '<td id="parts-id-'.$x.'"></td>';
                $output .= '<td id="parts-qty-'.$x.'" style="text-align: center;"></td>';
                $output .= '<td id="parts-del-'.$x.'" style="text-align: center;"></td>';
                $output .= '</tr>';
                $x += 1;
            }            
            $output .= '<tr>';
            $output .= '<td><input type="hidden" id="order-item-id" /></td>';
            $output .= '<td><select id="parts-id">'.$curtain_categories->parts_options().'</select></td>';
            $output .= '<td><input type="text" size="2" id="parts-qty" value="1" /></td>';
            $output .= '</tr>';
            $output .= '</tbody></table>';
            $output .= '</div>';

            if( isset($_GET['_edit']) ) {
                $_id = $_GET['_edit'];
                $row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}order_items WHERE curtain_order_id={$_id}", OBJECT );
                $output .= '<div id="dialog" title="Items update">';
                $output .= '<form method="post">';
                $output .= '<fieldset>';
                $output .= '<input type="hidden" name="_curtain_order_id" value="'.$row->curtain_order_id.'">';
                $output .= '<label for="select-category-id">Curtain Category</label>';
                $output .= '<select name="_curtain_category_id" id="select-category-id">'.$curtain_categories->select_options($row->curtain_category_id).'</select>';
                $output .= '<label for="select-model-id">Model</label>';
                $output .= '<select name="_curtain_model_id" id="select-model-id">'.$curtain_models->select_options($row->curtain_category_id, $row->curtain_model_id ).'</select>';
                $output .= '<label for="select-remote-id">Remote</label>';
                $output .= '<select name="_curtain_remote_id" id="select-remote-id">'.$curtain_remotes->select_options($row->curtain_remote_id).'</select>';
                $output .= '<label for="select-specification-id">Specification</label>';
                $output .= '<select name="_curtain_specification_id" id="select-specification-id">'.$curtain_specifications->select_options($row->curtain_category_id, $row->curtain_specification_id ).'</select>';

                $output .= '<label id="curtain-width-label" for="curtain-width">Width: min('.$curtain_categories->get_min_width($row->curtain_category_id).'),max('.$curtain_categories->get_max_width($row->curtain_category_id).')</label>';
                $output .= '<input type="text" name="_curtain_width" value="'.$row->curtain_width.'" id="curtain-width" class="text ui-widget-content ui-corner-all">';
                if ($row->curtain_category_id!=1) {
                    $output .= '<label id="curtain-height-label" for="curtain-height">Height: min('.$curtain_categories->get_min_height($row->curtain_category_id).'),max('.$curtain_categories->get_max_height($row->curtain_category_id).')</label>';
                    $output .= '<input type="text" name="_curtain_height" value="'.$row->curtain_height.'" id="curtain-height" class="text ui-widget-content ui-corner-all">';    
                }

                $output .= '<label for="order_item_qty">QTY</label>';
                $output .= '<input type="text" name="_shopping_item_qty" value="'.$row->order_item_qty.'" id="order_item_qty" class="text ui-widget-content ui-corner-all">';
                $output .= '</fieldset>';
                $output .= '<input type="hidden" name="_agent_submit" value="true">';
                $output .= '<input class="wp-block-button__link" type="submit" value="Update" name="_update" id="update-btn-'.$row->curtain_order_id.'">';
                $output .= '</form>';
                $output .= '</div>';
            }

            if( isset($_POST['_add']) ) {
                $output .= '<div id="dialog" title="Create new item">';
                $output .= '<form method="post">';
                $output .= '<fieldset>';
                $output .= '<label for="curtain-category-id">Curtain Category</label>';
                $output .= '<select name="_curtain_category_id" id="curtain-category-id">'.$curtain_categories->select_options().'</select>';
                $output .= '<label for="curtain-model-id">Model</label>';
                $output .= '<select name="_curtain_model_id" id="curtain-model-id">'.$curtain_models->select_options().'</select>';
                $output .= '<label for="curtain-remote-id">Remote</label>';
                $output .= '<select name="_curtain_remote_id" id="curtain-remote-id">'.$curtain_remotes->select_options().'</select>';
                $output .= '<label for="curtain-specification-id">Specification</label>';
                $output .= '<select name="_curtain_specification_id" id="curtain-specification-id">'.$curtain_specifications->select_options().'</select>';

                $output .= '<label id="curtain-width-label" for="curtain-width">Width: min(),max()</label>';
                $output .= '<input type="text" name="_curtain_width" id="curtain-width" class="text ui-widget-content ui-corner-all">';
                $output .= '<label id="curtain-height-label" for="curtain-height">Height: min(),max()</label>';
                $output .= '<input type="text" name="_curtain_height" id="curtain-height" class="text ui-widget-content ui-corner-all">';

                $output .= '<label for="order_item_qty">QTY</label>';
                $output .= '<input type="text" name="_shopping_item_qty" id="order_item_qty" class="text ui-widget-content ui-corner-all">';
                $output .= '</fieldset>';
                $output .= '<input type="hidden" name="_agent_submit" value="true">';
                $output .= '<input class="wp-block-button__link" type="submit" value="Create" name="_create">';
                $output .= '</form>';
                $output .= '</div>';
            }

            return $output;
        }

        function order_item_dialog_get_data() {
            global $wpdb;
            $curtain_categories = new curtain_categories();
            $curtain_models = new curtain_models();
            $curtain_remotes = new curtain_remotes();
            $curtain_specifications = new curtain_specifications();

            $_id = $_POST['_id'];
            $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}order_items WHERE curtain_order_id = %d", $_id ), OBJECT );
            $response = array();
            $response["curtain_category_id"] = $curtain_categories->select_options($row->curtain_category_id);
            $response["curtain_model_id"] = $curtain_models->select_options($row->curtain_category_id, $row->curtain_model_id );
            $response["curtain_remote_id"] = $curtain_remotes->select_options($row->curtain_remote_id);
            $response["curtain_specification_id"] = $curtain_specifications->select_options($row->curtain_category_id, $row->curtain_specification_id );
            $response["curtain_width"] = $row->curtain_width;
            $response["curtain_height"] = $row->curtain_height;
            $response["order_item_qty"] = $row->order_item_qty;

            $response["is_remote_hided"] = $curtain_categories->is_remote_hided($row->curtain_category_id);
            $response["is_specification_hided"] = $curtain_categories->is_specification_hided($row->curtain_category_id);
            $response["is_width_hided"] = $curtain_categories->is_width_hided($row->curtain_category_id);
            $response["is_height_hided"] = $curtain_categories->is_height_hided($row->curtain_category_id);
            $response['min_width'] = $curtain_categories->get_min_width($row->curtain_category_id);
            $response['max_width'] = $curtain_categories->get_max_width($row->curtain_category_id);
            $response['min_height'] = $curtain_categories->get_min_height($row->curtain_category_id);
            $response['max_height'] = $curtain_categories->get_max_height($row->curtain_category_id);

            echo json_encode( $response );
            wp_die();
        }

        function caculate_order_item_amount($_id=0) {
            global $wpdb;
            $curtain_categories = new curtain_categories();
            $curtain_models = new curtain_models();
            $curtain_remotes = new curtain_remotes();
            $curtain_specifications = new curtain_specifications();

            $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}order_items WHERE curtain_order_id = %d", $_id ), OBJECT );

            $m_price = $curtain_models->get_price($row->curtain_model_id);
            $r_price = $curtain_remotes->get_price($row->curtain_remote_id);
            $s_price = $curtain_specifications->get_price($row->curtain_specification_id);

            if ($curtain_categories->is_height_hided($row->curtain_category_id)){
                $spec_amount = $row->curtain_width/100 * $s_price;
            } else {
                $spec_amount = $row->curtain_width/100 * $row->curtain_height/100 * $s_price;
            }
            
            $sub_amount = 0;
            $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}sub_items WHERE order_item_id={$_id}", OBJECT );                
            foreach ( $results as $index=>$result ) {
                $parts_price = $curtain_models->get_price($result->parts_id);
                $parts_amount = $parts_price * $result->parts_qty;
                $sub_amount .= $parts_amount;
            }
            $amount = ($m_price + $sub_amount + $spec_amount) * $row->order_item_qty;

            return $amount;
        }

        function order_item_dialog_save_data() {
            $curtain_categories = new curtain_categories();
            $curtain_models = new curtain_models();
            $curtain_remotes = new curtain_remotes();
            $curtain_specifications = new curtain_specifications();

            $width = 1;
            $height = 1;
            $qty = 1;
            $amount = 0;

            if (is_numeric($_POST['_curtain_width'])) {
                $width = $_POST['_curtain_width'];
            }
            if (is_numeric($_POST['_curtain_height'])) {
                $height = $_POST['_curtain_height'];
            }
            if (is_numeric($_POST['_order_item_qty'])) {
                $qty = $_POST['_order_item_qty'];
            }
            $m_price = $curtain_models->get_price($_POST['_curtain_model_id']);
            $r_price = $curtain_remotes->get_price($_POST['_curtain_remote_id']);
            $s_price = $curtain_specifications->get_price($_POST['_curtain_specification_id']);
            if ($curtain_categories->is_height_hided($_POST['_curtain_category_id'])){
                $amount = ($m_price + $r_price + $width/100 * $s_price) * $qty;
            } else {
                $amount = ($m_price + $r_price + $width/100 * $height/100 * $s_price) * $qty;
            }

            if( $_POST['_order_item_id']=='' ) {
                $this->insert_order_item(
                    array(
                        'curtain_agent_id'=>$this->curtain_agent_id,
                        'curtain_category_id'=>$_POST['_curtain_category_id'],
                        'curtain_model_id'=>$_POST['_curtain_model_id'],
                        'curtain_remote_id'=>$_POST['_curtain_remote_id'],
                        'curtain_specification_id'=>$_POST['_curtain_specification_id'],
                        'curtain_width'=>$_POST['_curtain_width'],
                        'curtain_height'=>$_POST['_curtain_height'],
                        'order_item_qty'=>$_POST['_order_item_qty'],
                        'order_item_amount'=>$amount,
                        'is_checkout'=>0
                    )
                );
            } else {
                $this->update_order_items(
                    array(
                        'curtain_category_id'=>$_POST['_curtain_category_id'],
                        'curtain_model_id'=>$_POST['_curtain_model_id'],
                        'curtain_remote_id'=>$_POST['_curtain_remote_id'],
                        'curtain_specification_id'=>$_POST['_curtain_specification_id'],
                        'curtain_width'=>$_POST['_curtain_width'],
                        'curtain_height'=>$_POST['_curtain_height'],
                        'order_item_qty'=>$_POST['_order_item_qty'],
                        'order_item_amount'=>$this->caculate_order_item_amount($_POST['_order_item_id']),
                        //'order_item_amount'=>$amount,
                    ),
                    array(
                        'curtain_order_id'=>$_POST['_order_item_id']
                    )
                );
            }

            $response = array();
            echo json_encode( $response );
            wp_die();
        }

        function select_category_id() {
            $curtain_categories = new curtain_categories();
            $curtain_models = new curtain_models();
            $curtain_remotes = new curtain_remotes();
            $curtain_specifications = new curtain_specifications();

            $_id = $_POST['id'];
            $response = array();
            $response["curtain_model_id"] = $curtain_models->select_options($_id);
            $response["curtain_specification_id"] = $curtain_specifications->select_options($_id);
            $response["is_remote_hided"] = $curtain_categories->is_remote_hided($_id);
            $response["is_specification_hided"] = $curtain_categories->is_specification_hided($_id);
            $response["is_width_hided"] = $curtain_categories->is_width_hided($_id);
            $response["is_height_hided"] = $curtain_categories->is_height_hided($_id);
            $response['min_width'] = $curtain_categories->get_min_width($_id);
            $response['max_width'] = $curtain_categories->get_max_width($_id);
            $response['min_height'] = $curtain_categories->get_min_height($_id);
            $response['max_height'] = $curtain_categories->get_max_height($_id);
            echo json_encode( $response );
            wp_die();
        }

        function select_order_status() {
            $this->update_customer_orders(
                array(
                    'customer_order_status'=>$_POST['_customer_order_status'],
                ),
                array(
                    'customer_order_number'=>$_POST['_customer_order_number'],
                )
            );
            $this->order_status_notice($_POST['_customer_order_number'], $_POST['_customer_order_status']);

            $response = array();
            echo json_encode( $response );
            wp_die();
        }
        
        function sub_items_dialog_get_data() {
            global $wpdb;
            $curtain_categories = new curtain_categories();
            $curtain_models = new curtain_models();

            $_id = $_POST['_id'];
            $sub_item_list = array();
            $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}sub_items WHERE order_item_id={$_id}", OBJECT );                
            foreach ( $results as $index=>$result ) {
                $value = array();
                $value["sub_item_id"] = $result->sub_item_id;
                $value["parts_id"] = $curtain_models->get_description($result->parts_id);
                $value["parts_qty"] = $result->parts_qty;
                array_push($sub_item_list, $value);
            }
            $response = array();
            $response["sub_item_list"] = $sub_item_list;
            $response["parts_options"] = $curtain_categories->parts_options();
            echo json_encode( $response );
            wp_die();
        }

        function sub_items_dialog_save_data() {
            if( $_POST['_sub_item_id']=='' ) {
                $this->insert_sub_item(
                    array(
                        'order_item_id'=>$_POST['_order_item_id'],
                        'parts_id'=>$_POST['_parts_id'],
                        'parts_qty'=>$_POST['_parts_qty'],
                    )
                );
                $this->update_order_items(
                    array(
                        'order_item_amount'=>$this->caculate_order_item_amount($_POST['_order_item_id']),
                    ),
                    array(
                        'curtain_order_id'=>$_POST['_order_item_id']
                    )
                );
            }

            $response = array();
            echo json_encode( $response );
            wp_die();
        }

        public function delete_sub_items($where=[]) {
            global $wpdb;
            $table = $wpdb->prefix.'sub_items';
            $wpdb->delete($table, $where);
        }

        public function insert_sub_item($data=[]) {
            global $wpdb;
            $table = $wpdb->prefix.'sub_items';
            $data['create_timestamp'] = time();
            $data['update_timestamp'] = time();
            $wpdb->insert($table, $data);
            return $wpdb->insert_id;
        }

        public function insert_customer_order($data=[]) {
            global $wpdb;
            $table = $wpdb->prefix.'customer_orders';
            $data['create_timestamp'] = time();
            $data['update_timestamp'] = time();
            $wpdb->insert($table, $data);
            return $wpdb->insert_id;
        }

        public function update_customer_orders($data=[], $where=[]) {
            global $wpdb;
            $table = $wpdb->prefix.'customer_orders';
            $data['update_timestamp'] = time();
            $wpdb->update($table, $data, $where);
        }

        public function delete_customer_orders($where=[]) {
            global $wpdb;
            $table = $wpdb->prefix.'customer_orders';
            $wpdb->delete($table, $where);
        }

        public function insert_order_item($data=[]) {
            global $wpdb;
            $table = $wpdb->prefix.'order_items';
            $data['create_timestamp'] = time();
            $data['update_timestamp'] = time();
            $wpdb->insert($table, $data);
            return $wpdb->insert_id;
        }

        public function update_order_items($data=[], $where=[]) {
            global $wpdb;
            $table = $wpdb->prefix.'order_items';
            $data['update_timestamp'] = time();
            $wpdb->update($table, $data, $where);
        }

        public function delete_order_items($where=[]) {
            global $wpdb;
            $table = $wpdb->prefix.'order_items';
            $wpdb->delete($table, $where);
        }

        public function create_tables() {
            global $wpdb;
            $charset_collate = $wpdb->get_charset_collate();
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
            $sql = "CREATE TABLE `{$wpdb->prefix}customer_orders` (
                customer_order_id int NOT NULL AUTO_INCREMENT,
                customer_order_number varchar(20) UNIQUE,
                curtain_agent_id int(10),
                customer_order_amount decimal(10,0),
                customer_order_status varchar(10),
                create_timestamp int(10),
                update_timestamp int(10),
                PRIMARY KEY (customer_order_id)
            ) $charset_collate;";
            dbDelta($sql);

            $sql = "CREATE TABLE `{$wpdb->prefix}order_items` (
                curtain_order_id int NOT NULL AUTO_INCREMENT,
                customer_order_number varchar(20),
                curtain_agent_id int(10),
                curtain_category_id int(10),
                curtain_model_id int(10),
                curtain_remote_id int(10),
                curtain_specification_id int(10),
                curtain_width int(10),
                curtain_height int(10),
                order_item_qty int(10),
                order_item_amount decimal(10,0),
                is_checkout tinyint,
                create_timestamp int(10),
                update_timestamp int(10),
                PRIMARY KEY (curtain_order_id)
            ) $charset_collate;";
            dbDelta($sql);

            $sql = "CREATE TABLE `{$wpdb->prefix}sub_items` (
                sub_item_id int NOT NULL AUTO_INCREMENT,
                order_item_id int,
                parts_id int,
                parts_qty int,
                create_timestamp int(10),
                update_timestamp int(10),
                PRIMARY KEY (sub_item_id)
            ) $charset_collate;";
            dbDelta($sql);
        }
    }
    $my_class = new curtain_orders();
}