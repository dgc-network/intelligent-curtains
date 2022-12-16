<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('curtain_products')) {
    class curtain_products {
        /**
         * Class constructor
         */
        public function __construct() {
            $this->create_tables();
        }

        public function list_curtain_products() {
            global $wpdb;
            $curtain_service = new curtain_service();

            if( isset($_SESSION['line_user_id']) ) {
                $_option_page = 'Products';
                $permission = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}user_permissions WHERE line_user_id = %s AND service_option_id= %d", $_SESSION['line_user_id'], $curtain_service->get_id($_option_page) ), OBJECT );            
                if (is_null($permission) || !empty($wpdb->last_error)) {
                    if ( $_GET['_check_permission'] != 'false' ) {
                        return 'You have not permission to access this page. Please check to the administrators.';
                    }
                }
            } else {
                if ( $_GET['_check_permission'] != 'false' ) {
                    return 'You have not permission to access this page. Please check to the administrators.';
                }
            }

            if( isset($_POST['_create']) ) {
                $data=array();
                $data['curtain_product_name']=$_POST['_curtain_product_name'];
                $this->insert_curtain_product($data);
            }
            
            if( isset($_POST['_update']) ) {
                $data=array();
                $data['curtain_product_name']=$_POST['_curtain_product_name'];
                $where=array();
                $where['curtain_product_id']=$_POST['_curtain_product_id'];
                $this->update_curtain_products($data, $where);
                ?><script>window.location.replace("?_update=");</script><?php
            }

            if( isset($_GET['_delete']) ) {
                $where=array();
                $where['curtain_product_id']=$_GET['_delete'];
                $this->delete_curtain_products($where);
            }

            global $wpdb;
            if( isset($_POST['_where']) ) {
                $where='"%'.$_POST['_where'].'%"';
                $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}curtain_products WHERE product_name LIKE {$where}", OBJECT );
                unset($_POST['_where']);
            } else {
                $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}curtain_products", OBJECT );
            }
            $output  = '<h2>Curtain Products</h2>';
            $output .= '<div style="display: flex; justify-content: space-between; margin: 5px;">';
            $output .= '<div>';
            $output .= '<form method="post">';
            $output .= '<input class="wp-block-button__link" type="submit" value="Create" name="_add">';
            $output .= '</form>';
            $output .= '</div>';
            $output .= '<div style="text-align: right">';
            $output .= '<form method="post">';
            $output .= '<input style="display:inline" type="text" name="_where" placeholder="Search...">';
            $output .= '<input style="display:inline" type="submit" value="Search" name="submit_action">';
            $output .= '</form>';
            $output .= '</div>';
            $output .= '</div>';

            $output .= '<div class="ui-widget">';
            $output .= '<table id="products" class="ui-widget ui-widget-content">';
            $output .= '<thead><tr class="ui-widget-header ">';
            $output .= '<th></th>';
            $output .= '<th>product</th>';
            $output .= '<th>update_time</th>';
            $output .= '<th></th>';
            $output .= '</tr></thead>';
            $output .= '<tbody>';
            foreach ( $results as $index=>$result ) {
                $output .= '<tr>';
                $output .= '<td style="text-align: center;">';
                $output .= '<span id="edit-btn-'.$result->curtain_product_id.'"><i class="fa-regular fa-pen-to-square"></i></span>';
                $output .= '</td>';
                $output .= '<td>'.$result->curtain_product_name.'</td>';
                $output .= '<td>'.wp_date( get_option('date_format'), $result->update_timestamp ).' '.wp_date( get_option('time_format'), $result->update_timestamp ).'</td>';
                $output .= '<td style="text-align: center;">';
                $output .= '<span id="del-btn-'.$result->curtain_product_id.'"><i class="fa-regular fa-trash-can"></i></span>';
                $output .= '</td>';
                $output .= '</tr>';
            }
            $output .= '</tbody></table></div>';

            if( isset($_GET['_edit']) ) {
                $_id = $_GET['_edit'];
                $row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}curtain_products WHERE curtain_product_id={$_id}", OBJECT );
                $output .= '<div id="dialog" title="Curtain product update">';
                $output .= '<form method="post">';
                $output .= '<fieldset>';
                $output .= '<input type="hidden" value="'.$row->curtain_product_id.'" name="_curtain_product_id">';
                $output .= '<label for="curtain-product-name">Product Name</label>';
                $output .= '<input type="text" name="_curtain_product_name" value="'.$row->curtain_product_name.'" id="curtain-product-name" class="text ui-widget-content ui-corner-all">';
                $output .= '</fieldset>';
                $output .= '<input class="wp-block-button__link" type="submit" value="Update" name="_update">';
                $output .= '</form>';
                $output .= '</div>';
            }

            if( isset($_POST['_add']) ) {
                $output .= '<div id="dialog" title="Create new product">';
                $output .= '<form method="post">';
                $output .= '<fieldset>';
                $output .= '<label for="curtain-product-name">Product Name</label>';
                $output .= '<input type="text" name="_curtain_product_name" id="curtain-product-name" class="text ui-widget-content ui-corner-all">';
                $output .= '</fieldset>';
                $output .= '<input class="wp-block-button__link" type="submit" value="Create" name="_create">';
                $output .= '</form>';
                $output .= '</div>';
            }
            return $output;
        }

        public function insert_curtain_product($data=[]) {
            global $wpdb;
            $table = $wpdb->prefix.'curtain_products';
            $data['create_timestamp'] = time();
            $data['update_timestamp'] = time();
            $wpdb->insert($table, $data);        
            return $wpdb->insert_id;
        }

        public function update_curtain_products($data=[], $where=[]) {
            global $wpdb;
            $table = $wpdb->prefix.'curtain_products';
            $data['update_timestamp'] = time();
            $wpdb->update($table, $data, $where);
        }

        public function delete_curtain_products($where=[]) {
            global $wpdb;
            $table = $wpdb->prefix.'curtain_products';
            $wpdb->delete($table, $where);
        }

        public function get_name( $_id=0 ) {
            global $wpdb;
            $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}curtain_products WHERE curtain_product_id = %d", $_id ), OBJECT );
            return $row->curtain_product_name;
        }

        public function select_options( $_id=0 ) {
            global $wpdb;
            $output = '<option value="0">-- Select an option --</option>';
            $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}curtain_products", OBJECT );
            foreach ($results as $index => $result) {
                if ( $result->curtain_product_id == $_id ) {
                    $output .= '<option value="'.$result->curtain_product_id.'" selected>';
                } else {
                    $output .= '<option value="'.$result->curtain_product_id.'">';
                }
                $output .= $result->curtain_product_name;
                $output .= '</option>';        
            }
            $output .= '<option value="0">-- Remove this --</option>';
            return $output;
        }

        public function create_tables() {
            global $wpdb;
            $charset_collate = $wpdb->get_charset_collate();
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
            $sql = "CREATE TABLE `{$wpdb->prefix}curtain_products` (
                curtain_product_id int NOT NULL AUTO_INCREMENT,
                curtain_product_name varchar(50),
                create_timestamp int(10),
                update_timestamp int(10),
                PRIMARY KEY (curtain_product_id)
            ) $charset_collate;";
            dbDelta($sql);
        }
    }
    $my_class = new curtain_products();
    add_shortcode( 'curtain-product-list', array( $my_class, 'list_curtain_products' ) );
}