<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('service_links')) {
    class service_links {
        private $_wp_page_title;
        private $_wp_page_postid;
        /**
         * Class constructor
         */
        public function __construct() {
            $this->_wp_page_title = 'Links';
            $this->_wp_page_postid = general_helps::create_page($this->_wp_page_title, 'service-link-list', 'system');
            add_shortcode( 'service-link-list', array( $this, 'list_service_links' ) );
            $this->create_tables();
            $this->init_service_links();
        }

        public function init_service_links() {
            $this->insert_service_link(
                array(
                    'service_link_title'    => 'user_registry',
                    'service_link_uri'      => 'https://disabused-shop.000webhostapp.com/images/image003',
                    'service_link_category' => 'imagemap',
                )
            );
            $this->insert_service_link(
                array(
                    'service_link_title'    => 'registry_error',
                    'service_link_uri'      => 'https://disabused-shop.000webhostapp.com/images/image002',
                    'service_link_category' => 'imagemap',
                )
            );
            $this->insert_service_link(
                array(
                    'service_link_title'    => 'agent_registry',
                    'service_link_uri'      => 'https://disabused-shop.000webhostapp.com/images/image001',
                    'service_link_category' => 'imagemap',
                )
            );
        }

        public function list_service_links() {
            global $wpdb;
            /** Check the permission */
            if ( !is_user_logged_in() ) return '<div style="text-align:center;"><h3>You did not login the system. Please login first.</h3></div>';
            $user = wp_get_current_user();
            if ( !$user->has_cap('manage_options') ) return '<div style="text-align:center;"><h3>You did not have the cpability to access this system.<br>Please contact the administrator.</h3></div>';

            /** Post the result */
            if( isset($_POST['_create']) ) {
                $this->insert_service_link(
                    array(
                        'service_link_title'=>$_POST['_service_link_title'],
                        'service_link_uri'=>$_POST['_service_link_uri'],
                        'service_link_category'=>'view'
                    )
                );
            }
        
            if( isset($_POST['_update']) ) {
                $this->update_service_links(
                    array(
                        'service_link_title'=>$_POST['_service_link_title'],
                        'service_link_uri'=>$_POST['_service_link_uri'],
                    ),
                    array(
                        'service_link_id'=>$_POST['_service_link_id'],
                    )
                );
                ?><script>window.location.replace("?_update=");</script><?php
            }

            if( isset($_GET['_delete']) ) {
                $this->delete_service_links(
                    array(
                        'service_link_id'=>$_GET['_delete']
                    )
                );
            }

            /** List */
            $output  = '<h2>Service Links</h2>';
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
            $output .= '<table class="ui-widget ui-widget-content">';
            $output .= '<thead><tr class="ui-widget-header ">';
            $output .= '<th></th>';
            $output .= '<th>title</th>';
            $output .= '<th>uri</th>';
            $output .= '<th>update_time</th>';
            $output .= '<th></th>';
            $output .= '</tr></thead>';

            $output .= '<tbody>';
            $_addition = array('service_link_category="view"');
            $results = general_helps::get_search_results($wpdb->prefix.'service_links', $_POST['_where']);
            foreach ( $results as $index=>$result ) {
                $output .= '<tr>';
                $output .= '<td style="text-align: center;">';
                $output .= '<span id="btn-edit-'.$result->service_link_id.'"><i class="fa-regular fa-pen-to-square"></i></span>';
                $output .= '</td>';
                $output .= '<td>'.$result->service_link_title.'</td>';
                $output .= '<td>'.$result->service_link_uri.'</td>';
                $output .= '<td>'.wp_date( get_option('date_format'), $result->update_timestamp ).' '.wp_date( get_option('time_format'), $result->update_timestamp ).'</td>';
                $output .= '<td style="text-align: center;">';
                $output .= '<span id="btn-del-'.$result->service_link_id.'"><i class="fa-regular fa-trash-can"></i></span>';
                $output .= '</td>';
                $output .= '</tr>';
            }
            $output .= '</tbody></table></div>';

            if( isset($_GET['_edit']) ) {
                $_id = $_GET['_edit'];
                $row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}service_links WHERE service_link_id={$_id}", OBJECT );
                $output .= '<div id="dialog" title="Service Link update">';
                $output .= '<form method="post">';
                $output .= '<fieldset>';
                $output .= '<input type="hidden" value="'.$row->service_link_id.'" name="_service_link_id">';
                $output .= '<label for="service-link-title">Title</label>';
                $output .= '<input type="text" name="_service_link_title" value="'.$row->service_link_title.'" id="service-link-title" class="text ui-widget-content ui-corner-all">';
                $output .= '<label for="service-link-uri">Uri</label>';
                $output .= '<input type="text" name="_service_link_uri" value="'.$row->service_link_uri.'" id="service-link-uri" class="text ui-widget-content ui-corner-all">';
                $output .= '</fieldset>';
                $output .= '<input class="wp-block-button__link" type="submit" value="Update" name="_update">';
                $output .= '</form>';
                $output .= '</div>';
            }

            if( isset($_POST['_add']) ) {
                $output .= '<div id="dialog" title="New service link">';
                $output .= '<form method="post">';
                $output .= '<fieldset>';
                $output .= '<label for="service-link-title">Title</label>';
                $output .= '<input type="text" name="_service_link_title" id="service-link-title" class="text ui-widget-content ui-corner-all">';
                $output .= '<label for="service-link-uri">Uri</label>';
                $output .= '<input type="text" name="_service_link_uri" id="service-link-uri" class="text ui-widget-content ui-corner-all">';
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
            $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}service_links WHERE service_link_title = %s", $_title ), OBJECT );
            return $row->service_link_id;
        }

        public function get_name( $_id=0 ) {
            global $wpdb;
            $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}service_links WHERE service_link_id = %d", $_id ), OBJECT );
            return $row->service_link_title;
        }

        public function get_category( $_id=0 ) {
            global $wpdb;
            $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}service_links WHERE service_link_id = %d OR service_link_title = %s", $_id, $_id ), OBJECT );
            return $row->service_link_category;
        }

        public function get_link( $_title=0 ) {
            global $wpdb;
            $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}service_links WHERE service_link_id = %d OR service_link_title = %s", $_title, $_title ), OBJECT );
            return $row->service_link_uri;
        }

        public function create_tables() {
            global $wpdb;
            $charset_collate = $wpdb->get_charset_collate();
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
            $sql = "CREATE TABLE `{$wpdb->prefix}service_links` (
                service_link_id int NOT NULL AUTO_INCREMENT,
                service_link_title varchar(20) UNIQUE,
                service_link_uri varchar(255),
                service_link_category varchar(10),
                create_timestamp int(10),
                update_timestamp int(10),
                PRIMARY KEY (service_link_id)
            ) $charset_collate;";
            dbDelta($sql);            
        }
    }
    $my_class = new service_links();
}
?>