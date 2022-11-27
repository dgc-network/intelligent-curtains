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
            add_shortcode( 'order-item-list', array( __CLASS__, 'list_order_items' ) );
            self::create_tables();
        }

        function list_order_items() {

            global $wpdb;
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
                    $model = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}curtain_models WHERE curtain_model_id = %d", $result->curtain_model_id ), OBJECT );            
                    $output .= '<td>'.$model->curtain_model_name.'</td>';
                    $output .= '<td>'.$result->specification.'</td>';
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
                            $data['specification']=$result->specification;
                            $data['curtain_agent_id']=$result->curtain_agent_id;
                            //$data['x']=$x;
                            $serial_number::insert_serial_number($data, $x);
                            $x = $x + 1;
                        }
                    }
                }                
            }
            
            if( isset($_POST['_create']) ) {
                $data=array();
                $data['curtain_agent_id']=$curtain_agent_id;
                $data['curtain_model_id']=$_POST['_curtain_model_id'];
                $data['specification']=$_POST['_specification'];
                $data['order_item_qty']=$_POST['_order_item_qty'];
                $data['order_item_amount']=$_POST['_order_item_amount'];
                $data['is_checkout']=0;
                $result = self::insert_order_item($data);
            }

            if( isset($_POST['_update']) ) {
                $data=array();
                $data['curtain_model_id']=$_POST['_curtain_model_id'];
                $data['specification']=$_POST['_specification'];
                $data['order_item_qty']=$_POST['_order_item_qty'];
                $data['order_item_amount']=$_POST['_order_item_amount'];
                $where=array();
                $where['curtain_order_id']=$_POST['_curtain_order_id'];
                $result = self::update_order_items($data, $where);
            }

            if( isset($_POST['_delete']) ) {
                $where=array();
                $where['curtain_order_id']=$_POST['_curtain_order_id'];
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
            $output  = '<h2>Order Items - '.$agent->agent_name.'</h2>';            
            $output .= '<div style="text-align: right;">';
            $output .= '<form method="post">';
            $output .= '<input style="display:inline" type="text" name="_where" placeholder="Search...">';
            $output .= '<input style="display:inline" type="submit" value="Search" name="submit_action">';
            $output .= '</form>';
            $output .= '</div>';
            $output .= '<div class="ui-widget">';
            $output .= '<table id="orders" class="ui-widget ui-widget-content">';
            $output .= '<thead><tr class="ui-widget-header ">';
            $output .= '<th>date/time</th>';
            $output .= '<th>model</th>';
            $output .= '<th>spec</th>';
            $output .= '<th>QTY</th>';
            $output .= '<th>amount</th>';
            $output .= '<th>remark</th>';
            $output .= '</tr></thead>';
            $output .= '<tbody>';
            foreach ( $results as $index=>$result ) {
                $output .= '<tr>';
                $output .= '<td>';
                $output .= '<form method="post">';
                $output .= '<input type="hidden" value="'.$result->curtain_order_id.'" name="_id">';
                if ( $result->is_checkout==1) {
                    $output .= wp_date( get_option('date_format'), $result->create_timestamp ).' '.wp_date( get_option('time_format'), $result->create_timestamp );
                } else {
                    $output .= '<input type="submit" value="'.wp_date( get_option('date_format'), $result->create_timestamp ).' '.wp_date( get_option('time_format'), $result->create_timestamp ).'">';
                }
                $output .= '</form>';
                $output .= '</td>';
                $model = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}curtain_models WHERE curtain_model_id = %d", $result->curtain_model_id ), OBJECT );            
                $output .= '<td>'.$model->curtain_model_name.'</td>';
                $output .= '<td>'.$result->specification.'</td>';
                $output .= '<td>'.$result->order_item_qty.'</td>';
                $output .= '<td>'.$result->order_item_amount.'</td>';
                if ( $result->is_checkout==1 ) {
                    $output .= '<td>checkout already</td>';
                } else {
                    $output .= '<td></td>';
                }
                $output .= '</tr>';
            }
            $output .= '</tbody></table></div>';
            $output .= '<form method="post">';
            $output .= '<input class="wp-block-button__link" type="submit" value="Create Item" name="_mode">';
            $output .= '<input class="wp-block-button__link" type="submit" value="Checkout" name="_checkout_list">';
            $output .= '</form>';

            if( isset($_POST['_mode']) || isset($_POST['_id']) ) {
                $_id = $_POST['_id'];
                $curtain_models = new curtain_models();
                $row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}order_items WHERE curtain_order_id={$_id}", OBJECT );
                if (is_null($row) || !empty($wpdb->last_error)) {
                    $output .= '<div id="dialog" title="Create new item">';
                    $output .= '<form method="post">';
                    $output .= '<fieldset>';
                    $agent = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}curtain_agents WHERE curtain_agent_id = %d", $curtain_agent_id ), OBJECT );            
                    $output .= '<label for="curtain_agent_id">Agent</label>';
                    $output .= '<input type="text" disabled value="'.$agent->agent_name.'" id="curtain_agent_id" class="text ui-widget-content ui-corner-all">';
                    $output .= '<label for="curtain_model_id">Model</label>';
                    $output .= '<select name="_curtain_model_id" id="curtain_model_id">'.$curtain_models->select_options().'</select>';
                    $output .= '<label for="specification">Specification</label>';
                    $output .= '<input type="text" name="_specification" id="specification" class="text ui-widget-content ui-corner-all">';
                    $output .= '<label for="order_item_qty">QTY</label>';
                    $output .= '<input type="text" name="_order_item_qty" id="order_item_qty" class="text ui-widget-content ui-corner-all">';
                    $output .= '<label for="order_item_amount">Amount</label>';
                    $output .= '<input type="text" name="_order_item_amount" id="order_item_amount" class="text ui-widget-content ui-corner-all">';
                    $output .= '</fieldset>';
                    $output .= '<input class="wp-block-button__link" type="submit" value="Create" name="_create">';
                    $output .= '<input class="wp-block-button__link" type="submit" value="Cancel"';
                    $output .= '</form>';
                    $output .= '</div>';
                } else {
                    $output .= '<div id="dialog" title="Order item update">';
                    $output .= '<form method="post">';
                    $output .= '<fieldset>';
                    $output .= '<input type="hidden" name="_curtain_order_id" value="'.$row->curtain_order_id.'">';
                    $agent = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}curtain_agents WHERE curtain_agent_id = %d", $row->curtain_agent_id ), OBJECT );
                    $output .= '<label for="curtain_agent_id">Agent</label>';
                    $output .= '<input type="text" disabled value="'.$agent->agent_name.'" id="curtain_agent_id" class="text ui-widget-content ui-corner-all">';
                    $output .= '<label for="curtain_model_id">Model</label>';
                    $output .= '<select name="_curtain_model_id" id="curtain_model_id">'.$curtain_models->select_options($row->curtain_model_id).'</select>';
                    $output .= '<label for="specification">Specification</label>';
                    $output .= '<input type="text" name="_specification" value="'.$row->specification.'" id="specification" class="text ui-widget-content ui-corner-all">';
                    $output .= '<label for="order_item_qty">QTY</label>';
                    $output .= '<input type="text" name="_order_item_qty" value="'.$row->order_item_qty.'" id="order_item_qty" class="text ui-widget-content ui-corner-all">';
                    $output .= '<label for="order_item_amount">Amount</label>';
                    $output .= '<input type="text" name="_order_item_amount" value="'.$row->order_item_amount.'" id="order_item_amount" class="text ui-widget-content ui-corner-all">';
                    $output .= '</fieldset>';
                    $output .= '<input class="wp-block-button__link" type="submit" value="Update" name="_update">';
                    $output .= '<input class="wp-block-button__link" type="submit" value="Delete" name="_delete" id="delete-btn-">';
                    $output .= '</form>';
                    $output .= '</div>';
                }
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
                curtain_model_id int(10),
                specification varchar(10),
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
    new order_items();
}