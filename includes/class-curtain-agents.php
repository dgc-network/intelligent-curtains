<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('curtain_agents')) {
    class curtain_agents {
        private $_wp_page_title;
        private $_wp_page_postid;
        /**
         * Class constructor
         */
        public function __construct() {
            $this->create_tables();
            $this->_wp_page_title = 'Agents';
            $this->_wp_page_postid = general_helps::create_page($this->_wp_page_title, 'curtain-agent-list');
            //add_shortcode( 'curtain-agent-list', array( $this, 'list_curtain_agents' ) );
            add_action( 'wp_ajax_agent_dialog_get_data', array( $this, 'agent_dialog_get_data' ) );
            add_action( 'wp_ajax_nopriv_agent_dialog_get_data', array( $this, 'agent_dialog_get_data' ) );
            add_action( 'wp_ajax_agent_dialog_save_data', array( $this, 'agent_dialog_save_data' ) );
            add_action( 'wp_ajax_nopriv_agent_dialog_save_data', array( $this, 'agent_dialog_save_data' ) );

            add_shortcode( 'curtain-agent-list', array( $this, 'display_shortcode' ) );
            add_action( 'init', array( $this, 'register_curtain_agent_post_type' ) );
            add_action( 'wp_ajax_get_curtain_agent_dialog_data', array( $this, 'get_curtain_agent_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_get_curtain_agent_dialog_data', array( $this, 'get_curtain_agent_dialog_data' ) );
            add_action( 'wp_ajax_set_curtain_agent_dialog_data', array( $this, 'set_curtain_agent_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_set_curtain_agent_dialog_data', array( $this, 'set_curtain_agent_dialog_data' ) );
            add_action( 'wp_ajax_del_curtain_agent_dialog_data', array( $this, 'del_curtain_agent_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_del_curtain_agent_dialog_data', array( $this, 'del_curtain_agent_dialog_data' ) );

        }

        function register_curtain_agent_post_type() {
            $labels = array(
                'menu_name'     => _x('curtain-agent', 'admin menu', 'textdomain'),
            );
            $args = array(
                'labels'        => $labels,
                'public'        => true,
                'rewrite'       => array('slug' => 'curtain-agents'),
                'supports'      => array('title', 'editor', 'custom-fields'),
                'has_archive'   => true,
                'show_in_menu'  => false,
            );
            register_post_type( 'curtain-agent', $args );
        }

        function display_shortcode() {
            // Check if the user is logged in
            if (is_user_logged_in()) {

                // delete curtain-agent post 2024-4-27
                if (isset($_GET['_delete_curtain_agents_post'])) {
                    // Get all curtain-agent posts
                    $args = array(
                        'post_type'      => 'curtain-agent',
                        'posts_per_page' => -1, // Get all posts
                        'fields'         => 'ids', // Retrieve only post IDs
                    );
                    $posts = get_posts($args);
                
                    // Loop through each post and delete it
                    foreach ($posts as $post_id) {
                        wp_delete_post($post_id, true); // Set the second parameter to true to force delete
                    }
                }

                // curtain_agents_table_to_post migration 2024-4-27
                if (isset($_GET['_migrate_curtain_agents_table_to_post'])) {
                    global $wpdb;
                    $results = general_helps::get_search_results($wpdb->prefix.'curtain_agents', $_POST['_where']);
                    foreach ( $results as $result ) {
                        $current_user_id = get_current_user_id();
                        $new_post = array(
                            'post_title'    => 'New agent',
                            'post_content'  => 'Your post content goes here.',
                            'post_status'   => 'publish',
                            'post_author'   => $current_user_id,
                            'post_type'     => 'curtain-agent',
                        );    
                        $curtain_agent_id = wp_insert_post($new_post);
                        update_post_meta( $curtain_agent_id, 'curtain_agent_number', $result->agent_number );
                        update_post_meta( $curtain_agent_id, 'curtain_agent_name', $result->agent_name );
                        update_post_meta( $curtain_agent_id, 'curtain_agent_contact', $result->contact1 );
                        update_post_meta( $curtain_agent_id, 'curtain_agent_phone', $result->phone1 );
                        update_post_meta( $curtain_agent_id, 'curtain_agent_address', $result->agent_address );
                        update_post_meta( $curtain_agent_id, 'curtain_agent_password', $result->agent_password );                
                    }
                }

                // curtain-agents start point 2024-4-27
                $this->display_curtain_agent_list();
            } else {
                //user_did_not_login_yet();
            }        
        }

        function display_curtain_agent_list() {
            ?>
            <div class="ui-widget" id="result-container">
            <h2 style="display:inline;"><?php echo __( 'Curtain agents', 'your-text-domain' );?></h2>
            <fieldset>
                <div style="display:flex; justify-content:space-between; margin:5px;">
                    <div>
                    </div>
                    <div style="text-align:right; display:flex;">
                        <input type="text" id="search-agent" style="display:inline" placeholder="Search..." />
                    </div>
                </div>
        
                <table class="ui-widget" style="width:100%;">
                    <thead>
                        <tr>
                            <th><?php echo __( 'Agent', 'your-text-domain' );?></th>
                            <th><?php echo __( 'Name', 'your-text-domain' );?></th>
                            <th><?php echo __( 'Contact', 'your-text-domain' );?></th>
                            <th><?php echo __( 'Phone', 'your-text-domain' );?></th>
                            <th><?php echo __( 'Address', 'your-text-domain' );?></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    // Define the custom pagination parameters
                    $posts_per_page = get_option('operation_row_counts');
                    $current_page = max(1, get_query_var('paged')); // Get the current page number
                    $query = $this->retrieve_curtain_agent_data($current_page);
                    $total_posts = $query->found_posts;
                    $total_pages = ceil($total_posts / $posts_per_page); // Calculate the total number of pages
        
                    if ($query->have_posts()) :
                        while ($query->have_posts()) : $query->the_post();
                            $curtain_agent_number = get_post_meta(get_the_ID(), 'curtain_agent_number', true);
                            $curtain_agent_name = get_post_meta(get_the_ID(), 'curtain_agent_name', true);
                            $curtain_agent_contact = get_post_meta(get_the_ID(), 'curtain_agent_contact', true);
                            $curtain_agent_phone = get_post_meta(get_the_ID(), 'curtain_agent_phone', true);
                            $curtain_agent_address = get_post_meta(get_the_ID(), 'curtain_agent_address', true);
                            ?>
                            <tr id="edit-curtain-agent-<?php the_ID();?>">
                                <td style="text-align:center;"><?php echo esc_html($curtain_agent_number);?></td>
                                <td style="text-align:center;"><?php echo esc_html($curtain_agent_name);?></td>
                                <td style="text-align:center;"><?php echo esc_html($curtain_agent_contact);?></td>
                                <td style="text-align:center;"><?php echo esc_html($curtain_agent_phone);?></td>
                                <td><?php echo esc_html($curtain_agent_address);?></td>
                            </tr>
                            <?php
                        endwhile;
                        wp_reset_postdata();
                    endif;
                    ?>
                    </tbody>
                </table>
                <div id="new-curtain-agent" class="custom-button" style="border:solid; margin:3px; text-align:center; border-radius:5px; font-size:small;">+</div>
                <?php
                // Display pagination links
                echo '<div class="pagination">';
                if ($current_page > 1) echo '<span class="custom-button"><a href="' . esc_url(get_pagenum_link($current_page - 1)) . '"> < </a></span>';
                echo '<span class="page-numbers">' . sprintf(__('Page %d of %d', 'textdomain'), $current_page, $total_pages) . '</span>';
                if ($current_page < $total_pages) echo '<span class="custom-button"><a href="' . esc_url(get_pagenum_link($current_page + 1)) . '"> > </a></span>';
                echo '</div>';
                ?>
            </fieldset>
            </div>
            <?php
            echo $this->display_curtain_agent_dialog();
        }

        function retrieve_curtain_agent_data($current_page = 1) {
            // Define the arguments for the WP_Query
            $posts_per_page = get_option('operation_row_counts');
            $args = array(
                'post_type'      => 'curtain-agent',
                'posts_per_page' => $posts_per_page,
                'paged'          => $current_page,
                'meta_key'       => 'curtain_agent_number', // Meta key for sorting
                'orderby'        => 'meta_value', // Sort by meta value
                'order'          => 'ASC', // Sorting order (ascending)
            );        
            
            // Add meta query for searching across all meta keys
            $search_query = sanitize_text_field($_GET['_search']);
            $meta_keys = get_post_type_meta_keys('curtain-agent');
            $meta_query_all_keys = array('relation' => 'OR');
            foreach ($meta_keys as $meta_key) {
                $meta_query_all_keys[] = array(
                    'key'     => $meta_key,
                    'value'   => $search_query,
                    'compare' => 'LIKE',
                );
            }            
            $args['meta_query'] = $meta_query_all_keys;
        
            // Execute the query
            $query = new WP_Query($args);
            
            return $query;
        }

        function display_curtain_agent_dialog($curtain_agent_id=false) {            
            $curtain_agent_number = get_post_meta($curtain_agent_id, 'curtain_agent_number', true);
            $curtain_agent_name = get_post_meta($curtain_agent_id, 'curtain_agent_name', true);
            $curtain_agent_contact = get_post_meta($curtain_agent_id, 'curtain_agent_contact', true);
            $curtain_agent_phone = get_post_meta($curtain_agent_id, 'curtain_agent_phone', true);
            $curtain_agent_address = get_post_meta($curtain_agent_id, 'curtain_agent_address', true);
            ob_start();
            ?>
            <div id="curtain-agent-dialog" title="Agent dialog">
            <fieldset>
                <input type="hidden" id="curtain-agent-id" value="<?php echo esc_attr($curtain_agent_id);?>" />
                <label for="curtain-agent-number"><?php echo __( 'Number', 'your-text-domain' );?></label>
                <input type="text" id="curtain-agent-number" value="<?php echo esc_html($curtain_agent_number);?>" class="text ui-widget-content ui-corner-all" />
                <label for="curtain-agent-name"><?php echo __( 'Name', 'your-text-domain' );?></label>
                <input type="text" id="curtain-agent-name" value="<?php echo esc_html($curtain_agent_name);?>" class="text ui-widget-content ui-corner-all" />
                <label for="curtain-agent-contact"><?php echo __( 'Contact', 'your-text-domain' );?></label>
                <input type="text" id="curtain-agent-contact" value="<?php echo esc_html($curtain_agent_contact);?>" class="text ui-widget-content ui-corner-all" />
                <label for="curtain-agent-phone"><?php echo __( 'Phone', 'your-text-domain' );?></label>
                <input type="text" id="curtain-agent-phone" value="<?php echo esc_html($curtain_agent_phone);?>" class="text ui-widget-content ui-corner-all" />
                <label for="curtain-agent-address"><?php echo __( 'Address', 'your-text-domain' );?></label>
                <input type="text" id="curtain-agent-address" value="<?php echo esc_html($curtain_agent_address);?>" class="text ui-widget-content ui-corner-all" />
            </fieldset>
            </div>
            <?php
            $html = ob_get_clean();
            return $html;        
        }

        function get_curtain_agent_dialog_data() {
            $response = array();
            if (isset($_POST['_curtain_agent_id'])) {
                $curtain_agent_id = sanitize_text_field($_POST['_curtain_agent_id']);
                $response['html_contain'] = $this->display_curtain_agent_dialog($curtain_agent_id);
            } else {
                $response['html_contain'] = 'Invalid AJAX request!';
            }
            wp_send_json($response);
        }

        function set_curtain_agent_dialog_data() {
            $response = array();
            if( isset($_POST['_curtain_agent_id']) ) {
                // Update the meta data
                $curtain_agent_id = sanitize_text_field($_POST['_curtain_agent_id']);
                update_post_meta( $curtain_agent_id, 'curtain_agent_number', sanitize_text_field($_POST['_curtain_agent_number']));
                update_post_meta( $curtain_agent_id, 'curtain_agent_name', sanitize_text_field($_POST['_curtain_agent_name']));
                update_post_meta( $curtain_agent_id, 'curtain_agent_contact', sanitize_text_field($_POST['_curtain_agent_contact']));
                update_post_meta( $curtain_agent_id, 'curtain_agent_phone', sanitize_text_field($_POST['_curtain_agent_phone']));
                update_post_meta( $curtain_agent_id, 'curtain_agent_address', sanitize_text_field($_POST['_curtain_agent_address']));
            } else {
                $current_user_id = get_current_user_id();
                $new_post = array(
                    'post_title'    => 'New agent',
                    'post_content'  => 'Your post content goes here.',
                    'post_status'   => 'publish',
                    'post_author'   => $current_user_id,
                    'post_type'     => 'curtain-agent',
                );    
                $post_id = wp_insert_post($new_post);
            }
            wp_send_json($response);
        }

        function del_curtain_agent_dialog_data() {
            $response = array();
            if( isset($_POST['_curtain_agent_id']) ) {
                $curtain_agent_id = sanitize_text_field($_POST['_curtain_agent_id']);
                wp_delete_post($curtain_agent_id, true);
            }
            wp_send_json($response);
        }

        function select_curtain_agent_options($selected_option=0) {
            $args = array(
                'post_type'      => 'curtain-agent',
                'posts_per_page' => -1,
                'meta_key'       => 'curtain_agent_number', // Meta key for sorting
                'orderby'        => 'meta_value', // Sort by meta value
                'order'          => 'ASC', // Sorting order (ascending)
            );
            $query = new WP_Query($args);

            $options = '<option value="">Select agent</option>';
            while ($query->have_posts()) : $query->the_post();
                $selected = ($selected_option == get_the_ID()) ? 'selected' : '';
                $curtain_agent_number = get_post_meta(get_the_ID(), 'curtain_agent_number', true);
                $curtain_agent_name = get_post_meta(get_the_ID(), 'curtain_agent_name', true);
                $curtain_agent_title = $curtain_agent_name.'('.$curtain_agent_number.')';
                $options .= '<option value="' . esc_attr(get_the_ID()) . '" '.$selected.' />' . esc_html($curtain_agent_title) . '</option>';
            endwhile;
            wp_reset_postdata();
            return $options;
        }
        
        

        public function list_curtain_agents() {
            // 2024-4-27 Modify the curtain-agent as the post type
            $this->display_curtain_agent_list();

            

            global $wpdb;

            /** Check the permission */
            if ( !is_user_logged_in() ) return '<div style="text-align:center;"><h3>You did not login the system. Please login first.</h3></div>';
            $user = wp_get_current_user();
            if ( !$user->has_cap('manage_options') ) return '<div style="text-align:center;"><h3>You did not have the cpability to access this system.<br>Please contact the administrator.</h3></div>';

            /** Post the result */
            if( isset($_POST['_create']) ) {
                $this->insert_curtain_agent(
                    array(
                        'agent_number'=>$_POST['_agent_number'],
                        'agent_password'=>$_POST['_agent_password'],
                        'agent_name'=>$_POST['_agent_name'],
                        'agent_address'=>$_POST['_agent_address'],
                        'contact1'=>$_POST['_contact1'],
                        'phone1'=>$_POST['_phone1'],
                        'contact2'=>$_POST['_contact2'],
                        'phone2'=>$_POST['_phone2']
                    )
                );
            }
        
            if( isset($_POST['_update']) ) {
                $this->update_curtain_agents(
                    array(
                        'agent_number'=>$_POST['_agent_number'],
                        'agent_password'=>$_POST['_agent_password'],
                        'agent_name'=>$_POST['_agent_name'],
                        'agent_address'=>$_POST['_agent_address'],
                        'contact1'=>$_POST['_contact1'],
                        'phone1'=>$_POST['_phone1'],
                        'contact2'=>$_POST['_contact2'],
                        'phone2'=>$_POST['_phone2']
                    ),
                    array(
                        'curtain_agent_id'=>$_POST['_curtain_agent_id']
                    )
                );
                ?><script>window.location.replace("?_update=");</script><?php
            }

            if( isset($_GET['_delete']) ) {
                $this->delete_curtain_agents(
                    array(
                        'curtain_agent_id'=>$_GET['_delete']
                    )
                );
            }

            /** List */
            $output  = '<h2>Curtain Agents</h2>';
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
            $output .= '<table id="agents" class="ui-widget ui-widget-content">';
            $output .= '<thead><tr class="ui-widget-header ">';
            $output .= '<th></th>';
            $output .= '<th>agent</th>';
            $output .= '<th>name</th>';
            $output .= '<th>contact</th>';
            $output .= '<th>phone</th>';
            $output .= '<th>address</th>';
            //$output .= '<th>update_time</th>';
            $output .= '<th></th>';
            $output .= '</tr></thead>';

            $output .= '<tbody>';
            $results = general_helps::get_search_results($wpdb->prefix.'curtain_agents', $_POST['_where']);
            foreach ( $results as $index=>$result ) {
                $output .= '<tr>';
                $output .= '<td style="text-align: center;">';
                //$output .= '<span id="btn-edit-'.$result->curtain_agent_id.'"><i class="fa-regular fa-pen-to-square"></i></span>';
                $output .= '<span id="btn-agent-'.$result->curtain_agent_id.'"><i class="fa-regular fa-pen-to-square"></i></span>';
                $output .= '</td>';
                $output .= '<td style="text-align: center;">'.$result->agent_number.'</td>';
                $output .= '<td>'.$result->agent_name.'</td>';
                $output .= '<td style="text-align: center;">'.$result->contact1.'</td>';
                $output .= '<td style="text-align: center;">'.$result->phone1.'</td>';
                $output .= '<td>'.$result->agent_address.'</td>';
                //$output .= '<td>'.wp_date( get_option('date_format'), $result->update_timestamp ).' '.wp_date( get_option('time_format'), $result->update_timestamp ).'</td>';
                $output .= '<td style="text-align: center;">';
                $output .= '<span id="btn-del-'.$result->curtain_agent_id.'"><i class="fa-regular fa-trash-can"></i></span>';
                $output .= '</td>';
                $output .= '</tr>';
            }
            $output .= '<tr><td colspan="7"><div id="btn-agent" style="border:solid; margin:3px; text-align:center; border-radius:5px">+</div></td></tr>';
            $output .= '</tbody></table></div>';

            /** Agent Dialog */
            $output .= '<div id="agent-dialog" title="Agent dialog">';
            $output .= '<fieldset>';
            $output .= '<input type="hidden" id="curtain-agent-id" />';
            $output .= '<div id="agent-zone1">';
            $output .= '<div id="agent-zone1-left" style="display:inline-block; width:45%; margin-right:10px;">';
            $output .= '<label for="curtain-agent-number">Agent Number</label>';
            $output .= '<input type="text" id="curtain-agent-number" />';
            $output .= '</div>';
            $output .= '<div id="agent-zone1-right" style="display:inline-block; width:45%;">';
            $output .= '<label for="curtain-agent-password">Agent Password</label>';
            $output .= '<input type="text" id="curtain-agent-password" />';
            $output .= '</div>';
            $output .= '</div>';

            $output .= '<label for="curtain-agent-name">Agent Name</label>';
            $output .= '<input type="text" id="curtain-agent-name" size="38" />';

            $output .= '<div id="agent-zone2">';
            $output .= '<div id="agent-zone2-left" style="display:inline-block; width:45%; margin-right:10px;">';
            $output .= '<label for="curtain-agent-contact1">Contact</label>';
            $output .= '<input type="text" id="curtain-agent-contact1" />';
            $output .= '</div>';
            $output .= '<div id="agent-zone2-right" style="display:inline-block; width:45%;">';
            $output .= '<label for="curtain-agent-phone1">Phone</label>';
            $output .= '<input type="text" id="curtain-agent-phone1" />';
            $output .= '</div>';
            $output .= '</div>';

            $output .= '<label for="curtain-agent-address">Agent Address</label>';
            $output .= '<input type="text" id="curtain-agent-address" size="38" />';
            $output .= '</fieldset>';
            $output .= '</div>';

            if( isset($_GET['_edit']) ) {
                $_id = $_GET['_edit'];
                $row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}curtain_agents WHERE curtain_agent_id={$_id}", OBJECT );
                $output .= '<div id="dialog" title="Curtain agent update">';
                $output .= '<form method="post">';
                $output .= '<fieldset>';
                $output .= '<input type="hidden" value="'.$row->curtain_agent_id.'" name="_curtain_agent_id">';
                $output .= '<label for="agent-number">Agent Number</label>';
                $output .= '<input type="text" name="_agent_number" value="'.$row->agent_number.'" id="agent-number" class="text ui-widget-content ui-corner-all">';
                $output .= '<label for="agent-password">Agent Password</label>';
                $output .= '<input type="text" name="_agent_password" value="'.$row->agent_password.'" id="agent-password" class="text ui-widget-content ui-corner-all">';
                $output .= '<label for="agent-name">Agent Name</label>';
                $output .= '<input type="text" name="_agent_name" value="'.$row->agent_name.'" id="agent-name" class="text ui-widget-content ui-corner-all">';
                $output .= '<label for="contact1">Contact</label>';
                $output .= '<input type="text" name="_contact1" value="'.$row->contact1.'" id="contact1" class="text ui-widget-content ui-corner-all">';
                $output .= '<label for="phone1">Phone</label>';
                $output .= '<input type="text" name="_phone1" value="'.$row->phone1.'" id="phone1" class="text ui-widget-content ui-corner-all">';
                $output .= '</fieldset>';
                $output .= '<input class="wp-block-button__link" type="submit" value="Update" name="_update">';
                $output .= '</form>';
                $output .= '</div>';
            }

            if( isset($_POST['_add']) ) {
                $output .= '<div id="dialog" title="Create new agent">';
                $output .= '<form method="post">';
                $output .= '<fieldset>';
                $output .= '<label for="agent-number">Agent Number</label>';
                $output .= '<input type="text" name="_agent_number" id="agent-number" class="text ui-widget-content ui-corner-all">';
                $output .= '<label for="agent-password">Agent Password</label>';
                $output .= '<input type="text" name="_agent_password" id="agent-password" class="text ui-widget-content ui-corner-all">';
                $output .= '<label for="agent-name">Agent Name</label>';
                $output .= '<input type="text" name="_agent_name" id="agent-name" class="text ui-widget-content ui-corner-all"';
                $output .= '<label for="contact1">Contact</label>';
                $output .= '<input type="text" name="_contact1" id="contact1" class="text ui-widget-content ui-corner-all"';
                $output .= '<label for="phone1">Phone</label>';
                $output .= '<input type="text" name="_phone1" id="phone1" class="text ui-widget-content ui-corner-all"';
                $output .= '</fieldset>';
                $output .= '<input class="wp-block-button__link" type="submit" value="Create" name="_create">';
                $output .= '</form>';
                $output .= '</div>';
            }
            return $output;
        }

        function agent_dialog_get_data() {
            global $wpdb;
            $_id = $_POST['_id'];
            $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}curtain_agents WHERE curtain_agent_id = %d", $_id ), OBJECT );
            $response = array();
            $response["curtain_agent_number"] = $row->agent_number;
            $response["curtain_agent_password"] = $row->agent_password;
            $response["curtain_agent_name"] = $row->agent_name;
            $response["curtain_agent_contact1"] = $row->contact1;
            $response["curtain_agent_phone1"] = $row->phone1;
            $response["curtain_agent_address"] = $row->agent_address;
            echo json_encode( $response );
            wp_die();
        }

        function agent_dialog_save_data() {
            if( $_POST['_curtain_agent_id']=='' ) {
                $this->insert_curtain_agent(
                    array(
                        'agent_number'=>$_POST['_curtain_agent_number'],
                        'agent_password'=>$_POST['_curtain_agent_password'],
                        'agent_name'=>$_POST['_curtain_agent_name'],
                        'contact1'=>$_POST['_curtain_agent_contact1'],
                        'phone1'=>$_POST['_curtain_agent_phone1'],
                        'agent_address'=>$_POST['_curtain_agent_address'],
                    )
                );
            } else {
                $this->update_curtain_agents(
                    array(
                        'agent_number'=>$_POST['_curtain_agent_number'],
                        'agent_password'=>$_POST['_curtain_agent_password'],
                        'agent_name'=>$_POST['_curtain_agent_name'],
                        'contact1'=>$_POST['_curtain_agent_contact1'],
                        'phone1'=>$_POST['_curtain_agent_phone1'],
                        'agent_address'=>$_POST['_curtain_agent_address'],
                    ),
                    array(
                        'curtain_agent_id'=>$_POST['_curtain_agent_id']
                    )
                );
            }

            $response = array();
            echo json_encode( $response );
            wp_die();
        }

        public function insert_curtain_agent($data=[]) {
            global $wpdb;
            $table = $wpdb->prefix.'curtain_agents';
            $data['create_timestamp'] = time();
            $data['update_timestamp'] = time();
            $wpdb->insert($table, $data);        
            return $wpdb->insert_id;
        }

        public function update_curtain_agents($data=[], $where=[]) {
            global $wpdb;
            $table = $wpdb->prefix.'curtain_agents';
            $data['update_timestamp'] = time();
            $wpdb->update($table, $data, $where);
        }

        public function delete_curtain_agents($where=[]) {
            global $wpdb;
            $table = $wpdb->prefix.'curtain_agents';
            $wpdb->delete($table, $where);
        }

        public function insert_agent_operator($data=[]) {
            global $wpdb;
            $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}agent_operators WHERE curtain_agent_id = %d AND curtain_user_id = %d", $data['curtain_agent_id'], $data['curtain_user_id'] ), OBJECT );
            if (is_null($row) || !empty($wpdb->last_error)) {
                $table = $wpdb->prefix.'agent_operators';
                $data['create_timestamp'] = time();
                $data['update_timestamp'] = time();
                $wpdb->insert($table, $data);        
                return $wpdb->insert_id;    
            } else {
                return $row->agent_operator_id;
            }
        }

        public function delete_agent_operators($where=[]) {
            global $wpdb;
            $table = $wpdb->prefix.'agent_operators';
            $wpdb->delete($table, $where);
        }

        public function get_id( $_name='' ) {
            global $wpdb;
            $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}curtain_agents WHERE agent_number = %s OR agent_name = %s", $_name, $_name ), OBJECT );
            return $row->curtain_agent_id;
        }

        public function get_agent_by_user( $_id=0 ) {
            global $wpdb;
            $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}agent_operators WHERE curtain_user_id = %d", $_id ), OBJECT );
            return $row->curtain_agent_id;
        }

        public function get_name( $_id=0 ) {
            global $wpdb;
            $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}curtain_agents WHERE curtain_agent_id = %d", $_id ), OBJECT );
            return $row->agent_name.'('.$row->agent_number.')';
        }

        public function get_contact( $_id=0 ) {
            global $wpdb;
            $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}curtain_agents WHERE curtain_agent_id = %d", $_id ), OBJECT );
            return $row->contact1;
        }

        public function get_phone( $_id=0 ) {
            global $wpdb;
            $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}curtain_agents WHERE curtain_agent_id = %d", $_id ), OBJECT );
            return $row->phone1;
        }

        public function get_address( $_id=0 ) {
            global $wpdb;
            $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}curtain_agents WHERE curtain_agent_id = %d", $_id ), OBJECT );
            return $row->agent_address;
        }

        public function get_name_by_no( $_no='' ) {
            global $wpdb;
            $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}curtain_agents WHERE agent_number = %s", $_no ), OBJECT );
            return $row->agent_name.'('.$row->agent_number.')';
        }

        public function select_options( $_id=0 ) {
            global $wpdb;
            $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}curtain_agents", OBJECT );
            $output = '<option value="0">-- Select an option --</option>';
            foreach ($results as $result) {
                if ( $result->curtain_agent_id == $_id ) {
                    $output .= '<option value="'.$result->curtain_agent_id.'" selected>';
                } else {
                    $output .= '<option value="'.$result->curtain_agent_id.'">';
                }
                $output .= $result->agent_name.'('.$result->agent_number.')';
                $output .= '</option>';        
            }
            $output .= '<option value="0">-- Remove this --</option>';
            return $output;    
        }

        public function create_tables() {
            global $wpdb;
            $charset_collate = $wpdb->get_charset_collate();
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
            $sql = "CREATE TABLE `{$wpdb->prefix}curtain_agents` (
                curtain_agent_id int NOT NULL AUTO_INCREMENT,
                agent_number varchar(5) UNIQUE,
                agent_password varchar(20),
                agent_name varchar(50),
                agent_address varchar(250),
                contact1 varchar(20),
                phone1 varchar(20),
                contact2 varchar(20),
                phone2 varchar(20),
                create_timestamp int(10),
                update_timestamp int(10),
                PRIMARY KEY (curtain_agent_id)
            ) $charset_collate;";
            dbDelta($sql);            

            $sql = "CREATE TABLE `{$wpdb->prefix}agent_operators` (
                agent_operator_id int NOT NULL AUTO_INCREMENT,
                curtain_agent_id int NOT NULL,
                curtain_user_id int NOT NULL,
                create_timestamp int(10),
                update_timestamp int(10),
                PRIMARY KEY (agent_operator_id)
            ) $charset_collate;";
            dbDelta($sql);            
        }
    }
    $my_class = new curtain_agents();
}