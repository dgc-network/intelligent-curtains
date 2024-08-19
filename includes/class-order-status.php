<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('order_status')) {
    class order_status {

        public function __construct() {

            add_shortcode( 'order-status-list', array( $this, 'display_shortcode' ) );
            //add_action( 'init', array( $this, 'register_order_status_post_type' ) );

            add_action( 'wp_ajax_get_order_status_dialog_data', array( $this, 'get_order_status_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_get_order_status_dialog_data', array( $this, 'get_order_status_dialog_data' ) );
            add_action( 'wp_ajax_set_order_status_dialog_data', array( $this, 'set_order_status_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_set_order_status_dialog_data', array( $this, 'set_order_status_dialog_data' ) );
            add_action( 'wp_ajax_del_order_status_dialog_data', array( $this, 'del_order_status_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_del_order_status_dialog_data', array( $this, 'del_order_status_dialog_data' ) );

        }

        function register_order_status_post_type() {
            $labels = array(
                'menu_name'     => _x('order-status', 'admin menu', 'textdomain'),
            );
            $args = array(
                'labels'        => $labels,
                'public'        => true,
                //'show_in_menu'  => false,
            );
            register_post_type( 'order-status', $args );
        }

        function display_shortcode() {
            if (current_user_can('administrator')) {
                $this->display_order_status_list();
            } else {
                ?>
                <div style="text-align:center;">
                    <h4><?php echo __( '你沒有讀取目前網頁的權限!', 'your-text-domain' );?></h4>
                </div>
                <?php
            }
        }

        function display_order_status_list() {
            ?>
            <div class="ui-widget" id="result-container">
            <h2 style="display:inline;"><?php echo __( '訂單狀態', 'your-text-domain' );?></h2>
            <fieldset>
                <div style="display:flex; justify-content:space-between; margin:5px;">
                    <div>
                    </div>
                    <div style="text-align:right; display:flex;">
                        <input type="text" id="search-status" style="display:inline" placeholder="Search..." />
                    </div>
                </div>
        
                <table class="ui-widget" style="width:100%;">
                    <thead>
                        <tr>
                            <th><?php echo __( 'Code', 'your-text-domain' );?></th>
                            <th><?php echo __( 'Title', 'your-text-domain' );?></th>
                            <th><?php echo __( 'Description', 'your-text-domain' );?></th>
                            <th><?php echo __( 'Action', 'your-text-domain' );?></th>
                            <th><?php echo __( 'Color', 'your-text-domain' );?></th>
                            <th><?php echo __( 'Next', 'your-text-domain' );?></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    // Define the custom pagination parameters
                    $posts_per_page = get_option('operation_row_counts');
                    $current_page = max(1, get_query_var('paged')); // Get the current page number
                    $query = $this->retrieve_order_status_data($current_page);
                    $total_posts = $query->found_posts;
                    $total_pages = ceil($total_posts / $posts_per_page); // Calculate the total number of pages
        
                    if ($query->have_posts()) :
                        while ($query->have_posts()) : $query->the_post();
                            $status_code = get_post_meta(get_the_ID(), 'status_code', true);
                            $status_title = get_the_title();
                            $status_description = get_the_content();
                            $status_action = get_post_meta(get_the_ID(), 'status_action', true);
                            $status_color = get_post_meta(get_the_ID(), 'status_color', true);
                            $next_status = get_post_meta(get_the_ID(), 'next_status', true);
                            ?>
                            <tr id="edit-order-status-<?php the_ID();?>">
                                <td style="text-align:center;"><?php echo esc_html($status_code);?></td>
                                <td style="text-align:center;"><?php echo esc_html($status_title);?></td>
                                <td><?php echo esc_html($status_description);?></td>
                                <td style="text-align:center;"><?php echo esc_html($status_action);?></td>
                                <td style="text-align:center;"><?php echo esc_html($status_color);?></td>
                                <td style="text-align:center;"><?php echo esc_html($next_status);?></td>
                            </tr>
                            <?php
                        endwhile;
                        wp_reset_postdata();
                    endif;
                    ?>
                    </tbody>
                </table>
                <div id="new-order-status" class="custom-button" style="border:solid; margin:3px; text-align:center; border-radius:5px; font-size:small;">+</div>
                <div class="pagination">
                    <?php
                    // Display pagination links
                    if ($current_page > 1) echo '<span class="custom-button"><a href="' . esc_url(get_pagenum_link($current_page - 1)) . '"> < </a></span>';
                    echo '<span class="page-numbers">' . sprintf(__('Page %d of %d', 'textdomain'), $current_page, $total_pages) . '</span>';
                    if ($current_page < $total_pages) echo '<span class="custom-button"><a href="' . esc_url(get_pagenum_link($current_page + 1)) . '"> > </a></span>';
                    ?>
                </div>
            </fieldset>
            </div>
            <div id="order-status-dialog" title="Status dialog"></div>            
            <?php
        }

        function retrieve_order_status_data($current_page = 1) {
            // Define the custom pagination parameters
            $posts_per_page = get_option('operation_row_counts');
            $search_query = sanitize_text_field($_GET['_search']);
            $args = array(
                'post_type'      => 'order-status',
                'posts_per_page' => $posts_per_page,
                'paged'          => $current_page,
                's'              => $search_query,                
                'meta_key'       => 'status_code', // Specify the meta key to order by
                'orderby'        => 'meta_value',  // Order by the meta value
                'order'          => 'ASC',         // Order direction (ASC or DESC)
            );        
            $query = new WP_Query($args);
            return $query;
        }
        
        function display_order_status_dialog($order_status_id=false) {            
            ob_start();
            $status_code = get_post_meta($order_status_id, 'status_code', true);
            $status_title = get_the_title($order_status_id);
            $status_description = get_post_field('post_content', $order_status_id);
            $status_action = get_post_meta($order_status_id, 'status_action', true);
            $status_color = get_post_meta($order_status_id, 'status_color', true);
            $next_status = get_post_meta($order_status_id, 'next_status', true);
            ?>
            <fieldset>
                <input type="hidden" id="order-status-id" value="<?php echo esc_attr($order_status_id);?>" />
                <label for="status-code"><?php echo __( '代碼', 'your-text-domain' );?></label>
                <input type="text" id="status-code" value="<?php echo esc_html($status_code);?>" class="text ui-widget-content ui-corner-all" />
                <label for="status-title"><?php echo __( '標題', 'your-text-domain' );?></label>
                <input type="text" id="status-title" value="<?php echo esc_html($status_title);?>" class="text ui-widget-content ui-corner-all" />
                <label for="status-description"><?php echo __( '說明', 'your-text-domain' );?></label>
                <textarea id="status-description" rows="3" style="width:100%;"><?php echo $status_description;?></textarea>
                <label for="status-action"><?php echo __( '執行', 'your-text-domain' );?></label>
                <input type="text" id="status-action" value="<?php echo esc_html($status_action);?>" class="text ui-widget-content ui-corner-all" />
                <label for="status-color"><?php echo __( '顏色', 'your-text-domain' );?></label>
                <input type="text" id="status-color" value="<?php echo esc_html($status_color);?>" class="text ui-widget-content ui-corner-all" />
                <label for="next-status"><?php echo __( 'Next代碼', 'your-text-domain' );?></label>
                <input type="text" id="next-status" value="<?php echo esc_html($next_status);?>" class="text ui-widget-content ui-corner-all" />
            </fieldset>
            <?php
            return ob_get_clean();
        }

        function get_order_status_dialog_data() {
            $response = array();
            $order_status_id = sanitize_text_field($_POST['_order_status_id']);
            $response['html_contain'] = $this->display_order_status_dialog($order_status_id);
            wp_send_json($response);
        }

        function set_order_status_dialog_data() {
            $response = array();
            if( isset($_POST['_order_status_id']) ) {
                // Update the meta data
                $order_status_id = sanitize_text_field($_POST['_order_status_id']);
                update_post_meta( $order_status_id, 'status_code', sanitize_text_field($_POST['_status_code']));
                update_post_meta( $order_status_id, 'status_action', sanitize_text_field($_POST['_status_action']));
                update_post_meta( $order_status_id, 'status_color', sanitize_text_field($_POST['_status_color']));
                update_post_meta( $order_status_id, 'next_status', sanitize_text_field($_POST['_next_status']));
                // Update the post title
                if (isset($_POST['_status_title'])) {
                    $updated_post = array(
                        'ID'         => $order_status_id,
                        'post_title' => sanitize_text_field($_POST['_status_title']),
                        'post_content' => sanitize_text_field($_POST['_status_description']),
                    );
                    wp_update_post($updated_post);
                }
            } else {
                $current_user_id = get_current_user_id();
                $new_post = array(
                    'post_title'    => 'New status',
                    'post_content'  => 'Your post content goes here.',
                    'post_status'   => 'publish',
                    'post_author'   => $current_user_id,
                    'post_type'     => 'order-status',
                );    
                $post_id = wp_insert_post($new_post);
                update_post_meta( $post_id, 'status_code', 'order0');
            }
            wp_send_json($response);
        }

        function del_order_status_dialog_data() {
            $response = array();
            $order_status_id = sanitize_text_field($_POST['_order_status_id']);
            wp_delete_post($order_status_id, true);
            wp_send_json($response);
        }

        function select_order_status_options($selected_option=0) {
            $args = array(
                'post_type'      => 'order-status',
                'posts_per_page' => -1,
                'meta_key'       => 'status_code', // Meta key for sorting
                'orderby'        => 'meta_value', // Sort by meta value
                'order'          => 'ASC', // Sorting order (ascending)
            );
            $query = new WP_Query($args);

            $options = '<option value="">Select status</option>';
            while ($query->have_posts()) : $query->the_post();
                $selected = ($selected_option == get_the_ID()) ? 'selected' : '';
                $status_code = get_post_meta(get_the_ID(), 'status_code', true);
                $status_title = get_the_title();
                $status_content = get_the_content();
                $status_title = $status_content.'('.$status_code.')';
                $options .= '<option value="' . esc_attr(get_the_ID()) . '" '.$selected.' />' . esc_html($status_title) . '</option>';
            endwhile;
            wp_reset_postdata();
            return $options;
        }        
    }
    $order_status = new order_status();
}