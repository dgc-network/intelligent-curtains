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
            $this->_wp_page_title = 'Categories';
            $this->_wp_page_postid = general_helps::create_page($this->_wp_page_title, 'curtain-category-list');
            add_shortcode( 'curtain-category-list', array( $this, 'list_curtain_categories' ) );
            $this->create_tables();
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
                        'min_length'=>$_POST['_min_length'],
                        'max_length'=>$_POST['_max_length'],
                    )
                );
            }
            
            if( isset($_POST['_update']) ) {
                $this->update_curtain_categories(
                    array(
                        'min_width'=>$_POST['_min_width'],
                        'max_width'=>$_POST['_max_width'],
                        'min_length'=>$_POST['_min_length'],
                        'max_length'=>$_POST['_max_length'],
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
            $output .= '<input class="wp-block-button__link" type="submit" value="Create" name="_add">';
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
            $output .= '<th>min.</th>';
            $output .= '<th>max.</th>';
            $output .= '<th>update_time</th>';
            $output .= '<th></th>';
            $output .= '</tr></thead>';

            $output .= '<tbody>';
            $results = general_helps::get_search_results($wpdb->prefix.'curtain_categories', $_POST['_where']);
            foreach ( $results as $index=>$result ) {
                $output .= '<tr>';
                $output .= '<td style="text-align: center;">';
                $output .= '<span id="btn-edit-'.$result->curtain_category_id.'"><i class="fa-regular fa-pen-to-square"></i></span>';
                $output .= '</td>';
                $output .= '<td>'.$result->curtain_category_name.'</td>';
                $output .= '<td>'.$result->min_width.'</td>';
                $output .= '<td>'.$result->max_width.'</td>';
                $output .= '<td>'.wp_date( get_option('date_format'), $result->update_timestamp ).' '.wp_date( get_option('time_format'), $result->update_timestamp ).'</td>';
                $output .= '<td style="text-align: center;">';
                $output .= '<span id="btn-del-'.$result->curtain_category_id.'"><i class="fa-regular fa-trash-can"></i></span>';
                $output .= '</td>';
                $output .= '</tr>';
            }
            $output .= '</tbody></table></div>';

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
                $output .= '<label for="min-length">Length Min.(cm)</label>';
                $output .= '<input type="text" name="_min_length" value="'.$row->min_length.'" id="min-length" class="text ui-widget-content ui-corner-all">';
                $output .= '<label for="max-length">Length Max.(cm)</label>';
                $output .= '<input type="text" name="_max_length" value="'.$row->max_length.'" id="max-length" class="text ui-widget-content ui-corner-all">';
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
                $output .= '<label for="min-length">Length Min.(cm)</label>';
                $output .= '<input type="text" name="_min_length" id="min-length" class="text ui-widget-content ui-corner-all">';
                $output .= '<label for="max-length">Length Max.(cm)</label>';
                $output .= '<input type="text" name="_max_length" id="max-length" class="text ui-widget-content ui-corner-all">';
                $output .= '</fieldset>';
                $output .= '<input class="wp-block-button__link" type="submit" value="Create" name="_create">';
                $output .= '</form>';
                $output .= '</div>';
            }
            return $output;
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

        public function create_tables() {
            global $wpdb;
            $charset_collate = $wpdb->get_charset_collate();
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
            $sql = "CREATE TABLE `{$wpdb->prefix}curtain_categories` (
                curtain_category_id int NOT NULL AUTO_INCREMENT,
                curtain_category_name varchar(50),
                min_width int,
                max_width int,
                min_length int,
                max_length int,
                create_timestamp int(10),
                update_timestamp int(10),
                PRIMARY KEY (curtain_category_id)
            ) $charset_collate;";
            dbDelta($sql);
        }
    }
    $my_class = new curtain_categories();
}