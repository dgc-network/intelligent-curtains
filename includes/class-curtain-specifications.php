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
            $this->create_tables();
        }

        public function list_curtain_specifications() {
            global $wpdb;
            $curtain_service = new curtain_service();
            $curtain_categories = new curtain_categories();

            if( isset($_SESSION['line_user_id']) ) {
                $_option_page = 'Specifications';
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
                $data['curtain_specification_name']=$_POST['_curtain_specification_name'];
                $data['specification_description']=$_POST['_specification_description'];
                $data['specification_price']=$_POST['_specification_price'];
                $data['specification_unit']=$_POST['_specification_unit'];
                $data['curtain_category_id']=$_POST['_curtain_category_id'];
                $data['length_only']=$_POST['_length_only'];
                $this->insert_curtain_specification($data);
            }
            
            if( isset($_POST['_update']) ) {
                $data=array();
                $data['curtain_specification_name']=$_POST['_curtain_specification_name'];
                $data['specification_description']=$_POST['_specification_description'];
                $data['specification_price']=$_POST['_specification_price'];
                $data['specification_unit']=$_POST['_specification_unit'];
                $data['curtain_category_id']=$_POST['_curtain_category_id'];
                $data['length_only']=$_POST['_length_only'];
                $where=array();
                $where['curtain_specification_id']=$_POST['_curtain_specification_id'];
                $this->update_curtain_specifications($data, $where);
                ?><script>window.location.replace("?_update=");</script><?php
            }

            if( isset($_GET['_delete']) ) {
                $where=array();
                $where['curtain_specification_id']=$_GET['_delete'];
                $this->delete_curtain_specifications($where);
            }

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
            $output .= '<th>category</th>';
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
                $row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}curtain_categories WHERE curtain_category_id={$result->curtain_category_id}", OBJECT );
                $output .= '<td>'.$row->curtain_category_name.'</td>';
                $output .= '<td style="text-align: center;">'.$result->specification_unit.'</td>';
                $output .= '<td style="text-align: center;">'.$result->specification_price.'</td>';
                $output .= '<td>'.wp_date( get_option('date_format'), $result->update_timestamp ).' '.wp_date( get_option('time_format'), $result->update_timestamp ).'</td>';
                $output .= '<td style="text-align: center;">';
                $output .= '<span id="del-btn-'.$result->curtain_specification_id.'"><i class="fa-regular fa-trash-can"></i></span>';
                $output .= '</td>';
                $output .= '</tr>';
            }
            $output .= '</tbody></table></div>';

            if( isset($_GET['_edit']) ) {
                $_id = $_GET['_edit'];
                $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}curtain_specifications WHERE curtain_specification_id = %d", $_id ), OBJECT );
                $output .= '<div id="dialog" title="Curtain specification update">';
                $output .= '<form method="post">';
                $output .= '<fieldset>';
                $output .= '<input type="hidden" value="'.$row->curtain_specification_id.'" name="_curtain_specification_id">';
                $output .= '<label for="curtain-specification-name">Specification</label>';
                $output .= '<input type="text" name="_curtain_specification_name" value="'.$row->curtain_specification_name.'" id="curtain-specification-name" class="text ui-widget-content ui-corner-all">';
                $output .= '<label for="specification-description">Description</label>';
                $output .= '<input type="text" name="_specification_description" value="'.$row->specification_description.'" id="specification-description" class="text ui-widget-content ui-corner-all">';
                $output .= '<label for="specification-price">Price</label>';
                $output .= '<input type="text" name="_specification_price" value="'.$row->specification_price.'" id="specification-price" class="text ui-widget-content ui-corner-all">';
                $output .= '<label for="specification-unit">Unit</label>';
                $output .= '<input type="text" name="_specification_unit" value="'.$row->specification_unit.'" id="specification-unit" class="text ui-widget-content ui-corner-all">';
                $output .= '<label for="curtain_category_id">Curtain Category</label>';
                $output .= '<select name="_curtain_category_id" id="curtain_category_id">'.$curtain_categories->select_options($row->curtain_category_id).'</select>';
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
                $output .= '<label for="curtain_category_id">Curtain Category</label>';
                $output .= '<select name="_curtain_category_id" id="curtain_category_id">'.$curtain_categories->select_options().'</select>';
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

        public function insert_curtain_specification($data=[]) {
            global $wpdb;
            $table = $wpdb->prefix.'curtain_specifications';
            $data['create_timestamp'] = time();
            $data['update_timestamp'] = time();
            $wpdb->insert($table, $data);        
            return $wpdb->insert_id;
        }

        public function update_curtain_specifications($data=[], $where=[]) {
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

        public function select_options( $_id=0, $_category_id=0 ) {
            global $wpdb;
            $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}curtain_specifications WHERE curtain_category_id={$_category_id}", OBJECT );
            $output = '<option value="0">-- Select an option --</option>';
            foreach ($results as $index => $result) {
                if ( $result->curtain_specification_id == $_id ) {
                    $output .= '<option value="'.$result->curtain_specification_id.'" selected>';
                } else {
                    $output .= '<option value="'.$result->curtain_specification_id.'">';
                }
                $output .= $result->curtain_specification_name.'('.$result->specification_description.')';
                $output .= '</option>';        
            }
            $output .= '<option value="0">-- Remove this --</option>';
            return $output;
        }

        public function create_tables() {
            global $wpdb;
            $charset_collate = $wpdb->get_charset_collate();
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
            $sql = "CREATE TABLE `{$wpdb->prefix}curtain_specifications` (
                curtain_specification_id int NOT NULL AUTO_INCREMENT,
                curtain_specification_name varchar(5),
                specification_description varchar(50),
                specification_price decimal(10,2),
                specification_unit varchar(10),
                curtain_category_id int(10),
                length_only int(1),
                create_timestamp int(10),
                update_timestamp int(10),
                PRIMARY KEY (curtain_specification_id)
            ) $charset_collate;";
            dbDelta($sql);
        }
    }
    $my_class = new curtain_specifications();
    add_shortcode( 'curtain-specification-list', array( $my_class, 'list_curtain_specifications' ) );
}