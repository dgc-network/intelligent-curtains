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
            self::create_tables();
        }

        function enqueue_scripts() {		
            wp_enqueue_script( 'custom-curtain-models', plugin_dir_url( __DIR__ ) . 'assets/js/custom-curtain-models.js', array( 'jquery' ), time(), true );
        }    

        public function list_curtain_products() {

            global $wpdb;
            if( isset($_SESSION['username']) ) {
                $option = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}service_options WHERE service_option_page = %s", '_products_page' ), OBJECT );
                $user = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}curtain_users WHERE line_user_id = %s", $_SESSION['username'] ), OBJECT );
                $permission = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}user_permissions WHERE curtain_user_id = %d AND service_option_id= %d", $user->curtain_user_id, $option->service_option_id ), OBJECT );            
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
                $result = self::insert_curtain_product($data);
            }
            
            if( isset($_POST['_update']) ) {
                $data=array();
                $data['curtain_product_name']=$_POST['_curtain_product_name'];
                $where=array();
                $where['curtain_product_id']=$_POST['_curtain_product_id'];
                $result = self::update_curtain_products($data, $where);
            }

            if( isset($_GET['_delete']) ) {
                $where=array();
                $where['curtain_product_id']=$_GET['_delete'];
                $result = self::delete_curtain_products($where);
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
            $output .= '<div style="text-align: right">';
            $output .= '<form method="post">';
            $output .= '<input style="display:inline" type="text" name="_where" placeholder="Search...">';
            $output .= '<input style="display:inline" type="submit" value="Search" name="submit_action">';
            $output .= '</form>';
            $output .= '</div>';
            $output .= '<div class="ui-widget">';
            $output .= '<table id="products" class="ui-widget ui-widget-content">';
            $output .= '<thead><tr class="ui-widget-header ">';
            $output .= '<th>id</th>';
            $output .= '<th>product</th>';
            $output .= '<th>update_time</th>';
            $output .= '</tr></thead>';
            $output .= '<tbody>';
            foreach ( $results as $index=>$result ) {
                $output .= '<tr>';
                $output .= '<td>'.$result->curtain_product_id.'</a></td>';
                $output .= '<td><form method="post">';
                $output .= '<input type="hidden" value="'.$result->curtain_product_id.'" name="_id">';
                $output .= '<input type="submit" value="'.$result->curtain_product_name.'">';
                $output .= '</form></td>';
                $output .= '<td>'.wp_date( get_option('date_format'), $result->update_timestamp ).' '.wp_date( get_option('time_format'), $result->update_timestamp ).'</td>';
                $output .= '</tr>';
            }
            $output .= '</tbody></table></div>';
            $output .= '<form method="post">';
            $output .= '<input id="create-model" class="wp-block-button__link" type="submit" value="Create" name="_mode">';
            $output .= '</form>';

            if( isset($_POST['_mode']) || isset($_POST['_id']) ) {
                $_id = $_POST['_id'];
                global $wpdb;
                $row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}curtain_products WHERE curtain_product_id={$_id}", OBJECT );
                if (is_null($row) || !empty($wpdb->last_error)) {
                    $output .= '<div id="dialog" title="Create new product">';
                    $output .= '<form method="post">';
                    $output .= '<fieldset>';
                    $output .= '<label for="curtain-product-name">Product Name</label>';
                    $output .= '<input type="text" name="_curtain_product_name" id="curtain-product-name" class="text ui-widget-content ui-corner-all">';
                    $output .= '</fieldset>';
                    $output .= '<input class="wp-block-button__link" type="submit" value="Create" name="_create">';
                    $output .= '</form>';
                    $output .= '</div>';
                } else {                    
                    $output .= '<div id="dialog" title="Curtain product update">';
                    $output .= '<form method="post">';
                    $output .= '<fieldset>';
                    $output .= '<input type="hidden" value="'.$row->curtain_product_id.'" name="_curtain_product_id">';
                    $output .= '<label for="curtain-product-name">Product Name</label>';
                    $output .= '<input type="text" name="_curtain_product_name" id="curtain-product-name" class="text ui-widget-content ui-corner-all" value="'.$row->curtain_model_name.'">';
                    $output .= '</fieldset>';
                    $output .= '<input class="wp-block-button__link" type="submit" value="Update" name="_update">';
                    $output .= '</form>';
                    $output .= '</div>';
                }
            }
            return $output;
        }

        function insert_curtain_product($data=[]) {
            global $wpdb;
            $table = $wpdb->prefix.'curtain_products';
            $data['create_timestamp'] = time();
            $data['update_timestamp'] = time();
            $wpdb->insert($table, $data);        
            return $wpdb->insert_id;
        }

        function update_curtain_products($data=[], $where=[]) {
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

        public function select_options( $default_id=null ) {
            global $wpdb;
            $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}curtain_products", OBJECT );
            $output = '<option value="0">-- Select an option --</option>';
            foreach ($results as $index => $result) {
                if ( $result->curtain_product_id == $default_id ) {
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

        function create_tables() {
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
    $curtain_products = new curtain_products();
    add_shortcode( 'curtain-product-list', array( $curtain_products, 'list_curtain_products' ) );
}