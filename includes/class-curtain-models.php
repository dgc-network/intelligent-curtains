<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('curtain_models')) {
    class curtain_models {
        /**
         * Class constructor
         */
        public function __construct() {
            self::create_tables();
        }

        public function list_curtain_models() {

            global $wpdb;
            if( isset($_SESSION['username']) ) {
                $option = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}service_options WHERE service_option_page = %s", '_models_page' ), OBJECT );
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
                $data['curtain_model_name']=$_POST['_curtain_model_name'];
                $data['model_description']=$_POST['_model_description'];
                $data['model_price']=$_POST['_model_price'];
                $data['curtain_product_id']=$_POST['_curtain_product_id'];
                $data['curtain_vendor_name']=$_POST['_curtain_vendor_name'];
                $result = self::insert_curtain_model($data);
            }
            
            if( isset($_POST['_update']) ) {
                $data=array();
                $data['curtain_model_name']=$_POST['_curtain_model_name'];
                $data['model_description']=$_POST['_model_description'];
                $data['model_price']=$_POST['_model_price'];
                $data['curtain_product_id']=$_POST['_curtain_product_id'];
                $data['curtain_vendor_name']=$_POST['_curtain_vendor_name'];
                $where=array();
                $where['curtain_model_id']=$_POST['_curtain_model_id'];
                $result = self::update_curtain_models($data, $where);
                ?><script>window.location.replace("?_update=");</script><?php
            }

            if( isset($_GET['_delete']) ) {
                $where=array();
                $where['curtain_model_id']=$_GET['_delete'];
                $result = self::delete_curtain_models($where);
            }

            global $wpdb;
            if( isset($_POST['_where']) ) {
                $where='"%'.$_POST['_where'].'%"';
                $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}curtain_models WHERE model_description LIKE {$where}", OBJECT );
                unset($_POST['_where']);
            } else {
                $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}curtain_models", OBJECT );
            }
            $output  = '<h2>Curtain Models</h2>';
            $output .= '<div style="text-align: right">';
            $output .= '<form method="post">';
            $output .= '<input style="display:inline" type="text" name="_where" placeholder="Search...">';
            $output .= '<input style="display:inline" type="submit" value="Search" name="submit_action">';
            $output .= '</form>';
            $output .= '</div>';
            $output .= '<div class="ui-widget">';
            $output .= '<table id="users" class="ui-widget ui-widget-content">';
            $output .= '<thead><tr class="ui-widget-header ">';
            $output .= '<th></th>';
            $output .= '<th>model</th>';
            $output .= '<th>description</th>';
            $output .= '<th>price</th>';
            $output .= '<th>vendor</th>';
            $output .= '<th>update_time</th>';
            $output .= '<th></th>';
            $output .= '</tr></thead>';
            $output .= '<tbody>';
            foreach ( $results as $index=>$result ) {
                $output .= '<tr>';
                $output .= '<td style="text-align: center;">';
                $output .= '<span id="edit-btn-'.$result->curtain_model_id.'"><i class="fa-regular fa-pen-to-square"></i></span>';
                $output .= '</td>';
                $output .= '<td style="text-align: center;">'.$result->curtain_model_name.'</td>';
                $output .= '<td>'.$result->model_description.'</td>';
                $output .= '<td style="text-align: center;">'.$result->model_price.'</td>';
                $output .= '<td>'.$result->curtain_vendor_name.'</td>';
                $output .= '<td>'.wp_date( get_option('date_format'), $result->update_timestamp ).' '.wp_date( get_option('time_format'), $result->update_timestamp ).'</td>';
                $output .= '<td style="text-align: center;">';
                $output .= '<span id="del-btn-'.$result->curtain_model_id.'"><i class="fa-regular fa-trash-can"></i></span>';
                $output .= '</td>';
                $output .= '</tr>';
            }
            $output .= '</tbody></table></div>';
            $output .= '<form method="post">';
            $output .= '<input id="create-model" class="wp-block-button__link" type="submit" value="Create" name="_add">';
            $output .= '</form>';

            if( isset($_GET['_edit']) ) {
                $_id = $_GET['_edit'];
                $curtain_products = new curtain_products();
                $row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}curtain_models WHERE curtain_model_id={$_id}", OBJECT );
                $output .= '<div id="dialog" title="Curtain model update">';
                $output .= '<form method="post">';
                $output .= '<fieldset>';
                $output .= '<input type="hidden" value="'.$row->curtain_model_id.'" name="_curtain_model_id">';
                $output .= '<label for="curtain-model-name">Model Name</label>';
                $output .= '<input type="text" name="_curtain_model_name" id="curtain-model-name" class="text ui-widget-content ui-corner-all" value="'.$row->curtain_model_name.'">';
                $output .= '<label for="model-description">Description</label>';
                $output .= '<input type="text" name="_model_description" id="model-description" class="text ui-widget-content ui-corner-all" value="'.$row->model_description.'">';
                $output .= '<label for="model-price">Price</label>';
                $output .= '<input type="text" name="_model_price" id="model-price" class="text ui-widget-content ui-corner-all" value="'.$row->model_price.'">';
                $output .= '<label for="curtain_product_id">Product</label>';
                $output .= '<select name="_curtain_product_id" id="curtain_product_id">'.$curtain_products->select_options($row->curtain_product_id).'</select>';
                $output .= '<label for="curtain-vendor-name">Curtain Vendor</label>';
                $output .= '<input type="text" name="_curtain_vendor_name" id="curtain-vendor-name" class="text ui-widget-content ui-corner-all" value="'.$row->curtain_vendor_name.'">';
                $output .= '</fieldset>';
                $output .= '<input class="wp-block-button__link" type="submit" value="Update" name="_update">';
                $output .= '</form>';
                $output .= '</div>';
            }

            if( isset($_POST['_add']) ) {
                $curtain_products = new curtain_products();
                $output .= '<div id="dialog" title="Create new model">';
                $output .= '<form method="post">';
                $output .= '<fieldset>';
                $output .= '<label for="curtain-model-name">Model Name</label>';
                $output .= '<input type="text" name="_curtain_model_name" id="curtain-model-name" class="text ui-widget-content ui-corner-all">';
                $output .= '<label for="model-description">Description</label>';
                $output .= '<input type="text" name="_model_description" id="model-description" class="text ui-widget-content ui-corner-all">';
                $output .= '<label for="model-price">Price</label>';
                $output .= '<input type="text" name="_model_price" id="model-price" class="text ui-widget-content ui-corner-all">';
                $output .= '<label for="curtain_product_id">Product</label>';
                $output .= '<select name="_curtain_product_id" id="curtain_product_id">'.$curtain_products->select_options().'</select>';
                $output .= '<label for="curtain-vendor-name">Curtain Vendor</label>';
                $output .= '<input type="text" name="_curtain_vendor_name" id="curtain-vendor-name" class="text ui-widget-content ui-corner-all">';
                $output .= '</fieldset>';
                $output .= '<input class="wp-block-button__link" type="submit" value="Create" name="_create">';
                $output .= '</form>';
                $output .= '</div>';
            }
            return $output;
        }

        function insert_curtain_model($data=[]) {
            global $wpdb;
            $table = $wpdb->prefix.'curtain_models';
            $data['create_timestamp'] = time();
            $data['update_timestamp'] = time();
            $wpdb->insert($table, $data);        
            return $wpdb->insert_id;
        }

        function update_curtain_models($data=[], $where=[]) {
            global $wpdb;
            $table = $wpdb->prefix.'curtain_models';
            $data['update_timestamp'] = time();
            $wpdb->update($table, $data, $where);
        }

        public function delete_curtain_models($where=[]) {
            global $wpdb;
            $table = $wpdb->prefix.'curtain_models';
            $wpdb->delete($table, $where);
        }

        public function get_name( $_id=0 ) {
            global $wpdb;
            $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}curtain_models WHERE curtain_model_id = %d", $_id ), OBJECT );
            return $row->curtain_model_name;
        }

        public function get_price( $_id=0 ) {
            global $wpdb;
            $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}curtain_models WHERE curtain_model_id = %d", $_id ), OBJECT );
            return $row->model_price;
        }

        public function select_options( $_id=0 ) {
            global $wpdb;
            $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}curtain_models", OBJECT );
            $output = '<option value="0">-- Select an option --</option>';
            foreach ($results as $index => $result) {
                if ( $result->curtain_model_id == $_id ) {
                    $output .= '<option value="'.$result->curtain_model_id.'" selected>';
                } else {
                    $output .= '<option value="'.$result->curtain_model_id.'">';
                }
                $output .= $result->curtain_model_name;
                $output .= '</option>';        
            }
            $output .= '<option value="0">-- Remove this --</option>';
            return $output;
        }

        function create_tables() {
            global $wpdb;
            $charset_collate = $wpdb->get_charset_collate();
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
            $sql = "CREATE TABLE `{$wpdb->prefix}curtain_models` (
                curtain_model_id int NOT NULL AUTO_INCREMENT,
                curtain_model_name varchar(5) UNIQUE,
                model_description varchar(50),
                model_price decimal(10,2),
                curtain_vendor_name varchar(50),
                curtain_product_id int(10),
                create_timestamp int(10),
                update_timestamp int(10),
                PRIMARY KEY (curtain_model_id)
            ) $charset_collate;";
            dbDelta($sql);
        }
    }
    $curtain_models = new curtain_models();
    add_shortcode( 'curtain-model-list', array( $curtain_models, 'list_curtain_models' ) );
}