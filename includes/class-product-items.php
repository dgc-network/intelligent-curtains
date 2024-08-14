<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('product_items')) {
    class product_items {

        public function __construct() {

            add_shortcode( 'product-item-list', array( $this, 'display_shortcode' ) );
            add_action( 'init', array( $this, 'register_product_item_post_type' ) );
            add_action( 'wp_ajax_get_product_item_dialog_data', array( $this, 'get_product_item_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_get_product_item_dialog_data', array( $this, 'get_product_item_dialog_data' ) );
            add_action( 'wp_ajax_set_product_item_dialog_data', array( $this, 'set_product_item_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_set_product_item_dialog_data', array( $this, 'set_product_item_dialog_data' ) );
            add_action( 'wp_ajax_del_product_item_dialog_data', array( $this, 'del_product_item_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_del_product_item_dialog_data', array( $this, 'del_product_item_dialog_data' ) );

        }

        function register_product_item_post_type() {
            $labels = array(
                'menu_name'     => _x('product-item', 'admin menu', 'textdomain'),
            );
            $args = array(
                'labels'        => $labels,
                'public'        => true,
                //'show_in_menu'  => false,
            );
            register_post_type( 'product-item', $args );
        }

        function display_shortcode() {
            if (current_user_can('administrator')) {
                if (isset($_GET['_copy_curtain_model_to_product_item'])) $this->copy_curtain_model_to_product_item();
                if (isset($_GET['_copy_curtain_spec_to_product_item'])) $this->copy_curtain_spec_to_product_item();

                $this->display_product_item_list();
            } else {
                ?>
                <div style="text-align:center;">
                    <h4><?php echo __( '你沒有讀取目前網頁的權限!', 'your-text-domain' );?></h4>
                </div>
                <?php
            }
        }

        function display_product_item_list() {
            $curtain_categories = new curtain_categories();
            ?>
            <div class="ui-widget" id="result-container">
            <h2 style="display:inline;"><?php echo __( 'Product items', 'your-text-domain' );?></h2>
            <fieldset>
                <div style="display:flex; justify-content:space-between; margin:5px;">
                    <div>
                        <select id="select-category-in-product"><?php echo $curtain_categories->select_curtain_category_options($_GET['_category']);?></select>
                    </div>
                    <div style="text-align:right; display:flex;">
                        <input type="text" id="search-product" style="display:inline" placeholder="Search..." />
                    </div>
                </div>
        
                <table class="ui-widget" style="width:100%;">
                    <thead>
                        <tr>
                            <th><?php echo __( 'Product', 'your-text-domain' );?></th>
                            <th><?php echo __( 'Description', 'your-text-domain' );?></th>
                            <th><?php echo __( 'Price', 'your-text-domain' );?></th>
                            <th><?php echo __( 'Vendor', 'your-text-domain' );?></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    // Define the custom pagination parameters
                    $posts_per_page = get_option('operation_row_counts');
                    $current_page = max(1, get_query_var('paged')); // Get the current page number
                    $query = $this->retrieve_product_item_data($current_page);
                    $total_posts = $query->found_posts;
                    $total_pages = ceil($total_posts / $posts_per_page); // Calculate the total number of pages
        
                    if ($query->have_posts()) :
                        while ($query->have_posts()) : $query->the_post();
                            $product_item_title = get_the_title();
                            $product_item_content = get_post_field('post_content', get_the_ID());
                            $product_item_price = get_post_meta(get_the_ID(), 'product_item_price', true);
                            $curtain_category_id = get_post_meta(get_the_ID(), 'curtain_category_id', true);
                            $product_item_vendor = get_post_meta(get_the_ID(), 'product_item_vendor', true);
                            $curtain_agent_name = get_post_meta($product_item_vendor, 'curtain_agent_name', true);
                            ?>
                            <tr id="edit-product-item-<?php the_ID();?>">
                                <td style="text-align:center;"><?php echo esc_html(get_the_title());?></td>
                                <td><?php echo esc_html($product_item_content);?></td>
                                <td style="text-align:center;"><?php echo esc_html($product_item_price);?></td>
                                <td style="text-align:center;"><?php echo esc_html($curtain_agent_name);?></td>
                            </tr>
                            <?php
                        endwhile;
                        wp_reset_postdata();
                    endif;
                    ?>
                    </tbody>
                </table>
                <div id="new-product-item" class="custom-button" style="border:solid; margin:3px; text-align:center; border-radius:5px; font-size:small;">+</div>
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
            <div id="product-item-dialog" title="Product dialog"></div>
            <?php
        }

        function retrieve_product_item_data($current_page = 1) {
            // Define the custom pagination parameters
            $posts_per_page = get_option('operation_row_counts');
        
            $search_query = sanitize_text_field($_GET['_search']);
            $select_category = sanitize_text_field($_GET['_category']);
            $category_filter = array(
                'key'     => 'curtain_category_id',
                'value'   => $select_category,
                'compare' => '=',
            );
        
            $args = array(
                'post_type'      => 'product-item',
                'posts_per_page' => $posts_per_page,
                'paged'          => $current_page,
                's'              => $search_query,  
                'meta_query'     => array(
                    ($select_category) ? $category_filter : '',
                ),
                'orderby'        => 'title', // Sort by title
                'order'          => 'ASC',
            );        
        
            $query = new WP_Query($args);
            return $query;
        }

        function display_product_item_dialog($product_item_id=false) {            
            $curtain_categories = new curtain_categories();
            $curtain_agents = new curtain_agents();
            $product_item_title = get_the_title($product_item_id);
            $product_item_content = get_post_field('post_content', $product_item_id);
            $product_item_price = get_post_meta($product_item_id, 'product_item_price', true);
            $product_item_vendor = get_post_meta($product_item_id, 'product_item_vendor', true);
            $curtain_category_id = get_post_meta($product_item_id, 'curtain_category_id', true);
            $is_curtain_model = get_post_meta($product_item_id, 'is_curtain_model', true);
            $is_curtain_model_checked = ($is_curtain_model == 1) ? 'checked' : '';
            $is_specification = get_post_meta($product_item_id, 'is_specification', true);
            $is_specification_checked = ($is_specification == 1) ? 'checked' : '';
            ob_start();
            ?>
            <fieldset>
                <input type="hidden" id="product-item-id" value="<?php echo esc_attr($product_item_id);?>" />
                <label for="product-item-title"><?php echo __( 'Title', 'your-text-domain' );?></label>
                <input type="text" id="product-item-title" value="<?php echo esc_html($product_item_title);?>" class="text ui-widget-content ui-corner-all" />
                <label for="product-item-content"><?php echo __( 'Content', 'your-text-domain' );?></label>
                <input type="text" id="product-item-content" value="<?php echo esc_html($product_item_content);?>" class="text ui-widget-content ui-corner-all" />
                <label for="product-item-price"><?php echo __( 'Price', 'your-text-domain' );?></label>
                <input type="text" id="product-item-price" value="<?php echo esc_html($product_item_price);?>" class="text ui-widget-content ui-corner-all" />
                <label for="curtain-category-id"><?php echo __( 'Category', 'your-text-domain' );?></label>
                <select id="curtain-category-id" class="select ui-widget-content ui-corner-all"><?php echo $curtain_categories->select_curtain_category_options($curtain_category_id);?></select>
                <label for="product-item-vendor"><?php echo __( 'Vendor', 'your-text-domain' );?></label>
                <select id="product-item-vendor" class="select ui-widget-content ui-corner-all"><?php echo $curtain_agents->select_curtain_agent_options($product_item_vendor);?></select>
                <input type="checkbox" id="is-curtain-model" class="checkbox ui-widget-content ui-corner-all" style="display:inline-block; width:5%;" <?php echo $is_curtain_model_checked;?> /> Is curtain model.<br>
                <input type="checkbox" id="is-specification" class="checkbox ui-widget-content ui-corner-all" style="display:inline-block; width:5%;" <?php echo $is_specification_checked;?> /> Is specification.<br>
            </fieldset>
            <?php
            return ob_get_clean();
        }

        function get_product_item_dialog_data() {
            $response = array();
            if (isset($_POST['_product_item_id'])) {
                $product_item_id = sanitize_text_field($_POST['_product_item_id']);
                $response['html_contain'] = $this->display_product_item_dialog($product_item_id);
            } else {
                $response['html_contain'] = 'Invalid AJAX request!';
            }
            wp_send_json($response);
        }

        function set_product_item_dialog_data() {
            $response = array();
            if( isset($_POST['_product_item_id']) ) {
                // Update the meta data
                $product_item_id = sanitize_text_field($_POST['_product_item_id']);
                update_post_meta( $product_item_id, 'curtain_category_id', sanitize_text_field($_POST['_curtain_category_id']));
                update_post_meta( $product_item_id, 'product_item_price', sanitize_text_field($_POST['_product_item_price']));
                update_post_meta( $product_item_id, 'product_item_vendor', sanitize_text_field($_POST['_product_item_vendor']));
                update_post_meta( $product_item_id, 'is_curtain_model', sanitize_text_field($_POST['_is_curtain_model']));
                update_post_meta( $product_item_id, 'is_specification', sanitize_text_field($_POST['_is_specification']));
                // Update the post title
                $updated_post = array(
                    'ID'         => $product_item_id,
                    'post_title' => sanitize_text_field($_POST['_product_item_title']),
                    'post_content' => sanitize_text_field($_POST['_product_item_content']),
                );
                wp_update_post($updated_post);
            } else {
                $current_user_id = get_current_user_id();
                $new_post = array(
                    'post_title'    => 'New product',
                    'post_content'  => 'Your post content goes here.',
                    'post_status'   => 'publish',
                    'post_author'   => $current_user_id,
                    'post_type'     => 'product-item',
                );    
                $post_id = wp_insert_post($new_post);
            }
            wp_send_json($response);
        }

        function del_product_item_dialog_data() {
            $response = array();
            if( isset($_POST['_product_item_id']) ) {
                $product_item_id = sanitize_text_field($_POST['_product_item_id']);
                wp_delete_post($product_item_id, true);
            }
            wp_send_json($response);
        }

        function select_product_item_options($selected_option=0, $curtain_category_id=0) {
            $args = array(
                'post_type'      => 'product-item',
                'posts_per_page' => -1,
                'meta_query'     => array(
                    array(
                        'key'   => 'curtain_category_id',
                        'value' => $curtain_category_id,
                    ),
                ),
            );
            $query = new WP_Query($args);
        
            $options = '<option value="">Select model</option>';
            while ($query->have_posts()) : $query->the_post();
                $selected = ($selected_option == get_the_ID()) ? 'selected' : '';
                $option = get_the_content().'('.get_the_title().')';
                $options .= '<option value="' . esc_attr(get_the_ID()) . '" '.$selected.' />' . esc_html($option) . '</option>';
            endwhile;
            wp_reset_postdata();
            return $options;
        }

        function copy_curtain_model_to_product_item() {
            $args = array(
                'post_type'      => 'curtain-model',
                'posts_per_page' => -1,
            );
        
            $query = new WP_Query($args);
        
            // Create an array to map old parent_category values to new iso-category IDs
            $category_mapping = array();
        
            if ($query->have_posts()) {
                while ($query->have_posts()) {
                    $query->the_post();
        
                    // Get the current post ID and data
                    $current_post_id = get_the_ID();
                    $current_post    = get_post($current_post_id);
        
                    // Prepare the new post data
                    $new_post = array(
                        'post_title'    => $current_post->post_title,
                        'post_content'  => $current_post->post_content,
                        'post_status'   => 'publish', // or $current_post->post_status if you want to keep the same status
                        'post_author'   => $current_post->post_author,
                        'post_type'     => 'product-item',
                        'post_date'     => $current_post->post_date,
                        'post_date_gmt' => $current_post->post_date_gmt,
                    );
        
                    // Insert the new post and get the new post ID
                    $new_post_id = wp_insert_post($new_post);
        
                    if ($new_post_id) {
                        // Get all meta data for the current post
                        $post_meta = get_post_meta($current_post_id);
        
                        // Copy each meta field to the new post
                        foreach ($post_meta as $meta_key => $meta_values) {
                            foreach ($meta_values as $meta_value) {
                                add_post_meta($new_post_id, $meta_key, $meta_value);
                            }
                        }
        
                        $curtain_model_price = get_post_meta($new_post_id, 'curtain_model_price', true);
                        update_post_meta($new_post_id, 'product_item_price', $curtain_model_price);

                        // Map the old parent_category value to the new iso-category post ID
                        $category_mapping[$current_post_id] = $new_post_id;
                    }
                }
        
                // Reset post data
                wp_reset_postdata();
            }
        }

        function copy_curtain_spec_to_product_item() {
            $args = array(
                'post_type'      => 'curtain-spec',
                'posts_per_page' => -1,
            );
        
            $query = new WP_Query($args);
        
            // Create an array to map old parent_category values to new iso-category IDs
            $category_mapping = array();
        
            if ($query->have_posts()) {
                while ($query->have_posts()) {
                    $query->the_post();
        
                    // Get the current post ID and data
                    $current_post_id = get_the_ID();
                    $current_post    = get_post($current_post_id);
        
                    // Prepare the new post data
                    $new_post = array(
                        'post_title'    => $current_post->post_title,
                        'post_content'  => $current_post->post_content,
                        'post_status'   => 'publish', // or $current_post->post_status if you want to keep the same status
                        'post_author'   => $current_post->post_author,
                        'post_type'     => 'product-item',
                        'post_date'     => $current_post->post_date,
                        'post_date_gmt' => $current_post->post_date_gmt,
                    );
        
                    // Insert the new post and get the new post ID
                    $new_post_id = wp_insert_post($new_post);
        
                    if ($new_post_id) {
                        // Get all meta data for the current post
                        $post_meta = get_post_meta($current_post_id);
        
                        // Copy each meta field to the new post
                        foreach ($post_meta as $meta_key => $meta_values) {
                            foreach ($meta_values as $meta_value) {
                                add_post_meta($new_post_id, $meta_key, $meta_value);
                            }
                        }
        
                        $curtain_specification_price = get_post_meta($new_post_id, 'curtain_specification_price', true);
                        update_post_meta($new_post_id, 'product_item_price', $curtain_specification_price);

                        // Map the old parent_category value to the new iso-category post ID
                        $category_mapping[$current_post_id] = $new_post_id;
                    }
                }
        
                // Reset post data
                wp_reset_postdata();
            }
        }

    }
    $models_class = new product_items();
}