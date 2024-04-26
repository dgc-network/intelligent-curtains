<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('curtain_orders')) {
    class curtain_orders {
        private $_wp_page_title;
        private $_wp_page_postid;
        private $see_more;
        private $curtain_agent_id;

        // Class constructor
        public function __construct() {
            $this->create_tables();
            $this->_wp_page_title = 'Orders';
            $this->_wp_page_postid = general_helps::create_page($this->_wp_page_title, 'shopping-item-list', 'system');
            add_shortcode( 'shopping-item-list', array( $this, 'list_order_items' ) );
            if (file_exists(plugin_dir_path( __DIR__ ).'assets/templates/see_more.json')) {
                $this->see_more = file_get_contents(plugin_dir_path( __DIR__ ).'assets/templates/see_more.json');
                $this->see_more = json_decode($this->see_more, true);
            }
            add_action( 'wp_ajax_order_item_dialog_add_data', array( $this, 'order_item_dialog_add_data' ) );
            add_action( 'wp_ajax_nopriv_order_item_dialog_add_data', array( $this, 'order_item_dialog_add_data' ) );
            add_action( 'wp_ajax_order_item_dialog_get_data', array( $this, 'order_item_dialog_get_data' ) );
            add_action( 'wp_ajax_nopriv_order_item_dialog_get_data', array( $this, 'order_item_dialog_get_data' ) );
            add_action( 'wp_ajax_order_item_dialog_save_data', array( $this, 'order_item_dialog_save_data' ) );
            add_action( 'wp_ajax_nopriv_order_item_dialog_save_data', array( $this, 'order_item_dialog_save_data' ) );
            add_action( 'wp_ajax_select_order_status', array( $this, 'select_order_status' ) );
            add_action( 'wp_ajax_nopriv_select_order_status', array( $this, 'select_order_status' ) );
            add_action( 'wp_ajax_select_category_id', array( $this, 'select_category_id' ) );
            add_action( 'wp_ajax_nopriv_select_category_id', array( $this, 'select_category_id' ) );
            add_action( 'wp_ajax_select_order_status', array( $this, 'select_order_status' ) );
            add_action( 'wp_ajax_nopriv_select_order_status', array( $this, 'select_order_status' ) );
            add_action( 'wp_ajax_sub_items_dialog_get_data', array( $this, 'sub_items_dialog_get_data' ) );
            add_action( 'wp_ajax_nopriv_sub_items_dialog_get_data', array( $this, 'sub_items_dialog_get_data' ) );
            add_action( 'wp_ajax_sub_items_dialog_save_data', array( $this, 'sub_items_dialog_save_data' ) );
            add_action( 'wp_ajax_nopriv_sub_items_dialog_save_data', array( $this, 'sub_items_dialog_save_data' ) );

            add_action( 'init', array( $this, 'register_customer_order_post_type' ) );
            add_action( 'init', array( $this, 'register_order_item_post_type' ) );
            //add_action( 'init', array( $this, 'register_curtain_category_post_type' ) );
            //add_action( 'init', array( $this, 'register_curtain_model_post_type' ) );
            //add_action( 'init', array( $this, 'register_curtain_specification_post_type' ) );
            add_action( 'wp_ajax_get_quotation_dialog_data', array( $this, 'get_quotation_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_get_quotation_dialog_data', array( $this, 'get_quotation_dialog_data' ) );
            add_action( 'wp_ajax_set_quotation_dialog_data', array( $this, 'set_quotation_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_set_quotation_dialog_data', array( $this, 'set_quotation_dialog_data' ) );
            add_action( 'wp_ajax_del_quotation_dialog_data', array( $this, 'del_quotation_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_del_quotation_dialog_data', array( $this, 'del_quotation_dialog_data' ) );
            add_action( 'wp_ajax_get_order_item_dialog_data', array( $this, 'get_order_item_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_get_order_item_dialog_data', array( $this, 'get_order_item_dialog_data' ) );
            add_action( 'wp_ajax_set_order_item_dialog_data', array( $this, 'set_order_item_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_set_order_item_dialog_data', array( $this, 'set_order_item_dialog_data' ) );
            add_action( 'wp_ajax_del_order_item_dialog_data', array( $this, 'del_order_item_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_del_order_item_dialog_data', array( $this, 'del_order_item_dialog_data' ) );
    
        }

        // Register customer-order post type
        function register_customer_order_post_type() {
            $labels = array(
                'menu_name'     => _x('customer-order', 'admin menu', 'textdomain'),
            );
            $args = array(
                'labels'        => $labels,
                'public'        => true,
                'rewrite'       => array('slug' => 'customer-orders'),
                'supports'      => array('title', 'editor', 'custom-fields'),
                'has_archive'   => true,
                'show_in_menu'  => false,
            );
            register_post_type( 'customer-order', $args );
        }

        function register_order_item_post_type() {
            $labels = array(
                'menu_name'     => _x('order-item', 'admin menu', 'textdomain'),
            );
            $args = array(
                'labels'        => $labels,
                'public'        => true,
                'rewrite'       => array('slug' => 'order-items'),
                'supports'      => array('title', 'editor', 'custom-fields'),
                'has_archive'   => true,
                'show_in_menu'  => false,
            );
            register_post_type( 'order-item', $args );
        }

        function display_quotation_list() {
            if (isset($_GET['_is_admin'])) {
                echo '<input type="hidden" id="is-admin" value="1" />';
            }
            $current_user_id = get_current_user_id();
            $site_id = get_user_meta($current_user_id, 'site_id', true);
            $image_url = get_post_meta($site_id, 'image_url', true);
            ?>
            <div class="ui-widget" id="result-container">
            <h2 style="display:inline;"><?php echo __( '報價單', 'your-text-domain' );?></h2>
            <fieldset>
                <div id="document-setting-dialog" title="Document setting" style="display:none">
                <fieldset>
                    <input type="hidden" id="site-id" value="<?php echo $site_id;?>" />
                    <label for="site-title"> Site: </label>
                    <input type="text" id="site-title" value="<?php echo get_the_title($site_id);?>" class="text ui-widget-content ui-corner-all" disabled />
                </fieldset>
                </div>
            
                <div style="display:flex; justify-content:space-between; margin:5px;">
                    <div>
                        <select id="select-category"><?php //echo select_doc_category_option_data($_GET['_category']);?></select>
                    </div>
                    <div style="text-align:right; display:flex;">
                        <input type="text" id="search-document" style="display:inline" placeholder="Search..." />
                    </div>
                </div>
        
                <table class="ui-widget" style="width:100%;">
                    <thead>
                        <tr>
                            <th><?php echo __( '日期', 'your-text-domain' );?></th>
                            <th><?php echo __( '客戶', 'your-text-domain' );?></th>
                            <th><?php echo __( '金額', 'your-text-domain' );?></th>
                            <th><?php echo __( '備註', 'your-text-domain' );?></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    // Define the custom pagination parameters
                    $posts_per_page = get_option('operation_row_counts');
                    $current_page = max(1, get_query_var('paged')); // Get the current page number
                    $query = $this->retrieve_quotation_data($current_page);
                    $total_posts = $query->found_posts;
                    $total_pages = ceil($total_posts / $posts_per_page); // Calculate the total number of pages
        
                    if ($query->have_posts()) :
                        while ($query->have_posts()) : $query->the_post();
                            $customer_name = get_post_meta(get_the_ID(), 'customer_name', true);
                            $customer_order_amount = get_post_meta(get_the_ID(), 'customer_order_amount', true);
                            $customer_order_remark = get_post_meta(get_the_ID(), 'customer_order_remark', true);
                            ?>
                            <tr id="edit-quotation-<?php the_ID();?>">
                                <td style="text-align:center;"><?php echo esc_html(get_the_date());?></td>
                                <td><?php echo esc_html($customer_name);?></td>
                                <td style="text-align:center;"><?php echo esc_html($customer_order_amount);?></td>
                                <td><?php echo esc_html($customer_order_remark);?></td>
                            </tr>
                            <?php
                        endwhile;
                        wp_reset_postdata();
                    endif;
                    ?>
                    </tbody>
                </table>
                <div id="new-quotation" class="custom-button" style="border:solid; margin:3px; text-align:center; border-radius:5px; font-size:small;">+</div>
                <?php
                    // Display pagination links
                    echo '<div class="pagination">';
                    if ($current_page > 1) echo '<span class="button"><a href="' . esc_url(get_pagenum_link($current_page - 1)) . '"> < </a></span>';
                    echo '<span class="page-numbers">' . sprintf(__('Page %d of %d', 'textdomain'), $current_page, $total_pages) . '</span>';
                    if ($current_page < $total_pages) echo '<span class="button"><a href="' . esc_url(get_pagenum_link($current_page + 1)) . '"> > </a></span>';
                    echo '</div>';
                ?>
            </fieldset>
            </div>
            <?php echo $this->display_order_item_dialog();?>
            <?php
        }

        function retrieve_quotation_data($current_page = 1) {
            // Define the custom pagination parameters
            $posts_per_page = get_option('operation_row_counts');
            // Calculate the offset to retrieve the posts for the current page
            $offset = ($current_page - 1) * $posts_per_page;
        
            $current_user_id = get_current_user_id();
            $site_id = get_user_meta($current_user_id, 'site_id', true);
            $site_filter = array(
                'key'     => 'site_id',
                'value'   => $site_id,
                'compare' => '=',
            );
        
            $select_category = sanitize_text_field($_GET['_category']);
            $category_filter = array(
                'key'     => 'doc_category',
                'value'   => $select_category,
                'compare' => '=',
            );
        
            $search_query = sanitize_text_field($_GET['_search']);
            $number_filter = array(
                'key'     => 'doc_number',
                'value'   => $search_query,
                'compare' => 'LIKE',
            );
            $title_filter = array(
                'key'     => 'doc_title',
                'value'   => $search_query,
                'compare' => 'LIKE',
            );
        
            $args = array(
                'post_type'      => 'customer-order',
                'posts_per_page' => $posts_per_page,
                'paged'          => $current_page,
/*                
                //'posts_per_page' => 30,
                //'paged'          => (get_query_var('paged')) ? get_query_var('paged') : 1,
                'meta_query'     => array(
                    'relation' => 'OR',
                    array(
                        'relation' => 'AND',
                        ($site_id) ? $site_filter : '',
                        ($select_category) ? $category_filter : '',
                        ($search_query) ? $number_filter : '',
                    ),
                    array(
                        'relation' => 'AND',
                        ($site_id) ? $site_filter : '',
                        ($select_category) ? $category_filter : '',
                        ($search_query) ? $title_filter : '',
                    )
                ),
                'orderby'        => 'meta_value',
                'meta_key'       => 'doc_number',
                'order'          => 'ASC',
*/                
            );
        
            $query = new WP_Query($args);
            return $query;
        }
        
        function display_quotation_dialog($customer_order_id=false) {
            $customer_name = get_post_meta($customer_order_id, 'customer_name', true);
            $customer_order_remark = get_post_meta($customer_order_id, 'customer_order_remark', true);
            $customer_order_amount = get_post_meta($customer_order_id, 'customer_order_amount', true);
            ob_start();
            ?>
            <h2 style="display:inline;"><?php echo __( '報價單', 'your-text-domain' );?></h2>
            <fieldset>
            <input type="hidden" id="customer-order-id" value="<?php echo esc_attr($customer_order_id);?>" />
            <label for="customer-name"><?php echo __( '客戶名稱', 'your-text-domain' );?></label>
            <input type="text" id="customer-name" value="<?php echo esc_html($customer_name);?>" class="text ui-widget-content ui-corner-all" />
            <label for="customer-order-remark"><?php echo __( '備註', 'your-text-domain' );?></label>
            <textarea id="customer-order-remark" rows="3" style="width:100%;"><?php echo $customer_order_remark;?></textarea>
            <?php echo $this->display_order_item_list($customer_order_id);?>
            <hr>
            <input type="button" id="save-quotation" value="<?php echo __( 'Save', 'your-text-domain' );?>" style="margin:3px; display:inline;" />
            <input type="button" id="del-quotation" value="<?php echo __( 'Delete', 'your-text-domain' );?>" style="margin:3px; display:inline;" />
            </fieldset>
            <?php
            $html = ob_get_clean();
            return $html;
        }
        
        function get_quotation_dialog_data() {
            $response = array();
            if (isset($_POST['_customer_order_id'])) {
                $customer_order_id = sanitize_text_field($_POST['_customer_order_id']);
                $response['html_contain'] = $this->display_quotation_dialog($customer_order_id);
            } else {
                $response['html_contain'] = 'Invalid AJAX request!';
            }
            wp_send_json($response);
        }

        function set_quotation_dialog_data() {
            if( isset($_POST['_customer_order_id']) ) {
                // Update the quotation data
                $customer_order_id = sanitize_text_field($_POST['_customer_order_id']);
                update_post_meta( $customer_order_id, 'customer_name', sanitize_text_field($_POST['_customer_name']));
                update_post_meta( $customer_order_id, 'customer_order_amount', sanitize_text_field($_POST['_customer_order_amount']));
                update_post_meta( $customer_order_id, 'customer_order_remark', sanitize_text_field($_POST['_customer_order_remark']));
            } else {
                $current_user_id = get_current_user_id();
                $new_post = array(
                    'post_title'    => 'No title',
                    'post_content'  => 'Your post content goes here.',
                    'post_status'   => 'publish',
                    'post_author'   => $current_user_id,
                    'post_type'     => 'customer-order',
                );    
                $post_id = wp_insert_post($new_post);
                update_post_meta( $post_id, 'customer_name', 'New customer');
            }
            wp_send_json($response);
        }

        function del_quotation_dialog_data() {
            $response = array();
            if( isset($_POST['_customer_order_id']) ) {
                $customer_order_id = sanitize_text_field($_POST['_customer_order_id']);
                wp_delete_post($customer_order_id, true);
            }
            wp_send_json($response);
        }

        function display_order_item_list($customer_order_id=false) {
            $customer_order_amount = 0;
            ob_start();
            ?>
            <div id="order-item-container">
            <fieldset>
                <table style="width:100%;">
                    <thead>
                        <tr>
                            <th><?php echo __( 'Item', 'your-text-domain' );?></th>
                            <th><?php echo __( 'Description', 'your-text-domain' );?></th>
                            <th><?php echo __( 'QTY', 'your-text-domain' );?></th>
                            <th><?php echo __( 'Amount', 'your-text-domain' );?></th>
                        </tr>
                    </thead>
                    <tbody id="sortable-doc-field-list">
                        <?php
                        $query = $this->retrieve_order_item_data($customer_order_id);
                        if ($query->have_posts()) {
                            while ($query->have_posts()) : $query->the_post();
                                $curtain_category_id = get_post_meta(get_the_ID(), 'curtain_category_id', true);
                                $curtain_category_title = get_the_title($curtain_category_id);
                                $curtain_model_id = get_post_meta(get_the_ID(), 'curtain_model_id', true);
                                $curtain_model_description = get_post_field('post_content', $curtain_model_id);
                                $curtain_model_price = get_post_meta($curtain_model_id, 'curtain_model_price', true);
                                $curtain_specification_id = get_post_meta(get_the_ID(), 'curtain_specification_id', true);
                                $curtain_specification_price = get_post_meta($curtain_model_id, 'curtain_specification_price', true);
                                $order_item_description = $curtain_model_description.'('.get_the_title($curtain_model_id).')';
                                $curtain_width = get_post_meta(get_the_ID(), 'curtain_width', true);
                                $curtain_height = get_post_meta(get_the_ID(), 'curtain_height', true);
                                $order_item_qty = get_post_meta(get_the_ID(), 'order_item_qty', true);
                                //$order_item_amount = $order_item_qty * ($curtain_model_price + $curtain_specification_price * ($curtain_width*$curtain_height));
                                $customer_order_amount += $order_item_amount;

                                echo '<tr id="edit-order-item-'.esc_attr(get_the_ID()).'">';
                                echo '<td style="text-align:center;">'.esc_html($curtain_category_title).'</td>';
                                echo '<td>'.esc_html($order_item_description).'</td>';
                                echo '<td style="text-align:center;">'.esc_html($order_item_qty).'</td>';
                                echo '<td style="text-align:center;">'.esc_html($order_item_amount).'</td>';
                                echo '</tr>';
                            endwhile;
                            wp_reset_postdata();
                        }
                        ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td></td>
                            <td></td>
                            <td style="text-align:center;"><?php echo __( 'Sum', 'your-text-domain' );?></td>
                            <td style="text-align:center;"><?php echo $customer_order_amount;?></td>
                        </tr>
                    </tfoot>
                </table>
                <div id="new-order-item" class="custom-button" style="border:solid; margin:3px; text-align:center; border-radius:5px; font-size:small;">+</div>
            </fieldset>
            </div>
            <?php
            $html = ob_get_clean();
            return $html;    
        }
        
        function retrieve_order_item_data($customer_order_id = false) {
            $args = array(
                'post_type'      => 'order-item',
                'posts_per_page' => -1,
                'meta_query'     => array(
                    array(
                        'key'   => 'customer_order_id',
                        'value' => $customer_order_id,    
                    )
                )
            );
            $query = new WP_Query($args);
            return $query;
        }

        function set_order_item_dialog_data() {
            $response = array();
            if( isset($_POST['_order_item_id']) ) {
                // Update the quotation data
                $order_item_id = sanitize_text_field($_POST['_order_item_id']);
                update_post_meta( $order_item_id, 'curtain_category_id', sanitize_text_field($_POST['_curtain_category_id']));
                update_post_meta( $order_item_id, 'curtain_model_id', sanitize_text_field($_POST['_curtain_model_id']));
                update_post_meta( $order_item_id, 'curtain_specification_id', sanitize_text_field($_POST['_curtain_specification_id']));
                update_post_meta( $order_item_id, 'curtain_width', sanitize_text_field($_POST['_curtain_width']));
                update_post_meta( $order_item_id, 'curtain_height', sanitize_text_field($_POST['_curtain_height']));
                update_post_meta( $order_item_id, 'order_item_qty', sanitize_text_field($_POST['_order_item_qty']));
                update_post_meta( $order_item_id, 'order_item_note', sanitize_text_field($_POST['_order_item_note']));
                $customer_order_id = get_post_meta($order_item_id, 'customer_order_id', true);
                $response['html_contain'] = $this->display_order_item_list($customer_order_id);
            } else {
                $current_user_id = get_current_user_id();
                $new_post = array(
                    'post_title'    => 'No title',
                    'post_content'  => 'Your post content goes here.',
                    'post_status'   => 'publish',
                    'post_author'   => $current_user_id,
                    'post_type'     => 'order-item',
                );    
                $post_id = wp_insert_post($new_post);
                update_post_meta( $post_id, 'customer_order_id', $customer_order_id);
                $customer_order_id = sanitize_text_field($_POST['_customer_order_id']);
                $response['html_contain'] = $this->display_order_item_list($customer_order_id);
            }
            wp_send_json($response);
        }

        function display_order_item_dialog($order_item_id=false) {
            $curtain_agents = new curtain_agents();
            $curtain_categories = new curtain_categories();
            $curtain_models = new curtain_models();
            $curtain_specifications = new curtain_specifications();
            $curtain_category_id = get_post_meta($order_item_id, 'curtain_category_id', true);
            $curtain_model_id = get_post_meta($order_item_id, 'curtain_model_id', true);
            $curtain_specification_id = get_post_meta($order_item_id, 'curtain_specification_id', true);
            $curtain_width = get_post_meta($order_item_id, 'curtain_width', true);
            $curtain_height = get_post_meta($order_item_id, 'curtain_height', true);
            $order_item_qty = get_post_meta($order_item_id, 'order_item_qty', true);
            $order_item_note = get_post_meta($order_item_id, 'order_item_note', true);

            ob_start();
            ?>
            <div id="curtain-order-item-dialog" title="Order Item dialog">
            <fieldset>
                <input type="hidden" id="order-item-id" value="<?php echo $order_item_id;?>" />
                <label for="curtain-category-id">類別</label>
                <select id="curtain-category-id" class="text ui-widget-content ui-corner-all"><?php echo $curtain_categories->select_curtain_category_options($curtain_category_id);?></select>
                <label id="curtain-model-label" for="curtain-model-id">型號</label>
                <select id="curtain-model-id" class="text ui-widget-content ui-corner-all"><?php echo $curtain_models->select_curtain_model_options($curtain_model_id, $curtain_category_id);?></select>
                <div id="spec-div" style="display:none;">
                    <label id="curtain-specification-label" for="curtain-specification-id">規格</label>
                    <select id="curtain-specification-id" class="text ui-widget-content ui-corner-all"><?php echo $curtain_specifications->select_curtain_specification_options($curtain_specification_id, $curtain_category_id);?></select>
                    <label id="curtain-width-label" for="curtain-width">寬</label>
                    <input type="text" id="curtain-width" value="<?php echo $curtain_width;?>" />
                    <div id="height-div" style="display:none;">
                        <label id="curtain-height-label" for="curtain-height">高</label>
                        <input type="text" id="curtain-height" value="<?php echo $curtain_height;?>" />
                    </div>
                </div>
                <label for="order-item-qty">數量</label>
                <input type="text" id="order-item-qty" value="<?php echo $order_item_qty;?>" class="text ui-widget-content ui-corner-all" />
                <label for="order-item-note">備註</label>
                <input type="text" id="order-item-note" value="<?php echo $order_item_note;?>" class="text ui-widget-content ui-corner-all" />
            </fieldset>
            </div>
            <?php
            $html = ob_get_clean();
            return $html;
        }
        
        function del_order_item_dialog_data() {
            $response = array();
            if( isset($_POST['_order_item_id']) ) {
                $order_item_id = sanitize_text_field($_POST['_order_item_id']);
                $customer_order_id = get_post_meta($order_item_id, 'customer_order_id', true);
                wp_delete_post($order_item_id, true);
                $response['html_contain'] = $this->display_order_item_list($customer_order_id);
            }
            wp_send_json($response);
        }

        function get_order_item_dialog_data() {
            $response = array();
            if (isset($_POST['_order_item_id'])) {
                $order_item_id = sanitize_text_field($_POST['_order_item_id']);
                $response['html_contain'] = $this->display_order_item_dialog($order_item_id);
            } else {
                $response['html_contain'] = 'Invalid AJAX request!';
            }
            wp_send_json($response);
        }





        
        public function list_order_items() {
            // 2024-4-18 Wilson has requested to use the Quotation instead of the Shopping Cart List
            $this->display_quotation_list();


            global $wpdb;
            $curtain_agents = new curtain_agents();
            $curtain_categories = new curtain_categories();
            $curtain_models = new curtain_models();
            $curtain_remotes = new curtain_remotes();
            $curtain_specifications = new curtain_specifications();
            $serial_number = new serial_number();
            $curtain_service = new curtain_service();
            $system_status = new system_status();

            if ( !is_user_logged_in() ) {
                echo do_shortcode( '[qr-scanner-redirect]' );
            }
            $user = wp_get_current_user();
            $_agent_number = get_user_meta( $user->ID, 'agent_number', TRUE );
            $_agent_password = get_user_meta( $user->ID, 'agent_password', TRUE );

            if( isset($_agent_number) && isset($_agent_password) ) {
                $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}curtain_agents WHERE agent_number = %s AND agent_password = %s", $_agent_number, $_agent_password ), OBJECT );
                if ( is_null($row) || !empty($wpdb->last_error) ) {
                    $output  = '<div style="text-align:center;">';
                    $output .= '<h3>This is a wrong code, please click the Submit button below to re-login the agent order system.</h3>';
                    $output .= '<form method="post" style="display:inline-block; text-align:-webkit-center;">';
                    $output .= '<input type="submit" name="_agent_submit1" style="margin:3px;" value="Submit" />';
                    $output .= '</form>';
                    $output .= '</div>';
                    return $output;                        
                }
                $this->curtain_agent_id = $curtain_agents->get_id($_agent_number);

            } else {
                echo do_shortcode( '[qr-scanner-redirect]' );
            }

            if( isset($_GET['_delete_sub_item']) ) {
                $_id = $_GET['_delete_sub_item'];
                $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}sub_items WHERE sub_item_id = %d", $_id ), OBJECT );
                $this->delete_sub_items(
                    array(
                        'sub_item_id'=>$_GET['_delete_sub_item']
                    )
                );

                $this->update_order_items(
                    array(
                        'order_item_amount'=>$this->caculate_order_item_amount($row->order_item_id),
                    ),
                    array(
                        'curtain_order_id'=>$row->order_item_id
                    )
                );
            }

            if( isset($_GET['_delete_customer_order']) ) {
                $this->delete_customer_orders(
                    array(
                        'customer_order_number'=>$_GET['_delete_customer_order']
                    )
                );
            }

            // Print Customer Order
            if( isset($_POST['_order_status_submit']) ) {
                $this->update_customer_orders(
                    array(
                        'customer_order_status'=>$_POST['_customer_order_status'],
                    ),
                    array(
                        'customer_order_number'=>$_POST['_customer_order_number'],
                    )
                );
                $this->order_status_notice($_POST['_customer_order_number'], $_POST['_customer_order_status']);
            }

            if( isset($_GET['_print_customer_order']) ) {
                $_id = $_GET['_print_customer_order'];
                $row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}customer_orders WHERE customer_order_number={$_id}", OBJECT );
                $output  = '<div style="text-align:center;"><h2>客戶訂單</h2></div>';
                $output .= '<div class="ui-widget">';
                $output .= '<table id="order-header" class="ui-widget ui-widget-content">';
                $output .= '<tr>';
                $output .= '<td>訂單號碼:</td><td><span id="select-order-number">'.$row->customer_order_number.'</span></td>';
                $output .= '<td>訂單日期:</td><td>'.wp_date( get_option('date_format'), $row->create_timestamp ).'</td>';
                $output .= '</tr>';
                $output .= '<tr>';
                $output .= '<td>經銷商:</td><td>'.$curtain_agents->get_name($row->curtain_agent_id).'</td>';
                $output .= '<td>訂單狀態:</td>';
                if($user->has_cap('manage_options')){
                    $output .= '<td>';
                    $output .= '<form method="post" style="display:flex;">';
                    $output .= '<select id="customer-order-status" name="_customer_order_status">'.$system_status->select_options($row->customer_order_status).'</select>';
                    $output .= '<input type="hidden" id="customer-order-number" name="_customer_order_number" value="'.$row->customer_order_number.'" />';
                    $output .= '</form>';
                    $output .= '</td>';
                } else {
                    //$output .= '<td>'.$system_status->get_name($row->customer_order_status).'</td>';
                    $output .= '<td></td>';
                }
                $output .= '</tr>';
                $output .= '<tr>';
                $output .= '<td>聯絡人:</td><td>'.$curtain_agents->get_contact($row->curtain_agent_id).'</td>';
                $output .= '<td>電話:</td><td>'.$curtain_agents->get_phone($row->curtain_agent_id).'</td>';
                $output .= '</tr>';
                $output .= '<tr>';
                $output .= '<td>住址:</td><td colspan="3">'.$curtain_agents->get_address($row->curtain_agent_id).'</td>';
                $output .= '</tr>';
                $output .= '</table>';

                $output .= '<table id="orders" class="ui-widget ui-widget-content">';
                $output .= '<thead><tr class="ui-widget-header ">';
                $output .= '<th>#</th>';
                $output .= '<th>窗簾類型</th>';
                $output .= '<th>型號</th>';
                $output .= '<th>規格</th>';
                $output .= '<th>尺寸</th>';
                $output .= '<th>數量</th>';
                //$output .= '<th>Amount</th>';
                $output .= '<th>備註</th>';
                $output .= '<th></th>';
                $output .= '</tr></thead>';
                $output .= '<tbody>';

                $x=0;
                $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}order_items WHERE customer_order_number={$row->customer_order_number}", OBJECT );
                foreach ( $results as $index=>$result ) {
                    $output .= '<tr>';
                    $x=$x+1;
                    $output .= '<td style="text-align: center;">'.$x.'</td>';
                    $output .= '<td>'.$curtain_categories->get_name($result->curtain_category_id).'</td>';
                    $output .= '<td>'.$curtain_models->get_description($result->curtain_model_id);
                    $output .= '<br>'.$curtain_remotes->get_name($result->curtain_remote_id).'</td>';
                    if ($curtain_categories->is_specification_hided($result->curtain_category_id)) {
                        $output .= '<td></td>';
                    } else {
                        $output .= '<td>'.$curtain_specifications->get_description($result->curtain_specification_id).'</td>';
                    }

                    $output .= '<td>';
                    if ($curtain_categories->is_width_hided($result->curtain_category_id)) {
                        $output .= '';
                    } else {
                        $output .= 'Width:'.$result->curtain_width;
                    }
                    if ($curtain_categories->is_height_hided($result->curtain_category_id)) {
                        $output .= '';
                    } else {
                        $output .= '<br>Height:'.$result->curtain_height;
                    }
                    $output .= '</td>';

                    $output .= '<td style="text-align:center;">'.$result->order_item_qty.'</td>';
                    $output .= '<td>'.$result->order_item_note.'</td>';
                    $output .= '<td style="text-align: center;">';
                    $serials_page_url = '/serials/?_order_item_id='.$result->curtain_order_id;
                    $output .= '<a href="'.$serials_page_url.'">'.'<i class="fa-solid fa-qrcode"></i>'.'</a>';
                    $output .= '</td>';
                    $output .= '</tr>';

                    $sub_items = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}sub_items WHERE order_item_id={$result->curtain_order_id}", OBJECT );
                    foreach ( $sub_items as $sub_index=>$sub_item ) {
                        $output .= '<tr>';
                        $output .= '<td></td>';
                        $output .= '<td></td>';
                        $output .= '<td>'.$curtain_models->get_description($sub_item->parts_id).'</td>';
                        $output .= '<td></td>';
                        $output .= '<td></td>';
                        $output .= '<td style="text-align:center;">'.$sub_item->parts_qty.'</td>';
                        $output .= '<td></td>';
                        $output .= '<td></td>';
                        $output .= '</tr>';    
                    }
                }
                $output .= '</tbody></table></div>';
                return $output;
            }

            // Customer Orders List
            if( isset($_POST['_customer_orders']) ) {
                if($user->has_cap('manage_options')){
                    $output  = '<h2>Customer Orders - All</h2>';
                } else {
                    $output  = '<h2>Customer Orders - '.$curtain_agents->get_name($this->curtain_agent_id).'</h2>';
                }
                $output .= '<div class="ui-widget">';
                $output .= '<table id="orders" class="ui-widget ui-widget-content">';
                $output .= '<thead><tr class="ui-widget-header ">';
                $output .= '<th></th>';
                $output .= '<th>Order No.</th>';
                $output .= '<th>Date</th>';
                $output .= '<th>Agent</th>';
                $output .= '<th>Amount</th>';
                $output .= '<th>Status</th>';
                $output .= '<th></th>';
                $output .= '</tr></thead>';

                $output .= '<tbody>';
                $_conditions = array('curtain_agent_id='.$this->curtain_agent_id);
                if($user->has_cap('manage_options')){
                    $results = general_helps::get_search_results($wpdb->prefix.'customer_orders', $_POST['_where']);
                } else {
                    $results = general_helps::get_search_results($wpdb->prefix.'customer_orders', $_POST['_where'], $_conditions);
                }
                foreach ( $results as $index=>$result ) {
                    $output .= '<tr>';
                    $output .= '<td style="text-align: center;">';
                    $output .= '<span id="btn-print-customer-order-'.$result->customer_order_number.'"><i class="fa-regular fa-pen-to-square"></i></span>';
                    $output .= '</td>';
                    $output .= '<td style="text-align: center;">'.$result->customer_order_number.'</td>';
                    $output .= '<td style="text-align: center;">'.wp_date( get_option('date_format'), $result->create_timestamp ).'</td>';
                    $output .= '<td>'.$curtain_agents->get_name($result->curtain_agent_id).'</td>';
                    $output .= '<td style="text-align: center;">'.number_format_i18n($result->customer_order_amount).'</td>';
                    $output .= '<td>'.$system_status->get_name($result->customer_order_status).'</td>';
                    if($user->has_cap('manage_options')){
                        $output .= '<td style="text-align: center;">';
                        $output .= '<span id="btn-customer-order-del-'.$result->customer_order_number.'"><i class="fa-regular fa-trash-can"></i></span>';
                        $output .= '</td>';
                    }
                    $output .= '</tr>';
                }
                $output .= '</tbody></table></div>';
                return $output;
            }

            // Checkout
            if( isset($_POST['_checkout_submit']) ) {
                $customer_order_number=time();
                $customer_order_amount=0;
                $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}order_items WHERE curtain_agent_id={$this->curtain_agent_id} AND is_checkout=0", OBJECT );                
                foreach ( $results as $index=>$result ) {
                    $_is_checkout = '_is_checkout_'.$index;
                    if ( $_POST[$_is_checkout]==1 ) {
                        $this->update_order_items(
                            array(
                                'customer_order_number'=>$customer_order_number,
                                'is_checkout'=>1
                            ),
                            array(
                                'curtain_order_id'=>$result->curtain_order_id
                            )
                        );

                        $customer_order_amount=$customer_order_amount+$result->order_item_amount;

                        $x = 0;
                        while ($x < $result->order_item_qty) {
                            $serial_number->insert_serial_number(
                                array(
                                    'customer_order_number'=>$customer_order_number,
                                    'order_item_id'=>$result->curtain_order_id,
                                    'curtain_model_id'=>$result->curtain_model_id,
                                    'specification'   =>$curtain_specifications->get_name($result->curtain_specification_id).$result->curtain_width,
                                    'curtain_agent_id'=>$result->curtain_agent_id
                                ),
                                $x
                            );
                            $x = $x + 1;
                        }
                    }
                }

                // Convert the shopping items to customer orders and purchase order
                $this->insert_customer_order(
                    array(
                        'customer_order_number' => $customer_order_number,
                        'curtain_agent_id'      => $this->curtain_agent_id,
                        'customer_order_amount' => $customer_order_amount,
                        'customer_order_status' => 'order01' // order01: Completed the checkout but did not purchase yet
                    )
                );

                // Notice the admin about the order status
                $this->order_status_notice($customer_order_number, 'order01');
            }
            
            // Shopping Cart Item Create and Editing
            if( isset($_POST['_create']) ) {
                $width = 1;
                $height = 1;
                $qty = 1;
                if (is_numeric($_POST['_curtain_width'])) {
                    $width = $_POST['_curtain_width'];
                }
                if (is_numeric($_POST['_curtain_height'])) {
                    $height = $_POST['_curtain_height'];
                }
                if (is_numeric($_POST['_shopping_item_qty'])) {
                    $qty = $_POST['_shopping_item_qty'];
                }
                $m_price = $curtain_models->get_price($_POST['_curtain_model_id']);
                $r_price = $curtain_remotes->get_price($_POST['_curtain_remote_id']);
                $s_price = $curtain_specifications->get_price($_POST['_curtain_specification_id']);
                if ($curtain_specifications->is_length_only($_POST['_curtain_specification_id'])==1){
                    $amount = ($m_price + $r_price + $width/100 * $s_price) * $qty;
                } else {
                    $amount = ($m_price + $r_price + $width/100 * $height/100 * $s_price) * $qty;
                }
                $this->insert_order_item(
                    array(
                        'curtain_agent_id'=>$this->curtain_agent_id,
                        'curtain_category_id'=>$_POST['_curtain_category_id'],
                        'curtain_model_id'=>$_POST['_curtain_model_id'],
                        'curtain_remote_id'=>$_POST['_curtain_remote_id'],
                        'curtain_specification_id'=>$_POST['_curtain_specification_id'],
                        'curtain_width'=>$_POST['_curtain_width'],
                        'curtain_height'=>$_POST['_curtain_height'],
                        'order_item_qty'=>$_POST['_shopping_item_qty'],
                        'order_item_amount'=>$amount,
                        'is_checkout'=>0
                    )
                );
            }

            if( isset($_GET['_order_item_delete']) ) {
                $this->delete_order_items(
                    array(
                        //'curtain_order_id'=>$_GET['_delete']
                        'curtain_order_id'=>$_GET['_order_item_delete']
                    )
                );
            }

            // Shopping Cart List
            $output  = '<h2>Shopping Cart - '.$curtain_agents->get_name($this->curtain_agent_id).'</h2>';
            $output .= '<div style="display: flex; justify-content: space-between; margin: 5px;">';
            $output .= '<div>';
            $output .= '<form method="post">';
            $output .= '<input class="wp-block-button__link" type="submit" value="New Item" name="_add">';
            $output .= '<input class="wp-block-button__link" type="submit" value="我的訂單" name="_customer_orders">';
            $output .= '</form>';
            $output .= '</div>';
            $output .= '<div style="text-align: right;">';
            $output .= '<form method="post">';
            $output .= '<input style="display:inline" type="text" name="_where" placeholder="Search...">';
            $output .= '<input class="wp-block-button__link" type="submit" value="查詢" name="submit_action">';
            $output .= '</form>';
            $output .= '</div>';
            $output .= '</div>';

            $output .= '<div class="ui-widget">';
            $output .= '<table id="order-items" class="ui-widget ui-widget-content">';
            $output .= '<thead><tr class="ui-widget-header ">';
            $output .= '<th></th>';
            $output .= '<th></th>';
            $output .= '<th>時間</th>';
            $output .= '<th>窗簾種類</th>';
            $output .= '<th>型號</th>';
            $output .= '<th>配件</th>';
            $output .= '<th>數量</th>';
            $output .= '<th>金額</th>';
            $output .= '<th>備註</th>';
            $output .= '<th></th>';
            $output .= '</tr></thead>';

            $output .= '<form method="post">';
            $output .= '<tbody>';
            $_conditions = array('curtain_agent_id='.$this->curtain_agent_id, 'is_checkout=0');
            $results = general_helps::get_search_results($wpdb->prefix.'order_items', $_POST['_where'], $_conditions);
            foreach ( $results as $index=>$result ) {
                $output .= '<tr>';
                if ( $result->is_checkout==1 ) {
                    $output .= '<td></td>';
                    $output .= '<td></td>';
                } else {
                    $output .= '<td style="text-align: center;">';
                    $output .= '<input style="display:inline" type="checkbox" value="1" name="_is_checkout_'.$index.'">';
                    $output .= '</td>';
                    $output .= '<td style="text-align: center;">';
                    $output .= '<span style="margin-left:5px;" id="btn-order-item-'.$result->curtain_order_id.'"><i class="fa-regular fa-pen-to-square"></i></span>';
                    $output .= '</td>';
                }
                $output .= '<td>';
                $output .= wp_date( get_option('date_format'), $result->create_timestamp ).' '.wp_date( get_option('time_format'), $result->create_timestamp );
                $output .= '</td>';
                $output .= '<td>'.$curtain_categories->get_name($result->curtain_category_id).'</td>';
                $output .= '<td>'.$curtain_models->get_description($result->curtain_model_id).'</td>';
                $output .= '<td style="text-align: center;">';
                $output .= '<span id="btn-sub-items-'.$result->curtain_order_id.'"><i class="fa-solid fa-gifts"></i></span>';
                $output .= '</td>';
                $output .= '<td style="text-align: center;">'.$result->order_item_qty.'</td>';
                $output .= '<td style="text-align: center;">'.number_format_i18n($result->order_item_amount).'</td>';
                $output .= '<td>'.$result->order_item_note.'</td>';
                if ( $result->is_checkout==1 ) {
                    $output .= '<td>checkout already</td>';
                } else {
                    $output .= '<td style="text-align: center;">';
                    $output .= '<span id="btn-order-item-del-'.$result->curtain_order_id.'"><i class="fa-regular fa-trash-can"></i></span>';
                    $output .= '</td>';
                }
                $output .= '</tr>';
            }
            $output .= '</tbody></table></div>';
            $output .= '<input class="wp-block-button__link" type="submit" value="結帳" name="_checkout_submit">';
            $output .= '</form>';

            // Order Item Dialog
            $output .= '<div id="order-item-dialog" title="Order Item dialog">';
            $output .= '<fieldset>';
            $output .= '<input type="hidden" id="order-item-id">';
            $output .= '<label for="curtain-category-id">窗簾種類</label>';
            $output .= '<select id="curtain-category-id"></select>';
            $output .= '<label id="curtain-model-label" for="curtain-model-id">型號</label>';
            $output .= '<select id="curtain-model-id"></select>';
            $output .= '<label id="curtain-specification-label" for="curtain-specification-id">規格</label>';
            $output .= '<select id="curtain-specification-id"></select>';
            $output .= '<label id="curtain-width-label" for="curtain-width">寬</label>';
            $output .= '<input type="text" id="curtain-width" />';
            $output .= '<label id="curtain-height-label" for="curtain-height">高</label>';
            $output .= '<input type="text" id="curtain-height" />';    
            $output .= '<label for="order-item-qty">數量</label>';
            $output .= '<input type="text" id="order-item-qty" />';
            $output .= '<label for="order-item-note">備註</label>';
            $output .= '<input type="text" id="order-item-note" />';
            $output .= '</fieldset>';
            $output .= '</div>';

            // Sub Items Dialog
            $output .= '<div id="sub-items-dialog" title="Sub Items dialog">';
            $output .= '<table id="sub-items" class="ui-widget ui-widget-content">';
            $output .= '<thead><tr class="ui-widget-header ">';
            $output .= '<th></th>';
            $output .= '<th>附件</th>';
            $output .= '<th>數量</th>';
            $output .= '<th></th>';
            $output .= '</tr></thead>';
            $output .= '<tbody>';
            $x = 0;
            while ($x<10) {
                $output .= '<tr id="sub-item-'.$x.'" style="display:none;">';
                $output .= '<td>'.($x+1).'</td>';
                $output .= '<td id="parts-id-'.$x.'"></td>';
                $output .= '<td id="parts-qty-'.$x.'" style="text-align: center;"></td>';
                $output .= '<td id="parts-del-'.$x.'" style="text-align: center;"></td>';
                $output .= '</tr>';
                $x += 1;
            }            
            $output .= '<tr>';
            $output .= '<td>N</td>';
            $output .= '<td><select id="parts-id">'.$curtain_categories->parts_options().'</select></td>';
            $output .= '<td><input type="text" size="2" id="parts-qty" value="1" /></td>';
            $output .= '</tr>';
            $output .= '</tbody></table>';
            $output .= '</div>';

            if( isset($_POST['_add']) ) {
                $output .= '<div id="dialog" title="Create new item">';
                $output .= '<form method="post">';
                $output .= '<fieldset>';
                $output .= '<label for="curtain-category-id">窗簾種類</label>';
                $output .= '<select name="_curtain_category_id" id="curtain-category-id">'.$curtain_categories->select_options().'</select>';
                $output .= '<label for="curtain-model-id">型號</label>';
                $output .= '<select name="_curtain_model_id" id="curtain-model-id">'.$curtain_models->select_options().'</select>';
                $output .= '<label for="curtain-specification-id">規格</label>';
                $output .= '<select name="_curtain_specification_id" id="curtain-specification-id">'.$curtain_specifications->select_options().'</select>';

                $output .= '<label id="curtain-width-label" for="curtain-width">寬: min(),max()</label>';
                $output .= '<input type="text" name="_curtain_width" id="curtain-width" class="text ui-widget-content ui-corner-all">';
                $output .= '<label id="curtain-height-label" for="curtain-height">高: min(),max()</label>';
                $output .= '<input type="text" name="_curtain_height" id="curtain-height" class="text ui-widget-content ui-corner-all">';

                $output .= '<label for="order_item_qty">數量</label>';
                $output .= '<input type="text" name="_shopping_item_qty" id="order_item_qty" value="1" class="text ui-widget-content ui-corner-all">';

                $output .= '<label for="order_item_note">備註</label>';
                $output .= '<input type="text" name="_shopping_item_note" id="order_item_note" class="text ui-widget-content ui-corner-all">';
                $output .= '</fieldset>';
                $output .= '<input type="hidden" name="_agent_submit" value="true">';
                $output .= '<input class="wp-block-button__link" type="submit" value="Create" name="_create">';
                $output .= '</form>';
                $output .= '</div>';
            }
            return $output;
        }

        public function order_status_notice($customer_order_number, $customer_order_status) {
            global $wpdb;
            $system_status = new system_status();
            $line_bot_api = new line_bot_api();

            $link_uri = get_option('Orders').'?_print='.$customer_order_number;
            $order_status = 'Order status has been changed to '.$system_status->get_name($customer_order_status);

            $all_users = get_users();
            foreach($all_users as $user){
                if($user->has_cap('manage_options')){
                    $this->see_more["header"]["type"] = 'box';
                    $this->see_more["header"]["layout"] = 'vertical';
                    $this->see_more["header"]["backgroundColor"] = "#e3dee3";
                    $this->see_more["header"]["contents"][0]["type"] = 'text';
                    $this->see_more["header"]["contents"][0]["text"] = 'Order No.: '.$customer_order_number;

                    $this->see_more["body"]["contents"][0]["type"] = 'text';
                    $this->see_more["body"]["contents"][0]["text"] = $order_status;
                    $this->see_more["body"]["contents"][0]["wrap"] = true;

                    $this->see_more["footer"]["type"] = 'box';
                    $this->see_more["footer"]["layout"] = 'vertical';
                    $this->see_more["footer"]["backgroundColor"] = "#e3dee3";
                    $this->see_more["footer"]["contents"][0]["type"] = 'button';
                    $this->see_more["footer"]["contents"][0]["action"]["type"] = 'uri';
                    $this->see_more["footer"]["contents"][0]["action"]["label"] = 'Go back Order';
                    $this->see_more["footer"]["contents"][0]["action"]["uri"] = $link_uri;

                    $line_bot_api->pushMessage([
                        'to' => get_user_meta($user->ID, 'line_user_id', TRUE),
                        'messages' => [
                            [
                                "type" => "flex",
                                "altText" => 'System Notification',
                                'contents' => $this->see_more
                            ]
                        ]
                    ]);
                }            
            }
        }

        function order_item_dialog_get_data() {
            global $wpdb;
            $curtain_categories = new curtain_categories();
            $curtain_models = new curtain_models();
            $curtain_remotes = new curtain_remotes();
            $curtain_specifications = new curtain_specifications();

            $_id = $_POST['_id'];
            $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}order_items WHERE curtain_order_id = %d", $_id ), OBJECT );
            $response = array();
            $response["curtain_category_id"] = $curtain_categories->select_options($row->curtain_category_id);
            $response["curtain_model_id"] = $curtain_models->select_options($row->curtain_category_id, $row->curtain_model_id );
            $response["curtain_remote_id"] = $curtain_remotes->select_options($row->curtain_remote_id);
            $response["curtain_specification_id"] = $curtain_specifications->select_options($row->curtain_category_id, $row->curtain_specification_id );
            $response["curtain_width"] = $row->curtain_width;
            $response["curtain_height"] = $row->curtain_height;
            $response["order_item_qty"] = $row->order_item_qty;
            $response["order_item_note"] = $row->order_item_note;

            $response["is_remote_hided"] = $curtain_categories->is_remote_hided($row->curtain_category_id);
            $response["is_specification_hided"] = $curtain_categories->is_specification_hided($row->curtain_category_id);
            $response["is_width_hided"] = $curtain_categories->is_width_hided($row->curtain_category_id);
            $response["is_height_hided"] = $curtain_categories->is_height_hided($row->curtain_category_id);
            $response['min_width'] = $curtain_categories->get_min_width($row->curtain_category_id);
            $response['max_width'] = $curtain_categories->get_max_width($row->curtain_category_id);
            $response['min_height'] = $curtain_categories->get_min_height($row->curtain_category_id);
            $response['max_height'] = $curtain_categories->get_max_height($row->curtain_category_id);

            echo json_encode( $response );
            wp_die();
        }

        function order_item_dialog_save_data() {
            $curtain_models = new curtain_models();
            $curtain_remotes = new curtain_remotes();
            $curtain_specifications = new curtain_specifications();
            $width = 1;
            $height = 1;
            $qty = 1;
            if (is_numeric($_POST['_curtain_width'])) {
                $width = $_POST['_curtain_width'];
            }
            if (is_numeric($_POST['_curtain_height'])) {
                $height = $_POST['_curtain_height'];
            }
            if (is_numeric($_POST['_shopping_item_qty'])) {
                $qty = $_POST['_shopping_item_qty'];
            }
            $m_price = $curtain_models->get_price($_POST['_curtain_model_id']);
            $r_price = $curtain_remotes->get_price($_POST['_curtain_remote_id']);
            $s_price = $curtain_specifications->get_price($_POST['_curtain_specification_id']);
            if ($curtain_specifications->is_length_only($_POST['_curtain_specification_id'])==1){
                $amount = ($m_price + $r_price + $width/100 * $s_price) * $qty;
            } else {
                $amount = ($m_price + $r_price + $width/100 * $height/100 * $s_price) * $qty;
            }

            if( $_POST['_order_item_id']=='' ) {

                $this->insert_order_item(
                    array(
                        'curtain_agent_id'=>$this->curtain_agent_id,
                        'curtain_category_id'=>$_POST['_curtain_category_id'],
                        'curtain_model_id'=>$_POST['_curtain_model_id'],
                        'curtain_remote_id'=>$_POST['_curtain_remote_id'],
                        'curtain_specification_id'=>$_POST['_curtain_specification_id'],
                        'curtain_width'=>$_POST['_curtain_width'],
                        'curtain_height'=>$_POST['_curtain_height'],
                        'order_item_qty'=>$_POST['_shopping_item_qty'],
                        'order_item_note'=>$_POST['_shopping_item_note'],
                        'order_item_amount'=>$amount,
                        'is_checkout'=>0
                    )
                );
            } else {
                $this->update_order_items(
                    array(
                        'curtain_category_id'=>$_POST['_curtain_category_id'],
                        'curtain_model_id'=>$_POST['_curtain_model_id'],
                        'curtain_remote_id'=>$_POST['_curtain_remote_id'],
                        'curtain_specification_id'=>$_POST['_curtain_specification_id'],
                        'curtain_width'=>$_POST['_curtain_width'],
                        'curtain_height'=>$_POST['_curtain_height'],
                        'order_item_qty'=>$_POST['_order_item_qty'],
                        'order_item_note'=>$_POST['_order_item_note'],
                        'order_item_amount'=>$amount,
                    ),
                    array(
                        'curtain_order_id'=>$_POST['_order_item_id']
                    )
                );
            }

            $response = array();
            echo json_encode( $response );
            wp_die();
        }

        function caculate_order_item_amount($_id=0) {
            global $wpdb;
            $curtain_categories = new curtain_categories();
            $curtain_models = new curtain_models();
            $curtain_remotes = new curtain_remotes();
            $curtain_specifications = new curtain_specifications();

            $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}order_items WHERE curtain_order_id = %d", $_id ), OBJECT );

            $m_price = $curtain_models->get_price($row->curtain_model_id);
            $r_price = $curtain_remotes->get_price($row->curtain_remote_id);
            $s_price = $curtain_specifications->get_price($row->curtain_specification_id);

            if ($curtain_categories->is_height_hided($row->curtain_category_id)){
                if ($curtain_categories->is_width_hided($row->curtain_category_id)){
                    $spec_amount = 0;
                } else {
                    $spec_amount = $row->curtain_width/100 * $s_price;
                }
            } else {
                if ($curtain_categories->is_width_hided($row->curtain_category_id)){
                    $spec_amount = $row->curtain_height/100 * $s_price;
                } else {
                    $spec_amount = $row->curtain_width/100 * $row->curtain_height/100 * $s_price;
                }
            }
            
            $sub_amount = 0;
            $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}sub_items WHERE order_item_id={$_id}", OBJECT );                
            foreach ( $results as $index=>$result ) {
                $parts_price = $curtain_models->get_price($result->parts_id);
                $parts_amount = $parts_price * $result->parts_qty;
                $sub_amount = $sub_amount + $parts_amount;
            }
            $amount = ($m_price + $sub_amount + $spec_amount) * $row->order_item_qty;

            return $amount;
        }

        function select_category_id() {
            $curtain_categories = new curtain_categories();
            $curtain_models = new curtain_models();
            $curtain_remotes = new curtain_remotes();
            $curtain_specifications = new curtain_specifications();

            $_id = $_POST['id'];
            $response = array();
            $response["curtain_model_id"] = $curtain_models->select_options($_id);
            $response["curtain_specification_id"] = $curtain_specifications->select_options($_id);
            $response["is_remote_hided"] = $curtain_categories->is_remote_hided($_id);
            $response["is_specification_hided"] = $curtain_categories->is_specification_hided($_id);
            $response["is_width_hided"] = $curtain_categories->is_width_hided($_id);
            $response["is_height_hided"] = $curtain_categories->is_height_hided($_id);
            $response['min_width'] = $curtain_categories->get_min_width($_id);
            $response['max_width'] = $curtain_categories->get_max_width($_id);
            $response['min_height'] = $curtain_categories->get_min_height($_id);
            $response['max_height'] = $curtain_categories->get_max_height($_id);
            echo json_encode( $response );
            wp_die();
        }

        function select_order_status() {
            $this->update_customer_orders(
                array(
                    'customer_order_status'=>$_POST['_customer_order_status'],
                ),
                array(
                    'customer_order_number'=>$_POST['_customer_order_number'],
                )
            );
            $this->order_status_notice($_POST['_customer_order_number'], $_POST['_customer_order_status']);

            $response = array();
            echo json_encode( $response );
            wp_die();
        }
        
        function sub_items_dialog_get_data() {
            global $wpdb;
            $curtain_categories = new curtain_categories();
            $curtain_models = new curtain_models();

            $_id = $_POST['_id'];
            $sub_item_list = array();
            $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}sub_items WHERE order_item_id={$_id}", OBJECT );                
            foreach ( $results as $index=>$result ) {
                $value = array();
                $value["sub_item_id"] = $result->sub_item_id;
                $value["parts_id"] = $curtain_models->get_description($result->parts_id);
                $value["parts_qty"] = $result->parts_qty;
                array_push($sub_item_list, $value);
            }
            $response = array();
            $response["sub_item_list"] = $sub_item_list;
            $response["parts_options"] = $curtain_categories->parts_options();
            echo json_encode( $response );
            wp_die();
        }

        function sub_items_dialog_save_data() {
            if( $_POST['_sub_item_id']=='' ) {
                $this->insert_sub_item(
                    array(
                        'order_item_id'=>$_POST['_order_item_id'],
                        'parts_id'=>$_POST['_parts_id'],
                        'parts_qty'=>$_POST['_parts_qty'],
                    )
                );
                $this->update_order_items(
                    array(
                        'order_item_amount'=>$this->caculate_order_item_amount($_POST['_order_item_id']),
                    ),
                    array(
                        'curtain_order_id'=>$_POST['_order_item_id']
                    )
                );
            }

            $response = array();
            echo json_encode( $response );
            wp_die();
        }

        public function delete_sub_items($where=[]) {
            global $wpdb;
            $table = $wpdb->prefix.'sub_items';
            $wpdb->delete($table, $where);
        }

        public function insert_sub_item($data=[]) {
            global $wpdb;
            $table = $wpdb->prefix.'sub_items';
            $data['create_timestamp'] = time();
            $data['update_timestamp'] = time();
            $wpdb->insert($table, $data);
            return $wpdb->insert_id;
        }

        public function insert_customer_order($data=[]) {
            global $wpdb;
            $table = $wpdb->prefix.'customer_orders';
            $data['create_timestamp'] = time();
            $data['update_timestamp'] = time();
            $wpdb->insert($table, $data);
            return $wpdb->insert_id;
        }

        public function update_customer_orders($data=[], $where=[]) {
            global $wpdb;
            $table = $wpdb->prefix.'customer_orders';
            $data['update_timestamp'] = time();
            $wpdb->update($table, $data, $where);
        }

        public function delete_customer_orders($where=[]) {
            global $wpdb;
            $table = $wpdb->prefix.'customer_orders';
            $wpdb->delete($table, $where);
        }

        public function insert_order_item($data=[]) {
            global $wpdb;
            $table = $wpdb->prefix.'order_items';
            $data['create_timestamp'] = time();
            $data['update_timestamp'] = time();
            $wpdb->insert($table, $data);
            return $wpdb->insert_id;
        }

        public function update_order_items($data=[], $where=[]) {
            global $wpdb;
            $table = $wpdb->prefix.'order_items';
            $data['update_timestamp'] = time();
            $wpdb->update($table, $data, $where);
        }

        public function delete_order_items($where=[]) {
            global $wpdb;
            $table = $wpdb->prefix.'order_items';
            $wpdb->delete($table, $where);
        }

        public function create_tables() {
            global $wpdb;
            $charset_collate = $wpdb->get_charset_collate();
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
            $sql = "CREATE TABLE `{$wpdb->prefix}customer_orders` (
                customer_order_id int NOT NULL AUTO_INCREMENT,
                customer_order_number varchar(20) UNIQUE,
                curtain_agent_id int,
                customer_order_amount decimal(10,0),
                customer_order_status varchar(10),
                create_timestamp int(10),
                update_timestamp int(10),
                PRIMARY KEY (customer_order_id)
            ) $charset_collate;";
            dbDelta($sql);

            $sql = "CREATE TABLE `{$wpdb->prefix}order_items` (
                curtain_order_id int NOT NULL AUTO_INCREMENT,
                customer_order_number varchar(20),
                curtain_agent_id int,
                curtain_category_id int,
                curtain_model_id int,
                curtain_remote_id int,
                curtain_specification_id int,
                curtain_width int,
                curtain_height int,
                order_item_qty int DEFAULT 1,
                order_item_amount decimal(10,0),
                order_item_note text,
                is_checkout tinyint,
                create_timestamp int(10),
                update_timestamp int(10),
                PRIMARY KEY (curtain_order_id)
            ) $charset_collate;";
            dbDelta($sql);

            $sql = "CREATE TABLE `{$wpdb->prefix}sub_items` (
                sub_item_id int NOT NULL AUTO_INCREMENT,
                order_item_id int,
                parts_id int,
                parts_qty int DEFAULT 1,
                create_timestamp int(10),
                update_timestamp int(10),
                PRIMARY KEY (sub_item_id)
            ) $charset_collate;";
            dbDelta($sql);
        }
    }
    $my_class = new curtain_orders();
}