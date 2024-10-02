<?php
if (!defined('ABSPATH')) {
    exit;
}

add_action('init', 'general_helps::instance');
if (!class_exists('general_helps')) {

    class general_helps {

        /**
         * Actions that the Plugin runs before WordPress finishes loading and sending headers
         */
        static function instance() {
            return new self();
        }

        /**
         * Decryption: AES 256
         * @param edata  BASE64 encrypted string
         * @param string decrypt password
         * @return decrypt string
         */
        public static function decrypt($edata, $password) {
            $data = base64_decode($edata);
            $salt = substr($data, 0, 16);
            $ct = substr($data, 16);
            $rounds = 3; // depends on key length
            $data00 = $password.$salt;
            $hash = array();
            $hash[0] = hash('sha256', $data00, true);
            $result = $hash[0];
            for ($i = 1; $i < $rounds; $i++) {
                $hash[$i] = hash('sha256', $hash[$i - 1].$data00, true);
                $result .= $hash[$i];
            }
            $key = substr($result, 0, 32);
            $iv  = substr($result, 32,16);
            return openssl_decrypt($ct, 'AES-256-CBC', $key, 0, $iv);
        }

        /**
         * encrypt AES 256
         *
         * @param data $data
         * @param string $password
         * @return base64 encrypted data
         */
        public static function encrypt($data, $password) {
            // Set a random salt
            $salt = openssl_random_pseudo_bytes(16);
            $salted = '';
            $dx = '';
            // Salt the key(32) and iv(16) = 48
            while (strlen($salted) < 48) {
                $dx = hash('sha256', $dx.$password.$salt, true);
                $salted .= $dx;
            }
            $key = substr($salted, 0, 32);
            $iv  = substr($salted, 32,16);
            $encrypted_data = openssl_encrypt($data, 'AES-256-CBC', $key, 0, $iv);
            return base64_encode($salt . $encrypted_data);
        }

        /**
         * Generation/acquisition of notification tag (HTML)
         * @param message message to be notified
         * @param type Notification type (error/warning/success/info)
         * @retern Notification Tab (HTML)
         */
        public static function getNotice($message, $type) {
            return
                '<div class="notice notice-' . $type . ' is-dismissible">' .
                '<p><strong>' . esc_html($message) . '</strong></p>' .
                '<button type="button" class="notice-dismiss">' .
                '<span class="screen-reader-text">Dismiss this notice.</span>' .
                '</button>' .
                '</div>';
        }

        /**
         * Class constructor
         */
/*        
        public function __construct() {
            if (is_admin() && is_user_logged_in() && (is_super_admin() || current_user_can('administrator') || current_user_can('editor') || current_user_can('author'))) {
                // Add the Menu page at the top of the management interface
                //add_action('admin_menu', [$this, 'set_plugin_menu']);
                // Operations performed at the beginning of each admin page, 
                // before the page is rendered, the function for the Plugin to save preferences
                //add_action('admin_init', [$this, 'save_settings']);
            }        
        }
*/
        public static function push_imagemap_messages( $_contents=array() ) {
            $line_bot_api = new line_bot_api();
            $line_bot_api->pushMessage([
                'to' => $_contents['line_user_id'],
                'messages' => [
                    [
                        "type" => "imagemap",
                        "baseUrl" => $_contents["base_url"],
                        "altText" => $_contents["alt_text"],
                        "baseSize" => [
                            "width" => 1040,
                            "height" => 1040,
                        ],
                        "actions" => [
                            [
                                "type" => "uri",
                                "linkUri" => $_contents["link_uri"],
                                "area" => [
                                    "x" => 0,
                                    "y" => 0,
                                    "width" => 1040,
                                    "height" => 1040
                                ]
                            ],
                        ],
                    ]
                ]
            ]);
        }        

        public static function create_page($title_of_the_page, $content, $category='admin', $parent_id = NULL ) {
            $objPage = get_page_by_title($title_of_the_page, 'OBJECT', 'page');
            if( ! empty( $objPage ) ) {
                //add_option($title_of_the_page, get_permalink($objPage->ID));
                return $objPage->ID;
            }

            $alignfull = '<div class="wp-block-columns alignfull"><div class="wp-block-column">[';
            $content = $alignfull.$content.']</div></div>';

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

            //add_option($title_of_the_page, strtolower(str_replace(' ', '-', trim($title_of_the_page))));
            add_option($title_of_the_page, get_permalink($page_id));

            return $page_id;
        }
        
        public static function get_search_results( $table, $_search=array(), $_conditions=array() ) {            
            global $wpdb;
            $results = array();
            $where_condition = '';
            if ($_search!=array()) {
                if ($_search=='' && $_conditions==array()) {
                    $results = $wpdb->get_results( "SELECT * FROM ".$table, OBJECT );
                } else {
                    $existing_columns = $wpdb->get_col("DESC ".$table, 0);
                    $x = count($existing_columns);
                    foreach ($existing_columns as $existing_column) {
                        $where_condition .= $existing_column.' LIKE "%'.$_search.'%"';
                        $x = $x - 1 ;
                        if ($x > 0) {
                            $where_condition .= ' OR ';
                        }
                    }    
                }
                if ($where_condition != '') {
                    $where_condition = '( '.$where_condition.' )';
                }
            }

            if ($_conditions!=array()) {
                $x = 0;
                foreach ($_conditions as $_condition) {
                    if ($x > 0) {
                        $where_condition .= ' AND ';
                    }
                    $where_condition .= $_condition;
                    $x = $x + 1;
                }
            }

            if ($where_condition == '') {
                $results = $wpdb->get_results( "SELECT * FROM ".$table, OBJECT );
            } else {
                $where_condition = ' WHERE '.$where_condition;
                $results = $wpdb->get_results( "SELECT * FROM ".$table.$where_condition, OBJECT );
            }
            return $results;
        }
       
    }
}
?>