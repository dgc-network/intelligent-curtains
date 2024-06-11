<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('curtain_specifications')) {
    class curtain_specifications {

        public function __construct() {

            add_shortcode( 'curtain-specification-list', array( $this, 'display_shortcode' ) );
            add_action( 'init', array( $this, 'register_curtain_specification_post_type' ) );
            add_action( 'wp_ajax_get_curtain_specification_dialog_data', array( $this, 'get_curtain_specification_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_get_curtain_specification_dialog_data', array( $this, 'get_curtain_specification_dialog_data' ) );
            add_action( 'wp_ajax_set_curtain_specification_dialog_data', array( $this, 'set_curtain_specification_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_set_curtain_specification_dialog_data', array( $this, 'set_curtain_specification_dialog_data' ) );
            add_action( 'wp_ajax_del_curtain_specification_dialog_data', array( $this, 'del_curtain_specification_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_del_curtain_specification_dialog_data', array( $this, 'del_curtain_specification_dialog_data' ) );

        }

        function register_curtain_specification_post_type() {
            $labels = array(
                'menu_name'     => _x('curtain-spec', 'admin menu', 'textdomain'),
            );
            $args = array(
                'labels'        => $labels,
                'public'        => true,
                'rewrite'       => array('slug' => 'curtain-specs'),
                'supports'      => array('title', 'editor', 'custom-fields'),
                'has_archive'   => true,
                'show_in_menu'  => false,
            );
            register_post_type( 'curtain-spec', $args );
        }

        function display_shortcode() {
            if (current_user_can('administrator')) {
                $this->display_curtain_specification_list();
            } else {
                ?>
                <div style="text-align:center;">
                    <h4><?php echo __( '你沒有讀取目前網頁的權限!', 'your-text-domain' );?></h4>
                </div>
                <?php
            }
        }

        function display_curtain_specification_list() {
            $curtain_categories = new curtain_categories();
            ?>
            <div class="ui-widget" id="result-container">
            <h2 style="display:inline;"><?php echo __( 'Curtain specifications', 'your-text-domain' );?></h2>
            <fieldset>
                <div style="display:flex; justify-content:space-between; margin:5px;">
                    <div>
                        <select id="select-category-in-spec"><?php echo $curtain_categories->select_curtain_category_options($_GET['_category']);?></select>
                    </div>
                    <div style="text-align:right; display:flex;">
                        <input type="text" id="search-specification" style="display:inline" placeholder="Search..." />
                    </div>
                </div>
        
                <table class="ui-widget" style="width:100%;">
                    <thead>
                        <tr>
                            <th><?php echo __( 'Spec', 'your-text-domain' );?></th>
                            <th><?php echo __( 'Description', 'your-text-domain' );?></th>
                            <th><?php echo __( 'Category', 'your-text-domain' );?></th>
                            <th><?php echo __( 'Unit', 'your-text-domain' );?></th>
                            <th><?php echo __( 'Price', 'your-text-domain' );?></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    // Define the custom pagination parameters
                    $posts_per_page = get_option('operation_row_counts');
                    $current_page = max(1, get_query_var('paged')); // Get the current page number
                    $query = $this->retrieve_curtain_specification_data($current_page);
                    $total_posts = $query->found_posts;
                    $total_pages = ceil($total_posts / $posts_per_page); // Calculate the total number of pages
        
                    if ($query->have_posts()) :
                        while ($query->have_posts()) : $query->the_post();
                            $curtain_specification_title = get_the_title();
                            $curtain_specification_description = get_post_field('post_content', get_the_ID());
                            $curtain_category_id = get_post_meta(get_the_ID(), 'curtain_category_id', true);
                            $curtain_specification_unit = get_post_meta(get_the_ID(), 'curtain_specification_unit', true);
                            $curtain_specification_price = get_post_meta(get_the_ID(), 'curtain_specification_price', true);
                            ?>
                            <tr id="edit-curtain-specification-<?php the_ID();?>">
                                <td style="text-align:center;"><?php echo esc_html(get_the_title());?></td>
                                <td><?php echo esc_html($curtain_specification_description);?></td>
                                <td style="text-align:center;"><?php echo esc_html(get_the_title($curtain_category_id));?></td>
                                <td style="text-align:center;"><?php echo esc_html($curtain_specification_unit);?></td>
                                <td style="text-align:center;"><?php echo esc_html($curtain_specification_price);?></td>
                            </tr>
                            <?php
                        endwhile;
                        wp_reset_postdata();
                    endif;
                    ?>
                    </tbody>
                </table>
                <div id="new-curtain-specification" class="custom-button" style="border:solid; margin:3px; text-align:center; border-radius:5px; font-size:small;">+</div>
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
            <div id="curtain-specification-dialog" title="Specification dialog"></div>            
            <?php
        }

        function retrieve_curtain_specification_data($current_page = 1) {
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
                'post_type'      => 'curtain-spec',
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

        function display_curtain_specification_dialog($curtain_specification_id=false) {            
            $curtain_categories = new curtain_categories();
            $curtain_specification_title = get_the_title($curtain_specification_id);
            $curtain_specification_description = get_post_field('post_content', $curtain_specification_id);
            $curtain_category_id = get_post_meta($curtain_specification_id, 'curtain_category_id', true);
            $curtain_specification_unit = get_post_meta($curtain_specification_id, 'curtain_specification_unit', true);
            $curtain_specification_price = get_post_meta($curtain_specification_id, 'curtain_specification_price', true);
            ob_start();
            ?>
            <fieldset>
                <input type="hidden" id="curtain-specification-id" value="<?php echo esc_attr($curtain_specification_id);?>" />
                <label for="curtain-specification-title"><?php echo __( 'Title', 'your-text-domain' );?></label>
                <input type="text" id="curtain-specification-title" value="<?php echo esc_html($curtain_specification_title);?>" class="text ui-widget-content ui-corner-all" />
                <label for="curtain-specification-description"><?php echo __( 'Description', 'your-text-domain' );?></label>
                <input type="text" id="curtain-specification-description" value="<?php echo esc_html($curtain_specification_description);?>" class="text ui-widget-content ui-corner-all" />
                <label for="curtain-category-id"><?php echo __( 'Category', 'your-text-domain' );?></label>
                <select id="curtain-category-id"><?php echo $curtain_categories->select_curtain_category_options($curtain_category_id);?></select>
                <label for="curtain-specification-unit"><?php echo __( 'Unit', 'your-text-domain' );?></label>
                <input type="text" id="curtain-specification-unit" value="<?php echo esc_html($curtain_specification_unit);?>" class="text ui-widget-content ui-corner-all" />
                <label for="curtain-specification-price"><?php echo __( 'Price', 'your-text-domain' );?></label>
                <input type="text" id="curtain-specification-price" value="<?php echo esc_html($curtain_specification_price);?>" class="text ui-widget-content ui-corner-all" />
            </fieldset>
            <?php
            $html = ob_get_clean();
            return $html;        
        }

        function get_curtain_specification_dialog_data() {
            $response = array();
            if (isset($_POST['_curtain_specification_id'])) {
                $curtain_specification_id = sanitize_text_field($_POST['_curtain_specification_id']);
                $response['html_contain'] = $this->display_curtain_specification_dialog($curtain_specification_id);
            } else {
                $response['html_contain'] = 'Invalid AJAX request!';
            }
            wp_send_json($response);
        }

        function set_curtain_specification_dialog_data() {
            $response = array();
            if( isset($_POST['_curtain_specification_id']) ) {
                // Update the meta data
                $curtain_specification_id = sanitize_text_field($_POST['_curtain_specification_id']);
                update_post_meta( $curtain_specification_id, 'curtain_category_id', sanitize_text_field($_POST['_curtain_category_id']));
                update_post_meta( $curtain_specification_id, 'curtain_specification_unit', sanitize_text_field($_POST['_curtain_specification_unit']));
                update_post_meta( $curtain_specification_id, 'curtain_specification_price', sanitize_text_field($_POST['_curtain_specification_price']));
                // Update the post title
                $updated_post = array(
                    'ID'         => $curtain_specification_id,
                    'post_title' => sanitize_text_field($_POST['_curtain_specification_title']),
                    'post_content' => sanitize_text_field($_POST['_curtain_specification_description']),
                );
                wp_update_post($updated_post);
            } else {
                $current_user_id = get_current_user_id();
                $new_post = array(
                    'post_title'    => '-',
                    'post_content'  => 'Your post content goes here.',
                    'post_status'   => 'publish',
                    'post_author'   => $current_user_id,
                    'post_type'     => 'curtain-spec',
                );    
                $post_id = wp_insert_post($new_post);
            }
            wp_send_json($response);
        }

        function del_curtain_specification_dialog_data() {
            $response = array();
            if( isset($_POST['_curtain_specification_id']) ) {
                $curtain_specification_id = sanitize_text_field($_POST['_curtain_specification_id']);
                wp_delete_post($curtain_specification_id, true);
            }
            wp_send_json($response);
        }

        function select_curtain_specification_options($selected_option=0, $curtain_category_id=0) {
            $args = array(
                'post_type'      => 'curtain-spec',
                'posts_per_page' => -1,
                'meta_query'     => array(
                    array(
                        'key'   => 'curtain_category_id',
                        'value' => $curtain_category_id,
                    ),
                ),
            );
            $query = new WP_Query($args);
        
            $options = '<option value="">Select specification</option>';
            while ($query->have_posts()) : $query->the_post();
                $selected = ($selected_option == get_the_ID()) ? 'selected' : '';
                $option = get_the_content().'('.get_the_title().')';
                $options .= '<option value="' . esc_attr(get_the_ID()) . '" '.$selected.' />' . esc_html($option) . '</option>';
            endwhile;
            wp_reset_postdata();
            return $options;
        }
    }
    $specs_class = new curtain_specifications();
}