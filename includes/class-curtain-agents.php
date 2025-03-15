<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('curtain_agents')) {
    class curtain_agents {

        public function __construct() {
            add_shortcode( 'curtain-agent-list', array( $this, 'display_shortcode' ) );
            //add_action( 'init', array( $this, 'register_curtain_agent_post_type' ) );
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
            );
            register_post_type( 'curtain-agent', $args );
        }

        function display_shortcode() {
            if (current_user_can('administrator')) {
                $this->display_curtain_agent_list();
            } else {
                ?>
                <div style="text-align:center;">
                    <h4><?php echo __( '你沒有讀取目前網頁的權限!', 'textdomain' );?></h4>
                </div>
                <?php
            }
        }

        function display_curtain_agent_list() {
            ?>
            <div class="ui-widget" id="result-container">
            <h2 style="display:inline;"><?php echo __( 'Curtain agents', 'textdomain' );?></h2>
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
                            <th><?php echo __( 'Agent', 'textdomain' );?></th>
                            <th><?php echo __( 'Name', 'textdomain' );?></th>
                            <th><?php echo __( 'Contact', 'textdomain' );?></th>
                            <th><?php echo __( 'Phone', 'textdomain' );?></th>
                            <th><?php echo __( 'Address', 'textdomain' );?></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    // Define the custom pagination parameters
                    $posts_per_page = get_option('operation_row_counts');
                    $paged = max(1, get_query_var('paged')); // Get the current page number
                    $query = $this->retrieve_curtain_agent_data($paged);
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
                <div class="pagination">
                    <?php
                    // Display pagination links
                    if ($paged > 1) echo '<span class="custom-button"><a href="' . esc_url(get_pagenum_link($paged - 1)) . '"> < </a></span>';
                    echo '<span class="page-numbers">' . sprintf(__('Page %d of %d', 'textdomain'), $paged, $total_pages) . '</span>';
                    if ($paged < $total_pages) echo '<span class="custom-button"><a href="' . esc_url(get_pagenum_link($paged + 1)) . '"> > </a></span>';
                    ?>
                </div>
            </fieldset>
            </div>
            <div id="curtain-agent-dialog" title="Agent dialog"></div>            
            <?php
        }

        function retrieve_curtain_agent_data($paged = 1) {
            // Define the arguments for the WP_Query
            $posts_per_page = get_option('operation_row_counts');
            $args = array(
                'post_type'      => 'curtain-agent',
                'posts_per_page' => $posts_per_page,
                'paged'          => $paged,
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
            $order_status = new order_status();
            $curtain_agent_number = get_post_meta($curtain_agent_id, 'curtain_agent_number', true);
            $curtain_agent_name = get_post_meta($curtain_agent_id, 'curtain_agent_name', true);
            $curtain_agent_contact = get_post_meta($curtain_agent_id, 'curtain_agent_contact', true);
            $curtain_agent_phone = get_post_meta($curtain_agent_id, 'curtain_agent_phone', true);
            $curtain_agent_address = get_post_meta($curtain_agent_id, 'curtain_agent_address', true);
            $curtain_agent_status = get_post_meta($curtain_agent_id, 'curtain_agent_status', true);
            ob_start();
            ?>
            <fieldset>
                <input type="hidden" id="curtain-agent-id" value="<?php echo esc_attr($curtain_agent_id);?>" />
                <label for="curtain-agent-number"><?php echo __( 'Number', 'textdomain' );?></label>
                <input type="text" id="curtain-agent-number" value="<?php echo esc_html($curtain_agent_number);?>" class="text ui-widget-content ui-corner-all" />
                <label for="curtain-agent-name"><?php echo __( 'Name', 'textdomain' );?></label>
                <input type="text" id="curtain-agent-name" value="<?php echo esc_html($curtain_agent_name);?>" class="text ui-widget-content ui-corner-all" />
                <label for="curtain-agent-contact"><?php echo __( 'Contact', 'textdomain' );?></label>
                <input type="text" id="curtain-agent-contact" value="<?php echo esc_html($curtain_agent_contact);?>" class="text ui-widget-content ui-corner-all" />
                <label for="curtain-agent-phone"><?php echo __( 'Phone', 'textdomain' );?></label>
                <input type="text" id="curtain-agent-phone" value="<?php echo esc_html($curtain_agent_phone);?>" class="text ui-widget-content ui-corner-all" />
                <label for="curtain-agent-address"><?php echo __( 'Address', 'textdomain' );?></label>
                <input type="text" id="curtain-agent-address" value="<?php echo esc_html($curtain_agent_address);?>" class="text ui-widget-content ui-corner-all" />
                <label for="curtain-agent-status"><?php echo __( 'Status', 'textdomain' );?></label>
                <select id="curtain-agent-status" class="select ui-widget-content ui-corner-all"><?php echo $order_status->select_order_status_options($curtain_agent_status);?></select>
            </fieldset>
            <?php
            return ob_get_clean();
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
                update_post_meta( $curtain_agent_id, 'curtain_agent_status', sanitize_text_field($_POST['_curtain_agent_status']));
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
                update_post_meta( $post_id, 'curtain_agent_number', '-');
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
                $options .= '<option value="' . esc_attr(get_the_ID()) . '" '.$selected.' >' . esc_html($curtain_agent_title) . '</option>';
            endwhile;
            wp_reset_postdata();
            return $options;
        }

        function select_shipping_agent_options($selected_option=0) {
            $order_status = new order_status();
            $order_status_id = $order_status->get_order_status_id_by_code('order03');
            $args = array(
                'post_type'      => 'curtain-agent',
                'posts_per_page' => -1,
                'meta_query' => array(
                    array(
                        'key'   => 'curtain_agent_status',
                        'value' => $order_status_id,
                        //'compare' => '=',
                    ),
                ),
                'meta_key'       => 'curtain_agent_number', // Meta key for sorting
                'orderby'        => 'meta_value', // Sort by meta value
                'order'          => 'ASC', // Sorting order (ascending)
            );
            $query = new WP_Query($args);

            $options = '<option value="">Select delivery</option>';
            while ($query->have_posts()) : $query->the_post();
                $selected = ($selected_option == get_the_ID()) ? 'selected' : '';
                $curtain_agent_number = get_post_meta(get_the_ID(), 'curtain_agent_number', true);
                $curtain_agent_name = get_post_meta(get_the_ID(), 'curtain_agent_name', true);
                $curtain_agent_address = get_post_meta(get_the_ID(), 'curtain_agent_address', true);
                $curtain_agent_title = $curtain_agent_name.'('.$curtain_agent_number.')-'.$curtain_agent_address;
                $options .= '<option value="' . esc_attr(get_the_ID()) . '" '.$selected.' >' . esc_html($curtain_agent_title) . '</option>';
            endwhile;
            wp_reset_postdata();
            return $options;
        }
    }
    $agents_class = new curtain_agents();
}