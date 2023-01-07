<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('service_links')) {
    class service_links {
        private $_option_page;
        /**
         * Class constructor
         */
        public function __construct() {
            $this->_option_page = 'Links';
            $this->create_tables();
            add_shortcode( 'service-link-list', array( $this, 'list_service_links' ) );
            $option_pages = new option_pages();
            $option_pages->create_page($this->_option_page, '[service-link-list]');
        }

        public function list_service_links() {
            global $wpdb;
            $curtain_users = new curtain_users();

            if( isset($_SESSION['line_user_id']) ) {
                $permission = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}user_permissions WHERE line_user_id = %s AND option_page= %s", $_SESSION['line_user_id'], $this->_option_page ), OBJECT );            
                if (is_null($permission) || !empty($wpdb->last_error)) {
                    if ( $_GET['_check_permission'] != 'false' ) {
                        return 'You have not permission to access '.$_option_page.' page. Please check to the administrators.';
                    }
                }
            } else {
                if ( $_GET['_check_permission'] != 'false' ) {
                    return 'You have not permission to access this page. Please check to the administrators.';
                }
            }

            if( isset($_POST['_create']) ) {
                $this->insert_service_link(
                    array(
                        'service_option_title'=>$_POST['_service_option_title'],
                        'service_option_link'=>$_POST['_service_option_link'],
                        'service_option_category'=>$_POST['_service_option_category']
                    )
                );
            }
        
            if( isset($_POST['_update']) ) {
                $this->update_service_links(
                    array(
                        'service_option_title'=>$_POST['_service_option_title'],
                        'service_option_link'=>$_POST['_service_option_link'],
                        'service_option_category'=>$_POST['_service_option_category']
                    ),
                    array(
                        'service_option_id'=>$_POST['_service_option_id'],
                    )
                );
                ?><script>window.location.replace("?_update=");</script><?php
            }

            if( isset($_GET['_delete']) ) {
                $this->delete_service_links(
                    array(
                        'service_option_id'=>$_GET['_delete']
                    )
                );
            }

            if( isset($_POST['_where']) ) {
                $where='"%'.$_POST['_where'].'%"';
                $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}service_links WHERE service_option_title LIKE {$where}", OBJECT );
                unset($_POST['_where']);
            } else {
                $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}service_links", OBJECT );
            }
            $output  = '<h2>Service Options</h2>';
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
            $output .= '<table class="ui-widget ui-widget-content">';
            $output .= '<thead><tr class="ui-widget-header ">';
            $output .= '<th></th>';
            $output .= '<th>title</th>';
            $output .= '<th>category</th>';
            $output .= '<th>update_time</th>';
            $output .= '<th></th>';
            $output .= '</tr></thead>';
            $output .= '<tbody>';
            foreach ( $results as $index=>$result ) {
                $output .= '<tr>';
                $output .= '<td style="text-align: center;">';
                $output .= '<span id="btn-edit-'.$result->service_option_id.'"><i class="fa-regular fa-pen-to-square"></i></span>';
                $output .= '</td>';
                $output .= '<td>'.$result->service_option_title.'</td>';
                $output .= '<td>'.$result->service_option_category.'</td>';
                $output .= '<td>'.wp_date( get_option('date_format'), $result->update_timestamp ).' '.wp_date( get_option('time_format'), $result->update_timestamp ).'</td>';
                $output .= '<td style="text-align: center;">';
                $output .= '<span id="btn-del-'.$result->service_option_id.'"><i class="fa-regular fa-trash-can"></i></span>';
                $output .= '</td>';
                $output .= '</tr>';
            }
            $output .= '</tbody></table></div>';

            if( isset($_GET['_edit']) ) {
                $_id = $_GET['_edit'];
                $row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}service_links WHERE service_option_id={$_id}", OBJECT );
                $output .= '<div id="dialog" title="Service Option update">';
                $output .= '<form method="post">';
                $output .= '<fieldset>';
                $output .= '<input type="hidden" value="'.$row->service_option_id.'" name="_service_option_id">';
                $output .= '<label for="service-option-title">Option Title</label>';
                $output .= '<input type="text" name="_service_option_title" value="'.$row->service_option_title.'" id="service-option-title" class="text ui-widget-content ui-corner-all">';
                $output .= '<label for="service-option-link">Option Link/Page</label>';
                $output .= '<input type="text" name="_service_option_link" value="'.$row->service_option_link.'" id="service-option-link" class="text ui-widget-content ui-corner-all">';
                $output .= '<label for="service-option-category">Category</label>';
                $output .= '<input type="text" name="_service_option_category" value="'.$row->service_option_category.'" id="service-option-category" class="text ui-widget-content ui-corner-all">';
                $output .= '</fieldset>';
                $output .= '<input class="wp-block-button__link" type="submit" value="Update" name="_update">';
                $output .= '</form>';
                $output .= '</div>';
            }

            if( isset($_POST['_add']) ) {
                $output .= '<div id="dialog" title="Create new option">';
                $output .= '<form method="post">';
                $output .= '<fieldset>';
                $output .= '<label for="service_option_title">Option Title</label>';
                $output .= '<input type="text" name="_service_option_title" id="service_option_title" class="text ui-widget-content ui-corner-all">';
                $output .= '<label for="service_option_link">Option Link/Page</label>';
                $output .= '<input type="text" name="_service_option_link" id="service_option_link" class="text ui-widget-content ui-corner-all">';
                $output .= '<label for="service_option_category">Category</label>';
                $output .= '<input type="text" name="_service_option_category" id="service_option_category" class="text ui-widget-content ui-corner-all">';
                $output .= '</fieldset>';
                $output .= '<input class="wp-block-button__link" type="submit" value="Create" name="_create">';
                $output .= '</form>';
                $output .= '</div>';
            }
            return $output;
        }

        public function insert_service_link($data=[]) {
            global $wpdb;
            $table = $wpdb->prefix.'service_links';
            $data['create_timestamp'] = time();
            $data['update_timestamp'] = time();
            $wpdb->insert($table, $data);        
            return $wpdb->insert_id;
        }

        public function update_service_links($data=[], $where=[]) {
            global $wpdb;
            $table = $wpdb->prefix.'service_links';
            $data['update_timestamp'] = time();
            $wpdb->update($table, $data, $where);
        }

        public function delete_service_links($where=[]) {
            global $wpdb;
            $table = $wpdb->prefix.'service_links';
            $wpdb->delete($table, $where);
        }

        public function get_id( $_title='' ) {
            global $wpdb;
            $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}service_links WHERE service_option_title = %s", $_title ), OBJECT );
            return $row->service_option_id;
        }

        public function get_name( $_id=0 ) {
            global $wpdb;
            $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}service_links WHERE service_option_id = %d", $_id ), OBJECT );
            return $row->service_option_title;
        }

        public function get_category( $_id=0 ) {
            global $wpdb;
            $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}service_links WHERE service_option_id = %d OR service_option_title = %s", $_id, $_id ), OBJECT );
            return $row->service_option_category;
        }

        public function get_link( $_title=0 ) {
            global $wpdb;
            $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}service_links WHERE service_option_id = %d OR service_option_title = %s", $_title, $_title ), OBJECT );
            //return get_site_url().'/'.$row->service_option_link;
            return $row->service_option_link;
        }

        public function create_tables() {
            global $wpdb;
            $charset_collate = $wpdb->get_charset_collate();
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
            $sql = "CREATE TABLE `{$wpdb->prefix}service_links` (
                service_option_id int NOT NULL AUTO_INCREMENT,
                service_option_title varchar(20),
                service_option_link varchar(255),
                service_option_category varchar(10),
                create_timestamp int(10),
                update_timestamp int(10),
                PRIMARY KEY (service_option_id)
            ) $charset_collate;";
            dbDelta($sql);            
        }
    }
    $my_class = new service_links();
}
?>