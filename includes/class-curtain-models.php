<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('curtain_models')) {
    class curtain_models {
        private $_wp_page_title;
        private $_wp_page_postid;
        /**
         * Class constructor
         */
        public function __construct() {
            $this->create_tables();
            $this->_wp_page_title = 'Models';
            $this->_wp_page_postid = general_helps::create_page($this->_wp_page_title, 'curtain-model-list');
            add_shortcode( 'curtain-model-list', array( $this, 'list_curtain_models' ) );
            add_action( 'wp_ajax_model_dialog_get_data', array( $this, 'model_dialog_get_data' ) );
            add_action( 'wp_ajax_nopriv_model_dialog_get_data', array( $this, 'model_dialog_get_data' ) );
            add_action( 'wp_ajax_model_dialog_save_data', array( $this, 'model_dialog_save_data' ) );
            add_action( 'wp_ajax_nopriv_model_dialog_save_data', array( $this, 'model_dialog_save_data' ) );
        }

        public function list_curtain_models() {
            global $wpdb;
            $curtain_categories = new curtain_categories();
            /** Check the permission */
            if ( !is_user_logged_in() ) return '<div style="text-align:center;"><h3>You did not login the system. Please login first.</h3></div>';
            $user = wp_get_current_user();
            if ( !$user->has_cap('manage_options') ) return '<div style="text-align:center;"><h3>You did not have the cpability to access this system.<br>Please contact the administrator.</h3></div>';

            /** Post the result */
            if( isset($_POST['_create']) ) {
                $this->insert_curtain_model(
                    array(
                        'curtain_model_name'=>$_POST['_curtain_model_name'],
                        'model_description'=>$_POST['_model_description'],
                        'model_price'=>$_POST['_model_price'],
                        'curtain_category_id'=>$_POST['_curtain_category_id'],
                        'curtain_vendor_name'=>$_POST['_curtain_vendor_name']
                    )
                );
            }
            
            if( isset($_POST['_update']) ) {
                $this->update_curtain_models(
                    array(
                        'curtain_model_name'=>$_POST['_curtain_model_name'],
                        'model_description'=>$_POST['_model_description'],
                        'model_price'=>$_POST['_model_price'],
                        'curtain_category_id'=>$_POST['_curtain_category_id'],
                        'curtain_vendor_name'=>$_POST['_curtain_vendor_name']
                    ),
                    array(
                        'curtain_model_id'=>$_POST['_curtain_model_id'],
                    )
                );
                ?><script>window.location.replace("?_update=");</script><?php
            }

            if( isset($_GET['_delete']) ) {
                $this->delete_curtain_models(
                    array(
                        'curtain_model_id'=>$_GET['_delete']
                    )
                );
            }

            /** List */
            $output  = '<h2>Curtain Models</h2>';
            $output .= '<div style="display: flex; justify-content: space-between; margin: 5px;">';
            $output .= '<div>';
            $output .= '<form method="post">';
            //$output .= '<input class="wp-block-button__link" type="submit" value="Create" name="_add">';
            $output .= '</form>';
            $output .= '</div>';
            $output .= '<div style="text-align: right">';
            $output .= '<form method="post">';
            $output .= '<input style="display:inline" type="text" name="_where" placeholder="Search...">';
            $output .= '<input class="wp-block-button__link" type="submit" value="Search" name="submit_action">';
            $output .= '</form>';
            $output .= '</div>';
            $output .= '</div>';

            $output .= '<div class="ui-widget">';
            $output .= '<table id="models" class="ui-widget ui-widget-content">';
            $output .= '<thead><tr class="ui-widget-header ">';
            $output .= '<th></th>';
            $output .= '<th>model</th>';
            $output .= '<th>description</th>';
            $output .= '<th>price</th>';
            $output .= '<th>vendor</th>';
            $output .= '<th>update_time</th>';
            $output .= '<th></th>';
            $output .= '</tr></thead>';

            if( isset($_GET['_curtain_category_id']) ) {
                $curtain_category_id = $_GET['_curtain_category_id'];
                $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}curtain_models WHERE curtain_category_id={$curtain_category_id}", OBJECT );
            } else {
                $results = general_helps::get_search_results($wpdb->prefix.'curtain_models', $_POST['_where']);
            }
            $output .= '<tbody>';
            foreach ( $results as $index=>$result ) {
                $output .= '<tr>';
                $output .= '<td style="text-align: center;">';
                //$output .= '<span id="btn-edit-'.$result->curtain_model_id.'"><i class="fa-regular fa-pen-to-square"></i></span>';
                $output .= '<span id="btn-model-'.$result->curtain_model_id.'"><i class="fa-regular fa-pen-to-square"></i></span>';
                $output .= '</td>';
                $output .= '<td style="text-align: center;">'.$result->curtain_model_name.'</td>';
                $output .= '<td>'.$result->model_description.'</td>';
                $output .= '<td style="text-align: center;">'.$result->model_price.'</td>';
                $output .= '<td>'.$result->curtain_vendor_name.'</td>';
                $output .= '<td>'.wp_date( get_option('date_format'), $result->update_timestamp ).' '.wp_date( get_option('time_format'), $result->update_timestamp ).'</td>';
                $output .= '<td style="text-align: center;">';
                $output .= '<span id="btn-del-'.$result->curtain_model_id.'"><i class="fa-regular fa-trash-can"></i></span>';
                $output .= '</td>';
                $output .= '</tr>';
            }
            $output .= '<tr><td colspan="7"><div id="btn-model" style="border:solid; margin:3px; text-align:center; border-radius:5px">+</div></td></tr>';
            $output .= '</tbody></table></div>';

            /** Model Dialog */
            $output .= '<div id="model-dialog" title="Model dialog">';
            $output .= '<fieldset>';
            $output .= '<input type="hidden" id="curtain-model-id" />';
            $output .= '<label for="curtain-model-name">Model Name</label>';
            $output .= '<input type="text" id="curtain-model-name" />';
            $output .= '<label for="model-description">Description</label>';
            $output .= '<input type="text" id="model-description" />';
            $output .= '<label for="model-price">Price</label>';
            $output .= '<input type="text" id="model-price" />';
            $output .= '<label for="curtain-category-id">Curtain Category</label>';
            $output .= '<select id="curtain-category-id"></select>';
            $output .= '<label for="curtain-vendor-name">Curtain Vendor</label>';
            $output .= '<input type="text" id="curtain-vendor-name" />';
            $output .= '</fieldset>';
            $output .= '</div>';

            if( isset($_GET['_edit']) ) {
                $_id = $_GET['_edit'];
                $row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}curtain_models WHERE curtain_model_id={$_id}", OBJECT );
                $output .= '<div id="dialog" title="Curtain model update">';
                $output .= '<form method="post">';
                $output .= '<fieldset>';
                $output .= '<input type="hidden" value="'.$row->curtain_model_id.'" name="_curtain_model_id">';
                $output .= '<label for="curtain-model-name">Model Name</label>';
                $output .= '<input type="text" name="_curtain_model_name" value="'.$row->curtain_model_name.'" id="curtain-model-name" class="text ui-widget-content ui-corner-all">';
                $output .= '<label for="model-description">Description</label>';
                $output .= '<input type="text" name="_model_description" value="'.$row->model_description.'" id="model-description" class="text ui-widget-content ui-corner-all">';
                $output .= '<label for="model-price">Price</label>';
                $output .= '<input type="text" name="_model_price" value="'.$row->model_price.'" id="model-price" class="text ui-widget-content ui-corner-all">';
                $output .= '<label for="curtain-category-id">Curtain Category</label>';
                $output .= '<select name="_curtain_category_id" id="curtain-category-id">'.$curtain_categories->select_options($row->curtain_category_id).'</select>';
                $output .= '<label for="curtain-vendor-name">Curtain Vendor</label>';
                $output .= '<input type="text" name="_curtain_vendor_name" value="'.$row->curtain_vendor_name.'" id="curtain-vendor-name" class="text ui-widget-content ui-corner-all">';
                $output .= '</fieldset>';
                $output .= '<input class="wp-block-button__link" type="submit" value="Update" name="_update">';
                $output .= '</form>';
                $output .= '</div>';
            }

            if( isset($_POST['_add']) ) {
                $output .= '<div id="dialog" title="Create new model">';
                $output .= '<form method="post">';
                $output .= '<fieldset>';
                $output .= '<label for="curtain-model-name">Model Name</label>';
                $output .= '<input type="text" name="_curtain_model_name" id="curtain-model-name" class="text ui-widget-content ui-corner-all">';
                $output .= '<label for="model-description">Description</label>';
                $output .= '<input type="text" name="_model_description" id="model-description" class="text ui-widget-content ui-corner-all">';
                $output .= '<label for="model-price">Price</label>';
                $output .= '<input type="text" name="_model_price" id="model-price" class="text ui-widget-content ui-corner-all">';
                $output .= '<label for="curtain-category-id">Curtain Category</label>';
                $output .= '<select name="_curtain_category_id" id="curtain-category-id">'.$curtain_categories->select_options().'</select>';
                $output .= '<label for="curtain-vendor-name">Curtain Vendor</label>';
                $output .= '<input type="text" name="_curtain_vendor_name" id="curtain-vendor-name" class="text ui-widget-content ui-corner-all">';
                $output .= '</fieldset>';
                $output .= '<input class="wp-block-button__link" type="submit" value="Create" name="_create">';
                $output .= '</form>';
                $output .= '</div>';
            }
            return $output;
        }

        function model_dialog_get_data() {
            global $wpdb;
            $curtain_categories = new curtain_categories();
            $_id = $_POST['_id'];
            $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}curtain_models WHERE curtain_model_id = %d", $_id ), OBJECT );
            $response = array();
            $response["curtain_model_name"] = $row->curtain_model_name;
            $response["model_description"] = $row->model_description;
            $response["model_price"] = $row->model_price;
            $response["curtain_category_id"] = $curtain_categories->select_options($row->curtain_category_id);
            $response["curtain_vendor_name"] = $row->curtain_vendor_name;
            echo json_encode( $response );
            wp_die();
        }

        function model_dialog_save_data() {
            if( $_POST['_curtain_model_id']=='' ) {
                $this->insert_curtain_model(
                    array(
                        'curtain_model_name'=>$_POST['_curtain_model_name'],
                        'model_description'=>$_POST['_model_description'],
                        'model_price'=>$_POST['_model_price'],
                        'curtain_category_id'=>$_POST['_curtain_category_id'],
                        'curtain_vendor_name'=>$_POST['_curtain_vendor_name']
                    )
                );
            } else {
                $this->update_curtain_models(
                    array(
                        'curtain_model_name'=>$_POST['_curtain_model_name'],
                        'model_description'=>$_POST['_model_description'],
                        'model_price'=>$_POST['_model_price'],
                        'curtain_category_id'=>$_POST['_curtain_category_id'],
                        'curtain_vendor_name'=>$_POST['_curtain_vendor_name']
                    ),
                    array(
                        'curtain_model_id'=>$_POST['_curtain_model_id']
                    )
                );
            }

            $response = array();
            echo json_encode( $response );
            wp_die();
        }

        public function insert_curtain_model($data=[]) {
            global $wpdb;
            $table = $wpdb->prefix.'curtain_models';
            $data['create_timestamp'] = time();
            $data['update_timestamp'] = time();
            $wpdb->insert($table, $data);        
            return $wpdb->insert_id;
        }

        public function update_curtain_models($data=[], $where=[]) {
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

        public function get_description( $_id=0 ) {
            global $wpdb;
            $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}curtain_models WHERE curtain_model_id = %d", $_id ), OBJECT );
            return $row->model_description.'('.$row->curtain_model_name.')';
        }

        public function get_price( $_id=0 ) {
            global $wpdb;
            $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}curtain_models WHERE curtain_model_id = %d", $_id ), OBJECT );
            return $row->model_price;
        }

        public function select_options( $_category_id=0, $_id=0 ) {
            global $wpdb;
            $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}curtain_models WHERE curtain_category_id={$_category_id}", OBJECT );
            $output = '<option value="0">-- Select an option --</option>';
            foreach ($results as $index => $result) {
                if ( $result->curtain_model_id == $_id ) {
                    $output .= '<option value="'.$result->curtain_model_id.'" selected>';
                } else {
                    $output .= '<option value="'.$result->curtain_model_id.'">';
                }
                $output .= $result->curtain_model_name.'('.$result->model_description.')';
                $output .= '</option>';        
            }
            $output .= '<option value="0">-- Remove this --</option>';
            return $output;
        }

        public function create_tables() {
            global $wpdb;
            $charset_collate = $wpdb->get_charset_collate();
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
            $sql = "CREATE TABLE `{$wpdb->prefix}curtain_models` (
                curtain_model_id int NOT NULL AUTO_INCREMENT,
                curtain_model_name varchar(5) UNIQUE,
                model_description varchar(50),
                model_price decimal(10,2),
                curtain_vendor_name varchar(50),
                curtain_category_id int(10),
                create_timestamp int(10),
                update_timestamp int(10),
                PRIMARY KEY (curtain_model_id)
            ) $charset_collate;";
            dbDelta($sql);
        }
    }
    $my_class = new curtain_models();
}