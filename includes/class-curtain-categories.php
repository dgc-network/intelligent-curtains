<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('curtain_categories')) {
    class curtain_categories {
        private $_wp_page_title;
        private $_wp_page_postid;
        /**
         * Class constructor
         */
        public function __construct() {
            $this->create_tables();
            $this->_wp_page_title = 'Categories';
            $this->_wp_page_postid = general_helps::create_page($this->_wp_page_title, 'curtain-category-list');
            add_shortcode( 'curtain-category-list', array( $this, 'list_curtain_categories' ) );
            add_action( 'wp_ajax_category_dialog_get_data', array( $this, 'category_dialog_get_data' ) );
            add_action( 'wp_ajax_nopriv_category_dialog_get_data', array( $this, 'category_dialog_get_data' ) );
            add_action( 'wp_ajax_category_dialog_save_data', array( $this, 'category_dialog_save_data' ) );
            add_action( 'wp_ajax_nopriv_category_dialog_save_data', array( $this, 'category_dialog_save_data' ) );
        }

        public function list_curtain_categories() {
            global $wpdb;
            /** Check the permission */
            if ( !is_user_logged_in() ) return '<div style="text-align:center;"><h3>You did not login the system. Please login first.</h3></div>';
            $user = wp_get_current_user();
            if ( !$user->has_cap('manage_options') ) return '<div style="text-align:center;"><h3>You did not have the cpability to access this system.<br>Please contact the administrator.</h3></div>';

            /** Post the result */
            if( isset($_POST['_create']) ) {
                $this->insert_curtain_category(
                    array(
                        'curtain_category_name'=>$_POST['_curtain_category_name'],
                        'min_width'=>$_POST['_min_width'],
                        'max_width'=>$_POST['_max_width'],
                        'min_height'=>$_POST['_min_height'],
                        'max_height'=>$_POST['_max_height'],
                    )
                );
            }
            
            if( isset($_POST['_update']) ) {
                $this->update_curtain_categories(
                    array(
                        'min_width'=>$_POST['_min_width'],
                        'max_width'=>$_POST['_max_width'],
                        'min_height'=>$_POST['_min_height'],
                        'max_height'=>$_POST['_max_height'],
                        'curtain_category_name'=>$_POST['_curtain_category_name']
                    ),
                    array(
                        'curtain_category_id'=>$_POST['_curtain_category_id']
                    )
                );
                ?><script>window.location.replace("?_update=");</script><?php
            }

            if( isset($_GET['_delete']) ) {
                $this->delete_curtain_categories(
                    array(
                        'curtain_category_id'=>$_GET['_delete']
                    )
                );
            }

            /** List */
            $output  = '<h2>Curtain Categories</h2>';
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
            $output .= '<table id="categories" class="ui-widget ui-widget-content">';
            $output .= '<thead><tr class="ui-widget-header ">';
            $output .= '<th></th>';
            $output .= '<th>category</th>';
            $output .= '<th>remote</th>';
            $output .= '<th>spec</th>';
            $output .= '<th>width</th>';
            $output .= '<th>height</th>';
            $output .= '<th></th>';
            $output .= '</tr></thead>';

            $output .= '<tbody>';
            $results = general_helps::get_search_results($wpdb->prefix.'curtain_categories', $_POST['_where']);
            foreach ( $results as $index=>$result ) {
                $output .= '<tr>';
                $output .= '<td style="text-align: center;">';
                //$output .= '<span id="btn-edit-'.$result->curtain_category_id.'"><i class="fa-regular fa-pen-to-square"></i></span>';
                $output .= '<span id="btn-category-'.$result->curtain_category_id.'"><i class="fa-regular fa-pen-to-square"></i></span>';
                $output .= '</td>';
                $models_page_url = '/models/?_curtain_category_id='.$result->curtain_category_id;
                $output .= '<td><a href="'.$models_page_url.'">'.$result->curtain_category_name.'</a></td>';

                if ($result->hide_remote==1) {
                    $output .= '<td style="text-align: center;">N/A</td>';
                } else {
                    $remotes_page_url = '/remotes/?_curtain_category_id='.$result->curtain_category_id;
                    $output .= '<td style="text-align: center;"><a href="'.$remotes_page_url.'">remote</a></td>';
                }

                if ($result->hide_specification==1) {
                    $output .= '<td style="text-align: center;">N/A</td>';
                } else {
                    $specs_page_url = '/specifications/?_curtain_category_id='.$result->curtain_category_id;
                    $output .= '<td style="text-align: center;"><a href="'.$specs_page_url.'">spec</a></td>';
                }

                if ($result->hide_width==1) {
                    $output .= '<td style="text-align: center;">N/A</td>';
                } else {
                    $output .= '<td style="text-align: center;">'.$result->min_width.'cm ~ '.$result->max_width.'cm</td>';
                }

                if ($result->hide_height==1) {
                    $output .= '<td style="text-align: center;">N/A</td>';
                } else {
                    $output .= '<td style="text-align: center;">'.$result->min_height.'cm ~ '.$result->max_height.'cm</td>';
                }
                $output .= '<td style="text-align: center;">';
                $output .= '<span id="btn-del-'.$result->curtain_category_id.'"><i class="fa-regular fa-trash-can"></i></span>';
                $output .= '</td>';
                $output .= '</tr>';
            }
            $output .= '<tr><td colspan="7"><div id="btn-category" style="border:solid; margin:3px; text-align:center; border-radius:5px">+</div></td></tr>';
            $output .= '</tbody></table></div>';

            /** Category Dialog */
            $output .= '<div id="category-dialog" title="Category dialog">';
            $output .= '<fieldset>';
            $output .= '<input type="hidden" id="curtain-category-id" />';
            $output .= '<label for="curtain-category-name">Category Name</label>';
            $output .= '<input type="text" id="curtain-category-name" />';
            $output .= '<input type="checkbox" id="hide-remote" style="display:inline-block; width:5%; " /> Hide the Remote.<br>';
            $output .= '<input type="checkbox" id="hide-specification" style="display:inline-block; width:5%; " /> Hide the Specification.<br>';
            $output .= '<div>';
            $output .= '<input type="checkbox" id="hide-width" style="display:inline-block; width:5%; " /> Hide the Width.';
            $output .= '<div id="show-width">';
            $output .= '<input type="text" id="min-width" style="display:inline-block; width:25%;" />';
            $output .= ' cm ~ ';
            $output .= '<input type="text" id="max-width" style="display:inline-block; width:25%;" />';
            $output .= ' cm';
            $output .= '</div>';
            $output .= '</div>';
            $output .= '<div>';
            $output .= '<input type="checkbox" id="hide-height" style="display:inline-block; width:5%; " /> Hide the Height.';
            $output .= '<div id="show-height">';
            $output .= '<input type="text" id="min-height" style="display:inline-block; width:25%;" />';
            $output .= ' cm ~ ';
            $output .= '<input type="text" id="max-height" style="display:inline-block; width:25%;" />';
            $output .= ' cm';
            $output .= '</div>';
            $output .= '</div>';
            $output .= '<input type="checkbox" id="allow-parts" style="display:inline-block; width:5%; " /> Allow to support as the parts in sub-item.<br>';
            $output .= '</fieldset>';
            $output .= '</div>';

            if( isset($_GET['_edit']) ) {
                $_id = $_GET['_edit'];
                $row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}curtain_categories WHERE curtain_category_id={$_id}", OBJECT );
                $output .= '<div id="dialog" title="Category update">';
                $output .= '<form method="post">';
                $output .= '<fieldset>';
                $output .= '<input type="hidden" value="'.$row->curtain_category_id.'" name="_curtain_category_id">';
                $output .= '<label for="curtain-category-name">Category Name</label>';
                $output .= '<input type="text" name="_curtain_category_name" value="'.$row->curtain_category_name.'" id="curtain-category-name" class="text ui-widget-content ui-corner-all">';
                $output .= '<label for="min-width">Width Min.(cm)</label>';
                $output .= '<input type="text" name="_min_width" value="'.$row->min_width.'" id="min-width" class="text ui-widget-content ui-corner-all">';
                $output .= '<label for="max-width">Width Max.(cm)</label>';
                $output .= '<input type="text" name="_max_width" value="'.$row->max_width.'" id="max-width" class="text ui-widget-content ui-corner-all">';
                $output .= '<label for="min-height">Height Min.(cm)</label>';
                $output .= '<input type="text" name="_min_height" value="'.$row->min_height.'" id="min-height" class="text ui-widget-content ui-corner-all">';
                $output .= '<label for="max-height">Height Max.(cm)</label>';
                $output .= '<input type="text" name="_max_height" value="'.$row->max_height.'" id="max-height" class="text ui-widget-content ui-corner-all">';
                $output .= '</fieldset>';
                $output .= '<input class="wp-block-button__link" type="submit" value="Update" name="_update">';
                $output .= '</form>';
                $output .= '</div>';
            }

            if( isset($_POST['_add']) ) {
                $output .= '<div id="dialog" title="Create new category">';
                $output .= '<form method="post">';
                $output .= '<fieldset>';
                $output .= '<label for="curtain-category-name">Category Name</label>';
                $output .= '<input type="text" name="_curtain_category_name" id="curtain-category-name" class="text ui-widget-content ui-corner-all">';
                $output .= '<label for="min-width">Width Min.(cm)</label>';
                $output .= '<input type="text" name="_min_width" id="min-width" class="text ui-widget-content ui-corner-all">';
                $output .= '<label for="max-width">Width Max.(cm)</label>';
                $output .= '<input type="text" name="_max_width" id="max-width" class="text ui-widget-content ui-corner-all">';
                $output .= '<label for="min-height">Height Min.(cm)</label>';
                $output .= '<input type="text" name="_min_height" id="min-height" class="text ui-widget-content ui-corner-all">';
                $output .= '<label for="max-height">Height Max.(cm)</label>';
                $output .= '<input type="text" name="_max_height" id="max-height" class="text ui-widget-content ui-corner-all">';
                $output .= '</fieldset>';
                $output .= '<input class="wp-block-button__link" type="submit" value="Create" name="_create">';
                $output .= '</form>';
                $output .= '</div>';
            }
            
            return $output;
        }

        function category_dialog_get_data() {
            global $wpdb;
            $_id = $_POST['_id'];
            $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}curtain_categories WHERE curtain_category_id = %d", $_id ), OBJECT );
            $response = array();
            $response["curtain_category_name"] = $row->curtain_category_name;
            $response["allow_parts"] = $row->allow_parts;
            $response["hide_remote"] = $row->hide_remote;
            $response["hide_specification"] = $row->hide_specification;
            $response["hide_width"] = $row->hide_width;
            $response["min_width"] = $row->min_width;
            $response["max_width"] = $row->max_width;
            $response["hide_height"] = $row->hide_height;
            $response["min_height"] = $row->min_height;
            $response["max_height"] = $row->max_height;
            echo json_encode( $response );
            wp_die();
        }

        function category_dialog_save_data() {
            if( $_POST['_curtain_category_id']=='' ) {
                $this->insert_curtain_category(
                    array(
                        'curtain_category_name'=>$_POST['_curtain_category_name'],
                        'allow_parts'=>$_POST['_allow_parts'],
                        'hide_remote'=>$_POST['_hide_remote'],
                        'hide_specification'=>$_POST['_hide_specification'],
                        'hide_width'=>$_POST['_hide_width'],
                        'min_width'=>$_POST['_min_width'],
                        'max_width'=>$_POST['_max_width'],
                        'hide_height'=>$_POST['_hide_height'],
                        'min_height'=>$_POST['_min_height'],
                        'max_height'=>$_POST['_max_height'],
                    )
                );
            } else {
                $this->update_curtain_categories(
                    array(
                        'curtain_category_name'=>$_POST['_curtain_category_name'],
                        'allow_parts'=>$_POST['_allow_parts'],
                        'hide_remote'=>$_POST['_hide_remote'],
                        'hide_specification'=>$_POST['_hide_specification'],
                        'hide_width'=>$_POST['_hide_width'],
                        'min_width'=>$_POST['_min_width'],
                        'max_width'=>$_POST['_max_width'],
                        'hide_height'=>$_POST['_hide_height'],
                        'min_height'=>$_POST['_min_height'],
                        'max_height'=>$_POST['_max_height'],
                    ),
                    array(
                        'curtain_category_id'=>$_POST['_curtain_category_id']
                    )
                );
            }
            $response = array();
            echo json_encode( $response );
            wp_die();
        }

        public function insert_curtain_category($data=[]) {
            global $wpdb;
            $table = $wpdb->prefix.'curtain_categories';
            $data['create_timestamp'] = time();
            $data['update_timestamp'] = time();
            $wpdb->insert($table, $data);        
            return $wpdb->insert_id;
        }

        public function update_curtain_categories($data=[], $where=[]) {
            global $wpdb;
            $table = $wpdb->prefix.'curtain_categories';
            $data['update_timestamp'] = time();
            $wpdb->update($table, $data, $where);
        }

        public function delete_curtain_categories($where=[]) {
            global $wpdb;
            $table = $wpdb->prefix.'curtain_categories';
            $wpdb->delete($table, $where);
        }

        public function get_name( $_id=0 ) {
            global $wpdb;
            $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}curtain_categories WHERE curtain_category_id = %d", $_id ), OBJECT );
            return $row->curtain_category_name;
        }

        public function is_parts_allowed( $_id=0 ) {
            global $wpdb;
            $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}curtain_categories WHERE curtain_category_id = %d", $_id ), OBJECT );
            if ($row->allow_parts==1) {
                return true;
            } else {
                return false;
            }
        }

        public function is_remote_hided( $_id=0 ) {
            global $wpdb;
            $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}curtain_categories WHERE curtain_category_id = %d", $_id ), OBJECT );
            if ($row->hide_remote==1) {
                return true;
            } else {
                return false;
            }
        }

        public function is_specification_hided( $_id=0 ) {
            global $wpdb;
            $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}curtain_categories WHERE curtain_category_id = %d", $_id ), OBJECT );
            if ($row->hide_specification==1) {
                return true;
            } else {
                return false;
            }
        }

        public function is_width_hided( $_id=0 ) {
            global $wpdb;
            $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}curtain_categories WHERE curtain_category_id = %d", $_id ), OBJECT );
            if ($row->hide_width==1) {
                return true;
            } else {
                return false;
            }
        }

        public function is_height_hided( $_id=0 ) {
            global $wpdb;
            $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}curtain_categories WHERE curtain_category_id = %d", $_id ), OBJECT );
            if ($row->hide_height==1) {
                return true;
            } else {
                return false;
            }
        }

        public function get_min_width( $_id=0 ) {
            global $wpdb;
            $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}curtain_categories WHERE curtain_category_id = %d", $_id ), OBJECT );
            return $row->min_width;
        }

        public function get_max_width( $_id=0 ) {
            global $wpdb;
            $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}curtain_categories WHERE curtain_category_id = %d", $_id ), OBJECT );
            return $row->max_width;
        }

        public function get_min_height( $_id=0 ) {
            global $wpdb;
            $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}curtain_categories WHERE curtain_category_id = %d", $_id ), OBJECT );
            return $row->min_height;
        }

        public function get_max_height( $_id=0 ) {
            global $wpdb;
            $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}curtain_categories WHERE curtain_category_id = %d", $_id ), OBJECT );
            return $row->max_height;
        }

        public function select_options( $_id=0 ) {
            global $wpdb;
            $output = '<option value="0">-- Select an option --</option>';
            $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}curtain_categories", OBJECT );
            foreach ($results as $index => $result) {
                if ( $result->curtain_category_id == $_id ) {
                    $output .= '<option value="'.$result->curtain_category_id.'" selected>';
                } else {
                    $output .= '<option value="'.$result->curtain_category_id.'">';
                }
                $output .= $result->curtain_category_name;
                $output .= '</option>';        
            }
            $output .= '<option value="0">-- Remove this --</option>';
            return $output;
        }

        public function parts_options( $_id=0 ) {
            global $wpdb;
            $output = '<option value="0">-- Select an option --</option>';
            $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}curtain_categories WHERE allow_parts=1", OBJECT );
            foreach ($results as $index => $result) {
                $parts = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}curtain_models WHERE curtain_category_id={$result->curtain_category_id}", OBJECT );
                foreach ($parts as $sub_index => $sub_parts) {
                    if ( $sub_parts->curtain_model_id == $_id ) {
                        $output .= '<option value="'.$result->curtain_model_id.'" selected>';
                    } else {
                        $output .= '<option value="'.$result->curtain_model_id.'">';
                    }
                    $output .= $result->curtain_model_name;
                    $output .= '</option>';        
                }
            }
            $output .= '<option value="0">-- Remove this --</option>';
            return $output;
        }

        public function create_tables() {
            global $wpdb;
            $charset_collate = $wpdb->get_charset_collate();
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
            $sql = "CREATE TABLE `{$wpdb->prefix}curtain_categories` (
                curtain_category_id int NOT NULL AUTO_INCREMENT,
                curtain_category_name varchar(50),
                allow_parts tinyint,
                hide_remote tinyint,
                hide_specification tinyint,
                hide_width tinyint,
                min_width int,
                max_width int,
                hide_height tinyint,
                min_height int,
                max_height int,
                create_timestamp int(10),
                update_timestamp int(10),
                PRIMARY KEY (curtain_category_id)
            ) $charset_collate;";
            dbDelta($sql);
        }
    }
    $my_class = new curtain_categories();
}