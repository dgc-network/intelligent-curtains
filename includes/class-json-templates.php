<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('json_templates')) {
    class json_templates {
        private $_wp_page_title;
        private $_wp_page_postid;
        /**
         * Class constructor
         */
        public function __construct() {
            $this->_wp_page_title = 'Templates';
            $this->_wp_page_postid = get_page_by_title($this->_wp_page_title)->ID;
            $wp_pages = new wp_pages();
            $wp_pages->create_page($this->_wp_page_title, '[json-template-list]');            
            add_shortcode( 'json-template-list', array( $this, 'list_json_templates' ) );
            $this->create_tables();
        }

        public function list_json_templates() {
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
                $this->insert_json_template(
                    array(
                        'json_template_title'=>$_POST['_json_template_title'],
                        'json_template_text'=>esc_textarea($_POST['_json_template_text'])
                    )
                );
            }
            
            if( isset($_POST['_update']) ) {
                $this->update_json_templates(
                    array(
                        'json_template_title'=>$_POST['_json_template_title'],
                        'json_template_text'=>esc_textarea($_POST['_json_template_text'])
                    ),
                    array(
                        'json_template_id'=>$_POST['_json_template_id'],
                    )
                );
                ?><script>window.location.replace("?_update=");</script><?php
            }

            if( isset($_GET['_delete']) ) {
                $this->delete_json_templates(
                    array(
                        'json_template_id'=>$_GET['_delete']
                    )
                );
            }

            if( isset($_POST['_where']) ) {
                $where='"%'.$_POST['_where'].'%"';
                $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}json_templates WHERE curtain_remote_name LIKE {$where}", OBJECT );
                unset($_POST['_where']);
            } else {
                $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}json_templates", OBJECT );
            }
            $output  = '<h2>json template</h2>';
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
            $output .= '<table id="remotes" class="ui-widget ui-widget-content">';
            $output .= '<thead><tr class="ui-widget-header ">';
            $output .= '<th></th>';
            $output .= '<th>title</th>';
            $output .= '<th>json</th>';
            $output .= '<th>update_time</th>';
            $output .= '<th></th>';
            $output .= '</tr></thead>';
            $output .= '<tbody>';
            foreach ( $results as $index=>$result ) {
                $output .= '<tr>';
                $output .= '<td style="text-align: center;">';
                $output .= '<span id="btn-edit-'.$result->json_template_id.'"><i class="fa-regular fa-pen-to-square"></i></span>';
                $output .= '</td>';
                $output .= '<td>'.$result->json_template_title.'</td>';
                $output .= '<td>'.wp_trim_words($result->json_template_text).'</td>';
                $output .= '<td>'.wp_date( get_option('date_format'), $result->update_timestamp ).' '.wp_date( get_option('time_format'), $result->update_timestamp ).'</td>';
                $output .= '<td style="text-align: center;">';
                $output .= '<span id="btn-del-'.$result->json_template_id.'"><i class="fa-regular fa-trash-can"></i></span>';
                $output .= '</td>';
                $output .= '</tr>';
            }
            $output .= '</tbody></table></div>';

            if( isset($_GET['_edit']) ) {
                $_id = $_GET['_edit'];
                $row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}json_templates WHERE json_template_id={$_id}", OBJECT );
                $output .= '<div id="dialog" title="template update">';
                $output .= '<form method="post">';
                $output .= '<fieldset>';
                $output .= '<input type="hidden" value="'.$row->json_template_id.'" name="_json_template_id">';
                $output .= '<label for="json-template-title">Title</label>';
                $output .= '<input type="text" name="_json_template_title" value="'.$row->json_template_title.'" id="json-template-title" class="text ui-widget-content ui-corner-all">';
                $output .= '<label for="json-template-text">JSON</label>';
                $output .= '<textarea name="_json_template_text" rows="10" cols="50">'.$row->json_template_text.'</textarea>';
                //$output .= '<input type="text" name="_json_template_text" value="'.$row->json_template_text.'" id="json-template-text" class="text ui-widget-content ui-corner-all">';
                $output .= '</fieldset>';
                $output .= '<input class="wp-block-button__link" type="submit" value="Update" name="_update">';
                $output .= '</form>';
                $output .= '</div>';
            }

            if( isset($_POST['_add']) ) {
                $output .= '<div id="dialog" title="Create new template">';
                $output .= '<form method="post">';
                $output .= '<fieldset>';
                $output .= '<label for="json-template-title">Title</label>';
                $output .= '<input type="text" name="_json_template_title" id="json-template-title" class="text ui-widget-content ui-corner-all">';
                $output .= '<label for="json-template-text">JSON</label>';
                $output .= '<textarea name="_json_template_text" rows="10" cols="50"></textarea>';
                //$output .= '<input type="text" name="_json_template_text" id="json-template-text" class="text ui-widget-content ui-corner-all">';
                $output .= '</fieldset>';
                $output .= '<input class="wp-block-button__link" type="submit" value="Create" name="_create">';
                $output .= '</form>';
                $output .= '</div>';
            }
            return $output;
        }

        public function insert_json_template($data=[]) {
            global $wpdb;
            $table = $wpdb->prefix.'json_templates';
            $data['create_timestamp'] = time();
            $data['update_timestamp'] = time();
            $wpdb->insert($table, $data);        
            return $wpdb->insert_id;
        }

        public function update_json_templates($data=[], $where=[]) {
            global $wpdb;
            $table = $wpdb->prefix.'json_templates';
            $data['update_timestamp'] = time();
            $wpdb->update($table, $data, $where);
        }

        public function delete_json_templates($where=[]) {
            global $wpdb;
            $table = $wpdb->prefix.'json_templates';
            $wpdb->delete($table, $where);
        }

        public function get_json( $_id=0 ) {
            global $wpdb;
            $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}json_templates WHERE json_template_id = %d OR json_template_title = %s", $_id, $_id ), OBJECT );
            return $row->json_template_text;
        }

        public function create_tables() {
            global $wpdb;
            $charset_collate = $wpdb->get_charset_collate();
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
            $sql = "CREATE TABLE `{$wpdb->prefix}json_templates` (
                json_template_id int NOT NULL AUTO_INCREMENT,
                json_template_title varchar(20),
                json_template_text text,
                create_timestamp int(10),
                update_timestamp int(10),
                PRIMARY KEY (json_template_id)
            ) $charset_collate;";
            dbDelta($sql);
        }
    }
    $my_class = new json_templates();
}