<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('curtain_categories')) {
    class curtain_categories {

        public function __construct() {
            add_shortcode( 'curtain-category-list', array( $this, 'display_shortcode' ) );
            //add_action( 'init', array( $this, 'register_curtain_category_post_type' ) );
            add_action( 'wp_ajax_get_curtain_category_dialog_data', array( $this, 'get_curtain_category_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_get_curtain_category_dialog_data', array( $this, 'get_curtain_category_dialog_data' ) );
            add_action( 'wp_ajax_set_curtain_category_dialog_data', array( $this, 'set_curtain_category_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_set_curtain_category_dialog_data', array( $this, 'set_curtain_category_dialog_data' ) );
            add_action( 'wp_ajax_del_curtain_category_dialog_data', array( $this, 'del_curtain_category_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_del_curtain_category_dialog_data', array( $this, 'del_curtain_category_dialog_data' ) );
        }

        function register_curtain_category_post_type() {
            $labels = array(
                'menu_name'     => _x('curtain-category', 'admin menu', 'textdomain'),
            );
            $args = array(
                'labels'        => $labels,
                'public'        => true,
            );
            register_post_type( 'curtain-category', $args );
        }

        function display_shortcode() {
            if (current_user_can('administrator')) {
                $this->display_curtain_category_list();
            } else {
                ?>
                <div style="text-align:center;">
                    <h4><?php echo __( '你沒有讀取目前網頁的權限!', 'textdomain' );?></h4>
                </div>
                <?php
            }
        }

        function display_curtain_category_list() {
            ?>
            <div class="ui-widget" id="result-container">
            <h2 style="display:inline;"><?php echo __( '產品類別', 'textdomain' );?></h2>
            <fieldset>
                <div style="display:flex; justify-content:space-between; margin:5px;">
                    <div>
                    </div>
                    <div style="text-align:right; display:flex;">
                        <input type="text" id="search-category" style="display:inline" placeholder="Search..." />
                    </div>
                </div>
        
                <table class="ui-widget" style="width:100%;">
                    <thead>
                        <tr>
                            <th><?php echo __( '窗簾類別', 'textdomain' );?></th>
                            <th><?php echo __( '寬度設定', 'textdomain' );?></th>
                            <th><?php echo __( '高度設定', 'textdomain' );?></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    // Define the custom pagination parameters
                    $posts_per_page = get_option('operation_row_counts');
                    $paged = max(1, get_query_var('paged')); // Get the current page number
                    $query = $this->retrieve_curtain_category_data($paged);
                    $total_posts = $query->found_posts;
                    $total_pages = ceil($total_posts / $posts_per_page); // Calculate the total number of pages
        
                    if ($query->have_posts()) :
                        while ($query->have_posts()) : $query->the_post();
                            $curtain_min_width = get_post_meta(get_the_ID(), 'curtain_min_width', true);
                            $curtain_max_width = get_post_meta(get_the_ID(), 'curtain_max_width', true);
                            $curtain_min_height = get_post_meta(get_the_ID(), 'curtain_min_height', true);
                            $curtain_max_height = get_post_meta(get_the_ID(), 'curtain_max_height', true);
                            ?>
                            <tr id="edit-curtain-category-<?php the_ID();?>">
                                <td style="text-align:center;"><?php echo esc_html(get_the_title());?></td>
                                <td style="text-align:center;"><?php echo esc_html($curtain_min_width.'~'.$curtain_max_width);?></td>
                                <td style="text-align:center;"><?php echo esc_html($curtain_min_height.'~'.$curtain_max_height);?></td>
                            </tr>
                            <?php
                        endwhile;
                        wp_reset_postdata();
                    endif;
                    ?>
                    </tbody>
                </table>
                <div id="new-curtain-category" class="custom-button" style="border:solid; margin:3px; text-align:center; border-radius:5px; font-size:small;">+</div>
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
            <div id="curtain-category-dialog" title="Category dialog"></div>            
            <?php
        }

        function retrieve_curtain_category_data($paged = 1) {
            // Define the custom pagination parameters
            $posts_per_page = get_option('operation_row_counts');
            $search_query = sanitize_text_field($_GET['_search']);
            $args = array(
                'post_type'      => 'curtain-category',
                'posts_per_page' => $posts_per_page,
                'paged'          => $paged,
                's'              => $search_query,  
            );        
            $query = new WP_Query($args);
            return $query;
        }
        
        function display_curtain_category_dialog($curtain_category_id=false) {
            ob_start();            
            $curtain_category_title = get_the_title($curtain_category_id);
            $curtain_min_width = get_post_meta($curtain_category_id, 'curtain_min_width', true);
            $curtain_max_width = get_post_meta($curtain_category_id, 'curtain_max_width', true);
            $curtain_min_height = get_post_meta($curtain_category_id, 'curtain_min_height', true);
            $curtain_max_height = get_post_meta($curtain_category_id, 'curtain_max_height', true);
            $is_specification = get_post_meta($curtain_category_id, 'is_specification', true);
            $is_specification_checked = ($is_specification == 1) ? 'checked' : '';
            $height_hided = get_post_meta($curtain_category_id, 'height_hided', true);
            $is_height_checked = ($height_hided == 1) ? 'checked' : '';
            $height_excluded = get_post_meta($curtain_category_id, 'height_excluded', true);
            $is_height_excluded = ($height_excluded == 1) ? 'checked' : '';
            $category_disabled = get_post_meta($curtain_category_id, 'category_disabled', true);
            $is_disabled = ($category_disabled == 1) ? 'checked' : '';
            ?>
            <fieldset>
                <div>
                    <input type="checkbox" id="category-disabled" style="display:inline-block; width:5%;" <?php echo $is_disabled;?> />
                    <label for="category-disabled" style="display:inline-block;"><?php echo __( 'Category disabled.', 'textdomain' );?></label>
                </div>
                <input type="hidden" id="curtain-category-id" value="<?php echo esc_attr($curtain_category_id);?>" />
                <label for="curtain-category-title"><?php echo __( '窗簾類別', 'textdomain' );?></label>
                <input type="text" id="curtain-category-title" value="<?php echo esc_html($curtain_category_title);?>" class="text ui-widget-content ui-corner-all" />

                <input type="checkbox" id="is-specification" style="display:inline-block; width:5%; " <?php echo $is_specification_checked;?> /> Hide the Specification.
                <div>
                    <div id="show-width">
                        <label for="curtain-width" style="display:inline-block;"><?php echo __( 'Width: ', 'textdomain' );?></label>
                        <input type="text" id="curtain-min-width" value="<?php echo esc_html($curtain_min_width);?>" style="display:inline-block; width:25%;" /> cm ~ 
                        <input type="text" id="curtain-max-width" value="<?php echo esc_html($curtain_max_width);?>" style="display:inline-block; width:25%;" /> cm
                    </div>
                </div>
                <div>
                    <input type="checkbox" id="height-hided" style="display:inline-block; width:5%; " <?php echo $is_height_checked;?> /> Hide the Height.
                    <div id="show-height">
                        <label for="curtain-height" style="display:inline-block;"><?php echo __( 'Height: ', 'textdomain' );?></label>
                        <input type="text" id="curtain-min-height" value="<?php echo esc_html($curtain_min_height);?>" style="display:inline-block; width:25%;" /> cm ~ 
                        <input type="text" id="curtain-max-height" value="<?php echo esc_html($curtain_max_height);?>" style="display:inline-block; width:25%;" /> cm
                    </div>
                </div>
                <input type="checkbox" id="height-excluded" style="display:inline-block; width:5%; " <?php echo $is_height_excluded;?> /> Height excluded.
            </fieldset>
            <?php
            return ob_get_clean();
        }

        function get_curtain_category_dialog_data() {
            $response = array();
            if (isset($_POST['_curtain_category_id'])) {
                $curtain_category_id = sanitize_text_field($_POST['_curtain_category_id']);
                $response['html_contain'] = $this->display_curtain_category_dialog($curtain_category_id);
            } else {
                $response['html_contain'] = 'Invalid AJAX request!';
            }
            wp_send_json($response);
        }

        function set_curtain_category_dialog_data() {
            $response = array();
            if( isset($_POST['_curtain_category_id']) ) {
                // Update the meta data
                $curtain_category_id = (isset($_POST['_curtain_category_id'])) ? sanitize_text_field($_POST['_curtain_category_id']) : 0;
                $curtain_category_title = (isset($_POST['_curtain_category_title'])) ? sanitize_text_field($_POST['_curtain_category_title']) : '';
                $curtain_min_width = (isset($_POST['_curtain_min_width'])) ? sanitize_text_field($_POST['_curtain_min_width']) : 0;
                $curtain_max_width = (isset($_POST['_curtain_max_width'])) ? sanitize_text_field($_POST['_curtain_max_width']) : 0;
                $curtain_min_height = (isset($_POST['_curtain_min_height'])) ? sanitize_text_field($_POST['_curtain_min_height']) : 0;
                $curtain_max_height = (isset($_POST['_curtain_max_height'])) ? sanitize_text_field($_POST['_curtain_max_height']) : 0;
                $is_specification = (isset($_POST['_is_specification'])) ? sanitize_text_field($_POST['_is_specification']) : 0;
                $height_hided = (isset($_POST['_height_hided'])) ? sanitize_text_field($_POST['_height_hided']) : 0;
                $height_excluded = (isset($_POST['_height_excluded'])) ? sanitize_text_field($_POST['_height_excluded']) : 0;
                $category_disabled = (isset($_POST['_category_disabled'])) ? sanitize_text_field($_POST['_category_disabled']) : 0;
                update_post_meta($curtain_category_id, 'curtain_min_width', $curtain_min_width);
                update_post_meta($curtain_category_id, 'curtain_max_width', $curtain_max_width);
                update_post_meta($curtain_category_id, 'curtain_min_height', $curtain_min_height);
                update_post_meta($curtain_category_id, 'curtain_max_height', $curtain_max_height);
                update_post_meta($curtain_category_id, 'is_specification', $is_specification);
                update_post_meta($curtain_category_id, 'height_hided', $height_hided);
                update_post_meta($curtain_category_id, 'height_excluded', $height_excluded);
                update_post_meta($curtain_category_id, 'category_disabled', $category_disabled);
                // Update the post title
                $updated_post = array(
                    'ID'         => $curtain_category_id,
                    'post_title' => $curtain_category_title,
                );
                wp_update_post($updated_post);
            } else {
                $current_user_id = get_current_user_id();
                $new_post = array(
                    'post_title'    => 'New category',
                    'post_content'  => 'Your post content goes here.',
                    'post_status'   => 'publish',
                    'post_author'   => $current_user_id,
                    'post_type'     => 'curtain-category',
                );    
                $post_id = wp_insert_post($new_post);
            }
            wp_send_json($response);
        }

        function del_curtain_category_dialog_data() {
            $response = array();
            if( isset($_POST['_curtain_category_id']) ) {
                $curtain_category_id = sanitize_text_field($_POST['_curtain_category_id']);
                wp_delete_post($curtain_category_id, true);
            }
            wp_send_json($response);
        }

        function select_curtain_category_options($selected_option=0) {
            $args = array(
                'post_type'      => 'curtain-category',
                'posts_per_page' => -1,
                'meta_query' => array(
                    'relation' => 'OR',
                    array(
                        'key'   => 'category_disabled',
                        'compare' => 'NOT EXISTS',
                    ),
                    array(
                        'key'   => 'category_disabled',
                        'value' => 0,
                    ),
                ),
            );
            $query = new WP_Query($args);

            $options = '<option value="">'.__( 'Select option', 'textdomain' ).'</option>';
            while ($query->have_posts()) : $query->the_post();
                $selected = ($selected_option == get_the_ID()) ? 'selected' : '';
                $options .= '<option value="' . esc_attr(get_the_ID()) . '" '.$selected.' />' . esc_html(get_the_title()) . '</option>';
            endwhile;
            wp_reset_postdata();
            return $options;
        }
   }
    $categories_class = new curtain_categories();
}