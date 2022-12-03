<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('curtain_specifications')) {
    class curtain_specifications {
        /**
         * Class constructor
         */
        public function __construct() {
            self::create_tables();
        }

        public function list_curtain_specifications() {

            global $wpdb;
            if( isset($_SESSION['username']) ) {
                $option = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}service_options WHERE service_option_page = %s", '_specifications_page' ), OBJECT );
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
                $data['curtain_specification_name']=$_POST['_curtain_specification_name'];
                $data['specification_description']=$_POST['_specification_description'];
                $data['specification_price']=$_POST['_specification_price'];
                $data['specification_unit']=$_POST['_specification_unit'];
                $data['curtain_product_id']=$_POST['_curtain_product_id'];
                $data['length_only']=$_POST['_length_only'];
                $result = self::insert_curtain_specification($data);
            }
            
            if( isset($_POST['_update']) ) {
                $data=array();
                $data['curtain_specification_name']=$_POST['_curtain_specification_name'];
                $data['specification_description']=$_POST['_specification_description'];
                $data['specification_price']=$_POST['_specification_price'];
                $data['specification_unit']=$_POST['_specification_unit'];
                $data['curtain_product_id']=$_POST['_curtain_product_id'];
                $data['length_only']=$_POST['_length_only'];
                $where=array();
                $where['curtain_specification_id']=$_POST['_curtain_specification_id'];
                $result = self::update_curtain_specifications($data, $where);
                ?><script>window.location.replace("?_update=");</script><?php
            }

            if( isset($_GET['_delete']) ) {
                $where=array();
                $where['curtain_specification_id']=$_GET['_delete'];
                $result = self::delete_curtain_specifications($where);
            }

            global $wpdb;
            if( isset($_POST['_where']) ) {
                $where='"%'.$_POST['_where'].'%"';
                $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}curtain_specifications WHERE specification_description LIKE {$where}", OBJECT );
                unset($_POST['_where']);
            } else {
                $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}curtain_specifications", OBJECT );
            }
            $output  = '<h2>Curtain Specifications</h2>';
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
            $output .= '<table id="specifications" class="ui-widget ui-widget-content">';
            $output .= '<thead><tr class="ui-widget-header ">';
            $output .= '<th></th>';
            $output .= '<th>name</th>';
            $output .= '<th>description</th>';
            $output .= '<th>product</th>';
            $output .= '<th>unit</th>';
            $output .= '<th>price</th>';
            $output .= '<th>update_time</th>';
            $output .= '<th></th>';
            $output .= '</tr></thead>';
            $output .= '<tbody>';
            foreach ( $results as $index=>$result ) {
                $output .= '<tr>';
                $output .= '<td style="text-align: center;">';
                $output .= '<span id="edit-btn-'.$result->curtain_specification_id.'"><i class="fa-regular fa-pen-to-square"></i></span>';
                $output .= '</td>';
                $output .= '<td>'.$result->curtain_specification_name.'</td>';
                $output .= '<td>'.$result->specification_description.'</td>';
                $row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}curtain_products WHERE curtain_product_id={$result->curtain_product_id}", OBJECT );
                $output .= '<td>'.$row->curtain_product_name.'</td>';
                $output .= '<td style="text-align: center;">'.$result->specification_unit.'</td>';
                $output .= '<td style="text-align: center;">'.$result->specification_price.'</td>';
                $output .= '<td>'.wp_date( get_option('date_format'), $result->update_timestamp ).' '.wp_date( get_option('time_format'), $result->update_timestamp ).'</td>';
                $output .= '<td style="text-align: center;">';
                $output .= '<span id="del-btn-'.$result->curtain_specification_id.'"><i class="fa-regular fa-trash-can"></i></span>';
                $output .= '</td>';
                $output .= '</tr>';
            }
            $output .= '</tbody></table></div>';
            $output .= '<form method="post">';
            $output .= '<input class="wp-block-button__link" type="submit" value="Create" name="_add">';
            $output .= '</form>';

            if( isset($_GET['_edit']) ) {
                $_id = $_GET['_edit'];
                $curtain_products = new curtain_products();
                //$row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}curtain_specifications WHERE curtain_specification_id={$_id}", OBJECT );
                $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}curtain_specifications WHERE curtain_specification_id = %d", $_id ), OBJECT );
                $output .= '<div id="dialog" title="Curtain specification update">';
                $output .= '<form method="post">';
                $output .= '<fieldset>';
                $output .= '<input type="hidden" value="'.$row->curtain_specification_id.'" name="_curtain_specification_id">';
                $output .= '<label for="curtain-specification-name">Specification</label>';
                $output .= '<input type="text" name="_curtain_specification_name" id="curtain-specification-name" class="text ui-widget-content ui-corner-all" value="'.$row->curtain_specification_name.'">';
                $output .= '<label for="specification-description">Description</label>';
                $output .= '<input type="text" name="_specification_description" id="specification-description" class="text ui-widget-content ui-corner-all" value="'.$row->specification_description.'">';
                $output .= '<label for="specification-price">Price</label>';
                $output .= '<input type="text" name="_specification_price" id="specification-price" class="text ui-widget-content ui-corner-all" value="'.$row->specification_price.'">';
                $output .= '<label for="specification-unit">Unit</label>';
                $output .= '<input type="text" name="_specification_unit" id="specification-unit" class="text ui-widget-content ui-corner-all" value="'.$row->specification_unit.'">';
                $output .= '<label for="curtain_product_id">Product</label>';
                $output .= '<select name="_curtain_product_id" id="curtain_product_id">'.$curtain_products->select_options($row->curtain_product_id).'</select>';
                $output .= '<div style="display: flex;">';
                $output .= '<input type="checkbox" value="1" name="_length_only" id="length-only"';
                if ($row->length_only==1) {
                    $output .= ' checked';    
                }
                $output .= '><span>  </span>';
                $output .= '<label for="length-only">Length Only</label>';
                $output .= '</div>';
                $output .= '</fieldset>';
                $output .= '<input class="wp-block-button__link" type="submit" value="Update" name="_update">';
                $output .= '</form>';
                $output .= '</div>';
            }

            if( isset($_POST['_add']) ) {
                $curtain_products = new curtain_products();
                $output .= '<div id="dialog" title="Create new specification">';
                $output .= '<form method="post">';
                $output .= '<fieldset>';
                $output .= '<label for="curtain-specification-name">Specification</label>';
                $output .= '<input type="text" name="_curtain_specification_name" id="curtain-specification-name" class="text ui-widget-content ui-corner-all">';
                $output .= '<label for="specification-description">Description</label>';
                $output .= '<input type="text" name="_specification_description" id="specification-description" class="text ui-widget-content ui-corner-all">';
                $output .= '<label for="specification-price">Price</label>';
                $output .= '<input type="text" name="_specification_price" id="specification-price" class="text ui-widget-content ui-corner-all">';
                $output .= '<label for="specification-unit">Unit</label>';
                $output .= '<input type="text" name="_specification_unit" id="specification-unit" class="text ui-widget-content ui-corner-all">';
                $output .= '<label for="curtain_product_id">Product</label>';
                $output .= '<select name="_curtain_product_id" id="curtain_product_id">'.$curtain_products->select_options().'</select>';
                $output .= '<div style="display: flex;">';
                $output .= '<input type="checkbox" value="1" name="_length_only" id="length-only">';
                $output .= '<span>  </span>';
                $output .= '<label for="length-only">Length Only</label>';
                $output .= '</div>';
                $output .= '</fieldset>';
                $output .= '<input class="wp-block-button__link" type="submit" value="Create" name="_create">';
                $output .= '</form>';
                $output .= '</div>';
            }
            return $output;
        }

        function insert_curtain_specification($data=[]) {
            global $wpdb;
            $table = $wpdb->prefix.'curtain_specifications';
            $data['create_timestamp'] = time();
            $data['update_timestamp'] = time();
            $wpdb->insert($table, $data);        
            return $wpdb->insert_id;
        }

        function update_curtain_specifications($data=[], $where=[]) {
            global $wpdb;
            $table = $wpdb->prefix.'curtain_specifications';
            $data['update_timestamp'] = time();
            $wpdb->update($table, $data, $where);
        }

        public function delete_curtain_specifications($where=[]) {
            global $wpdb;
            $table = $wpdb->prefix.'curtain_specifications';
            $wpdb->delete($table, $where);
        }

        public function get_name( $_id=0 ) {
            global $wpdb;
            $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}curtain_specifications WHERE curtain_specification_id = %d", $_id ), OBJECT );
            return $row->curtain_specification_name;
        }

        public function get_price( $_id=0 ) {
            global $wpdb;
            $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}curtain_specifications WHERE curtain_specification_id = %d", $_id ), OBJECT );
            return $row->specification_price;
        }

        public function is_length_only( $_id=0 ) {
            global $wpdb;
            $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}curtain_specifications WHERE curtain_specification_id = %d", $_id ), OBJECT );
            return $row->length_only;
        }

        public function select_options( $_id=0 ) {
            global $wpdb;
            $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}curtain_specifications", OBJECT );
            $output = '<option value="0">-- Select an option --</option>';
            foreach ($results as $index => $result) {
                if ( $result->curtain_specification_id == $_id ) {
                    $output .= '<option value="'.$result->curtain_specification_id.'" selected>';
                } else {
                    $output .= '<option value="'.$result->curtain_specification_id.'">';
                }
                $output .= $result->curtain_specification_name;
                $output .= '</option>';        
            }
            $output .= '<option value="0">-- Remove this --</option>';
            return $output;
        }

        function create_tables() {
            global $wpdb;
            $charset_collate = $wpdb->get_charset_collate();
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
            $sql = "CREATE TABLE `{$wpdb->prefix}curtain_specifications` (
                curtain_specification_id int NOT NULL AUTO_INCREMENT,
                curtain_specification_name varchar(5) UNIQUE,
                specification_description varchar(50),
                specification_price decimal(10,2),
                specification_unit varchar(10),
                curtain_product_id int(10),
                length_only int(1),
                create_timestamp int(10),
                update_timestamp int(10),
                PRIMARY KEY (curtain_specification_id)
            ) $charset_collate;";
            dbDelta($sql);
        }
    }
    $curtain_specifications = new curtain_specifications();
    add_shortcode( 'curtain-specification-list', array( $curtain_specifications, 'list_curtain_specifications' ) );
}