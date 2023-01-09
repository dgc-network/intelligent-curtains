<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('wp_pages')) {
    class wp_pages {
        private $_wp_page_title;
        private $_wp_page_postid;
        /**
         * Class constructor
         */
        public function __construct() {
            $this->_wp_page_title = 'Pages';
            $this->_wp_page_postid = get_page_by_title($this->_wp_page_title)->ID;
            $this->create_page($this->_wp_page_title, '[wp-page-list]');
            add_shortcode( 'wp-page-list', array( $this, 'list_wp_pages' ) );
            $this->create_tables();
        }

        public function create_page($title_of_the_page,$content,$category='admin',$parent_id = NULL ) {
            $objPage = get_page_by_title($title_of_the_page, 'OBJECT', 'page');
            if( ! empty( $objPage ) ) {
                //echo "Page already exists:" . $title_of_the_page . "<br/>";
                return $objPage->ID;
            }
            
            $page_id = wp_insert_post(
                array(
                    'comment_status' => 'close',
                    'ping_status'    => 'close',
                    'post_author'    => 1,
                    'post_title'     => ucwords($title_of_the_page),
                    'post_name'      => strtolower(str_replace(' ', '-', trim($title_of_the_page))),
                    'post_status'    => 'publish',
                    'post_content'   => $content,
                    'post_type'      => 'page',
                    'post_parent'    =>  $parent_id //'id_of_the_parent_page_if_it_available'
                )
            );

            $this->insert_wp_page(
                array(
                    'wp_page_postid' => $page_id,
                    'wp_page_category' => $category,
                )
            );
            //echo "Created page_id=". $page_id." for page '".$title_of_the_page. "'<br/>";
            return $page_id;
        }
        
        public function list_wp_pages() {
            global $wpdb;
            $curtain_users = new curtain_users();

            if( isset($_SESSION['line_user_id']) ) {
                $permission = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}user_permissions WHERE line_user_id = %s AND wp_page_postid= %d", $_SESSION['line_user_id'], $this->_wp_page_postid ), OBJECT );            
                if (is_null($permission) || !empty($wpdb->last_error)) {
                    if ( $_GET['_check_permission'] != 'false' ) {
                        return 'You have not permission to access '.$_wp_page.' page. Please check to the administrators.';
                    }
                }
            } else {
                if ( $_GET['_check_permission'] != 'false' ) {
                    return 'You have not permission to access this page. Please check to the administrators.';
                }
            }

            if( isset($_POST['_create']) ) {
                $this->insert_wp_page(
                    array(
                        'wp_page_category'=>$_POST['_wp_page_category']
                    )
                );
            }
        
            if( isset($_POST['_update']) ) {
                $this->update_wp_pages(
                    array(
                        'wp_page_category'=>$_POST['_wp_page_category']
                    ),
                    array(
                        'wp_page_id'=>$_POST['_wp_page_id'],
                    )
                );
                ?><script>window.location.replace("?_update=");</script><?php
            }

            if( isset($_GET['_delete']) ) {
                $this->delete_wp_pages(
                    array(
                        'wp_page_id'=>$_GET['_delete']
                    )
                );
                $curtain_users->delete_user_permissions(
                    array(
                        'wp_page'=>$this->get_name($_GET['_delete'])
                    )
                );
                wp_delete_post($this->get_postid($_GET['_delete']), true);
            }

            if( isset($_POST['_where']) ) {
                $where='"%'.$_POST['_where'].'%"';
                $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wp_pages WHERE wp_page_title LIKE {$where}", OBJECT );
                unset($_POST['_where']);
            } else {
                $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wp_pages", OBJECT );
            }
            $output  = '<h2>Wordpress Pages</h2>';
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
            $output .= '<table class="ui-widget ui-widget-content">';
            $output .= '<thead><tr class="ui-widget-header ">';
            $output .= '<th></th>';
            $output .= '<th>title</th>';
            $output .= '<th>postid</th>';
            $output .= '<th>category</th>';
            $output .= '<th>update_time</th>';
            $output .= '<th></th>';
            $output .= '</tr></thead>';
            $output .= '<tbody>';
            foreach ( $results as $index=>$result ) {
                $output .= '<tr>';
                $output .= '<td style="text-align: center;">';
                $output .= '<span id="btn-edit-'.$result->wp_page_id.'"><i class="fa-regular fa-pen-to-square"></i></span>';
                $output .= '</td>';
                $output .= '<td>'.get_the_title($result->wp_page_postid).'</td>';
                $output .= '<td>'.$result->wp_page_postid.'</td>';
                $output .= '<td>'.$result->wp_page_category.'</td>';
                $output .= '<td>'.wp_date( get_option('date_format'), $result->update_timestamp ).' '.wp_date( get_option('time_format'), $result->update_timestamp ).'</td>';
                $output .= '<td style="text-align: center;">';
                $output .= '<span id="btn-del-'.$result->wp_page_id.'"><i class="fa-regular fa-trash-can"></i></span>';
                $output .= '</td>';
                $output .= '</tr>';
            }
            $output .= '</tbody></table></div>';

            if( isset($_GET['_edit']) ) {
                $_id = $_GET['_edit'];
                $row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wp_pages WHERE wp_page_id={$_id}", OBJECT );
                $output .= '<div id="dialog" title="page update">';
                $output .= '<form method="post">';
                $output .= '<fieldset>';
                $output .= '<input type="hidden" value="'.$row->wp_page_id.'" name="_wp_page_id">';
                $output .= '<label for="wp-page-title">Page Title</label>';
                $output .= '<input type="text" name="_wp_page_title" value="'.get_the_title($row->wp_page_postid).'" id="wp-page-title" class="text ui-widget-content ui-corner-all">';
                $output .= '<label for="wp-page-category">Category</label>';
                $output .= '<input type="text" name="_wp_page_category" value="'.$row->wp_page_category.'" id="wp-page-category" class="text ui-widget-content ui-corner-all">';
                $output .= '</fieldset>';
                $output .= '<input class="wp-block-button__link" type="submit" value="Update" name="_update">';
                $output .= '</form>';
                $output .= '</div>';
            }
            return $output;
        }

        public function insert_wp_page($data=[]) {
            global $wpdb;
            $table = $wpdb->prefix.'wp_pages';
            $data['create_timestamp'] = time();
            $data['update_timestamp'] = time();
            $wpdb->insert($table, $data);        
            return $wpdb->insert_id;
        }

        public function update_wp_pages($data=[], $where=[]) {
            global $wpdb;
            $table = $wpdb->prefix.'wp_pages';
            $data['update_timestamp'] = time();
            $wpdb->update($table, $data, $where);
        }

        public function delete_wp_pages($where=[]) {
            global $wpdb;
            $table = $wpdb->prefix.'wp_pages';
            $wpdb->delete($table, $where);
        }

        public function get_postid( $_id=0 ) {
            global $wpdb;
            $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}wp_pages WHERE wp_page_id = %d", $_id ), OBJECT );
            return $row->wp_page_postid;
        }

        public function get_category( $_id=0 ) {
            global $wpdb;
            $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}wp_pages WHERE wp_page_id = %d OR wp_page_postid = %d", $_id, $_id ), OBJECT );
            return $row->wp_page_category;
        }

        public function create_tables() {
            global $wpdb;
            $charset_collate = $wpdb->get_charset_collate();
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
            $sql = "CREATE TABLE `{$wpdb->prefix}wp_pages` (
                wp_page_id int NOT NULL AUTO_INCREMENT,
                wp_page_postid int NOT NULL,
                wp_page_category varchar(10),
                create_timestamp int(10),
                update_timestamp int(10),
                PRIMARY KEY (wp_page_id)
            ) $charset_collate;";
            dbDelta($sql);            
        }
    }
    $my_class = new wp_pages();
}
?>