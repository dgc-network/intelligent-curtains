<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('order_items')) {
    class order_items {
        /**
         * Class constructor
         */
        public function __construct() {
            //add_action( 'wp_ajax_select_product_id', array( __CLASS__, 'select_product_id' ) );
            //add_action( 'wp_ajax_nopriv_select_product_id', array( __CLASS__, 'select_product_id' ) );
            self::create_tables();
        }

        function select_product_id() {

            //$from = $_SESSION['username'];
            $_id = $_POST['id'];

            global $wpdb;
            $models = array();
            $models[] = '<option value="0">-- Select an option --</option>';
            $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}curtain_models WHERE curtain_product_id={$_id}" , OBJECT );
            foreach ($results as $index => $result) {
                $models[] = '<option value="'.$result->curtain_model_id.'">'.$result->curtain_model_name.'</option>';
            }
            $models[] = '<option value="0">-- Remove this --</option>';

            $specifications = array();
            $specifications[] = '<option value="0">-- Select an option --</option>';
            $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}curtain_specifications WHERE curtain_product_id={$_id}" , OBJECT );
            foreach ($results as $index => $result) {
                $specifications[] = '<option value="'.$result->curtain_specification_id.'">'.$result->curtain_specification_name.'</option>';
            }
            $specifications[] = '<option value="0">-- Remove this --</option>';

            $response = array();
            $response['currenttime'] = wp_date( get_option('time_format'), time() );
            $response['models'] = $models;;
            $response['specifications'] = $specifications;;
            echo json_encode( $response );
            
            wp_die();
        }

        function list_order_items() {
            global $wpdb;
            $curtain_products = new curtain_products();
            $curtain_models = new curtain_models();
            $curtain_specifications = new curtain_specifications();

            $curtain_agent_id = 0;
            if( isset($_SESSION['username']) ) {
                $user = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}curtain_users WHERE line_user_id = %s", $_SESSION['username'] ), OBJECT );
                $curtain_agent_id = $user->curtain_agent_id;
                if (is_null($user->curtain_agent_id) || $user->curtain_agent_id==0 || !empty($wpdb->last_error)) {
                    if ( $_GET['_check_permission'] != 'false' ) {
                        return 'You have not permission to access this page. Please check to the administrators.';
                    }
                }
            }

            if( isset($_POST['_checkout_list']) ) {
                $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}order_items WHERE curtain_agent_id={$curtain_agent_id} AND is_checkout=0", OBJECT );
                $agent = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}curtain_agents WHERE curtain_agent_id = %d", $curtain_agent_id ), OBJECT );            
                $output  = '<h2>Order Items Checkout - '.$agent->agent_name.'</h2>';            
                $output .= '<form method="post">';
                $output .= '<div class="ui-widget">';
                $output .= '<table id="orders" class="ui-widget ui-widget-content">';
                $output .= '<thead><tr class="ui-widget-header ">';
                $output .= '<th></th>';
                $output .= '<th>date/time</th>';
                $output .= '<th>product</th>';
                $output .= '<th>model</th>';
                $output .= '<th>spec</th>';
                $output .= '<th>QTY</th>';
                $output .= '<th>amount</th>';
                $output .= '<th>remark</th>';
                $output .= '</tr></thead>';
                $output .= '<tbody>';
                foreach ( $results as $index=>$result ) {
                    $output .= '<tr>';
                    $output .= '<td><input type="checkbox" value="1" name="_is_checkout_'.$index.'"></td>';
                    $output .= '<td>'.wp_date( get_option('date_format'), $result->create_timestamp ).' '.wp_date( get_option('time_format'), $result->create_timestamp ).'</td>';
                    $product = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}curtain_products WHERE curtain_product_id = %d", $result->curtain_product_id ), OBJECT );            
                    $output .= '<td>'.$product->curtain_product_name.'</td>';
                    $model = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}curtain_models WHERE curtain_model_id = %d", $result->curtain_model_id ), OBJECT );            
                    $output .= '<td>'.$model->curtain_model_name.'</td>';
                    $specification = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}curtain_specifications WHERE curtain_specification_id = %d", $result->curtain_specification_id ), OBJECT );            
                    $output .= '<td>'.$specification->curtain_specification_name.$result->curtain_width.'</td>';
                    $output .= '<td>'.$result->order_item_qty.'</td>';
                    $output .= '<td>'.$result->order_item_amount.'</td>';
                    $output .= '<td>'.'</td>';
                    $output .= '</tr>';
                }
                $output .= '</tbody></table></div>';
                $output .= '<form method="post">';
                $output .= '<input class="wp-block-button__link" type="submit" value="Checkout" name="_checkout_submit">';
                $output .= '</form>';
                return $output;
            }

            if( isset($_POST['_checkout_submit']) ) {
                $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}order_items WHERE curtain_agent_id={$curtain_agent_id} AND is_checkout=0", OBJECT );
                foreach ( $results as $index=>$result ) {
                    $_is_checkout = '_is_checkout_'.$index;
                    if ( $_POST[$_is_checkout]==1 ) {
                        $data=array();
                        $data['is_checkout']=1;
                        $where=array();
                        $where['curtain_order_id']=$result->curtain_order_id;
                        self::update_order_items($data, $where);

                        $serial_number = new serial_number();
                        $x = 0;
                        while ($x < $result->order_item_qty) {
                            $data=array();
                            $data['curtain_model_id']=$result->curtain_model_id;
                            $data['specification']=$result->curtain_width;
                            $data['curtain_agent_id']=$result->curtain_agent_id;
                            $serial_number->insert_serial_number($data, $x);
                            $x = $x + 1;
                        }
                    }
                }                
            }
            
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
                if (is_numeric($_POST['_order_item_qty'])) {
                    $qty = $_POST['_order_item_qty'];
                }
                $m_price = $curtain_models->get_price($_POST['_curtain_model_id']);
                $s_price = $curtain_specifications->get_price($_POST['_curtain_specification_id']);
                if ($curtain_specifications->is_length_only($_POST['_curtain_specification_id'])==1){
                    $amount = $m_price + $width/100 * $s_price * $qty;
                } else {
                    $amount = $m_price + $width/100 * $height/100 * $s_price * $qty;
                }

                $data=array();
                $data['curtain_agent_id']=$curtain_agent_id;
                $data['curtain_product_id']=$_POST['_curtain_product_id'];
                $data['curtain_model_id']=$_POST['_curtain_model_id'];
                $data['curtain_specification_id']=$_POST['_curtain_specification_id'];
                $data['curtain_width']=$_POST['_curtain_width'];
                $data['curtain_height']=$_POST['_curtain_height'];
                $data['order_item_qty']=$_POST['_order_item_qty'];
                $data['order_item_amount']=$amount;
                $data['is_checkout']=0;
                $result = self::insert_order_item($data);
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
                if (is_numeric($_POST['_order_item_qty'])) {
                    $qty = $_POST['_order_item_qty'];
                }
                $m_price = $curtain_models->get_price($_POST['_curtain_model_id']);
                $s_price = $curtain_specifications->get_price($_POST['_curtain_specification_id']);
                if ($curtain_specifications->is_length_only($_POST['_curtain_specification_id'])==1){
                    $amount = $m_price + $width/100 * $s_price * $qty;
                } else {
                    $amount = $m_price + $width/100 * $height/100 * $s_price * $qty;
                }

                $data=array();
                $data['curtain_product_id']=$_POST['_curtain_product_id'];
                $data['curtain_model_id']=$_POST['_curtain_model_id'];
                $data['curtain_specification_id']=$_POST['_curtain_specification_id'];
                $data['curtain_width']=$_POST['_curtain_width'];
                $data['curtain_height']=$_POST['_curtain_height'];
                $data['order_item_qty']=$_POST['_order_item_qty'];
                $data['order_item_amount']=$amount;
                $where=array();
                $where['curtain_order_id']=$_POST['_curtain_order_id'];
                $result = self::update_order_items($data, $where);
                ?><script>window.location.replace("?_update=");</script><?php
            }

            if( isset($_GET['_delete']) ) {
                $where=array();
                $where['curtain_order_id']=$_GET['_delete'];
                $result = self::delete_order_items($where);
            }

            if( isset($_POST['_where']) ) {
                $where='"%'.$_POST['_where'].'%"';
                $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}order_items WHERE curtain_agent_id={$curtain_agent_id}", OBJECT );
                unset($_POST['_where']);
            } else {
                $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}order_items WHERE curtain_agent_id={$curtain_agent_id} AND is_checkout=0", OBJECT );
            }
            $agent = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}curtain_agents WHERE curtain_agent_id = %d", $curtain_agent_id ), OBJECT );            
            $output  = '<h2>Order Items</h2>';
            $output .= '<div style="text-align: right;">';
            $output .= '<form method="post">';
            $output .= '<input style="display:inline" type="text" name="_where" placeholder="Search...">';
            $output .= '<input style="display:inline" type="submit" value="Search" name="submit_action">';
            $output .= '</form>';
            $output .= '</div>';
            $output .= '<div class="ui-widget">';
            $output .= '<table id="orders" class="ui-widget ui-widget-content">';
            $output .= '<thead><tr class="ui-widget-header ">';
            $output .= '<th></th>';
            $output .= '<th>date/time</th>';
            $output .= '<th>product</th>';
            $output .= '<th>model</th>';
            $output .= '<th>spec</th>';
            $output .= '<th>QTY</th>';
            $output .= '<th>amount</th>';
            $output .= '<th></th>';
            $output .= '</tr></thead>';
            $output .= '<tbody>';
            foreach ( $results as $index=>$result ) {
                $output .= '<tr>';
                if ( $result->is_checkout==1 ) {
                    $output .= '<td></td>';
                } else {
                    $output .= '<td style="text-align: center;">';
                    $output .= '<span id="edit-btn-'.$result->curtain_order_id.'"><i class="fa-regular fa-pen-to-square"></i></span>';
                    $output .= '</td>';
                }
                $output .= '<td>';
                $output .= wp_date( get_option('date_format'), $result->create_timestamp ).' '.wp_date( get_option('time_format'), $result->create_timestamp );
                $output .= '</td>';
                //$product = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}curtain_products WHERE curtain_product_id = %d", $result->curtain_product_id ), OBJECT );            
                //$output .= '<td>'.$product->curtain_product_name.'</td>';
                $output .= '<td>'.$curtain_products->get_name($result->curtain_product_id).'</td>';
                //$model = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}curtain_models WHERE curtain_model_id = %d", $result->curtain_model_id ), OBJECT );            
                //$output .= '<td style="text-align: center;">'.$model->curtain_model_name.'</td>';
                $output .= '<td style="text-align: center;">'.$curtain_models->get_name($result->curtain_model_id).'</td>';
                //$specification = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}curtain_specifications WHERE curtain_specification_id = %d", $result->curtain_specification_id ), OBJECT );            
                //$output .= '<td style="text-align: center;">'.$specification->curtain_specification_name.$result->curtain_width.'</td>';
                $output .= '<td style="text-align: center;">'.$curtain_specifications->get_name($result->curtain_specification_id).$result->curtain_width.'</td>';
                $output .= '<td style="text-align: center;">'.$result->order_item_qty.'</td>';
                $output .= '<td style="text-align: center;">'.$result->order_item_amount.'</td>';
                if ( $result->is_checkout==1 ) {
                    $output .= '<td>checkout already</td>';
                } else {
                    $output .= '<td style="text-align: center;">';
                    $output .= '<span id="del-btn-'.$result->curtain_order_id.'"><i class="fa-regular fa-trash-can"></i></span>';
                    $output .= '</td>';
                }
                $output .= '</tr>';
            }
            $output .= '</tbody></table></div>';
            $output .= '<form method="post">';
            $output .= '<input class="wp-block-button__link" type="submit" value="Create Item" name="_add">';
            $output .= '<input class="wp-block-button__link" type="submit" value="Checkout" name="_checkout_list">';
            $output .= '</form>';

            if( isset($_GET['_edit']) ) {
                $_id = $_GET['_edit'];
                $curtain_products = new curtain_products();
                $curtain_models = new curtain_models();
                $curtain_specifications = new curtain_specifications();
                $row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}order_items WHERE curtain_order_id={$_id}", OBJECT );
                $output .= '<div id="dialog" title="Order item update">';
                $output .= '<form method="post">';
                $output .= '<fieldset>';
                $output .= '<input type="hidden" name="_curtain_order_id" value="'.$row->curtain_order_id.'">';
                $output .= '<label for="select-product-id">Product</label>';
                $output .= '<select name="_curtain_product_id" id="select-product-id">'.$curtain_products->select_options($row->curtain_product_id).'</select>';
                $output .= '<label for="select-model-id">Model</label>';
                $output .= '<select name="_curtain_model_id" id="select-model-id">'.$curtain_models->select_options($row->curtain_model_id).'</select>';
                $output .= '<label for="select-specification-id">Specification</label>';
                $output .= '<select name="_curtain_specification_id" id="select-specification-id">'.$curtain_specifications->select_options($row->curtain_specification_id).'</select>';
                $output .= '<label for="curtain-dimension">Dimension</label>';
                $output .= '<div style="display: flex;">';
                $output .= '<input type="text" name="_curtain_width" value="'.$row->curtain_width.'" id="curtain-dimension" class="text ui-widget-content ui-corner-all">';
                $output .= '<span> x </span>';
                $output .= '<input type="text" name="_curtain_height" value="'.$row->curtain_height.'" id="curtain-dimension" class="text ui-widget-content ui-corner-all">';
                $output .= '</div>';
                $output .= '<label for="order_item_qty">QTY</label>';
                $output .= '<input type="text" name="_order_item_qty" value="'.$row->order_item_qty.'" id="order_item_qty" class="text ui-widget-content ui-corner-all">';
                $output .= '</fieldset>';
                $output .= '<input class="wp-block-button__link" type="submit" value="Update" name="_update" id="update-btn-'.$row->curtain_order_id.'">';
                $output .= '</form>';
                $output .= '</div>';
            }

            if( isset($_POST['_add']) ) {
                $curtain_products = new curtain_products();
                $curtain_models = new curtain_models();
                $curtain_specifications = new curtain_specifications();
                $output .= '<div id="dialog" title="Create new item">';
                $output .= '<form method="post">';
                $output .= '<fieldset>';
                $output .= '<label for="select-product-id">Product</label>';
                $output .= '<select name="_curtain_product_id" id="select-product-id">'.$curtain_products->select_options().'</select>';
                $output .= '<label for="select-model-id">Model</label>';
                $output .= '<select name="_curtain_model_id" id="select-model-id">'.$curtain_models->select_options().'</select>';
                $output .= '<label for="select-specification-id">Specification</label>';
                $output .= '<select name="_curtain_specification_id" id="select-specification-id">'.$curtain_specifications->select_options().'</select>';
                $output .= '<label for="curtain-dimension">Dimension</label>';
                $output .= '<div style="display: flex;">';
                $output .= '<input type="text" name="_curtain_width" id="curtain-width" class="text ui-widget-content ui-corner-all">';
                $output .= '<span> x </span>';
                $output .= '<input type="text" name="_curtain_height" id="curtain-height" class="text ui-widget-content ui-corner-all">';
                $output .= '</div>';
                $output .= '<label for="order_item_qty">QTY</label>';
                $output .= '<input type="text" name="_order_item_qty" id="order_item_qty" class="text ui-widget-content ui-corner-all">';
                $output .= '</fieldset>';
                $output .= '<input class="wp-block-button__link" type="submit" value="Create" name="_create">';
                $output .= '</form>';
                $output .= '</div>';
            }
            return $output;
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

        function create_tables() {
            global $wpdb;
            $charset_collate = $wpdb->get_charset_collate();
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
            $sql = "CREATE TABLE `{$wpdb->prefix}order_items` (
                curtain_order_id int NOT NULL AUTO_INCREMENT,
                order_master_id int(10),
                order_number varchar(50),
                curtain_agent_id int(10),
                curtain_product_id int(10),
                curtain_model_id int(10),
                curtain_specification_id int(10),
                curtain_width int(10),
                curtain_height int(10),
                order_item_qty int(10),
                order_item_amount decimal(10,2),
                is_checkout tinyint,
                create_timestamp int(10),
                update_timestamp int(10),
                PRIMARY KEY (curtain_order_id)
            ) $charset_collate;";
            dbDelta($sql);
        }
    }
    $order_items = new order_items();
    add_shortcode( 'order-item-list', array( $order_items, 'list_order_items' ) );
    add_action( 'wp_ajax_select_product_id', array( $order_items, 'select_product_id' ) );
    add_action( 'wp_ajax_nopriv_select_product_id', array( $order_items, 'select_product_id' ) );
}