<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('system_status')) {
    class system_status {
        private $_wp_page_title;
        private $_wp_page_postid;
        /**
         * Class constructor
         */
        public function __construct() {
            $this->_wp_page_title = 'Status';
            $this->_wp_page_postid = get_page_by_title($this->_wp_page_title)->ID;
            $wp_pages = new wp_pages();
            $wp_pages->create_page($this->_wp_page_title, 'system-status-list', 'system');            
            add_shortcode( 'system-status-list', array( $this, 'list_system_status' ) );
            $this->create_tables();
            $this->init_system_status();
        }

        public function init_system_status() {
            $this->insert_system_status(
                array(
                    'system_status_code'    => 'order01',
                    'system_status_title'   => 'Completed the checkout but did not purchase yet',
                    'system_status_category'=> 'order'
                )
            );
            $this->insert_system_status(
                array(
                    'system_status_code'    => 'order02',
                    'system_status_title'   => 'Completed the procurement but did not ship yet',
                    'system_status_category'=> 'order'
                )
            );
            $this->insert_system_status(
                array(
                    'system_status_code'    => 'order03',
                    'system_status_title'   => 'Completed the shipment but did not pay yet',
                    'system_status_category'=> 'order'
                )
            );
            $this->insert_system_status(
                array(
                    'system_status_code'    => 'order04',
                    'system_status_title'   => 'Completed the payment',
                    'system_status_category'=> 'order'
                )
            );
        }

        public function list_system_status() {
            global $wpdb;
            $wp_pages = new wp_pages();

            if( isset($_SESSION['line_user_id']) ) {
                $permission = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}user_permissions WHERE line_user_id = %s AND wp_page_postid= %d", $_SESSION['line_user_id'], $this->_wp_page_postid ), OBJECT );            
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
                $this->insert_system_status(
                    array(
                        'system_status_code'    => $_POST['_system_status_code'],
                        'system_status_title'   => $_POST['_system_status_title'],
                        'system_status_category'=> $_POST['_system_status_category']
                    )
                );
            }
            
            if( isset($_POST['_update']) ) {
                $this->update_system_status(
                    array(
                        'system_status_code'    => $_POST['_system_status_code'],
                        'system_status_title'   => $_POST['_system_status_title'],
                        'system_status_category'=> $_POST['_system_status_category']
                    ),
                    array(
                        'system_status_id'  => $_POST['_system_status_id']
                    )
                );
                ?><script>window.location.replace("?_update=");</script><?php
            }

            if( isset($_GET['_delete']) ) {
                $this->delete_system_status(
                    array(
                        'system_status_id'  => $_GET['_delete']
                    )
                );
            }

            /** List */
            $output  = '<h2>System Status</h2>';
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
            $output .= '<th>code</th>';
            $output .= '<th>ststus</th>';
            $output .= '<th>category</th>';
            $output .= '<th>update_time</th>';
            $output .= '<th></th>';
            $output .= '</tr></thead>';

            $output .= '<tbody>';
            $results = $wp_pages->get_search_results($wpdb->prefix.'system_status', $_POST['_where']);
            foreach ( $results as $index=>$result ) {
                $output .= '<tr>';
                $output .= '<td style="text-align: center;">';
                $output .= '<span id="btn-edit-'.$result->system_status_id.'"><i class="fa-regular fa-pen-to-square"></i></span>';
                $output .= '</td>';
                $output .= '<td>'.$result->system_status_code.'</td>';
                $output .= '<td>'.$result->system_status_title.'</td>';
                $output .= '<td>'.$result->system_status_category.'</td>';
                $output .= '<td>'.wp_date( get_option('date_format'), $result->update_timestamp ).' '.wp_date( get_option('time_format'), $result->update_timestamp ).'</td>';
                $output .= '<td style="text-align: center;">';
                $output .= '<span id="btn-del-'.$result->system_status_id.'"><i class="fa-regular fa-trash-can"></i></span>';
                $output .= '</td>';
                $output .= '</tr>';
            }
            $output .= '</tbody></table></div>';

            if( isset($_GET['_edit']) ) {
                $_id = $_GET['_edit'];
                $row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}system_status WHERE system_status_id={$_id}", OBJECT );
                $output .= '<div id="dialog" title="system status update">';
                $output .= '<form method="post">';
                $output .= '<fieldset>';
                $output .= '<input type="hidden" value="'.$row->system_status_id.'" name="_system_status_id">';
                $output .= '<label for="system-status-code">Code</label>';
                $output .= '<input type="text" name="_system_status_code" value="'.$row->system_status_code.'" id="system-status-code" class="text ui-widget-content ui-corner-all">';
                $output .= '<label for="system-status-title">Title</label>';
                $output .= '<input type="text" name="_system_status_title" value="'.$row->system_status_title.'" id="system-status-title" class="text ui-widget-content ui-corner-all">';
                $output .= '<label for="system-status-category">Category</label>';
                $output .= '<input type="text" name="_system_status_category" value="'.$row->system_status_category.'" id="system-status-category" class="text ui-widget-content ui-corner-all">';
                $output .= '</fieldset>';
                $output .= '<input class="wp-block-button__link" type="submit" value="Update" name="_update">';
                $output .= '</form>';
                $output .= '</div>';
            }

            if( isset($_POST['_add']) ) {
                $output .= '<div id="dialog" title="Create new status">';
                $output .= '<form method="post">';
                $output .= '<fieldset>';
                $output .= '<label for="system-status-code">Code</label>';
                $output .= '<input type="text" name="_system_status_code" id="system-status-code" class="text ui-widget-content ui-corner-all">';
                $output .= '<label for="system-status-title">Title</label>';
                $output .= '<input type="text" name="_system_status_title" id="system-status-title" class="text ui-widget-content ui-corner-all">';
                $output .= '<label for="system-status-category">Category</label>';
                $output .= '<input type="text" name="_system_status_category" id="system-status-category" class="text ui-widget-content ui-corner-all">';
                $output .= '</fieldset>';
                $output .= '<input class="wp-block-button__link" type="submit" value="Create" name="_create">';
                $output .= '</form>';
                $output .= '</div>';
            }
            return $output;
        }

        public function insert_system_status($data=[]) {
            global $wpdb;
            $table = $wpdb->prefix.'system_status';
            $data['create_timestamp'] = time();
            $data['update_timestamp'] = time();
            $wpdb->insert($table, $data);        
            return $wpdb->insert_id;
        }

        public function update_system_status($data=[], $where=[]) {
            global $wpdb;
            $table = $wpdb->prefix.'system_status';
            $data['update_timestamp'] = time();
            $wpdb->update($table, $data, $where);
        }

        public function delete_system_status($where=[]) {
            global $wpdb;
            $table = $wpdb->prefix.'system_status';
            $wpdb->delete($table, $where);
        }

        public function get_name( $_id=0 ) {
            global $wpdb;
            $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}system_status WHERE system_status_id = %d OR system_status_code = %s", $_id, $_id ), OBJECT );
            return $row->system_status_title;
        }

        public function select_options( $_code=0 ) {
            global $wpdb;
            //$output = '<option value="0">-- Select an option --</option>';
            $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}system_status", OBJECT );
            foreach ($results as $index => $result) {
                if ( $result->system_status_code == $_code ) {
                    $output .= '<option value="'.$result->system_status_code.'" selected>';
                } else {
                    $output .= '<option value="'.$result->system_status_code.'">';
                }
                $output .= $result->system_status_title;
                $output .= '</option>';        
            }
            //$output .= '<option value="0">-- Remove this --</option>';
            return $output;
        }

        public function create_tables() {
            global $wpdb;
            $charset_collate = $wpdb->get_charset_collate();
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
            $sql = "CREATE TABLE `{$wpdb->prefix}system_status` (
                system_status_id int NOT NULL AUTO_INCREMENT,
                system_status_code varchar(10) UNIQUE,
                system_status_title varchar(50),
                system_status_category varchar(20),
                create_timestamp int(10),
                update_timestamp int(10),
                PRIMARY KEY (system_status_id)
            ) $charset_collate;";
            dbDelta($sql);
        }
    }
    $my_class = new system_status();
}