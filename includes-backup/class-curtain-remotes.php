<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('curtain_remotes')) {
    class curtain_remotes {
        private $_wp_page_title;
        private $_wp_page_postid;
        /**
         * Class constructor
         */
        public function __construct() {
            $this->create_tables();
            $this->_wp_page_title = 'Remotes';
            $this->_wp_page_postid = general_helps::create_page($this->_wp_page_title, 'curtain-remote-list');
            add_shortcode( 'curtain-remote-list', array( $this, 'list_curtain_remotes' ) );
            add_action( 'wp_ajax_remote_dialog_get_data', array( $this, 'remote_dialog_get_data' ) );
            add_action( 'wp_ajax_nopriv_remote_dialog_get_data', array( $this, 'remote_dialog_get_data' ) );
            add_action( 'wp_ajax_remote_dialog_save_data', array( $this, 'remote_dialog_save_data' ) );
            add_action( 'wp_ajax_nopriv_remote_dialog_save_data', array( $this, 'remote_dialog_save_data' ) );
        }

        public function list_curtain_remotes() {
            global $wpdb;
            /** Check the permission */
            if ( !is_user_logged_in() ) return '<div style="text-align:center;"><h3>You did not login the system. Please login first.</h3></div>';
            $user = wp_get_current_user();
            if ( !$user->has_cap('manage_options') ) return '<div style="text-align:center;"><h3>You did not have the cpability to access this system.<br>Please contact the administrator.</h3></div>';

            /** Post the result */
            if( isset($_POST['_create']) ) {
                $this->insert_curtain_remote(
                    array(
                        'curtain_remote_name'=>$_POST['_curtain_remote_name'],
                        'curtain_remote_price'=>$_POST['_curtain_remote_price']
                    )
                );
            }
            
            if( isset($_POST['_update']) ) {
                $this->update_curtain_remotes(
                    array(
                        'curtain_remote_name'=>$_POST['_curtain_remote_name'],
                        'curtain_remote_price'=>$_POST['_curtain_remote_price']
                    ),
                    array(
                        'curtain_remote_id'=>$_POST['_curtain_remote_id'],
                    )
                );
                ?><script>window.location.replace("?_update=");</script><?php
            }

            if( isset($_GET['_delete']) ) {
                $this->delete_curtain_remotes(
                    array(
                        'curtain_remote_id'=>$_GET['_delete']
                    )
                );
            }

            /** List */
            $output  = '<h2>Curtain Remotes</h2>';
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
            $output .= '<table id="remotes" class="ui-widget ui-widget-content">';
            $output .= '<thead><tr class="ui-widget-header ">';
            $output .= '<th></th>';
            $output .= '<th>remote</th>';
            $output .= '<th>price</th>';
            $output .= '<th>update_time</th>';
            $output .= '<th></th>';
            $output .= '</tr></thead>';
            
            $output .= '<tbody>';
            $results = general_helps::get_search_results($wpdb->prefix.'curtain_remotes', $_POST['_where']);
            foreach ( $results as $index=>$result ) {
                $output .= '<tr>';
                $output .= '<td style="text-align: center;">';
                //$output .= '<span id="btn-edit-'.$result->curtain_remote_id.'"><i class="fa-regular fa-pen-to-square"></i></span>';
                $output .= '<span id="btn-remote-'.$result->curtain_remote_id.'"><i class="fa-regular fa-pen-to-square"></i></span>';
                $output .= '</td>';
                $output .= '<td>'.$result->curtain_remote_name.'</td>';
                $output .= '<td style="text-align: center;">'.$result->curtain_remote_price.'</td>';
                $output .= '<td style="text-align: center;">'.wp_date( get_option('date_format'), $result->update_timestamp ).' '.wp_date( get_option('time_format'), $result->update_timestamp ).'</td>';
                $output .= '<td style="text-align: center;">';
                $output .= '<span id="btn-del-'.$result->curtain_remote_id.'"><i class="fa-regular fa-trash-can"></i></span>';
                $output .= '</td>';
                $output .= '</tr>';
            }
            $output .= '<tr><td colspan="5"><div id="btn-remote" style="border:solid; margin:3px; text-align:center; border-radius:5px">+</div></td></tr>';
            $output .= '</tbody></table></div>';

            /** Remote Dialog */
            $output .= '<div id="remote-dialog" title="Remote dialog">';
            $output .= '<fieldset>';
            $output .= '<input type="hidden" id="curtain-remote-id" />';
            $output .= '<label for="curtain-remote-name">Remote Name</label>';
            $output .= '<input type="text" id="curtain-remote-name" />';
            $output .= '<label for="curtain-remote-price">Remote Price</label>';
            $output .= '<input type="text" id="curtain-remote-price" />';
            $output .= '</fieldset>';
            $output .= '</div>';

            if( isset($_GET['_edit']) ) {
                $_id = $_GET['_edit'];
                $row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}curtain_remotes WHERE curtain_remote_id={$_id}", OBJECT );
                $output .= '<div id="dialog" title="Curtain remote update">';
                $output .= '<form method="post">';
                $output .= '<fieldset>';
                $output .= '<input type="hidden" value="'.$row->curtain_remote_id.'" name="_curtain_remote_id">';
                $output .= '<label for="curtain-remote-name">Remote Name</label>';
                $output .= '<input type="text" name="_curtain_remote_name" value="'.$row->curtain_remote_name.'" id="curtain-remote-name" class="text ui-widget-content ui-corner-all">';
                $output .= '<label for="curtain-remote-price">Remote Price</label>';
                $output .= '<input type="text" name="_curtain_remote_price" value="'.$row->curtain_remote_price.'" id="curtain-remote-price" class="text ui-widget-content ui-corner-all">';
                $output .= '</fieldset>';
                $output .= '<input class="wp-block-button__link" type="submit" value="Update" name="_update">';
                $output .= '</form>';
                $output .= '</div>';
            }

            if( isset($_POST['_add']) ) {
                $output .= '<div id="dialog" title="Create new remote">';
                $output .= '<form method="post">';
                $output .= '<fieldset>';
                $output .= '<label for="curtain-remote-name">Remote Name</label>';
                $output .= '<input type="text" name="_curtain_remote_name" id="curtain-remote-name" class="text ui-widget-content ui-corner-all">';
                $output .= '<label for="curtain-remote-price">Remote Price</label>';
                $output .= '<input type="text" name="_curtain_remote_price" id="curtain-remote-price" class="text ui-widget-content ui-corner-all">';
                $output .= '</fieldset>';
                $output .= '<input class="wp-block-button__link" type="submit" value="Create" name="_create">';
                $output .= '</form>';
                $output .= '</div>';
            }
            return $output;
        }

        function remote_dialog_get_data() {
            global $wpdb;
            $_id = $_POST['_id'];
            $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}curtain_remotes WHERE curtain_remote_id = %d", $_id ), OBJECT );
            $response = array();
            $response["curtain_remote_name"] = $row->curtain_remote_name;
            $response["curtain_remote_price"] = $row->curtain_remote_price;
            echo json_encode( $response );
            wp_die();
        }

        function remote_dialog_save_data() {
            if( $_POST['_curtain_remote_id']=='' ) {
                $this->insert_curtain_remote(
                    array(
                        'curtain_remote_name'=>$_POST['_curtain_remote_name'],
                        'curtain_remote_price'=>$_POST['_curtain_remote_price']
                    )
                );
            } else {
                $this->update_curtain_remotes(
                    array(
                        'curtain_remote_name'=>$_POST['_curtain_remote_name'],
                        'curtain_remote_price'=>$_POST['_curtain_remote_price']
                    ),
                    array(
                        'curtain_remote_id'=>$_POST['_curtain_remote_id']
                    )
                );
            }

            $response = array();
            echo json_encode( $response );
            wp_die();
        }

        public function insert_curtain_remote($data=[]) {
            global $wpdb;
            $table = $wpdb->prefix.'curtain_remotes';
            $data['create_timestamp'] = time();
            $data['update_timestamp'] = time();
            $wpdb->insert($table, $data);        
            return $wpdb->insert_id;
        }

        public function update_curtain_remotes($data=[], $where=[]) {
            global $wpdb;
            $table = $wpdb->prefix.'curtain_remotes';
            $data['update_timestamp'] = time();
            $wpdb->update($table, $data, $where);
        }

        public function delete_curtain_remotes($where=[]) {
            global $wpdb;
            $table = $wpdb->prefix.'curtain_remotes';
            $wpdb->delete($table, $where);
        }

        public function get_name( $_id=0 ) {
            global $wpdb;
            $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}curtain_remotes WHERE curtain_remote_id = %d", $_id ), OBJECT );
            return $row->curtain_remote_name;
        }

        public function get_price( $_id=0 ) {
            global $wpdb;
            $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}curtain_remotes WHERE curtain_remote_id = %d", $_id ), OBJECT );
            return $row->curtain_remote_price;
        }

        public function select_options( $_id=0 ) {
            global $wpdb;
            $output = '<option value="0">-- Select an option --</option>';
            $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}curtain_remotes", OBJECT );
            foreach ($results as $index => $result) {
                if ( $result->curtain_remote_id == $_id ) {
                    $output .= '<option value="'.$result->curtain_remote_id.'" selected>';
                } else {
                    $output .= '<option value="'.$result->curtain_remote_id.'">';
                }
                $output .= $result->curtain_remote_name;
                $output .= '</option>';        
            }
            $output .= '<option value="0">-- Remove this --</option>';
            return $output;
        }

        public function create_tables() {
            global $wpdb;
            $charset_collate = $wpdb->get_charset_collate();
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
            $sql = "CREATE TABLE `{$wpdb->prefix}curtain_remotes` (
                curtain_remote_id int NOT NULL AUTO_INCREMENT,
                curtain_remote_name varchar(50),
                curtain_remote_price decimal(10,2),
                create_timestamp int(10),
                update_timestamp int(10),
                PRIMARY KEY (curtain_remote_id)
            ) $charset_collate;";
            dbDelta($sql);
        }
    }
    $my_class = new curtain_remotes();
}