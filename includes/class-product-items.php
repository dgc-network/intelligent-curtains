<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('product_items')) {
    class product_items {

        public function __construct() {
            add_shortcode( 'product-item-list', array( $this, 'display_shortcode' ) );
            //add_action( 'init', array( $this, 'register_product_item_post_type' ) );
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
            );
            register_post_type( 'product-item', $args );
        }

        function display_shortcode() {
            if (current_user_can('administrator')) {
                $this->display_product_item_list();
            } else {
                ?>
                <div style="text-align:center;">
                    <h4><?php echo __( '你沒有讀取目前網頁的權限!', 'text-domain' );?></h4>
                </div>
                <?php
            }
        }

        function display_product_item_list() {
            $curtain_categories = new curtain_categories();
            ?>
            <div class="ui-widget" id="result-container">
            <h2 style="display:inline;"><?php echo __( 'Product items', 'text-domain' );?></h2>
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
                            <th><?php echo __( 'Product', 'text-domain' );?></th>
                            <th><?php echo __( 'Description', 'text-domain' );?></th>
                            <th><?php echo __( 'Price', 'text-domain' );?></th>
                            <th><?php echo __( 'Vendor', 'text-domain' );?></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    // Define the custom pagination parameters
                    $posts_per_page = get_option('operation_row_counts');
                    $paged = max(1, get_query_var('paged')); // Get the current page number
                    $query = $this->retrieve_product_item_data($paged);
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
                    if ($paged > 1) echo '<span class="custom-button"><a href="' . esc_url(get_pagenum_link($paged - 1)) . '"> < </a></span>';
                    echo '<span class="page-numbers">' . sprintf(__('Page %d of %d', 'textdomain'), $paged, $total_pages) . '</span>';
                    if ($paged < $total_pages) echo '<span class="custom-button"><a href="' . esc_url(get_pagenum_link($paged + 1)) . '"> > </a></span>';
                    ?>
                </div>
            </fieldset>
            </div>
            <div id="product-item-dialog" title="Product dialog"></div>
            <?php
        }

        function retrieve_product_item_data($paged = 1) {
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
                'paged'          => $paged,
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
            ob_start();
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
            ?>
            <fieldset>
                <input type="hidden" id="product-item-id" value="<?php echo esc_attr($product_item_id);?>" />
                <label for="product-item-title"><?php echo __( 'Title', 'text-domain' );?></label>
                <input type="text" id="product-item-title" value="<?php echo esc_html($product_item_title);?>" class="text ui-widget-content ui-corner-all" />
                <label for="product-item-content"><?php echo __( 'Content', 'text-domain' );?></label>
                <input type="text" id="product-item-content" value="<?php echo esc_html($product_item_content);?>" class="text ui-widget-content ui-corner-all" />
                <label for="product-item-price"><?php echo __( 'Price', 'text-domain' );?></label>
                <input type="text" id="product-item-price" value="<?php echo esc_html($product_item_price);?>" class="text ui-widget-content ui-corner-all" />
                <label for="curtain-category-id"><?php echo __( 'Category', 'text-domain' );?></label>
                <select id="curtain-category-id" class="select ui-widget-content ui-corner-all"><?php echo $curtain_categories->select_curtain_category_options($curtain_category_id);?></select>
                <label for="product-item-vendor"><?php echo __( 'Vendor', 'text-domain' );?></label>
                <select id="product-item-vendor" class="select ui-widget-content ui-corner-all"><?php echo $curtain_agents->select_curtain_agent_options($product_item_vendor);?></select><br>
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
                    'post_content' => $_POST['_product_item_content'],
                );
                wp_update_post($updated_post);
            } else {
                $current_user_id = get_current_user_id();
                $new_post = array(
                    'post_title'    => '-',
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

        function select_product_item_options($selected_option=false, $curtain_category_id=false, $is_specification=false) {
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
            if ($is_specification) {
                $args['meta_query'][]=array(
                    array(
                        'key'   => 'is_specification',
                        'value' => 1,
                    ),
                );    
            } else {
                $args['meta_query'][] = array(
                    'relation' => 'OR',
                    array(
                        'key'     => 'is_specification',
                        'compare' => 'NOT EXISTS',
                    ),
                    array(
                        'key'     => 'is_specification',
                        'value'   => 1,
                        'compare' => '!=',
                    ),
                );
            }
            $query = new WP_Query($args);
        
            $options = '<option value="">Select product</option>';
            while ($query->have_posts()) : $query->the_post();
                $selected = ($selected_option == get_the_ID()) ? 'selected' : '';
                $option = get_the_content().'('.get_the_title().')';
                $options .= '<option value="' . esc_attr(get_the_ID()) . '" '.$selected.' />' . esc_html($option) . '</option>';
            endwhile;
            wp_reset_postdata();
            return $options;
        }
    }
    $models_class = new product_items();
}