<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('curtain_orders')) {
    class curtain_orders {

        public function __construct() {

            add_shortcode( 'shopping-item-list', array( $this, 'display_shortcode' ) );
            add_action( 'init', array( $this, 'register_customer_order_post_type' ) );
            add_action( 'init', array( $this, 'register_order_item_post_type' ) );

            add_action( 'wp_ajax_get_customer_order_dialog_data', array( $this, 'get_customer_order_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_get_customer_order_dialog_data', array( $this, 'get_customer_order_dialog_data' ) );
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
            add_action( 'wp_ajax_set_curtain_agent_id', array( $this, 'set_curtain_agent_id' ) );
            add_action( 'wp_ajax_nopriv_set_curtain_agent_id', array( $this, 'set_curtain_agent_id' ) );
            add_action( 'wp_ajax_proceed_customer_order_status', array( $this, 'proceed_customer_order_status' ) );
            add_action( 'wp_ajax_nopriv_proceed_customer_order_status', array( $this, 'proceed_customer_order_status' ) );
            add_action( 'wp_ajax_print_customer_order_data', array( $this, 'print_customer_order_data' ) );
            add_action( 'wp_ajax_nopriv_print_customer_order_data', array( $this, 'print_customer_order_data' ) );
            add_action( 'wp_ajax_get_account_receivable_summary_data', array( $this, 'get_account_receivable_summary_data' ) );
            add_action( 'wp_ajax_nopriv_get_account_receivable_summary_data', array( $this, 'get_account_receivable_summary_data' ) );
            add_action( 'wp_ajax_get_account_receivable_detail_data', array( $this, 'get_account_receivable_detail_data' ) );
            add_action( 'wp_ajax_nopriv_get_account_receivable_detail_data', array( $this, 'get_account_receivable_detail_data' ) );    
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

        function display_customer_service($qr_code_serial_no=false) {
            $serial_number_post = get_page_by_title($qr_code_serial_no);
            $order_item_id = get_post_meta($serial_number_post->ID, 'order_item_id', true);
            $customer_order_id = get_post_meta($order_item_id, 'customer_order_id', true);
            $customer_name = get_post_meta($customer_order_id, 'customer_name', true);
            ?>
            <div style="text-align:center;">
                <h3>Hi, <?php echo $customer_name;?></h3>
                <div>感謝您選購我們的電動窗簾</div>
            </div>
            <?php
/*
            $output = '';
            //$qr_code_serial_no = $_GET['serial_no'];
            $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}serial_number WHERE qr_code_serial_no = %s", $qr_code_serial_no ), OBJECT );            
            /** incorrect QR-code then display the admin link 
            if (is_null($row) || !empty($wpdb->last_error)) {                        
                $output .= '<div style="font-weight:700; font-size:xx-large;">Wrong Code</div>';

            /** registration for QR-code 
            } else {                        
                $output .= 'Hi, '.$user->display_name.'<br>';
                $output .= '感謝您選購我們的電動窗簾<br>';
                $model = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}curtain_models WHERE curtain_model_id = {$row->curtain_model_id}", OBJECT );
                if (!(is_null($model) || !empty($wpdb->last_error))) {
                    $output .= '型號:'.$model->curtain_model_name.' 規格: '.$row->specification.'<br>';
                }
                $serial_number->update_serial_number(
                    array('curtain_user_id'=>intval($user->ID)),
                    array('qr_code_serial_no'=>$qr_code_serial_no)
                );

                $output .= '<form method="post" style="display:inline-block; text-align:-webkit-center;">';
                $output .= '<fieldset>';
                $output .= '<label style="text-align:left;" for="_chat_message">Question:</label>';
                $output .= '<textarea name="_chat_message" rows="10" cols="50"></textarea>';
                $output .= '<input type="hidden" name="_chat_user_id" value="'.$user->ID.'" />';
                $output .= '<input type="hidden" name="_curtain_agent_id" value="'.$row->curtain_agent_id.'" />';
                $output .= '<input type="submit" name="_chat_submit" style="margin:3px;" value="Submit" />';
                $output .= '</fieldset>';
                $output .= '</form>';

            }
            $output .= '</div>';
            return $output;        
*/
        }

        function display_shortcode() {
            // Check if the user is logged in
            if (is_user_logged_in()) {
                $this->data_migration();

                // Start point
                if (isset($_GET['_is_admin'])) {
                    echo '<input type="hidden" id="is-admin" value="1" />';
                }
                if (isset($_GET['_serial_no'])) $this->display_customer_service($_GET['_serial_no']);

                $current_user = wp_get_current_user();
                $current_user_id = get_current_user_id();
                $is_warehouse_personnel = get_user_meta($current_user_id, 'is_warehouse_personnel', true);
                if ($is_warehouse_personnel) $this->display_shipping_list();
                else {
                    $curtain_agent_id = get_user_meta($current_user_id, 'curtain_agent_id', true);
                    if ($curtain_agent_id) {
                        if (isset($_GET['_id'])) {
                            echo '<div class="ui-widget" id="result-container">';
                            echo $this->display_customer_order_dialog($_GET['_id']);
                            echo '</div>';
                        } else if ($_GET['_category']==2) {
                            $this->display_customer_order_list();
                        } else {
                            $this->display_quotation_list();
                        }    
    
                    } else {
                        ?>
                        <div style="text-align:center;">
                            <h4><?php echo __( '經銷商登入/註冊', 'your-text-domain' );?></h4>
                            <fieldset>
                                <label style="text-align:left;" for="agent-number"><?php echo __( '經銷商代碼:', 'your-text-domain' );?></label>
                                <input type="text" id="agent-number" />
                                <label style="text-align:left;" for="agent-password"><?php echo __( '經銷商密碼:', 'your-text-domain' );?></label>
                                <input type="password" id="agent-password" />
                                <label style="text-align:left;" for="display-name"><?php echo __( 'Name:', 'your-text-domain' );?></label>
                                <input type="text" id="display-name" value="<?php echo $current_user->display_name;?>" />
                                <label style="text-align:left;" for="user-email"><?php echo __( 'Email:', 'your-text-domain' );?></label>
                                <input type="text" id="user-email" value="<?php echo $current_user->user_email;?>" />
                                <input type="button" id="agent-submit" style="margin:3px;" value="Submit" />
                            </fieldset>
                        </div>
                        <?php
                    }
    
                }

            } else {
                if (isset($_GET['_serial_no'])) $this->display_customer_service($_GET['_serial_no']);
                else user_did_not_login_yet();
            }        
        }

        function display_quotation_list() {
            $curtain_agents = new curtain_agents();
            if (!current_user_can('administrator')) $is_disabled='disabled';

            $current_user_id = get_current_user_id();
            if (isset($_GET['_curtain_agent_id'])) {
                $curtain_agent_id = sanitize_text_field($_GET['_curtain_agent_id']);
            } else {
                $curtain_agent_id = get_user_meta($current_user_id, 'curtain_agent_id', true);
            }
            ?>
            <div class="ui-widget" id="result-container">
            <div id="quotation-title"><h2 style="display:inline;"><?php echo __( '報價單', 'your-text-domain' );?></h2></div>
            <fieldset>
                <div style="display:flex; justify-content:space-between; margin:5px;">
                    <div id="quotation-select">
                        <select id="select-order-category">
                            <option value="1" selected><?php echo __( '報價單', 'your-text-domain' );?></option>
                            <option value="2"><?php echo __( '訂單總覽', 'your-text-domain' );?></option>
                        </select>
                    </div>
                    <div style="text-align:right; display:flex;">
                        <input type="text" id="search-order" style="display:inline" placeholder="Search..." />
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
                    $query = $this->retrieve_customer_order_data($current_page, $curtain_agent_id);
                    $total_posts = $query->found_posts;
                    $total_pages = ceil($total_posts / $posts_per_page); // Calculate the total number of pages
        
                    if ($query->have_posts()) :
                        while ($query->have_posts()) : $query->the_post();
                            $customer_name = get_post_meta(get_the_ID(), 'customer_name', true);
                            //$modified_time = get_post_modified_time('F j, Y g:i a', false, get_the_ID());
                            $modified_time = get_post_modified_time(get_option('date_format'), false, get_the_ID());
                            $customer_order_amount = get_post_meta(get_the_ID(), 'customer_order_amount', true);
                            $customer_order_amount = ($customer_order_amount) ? $customer_order_amount : 0;
                            $customer_order_remark = get_post_meta(get_the_ID(), 'customer_order_remark', true);
                            ?>
                            <tr id="edit-quotation-<?php the_ID();?>">
                                <td style="text-align:center;"><?php echo esc_html($modified_time);?></td>
                                <td><?php echo esc_html($customer_name);?></td>
                                <td style="text-align:center;"><?php echo number_format_i18n($customer_order_amount);?></td>
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
                <div class="pagination">
                    <?php
                    // Display pagination links
                    if ($current_page > 1) echo '<span class="custom-button"><a href="' . esc_url(get_pagenum_link($current_page - 1)) . '"> < </a></span>';
                    echo '<span class="page-numbers">' . sprintf(__('Page %d of %d', 'textdomain'), $current_page, $total_pages) . '</span>';
                    if ($current_page < $total_pages) echo '<span class="custom-button"><a href="' . esc_url(get_pagenum_link($current_page + 1)) . '"> > </a></span>';
                    ?>
                </div>
                <div style="display:flex; justify-content:space-between; margin:5px;">
                    <div>
                        <select id="select-curtain-agent" <?php echo $is_disabled;?>><?php echo $curtain_agents->select_curtain_agent_options($curtain_agent_id);?></select>                        
                    </div>
                    <div style="text-align:right; display:flex;">
                    </div>
                </div>        
            </fieldset>
            </div>
            <?php
        }

        function display_customer_order_list() {
            $curtain_agents = new curtain_agents();
            if (!current_user_can('administrator')) $is_disabled='disabled';

            $current_user_id = get_current_user_id();
            if (isset($_GET['_curtain_agent_id'])) {
                $curtain_agent_id = sanitize_text_field($_GET['_curtain_agent_id']);
            } else {
                $curtain_agent_id = get_user_meta($current_user_id, 'curtain_agent_id', true);
            }
            ?>
            <div class="ui-widget" id="result-container">
            <div id="customer-order-title"><h2><?php echo __( '訂單總覽', 'your-text-domain' );?></h2></div>
            <fieldset>
                <div style="display:flex; justify-content:space-between; margin:5px;">
                    <div id="customer-order-select">
                        <select id="select-order-category">
                            <option value="1"><?php echo __( '報價單', 'your-text-domain' );?></option>
                            <option value="2" selected><?php echo __( '訂單總覽', 'your-text-domain' );?></option>
                        </select>
                    </div>
                    <div style="text-align:right; display:flex;">
                        <input type="text" id="search-order" style="display:inline" placeholder="Search..." />
                    </div>
                </div>
        
                <table class="ui-widget" style="width:100%;">
                    <thead>
                        <tr>
                            <th><?php echo __( '訂單', 'your-text-domain' );?></th>
                            <th><?php echo __( '日期', 'your-text-domain' );?></th>
                            <th><?php echo __( '金額', 'your-text-domain' );?></th>
                            <th><?php echo __( '狀態', 'your-text-domain' );?></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    // Define the custom pagination parameters
                    $posts_per_page = get_option('operation_row_counts');
                    $current_page = max(1, get_query_var('paged')); // Get the current page number
                    $query = $this->retrieve_customer_order_data($current_page, $curtain_agent_id);
                    $total_posts = $query->found_posts;
                    $total_pages = ceil($total_posts / $posts_per_page); // Calculate the total number of pages
        
                    if ($query->have_posts()) :
                        while ($query->have_posts()) : $query->the_post();
                            $customer_name = get_post_meta(get_the_ID(), 'customer_name', true);
                            $agent_id = get_post_meta(get_the_ID(), 'curtain_agent_id', true);
                            $curtain_agent_number = get_post_meta($agent_id, 'curtain_agent_number', true);
                            $curtain_agent_name = get_post_meta($agent_id, 'curtain_agent_name', true);
                            $customer_order_number = get_post_meta(get_the_ID(), 'customer_order_number', true);
                            $customer_order_time = wp_date(get_option('date_format'), $customer_order_number);
                            $customer_order_amount = get_post_meta(get_the_ID(), 'customer_order_amount', true);
                            $customer_order_amount = ($customer_order_amount) ? $customer_order_amount : 0;
                            $order_status_id = get_post_meta(get_the_ID(), 'customer_order_status', true);
                            $status_color = get_post_meta($order_status_id, 'status_color', true);
                            $customer_order_status = get_post_field('post_content', $order_status_id);
                            if (current_user_can('administrator')) $customer_order_status = $curtain_agent_name.'('.$curtain_agent_number.'):'.$customer_order_status;
                            ?>
                            <tr id="edit-quotation-<?php the_ID();?>">
                                <td style="text-align:center;"><?php echo esc_html($customer_order_number);?></td>
                                <td style="text-align:center;"><?php echo esc_html($customer_order_time);?></td>
                                <td style="text-align:center;"><?php echo number_format_i18n($customer_order_amount);?></td>
                                <td style="color:<?php echo esc_attr($status_color);?>"><?php echo esc_html($customer_order_status);?></td>
                            </tr>
                            <?php
                        endwhile;
                        wp_reset_postdata();
                    endif;
                    ?>
                    </tbody>
                </table>
                <div class="pagination">
                    <?php
                    // Display pagination links
                    if ($current_page > 1) echo '<span class="custom-button"><a href="' . esc_url(get_pagenum_link($current_page - 1)) . '"> < </a></span>';
                    echo '<span class="page-numbers">' . sprintf(__('Page %d of %d', 'textdomain'), $current_page, $total_pages) . '</span>';
                    if ($current_page < $total_pages) echo '<span class="custom-button"><a href="' . esc_url(get_pagenum_link($current_page + 1)) . '"> > </a></span>';
                    ?>
                </div>
                <div style="display:flex; justify-content:space-between; margin:5px;">
                    <div>
                        <select id="select-curtain-agent" <?php echo $is_disabled;?>><?php echo $curtain_agents->select_curtain_agent_options($curtain_agent_id);?></select>                        
                    </div>
                    <div style="text-align:right; display:flex;">
                    </div>
                </div>        
            </fieldset>
            </div>
            <?php
        }

        function retrieve_customer_order_data($current_page = 1, $curtain_agent_id=false) {
            // Define the custom pagination parameters
            $posts_per_page = get_option('operation_row_counts');
        
            $current_user_id = get_current_user_id();
            if (isset($_GET['_curtain_agent_id'])) {
                $curtain_agent_id = sanitize_text_field($_GET['_curtain_agent_id']);
            } else {
                $curtain_agent_id = get_user_meta($current_user_id, 'curtain_agent_id', true);
            }

            $curtain_agent_filter = array(
                'key'     => 'curtain_agent_id',
                'value'   => $curtain_agent_id,
                'compare' => '=',
            );

            if (isset($_GET['_category'])) {
                $customer_order_category = sanitize_text_field($_GET['_category']);
                if ($customer_order_category==2 && current_user_can('administrator')) $curtain_agent_id='';
            } else {
                $customer_order_category = 1;
            }
            $order_category_filter = array(
                'key'     => 'customer_order_category',
                'value'   => $customer_order_category,
                'compare' => '=',
            );
        
            $args = array(
                'post_type'      => 'customer-order',
                'posts_per_page' => $posts_per_page,
                'paged'          => $current_page,
                'meta_query'     => array(
                    'relation' => 'AND',
                    ($curtain_agent_id) ? $curtain_agent_filter : '',
                    ($customer_order_category) ? $order_category_filter : '',
                ),
                'orderby'        => 'modified', // Sort by post modified time
                'order'          => 'DESC', // Sorting order (descending)
            );
        
            // Add meta query for searching across all meta keys
            $search_query = sanitize_text_field($_GET['_search']);
            $meta_keys = get_post_type_meta_keys('customer-order');
            $meta_query_all_keys = array('relation' => 'OR');
            foreach ($meta_keys as $meta_key) {
                $meta_query_all_keys[] = array(
                    'key'     => $meta_key,
                    'value'   => $search_query,
                    'compare' => 'LIKE',
                );
            }            
            $args['meta_query'][] = $meta_query_all_keys;
                    
            $query = new WP_Query($args);
            return $query;
        }
        
        function display_shipping_list() {
            ?>
            <div class="ui-widget" id="result-container">
            <div id="customer-order-title"><h2><?php echo __( 'Shipping list', 'your-text-domain' );?></h2></div>
            <fieldset>
                <div style="display:flex; justify-content:space-between; margin:5px;">
                    <div id="customer-order-select">
                    </div>
                    <div style="text-align:right; display:flex;">
                        <input type="text" id="search-order" style="display:inline" placeholder="Search..." />
                    </div>
                </div>
        
                <table class="ui-widget" style="width:100%;">
                    <thead>
                        <tr>
                            <th><?php echo __( '採購單號', 'your-text-domain' );?></th>
                            <th><?php echo __( '日期', 'your-text-domain' );?></th>
                            <th><?php echo __( '淘寶訂單號', 'your-text-domain' );?></th>
                            <th><?php echo __( '快遞單號', 'your-text-domain' );?></th>
                            <th><?php echo __( '送貨單號', 'your-text-domain' );?></th>
                            <th><?php echo __( '送貨日期', 'your-text-domain' );?></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    // Define the custom pagination parameters
                    $posts_per_page = get_option('operation_row_counts');
                    $current_page = max(1, get_query_var('paged')); // Get the current page number
                    $query = $this->retrieve_shipping_list_data($current_page);
                    $total_posts = $query->found_posts;
                    $total_pages = ceil($total_posts / $posts_per_page); // Calculate the total number of pages
        
                    if ($query->have_posts()) :
                        while ($query->have_posts()) : $query->the_post();
                            $customer_order_number = get_post_meta(get_the_ID(), 'customer_order_number', true);
                            $customer_order_time = wp_date(get_option('date_format'), $customer_order_number);
                            $taobao_order_number = get_post_meta(get_the_ID(), 'taobao_order_number', true);
                            $taobao_ship_number = get_post_meta(get_the_ID(), 'taobao_ship_number', true);
                            $curtain_ship_number = get_post_meta(get_the_ID(), 'curtain_ship_number', true);
                            $curtain_ship_date = get_post_meta(get_the_ID(), 'curtain_ship_date', true);
                            ?>
                            <tr id="edit-quotation-<?php the_ID();?>">
                                <td style="text-align:center;"><?php echo esc_html($customer_order_number);?></td>
                                <td style="text-align:center;"><?php echo esc_html($customer_order_time);?></td>
                                <td style="text-align:center;"><?php echo esc_html($taobao_order_number);?></td>
                                <td style="text-align:center;"><?php echo esc_html($taobao_ship_number);?></td>
                                <td style="text-align:center;"><?php echo esc_html($curtain_ship_number);?></td>
                                <td style="text-align:center;"><?php echo wp_date(get_option('date_format'), $curtain_ship_date);?></td>
                            </tr>
                            <?php
                        endwhile;
                        wp_reset_postdata();
                    endif;
                    ?>
                    </tbody>
                </table>
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
            <?php
        }

        function retrieve_shipping_list_data($current_page = 1, $curtain_agent_id=false) {
            // Define the custom pagination parameters
            $posts_per_page = get_option('operation_row_counts');

            $status_id_03 = $this->get_status_id_by_status_code('order03');
            $status_id_04 = $this->get_status_id_by_status_code('order04');
            $status_id_05 = $this->get_status_id_by_status_code('order05');

            $args = array(
                'post_type'      => 'customer-order',
                'posts_per_page' => $posts_per_page,
                'paged'          => $current_page,
                'meta_query'     => array(
                    'relation' => 'AND',
                    array(
                        'relation' => 'OR',
                        array(
                            'key'     => 'customer_order_status',
                            'value'   => $status_id_03,
                            'compare' => '=',
                        ),
                        array(
                            'key'     => 'customer_order_status',
                            'value'   => $status_id_04,
                            'compare' => '=',
                        ),
                        array(
                            'key'     => 'customer_order_status',
                            'value'   => $status_id_05,
                            'compare' => '=',
                        ),        
                    )
                ),
                'orderby'        => 'modified', // Sort by post modified time
                'order'          => 'DESC', // Sorting order (descending)
            );

            // Add meta query for searching across all meta keys
            $search_query = sanitize_text_field($_GET['_search']);
            $meta_keys = get_post_type_meta_keys('customer-order');
            $meta_query_all_keys = array('relation' => 'OR');
            foreach ($meta_keys as $meta_key) {
                $meta_query_all_keys[] = array(
                    'key'     => $meta_key,
                    'value'   => $search_query,
                    'compare' => 'LIKE',
                );
            }            
            $args['meta_query'][] = $meta_query_all_keys;

            $query = new WP_Query($args);
            return $query;
        }
        
        function get_status_id_by_status_code($status_code) {
            $args = array(
                'post_type'  => 'order-status',
                'meta_query' => array(
                    array(
                        'key'   => 'status_code',
                        'value' => $status_code,
                        'compare' => '='
                    )
                ),
                'fields' => 'ids',
                'posts_per_page' => 1
            );
        
            $posts = get_posts($args);
        
            if (!empty($posts)) {
                return $posts[0];
            } else {
                return false;
            }
        }
        
        function display_customer_order_dialog($customer_order_id=false, $is_admin=false) {
            $customer_name = get_post_meta($customer_order_id, 'customer_name', true);
            $customer_order_remark = get_post_meta($customer_order_id, 'customer_order_remark', true);
            $customer_order_category = get_post_meta($customer_order_id, 'customer_order_category', true);
            $customer_order_status = get_post_meta($customer_order_id, 'customer_order_status', true);
            $taobao_order_number = get_post_meta($customer_order_id, 'taobao_order_number', true);
            $taobao_ship_number = get_post_meta($customer_order_id, 'taobao_ship_number', true);
            $curtain_ship_number = get_post_meta($customer_order_id, 'curtain_ship_number', true);
            $curtain_ship_date = get_post_meta($customer_order_id, 'curtain_ship_date', true);

            $status_action = get_post_meta($customer_order_status, 'status_action', true);
            $status_code = get_post_meta($customer_order_status, 'status_code', true);
            $next_status_code = get_post_meta($customer_order_status, 'next_status', true);
            $next_status_id = $this->get_status_id_by_status_code($next_status_code);
            ob_start();
            if ($status_code) echo '<h2 style="display:inline;">'.__( get_the_title($customer_order_status), 'your-text-domain' ).'</h2>';
            else echo '<h2 style="display:inline;">'.__( '報價單', 'your-text-domain' ).'</h2>';
            ?>
            <fieldset>
                <input type="hidden" id="customer-order-id" value="<?php echo esc_attr($customer_order_id);?>" />
                <input type="hidden" id="status-code" value="<?php echo esc_attr($status_code);?>" />
                <label for="customer-name"><?php echo __( '客戶名稱', 'your-text-domain' );?></label>
                <input type="text" id="customer-name" value="<?php echo esc_attr($customer_name);?>" class="text ui-widget-content ui-corner-all" />
                <?php if ($status_code=="order01") { //填寫淘寶訂單號?>
                    <label for="taobao-order-number"><?php echo __( '淘寶訂單號', 'your-text-domain' );?></label>
                    <input type="text" id="taobao-order-number" value="<?php echo esc_attr($taobao_order_number);?>" class="text ui-widget-content ui-corner-all" />
                <?php } else {?>
                <?php if ($status_code=="order02") { //填寫快遞單號?>
                    <label for="taobao-order-number"><?php echo __( '淘寶訂單號', 'your-text-domain' );?></label>
                    <input type="text" id="taobao-order-number" value="<?php echo esc_attr($taobao_order_number);?>" class="text ui-widget-content ui-corner-all" />
                    <label for="taobao-ship-number"><?php echo __( '快遞單號', 'your-text-domain' );?></label>
                    <input type="text" id="taobao-ship-number" value="<?php echo esc_attr($taobao_ship_number);?>" class="text ui-widget-content ui-corner-all" />
                <?php } else {?>
                <?php if ($status_code=="order03"||$status_code=="order04") { //填寫送貨單號?>
                    <label for="taobao-order-number"><?php echo __( '淘寶訂單號', 'your-text-domain' );?></label>
                    <input type="text" id="taobao-order-number" value="<?php echo esc_attr($taobao_order_number);?>" class="text ui-widget-content ui-corner-all" />
                    <label for="taobao-ship-number"><?php echo __( '快遞單號', 'your-text-domain' );?></label>
                    <input type="text" id="taobao-ship-number" value="<?php echo esc_attr($taobao_ship_number);?>" class="text ui-widget-content ui-corner-all" />
                    <label for="curtain-ship-number"><?php echo __( '送貨單號', 'your-text-domain' );?></label>
                    <input type="text" id="curtain-ship-number" value="<?php echo esc_attr($curtain_ship_number);?>" class="text ui-widget-content ui-corner-all" />
                    <label for="curtain-ship-date"><?php echo __( '送貨日期', 'your-text-domain' );?></label>
                    <input type="text" id="curtain-ship-date" value="<?php echo esc_attr(wp_date(get_option('date_format'), $curtain_ship_date));?>" class="text ui-widget-content ui-corner-all" disabled />
                <?php } else {?>
                    <label for="customer-order-remark"><?php echo __( '備註', 'your-text-domain' );?></label>
                    <textarea id="customer-order-remark" rows="2" style="width:100%;"><?php echo $customer_order_remark;?></textarea>
                <?php }}}?>

                <?php if ($customer_order_category>1) {?>
                    <label for="customer-order-status"><?php echo __( '狀態', 'your-text-domain' );?></label>
                    <input type="text" id="customer-order-status" value="<?php echo esc_attr(get_post_field('post_content', $customer_order_status));?>" class="text ui-widget-content ui-corner-all" />
                <?php }?>
                <?php echo $this->display_order_item_list($customer_order_id, $is_admin);?>
                <div id="account-receivable-dialog" title="Account Receivable"></div>

                <?php if ($customer_order_category<=1 || $is_admin==1) {?>
                <hr>
                <div style="display:flex; justify-content:space-between; margin:5px;">
                    <div>
                        <input type="button" id="save-quotation" value="<?php echo __( 'Save', 'your-text-domain' );?>" style="margin:3px; display:inline;" />
                        <input type="button" id="del-quotation" value="<?php echo __( 'Delete', 'your-text-domain' );?>" style="margin:3px; display:inline;" />
                    </div>
                    <div style="text-align:right; display:flex;">
                        <?php $quotation_status_id = $this->get_status_id_by_status_code('order00');?>
                        <?php $quotation_status_action = get_post_meta($quotation_status_id, 'status_action', true);?>
                        <?php $quotation_next_status = $this->get_status_id_by_status_code('order01');?>
                        <input type="button" id="proceed-customer-order-status-<?php echo esc_attr($quotation_next_status);?>" value="<?php echo __( $quotation_status_action, 'your-text-domain' );?>" style="margin:3px; display:inline;" />
                    </div>
                </div>
                <?php 
                    } else {
                        $current_user_id = get_current_user_id();
                        $is_warehouse_personnel = get_user_meta($current_user_id, 'is_warehouse_personnel', true);
                        if (current_user_can('administrator')||$is_warehouse_personnel) {
                            echo '<hr>';
                            if ($status_code!="order05") echo '<input type="button" id="proceed-customer-order-status-'.$next_status_id.'" value="'.__( $status_action, 'your-text-domain' ).'" style="margin:3px; display:inline;" />';
                            echo '<input type="button" id="print-customer-order-'.$customer_order_id.'" value="'.__( '印出貨單', 'your-text-domain' ).'" style="margin:3px; display:inline;" />';
                            $curtain_agent_id = get_post_meta($customer_order_id, 'curtain_agent_id', true);
                            echo '<input type="button" id="account-receivable-'.$curtain_agent_id.'" value="'.__( '請款列表', 'your-text-domain' ).'" style="margin:3px; display:inline;" />';
                            if (current_user_can('administrator')) echo '<input type="button" id="cancel-customer-order-'.$customer_order_id.'" value="'.__( '取消本單', 'your-text-domain' ).'" style="margin:3px; display:inline;" />';
                            echo '<input type="button" id="exit-customer-order-dialog" value="'.__( 'Exit', 'your-text-domain' ).'" style="margin:3px; display:inline;" />';
                        }
                    }
                ?>
            </fieldset>
            <?php
            return ob_get_clean();
        }
        
        function print_customer_order_data() {
            $response = array();
            if (isset($_POST['_customer_order_id'])) {
                $customer_order_id = sanitize_text_field($_POST['_customer_order_id']);
                $customer_order_number = get_post_meta($customer_order_id, 'customer_order_number', true);
                $customer_order_time = wp_date(get_option('date_format'), $customer_order_number);
                $curtain_agent_id = get_post_meta($customer_order_id, 'curtain_agent_id', true);
                $curtain_agent_number = get_post_meta($curtain_agent_id, 'curtain_agent_number', true);
                $curtain_agent_name = get_post_meta($curtain_agent_id, 'curtain_agent_name', true);
                $curtain_agent_contact = get_post_meta($curtain_agent_id, 'curtain_agent_contact', true);
                $curtain_agent_phone = get_post_meta($curtain_agent_id, 'curtain_agent_phone', true);
                $curtain_agent_address = get_post_meta($curtain_agent_id, 'curtain_agent_address', true);
                $customer_order_remark = get_post_meta($customer_order_id, 'customer_order_remark', true);
                $customer_order_status = get_post_meta($customer_order_id, 'customer_order_status', true);
                ob_start();            
                ?>
                <h2 style="text-align:center;"><?php echo __( '出貨單', 'your-text-domain' );?></h2>
                <fieldset>
                    <input type="hidden" id="customer-order-id" value="<?php echo esc_attr($customer_order_id);?>" />
                    <table>
                        <thead>
                        <tr>
                            <th><?php echo __( '訂單號碼：', 'your-text-domain' );?></th>
                            <td><?php echo esc_html($customer_order_number);?></td>
                            <th><?php echo __( '訂單日期：', 'your-text-domain' );?></th>
                            <td><?php echo esc_html($customer_order_time);?></td>
                        </tr>
                        <tr>
                            <th><?php echo __( '客戶名稱：', 'your-text-domain' );?></th>
                            <td colspan=3><?php echo esc_html($curtain_agent_name.'('.$curtain_agent_number.')');?></td>
                        </tr>
                        <tr>
                            <th><?php echo __( '收件人：', 'your-text-domain' );?></th>
                            <td><?php echo esc_html($curtain_agent_contact);?></td>
                            <th><?php echo __( '聯絡電話：', 'your-text-domain' );?></th>
                            <td><?php echo esc_html($curtain_agent_phone);?></td>
                        </tr>
                        <tr>
                            <th><?php echo __( '收件地址：', 'your-text-domain' );?></th>
                            <td colspan=3><?php echo esc_html($curtain_agent_address);?></td>
                        </tr>
                        </thead>
                    </table>

                    <fieldset>
                    <table style="width:100%;">
                        <thead>
                            <tr>
                                <th><?php echo __( '產品', 'your-text-domain' );?></th>
                                <th><?php echo __( '規格', 'your-text-domain' );?></th>
                                <th><?php echo __( '尺寸', 'your-text-domain' );?></th>
                                <th><?php echo __( '數量', 'your-text-domain' );?></th>
                            </tr>
                        </thead>
                        </tfoot>
                            <?php
                            $query = $this->retrieve_order_item_data($customer_order_id);
                            if ($query->have_posts()) {
                                while ($query->have_posts()) : $query->the_post();
                                    $curtain_category_id = get_post_meta(get_the_ID(), 'curtain_category_id', true);
                                    $curtain_category_title = get_the_title($curtain_category_id);
                                    $is_specification = get_post_meta($curtain_category_id, 'is_specification', true);
                                    $is_height = get_post_meta($curtain_category_id, 'is_height', true);
                                    $curtain_model_id = get_post_meta(get_the_ID(), 'curtain_model_id', true);
                                    $curtain_model_description = get_post_field('post_content', $curtain_model_id);
                                    $curtain_model_price = get_post_meta($curtain_model_id, 'curtain_model_price', true);
                                    $curtain_model_price = ($curtain_model_price) ? $curtain_model_price : 0;
                                    $curtain_specification_id = get_post_meta(get_the_ID(), 'curtain_specification_id', true);
                                    $curtain_specification_description = get_post_field('post_content', $curtain_specification_id);
                                    $curtain_specification_price = get_post_meta($curtain_specification_id, 'curtain_specification_price', true);
                                    $curtain_specification_price = ($curtain_specification_price) ? $curtain_specification_price : 0;
    
                                    $curtain_width = get_post_meta(get_the_ID(), 'curtain_width', true);
                                    $curtain_width = ($curtain_width) ? $curtain_width : 1;
                                    $curtain_height = get_post_meta(get_the_ID(), 'curtain_height', true);
                                    $curtain_height = ($curtain_height) ? $curtain_height : 1;
                                    $order_item_qty = get_post_meta(get_the_ID(), 'order_item_qty', true);
                                    $order_item_qty = ($order_item_qty) ? $order_item_qty : 1;
    
                                    $curtain_specification_description .= ' W:'.$curtain_width;
                                    $order_item_description = $curtain_model_description.'('.get_the_title($curtain_model_id).')';                                
                                    $order_item_amount = $order_item_qty * ($curtain_model_price + $curtain_specification_price * ($curtain_width/100) * ($curtain_height/100));
                                    if ($is_height==1) $order_item_amount = $order_item_qty * ($curtain_model_price + $curtain_specification_price * ($curtain_width/100));
                                    else $curtain_specification_description .= ' H:'.$curtain_height;
                                    if ($is_specification==1) $order_item_amount = $order_item_qty * $curtain_model_price;
                                    else $order_item_description .= '<br>'.$curtain_specification_description;
                                    $customer_order_amount += $order_item_amount;
    
                                    echo '<tr>';
                                    echo '<td style="text-align:center;">'.esc_html($curtain_category_title).'</td>';
                                    echo '<td style="text-align:center;">'.esc_html($curtain_model_description).'</td>';
                                    if ($is_specification==1) echo '<td></td>';
                                    else echo '<td>'.$curtain_specification_description.'</td>';
                                    echo '<td style="text-align:center;">'.number_format_i18n($order_item_qty).'</td>';
                                    echo '</tr>';
                                endwhile;
                                wp_reset_postdata();
                            }
                            ?>
                        </tfoot>
                    </table>
                    <div><?php echo __( '備註:', 'your-text-domain' );?><?php echo esc_html($customer_order_remark);?></div>
                    </fieldset>
    
                    <hr>
                    <div style="display:flex; justify-content:space-between; margin:5px;">
                        <div>
                            <input type="button" id="exit-customer-order-printing" value="<?php echo __( 'Exit', 'your-text-domain' );?>" style="margin:3px; display:inline;" />
                        </div>
                        <div style="text-align:right; display:flex;">
                        </div>
                    </div>
                </fieldset>
                <?php
                //$html = ob_get_clean();
                $response['html_contain'] = ob_get_clean();
            }
            wp_send_json($response);
        }

        function display_account_receivable_dialog($curtain_agent_id=false) {
            $curtain_agent_number = get_post_meta($curtain_agent_id, 'curtain_agent_number', true);
            $curtain_agent_name = get_post_meta($curtain_agent_id, 'curtain_agent_name', true);
            ob_start();
            ?>
            <h2 style="display:inline;"><?php echo $curtain_agent_name.'('.$curtain_agent_number.')';?></h2>
            <table class="ui-widget" style="width:100%;">
                <thead>
                    <tr>
                        <th>#</th>
                        <th><?php echo __( '訂單號碼', 'your-text-domain' );?></th>
                        <th><?php echo __( '訂單日期', 'your-text-domain' );?></th>
                        <th><?php echo __( '金額', 'your-text-domain' );?></th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $status_id_04 = $this->get_status_id_by_status_code('order04');
                $status_id_05 = $this->get_status_id_by_status_code('order05');
                
                $args = array(
                    'post_type'      => 'customer-order',
                    'posts_per_page' => -1,
                    'meta_query'     => array(
                        'relation' => 'AND',
                        array(
                            'key'     => 'curtain_agent_id',
                            'value'   => $curtain_agent_id,
                            'compare' => '=',
                        ),
                        array(
                            'key'     => 'customer_order_status',
                            'value'   => $status_id_04,
                            'compare' => '=',
                        ),
                    ),
                    //'orderby'        => 'modified', // Sort by post modified time
                    //'order'          => 'DESC', // Sorting order (descending)
                );
    
                $query = new WP_Query($args);
    
                if ($query->have_posts()) :
                    $sum = 0;
                    while ($query->have_posts()) : $query->the_post();
                        $customer_order_number = get_post_meta(get_the_ID(), 'customer_order_number', true);
                        $customer_order_date = wp_date(get_option('date_format'), $customer_order_number);
                        $customer_order_amount = get_post_meta(get_the_ID(), 'customer_order_amount', true);
                        $sum += $customer_order_amount;
                        ?>
                        <tr>
                            <td style="text-align:center;"><input type="checkbox" class="customer_order_ids" id="<?php echo esc_html(get_the_ID());?>" checked /></td>
                            <td style="text-align:center;"><?php echo esc_html($customer_order_number);?></td>
                            <td style="text-align:center;"><?php echo esc_html($customer_order_date);?></td>
                            <td style="text-align:center;"><?php echo number_format_i18n($customer_order_amount);?></td>
                        </tr>
                        <?php
                    endwhile;                    
                    wp_reset_postdata();
                    ?><tr><td></td><td></td><td style="text-align:right;"><?php echo __( '總金額：', 'your-text-domain' );?></td><td><?php echo number_format_i18n($sum);?></td></tr><?php
                endif;
                ?>
                </tbody>
            </table>

            <?php
            return ob_get_clean();
        }

        function get_account_receivable_summary_data() {
            $response = array();
            $curtain_agent_id = sanitize_text_field($_POST['_curtain_agent_id']);
            $response['html_contain'] = $this->display_account_receivable_dialog($curtain_agent_id);
            wp_send_json($response);
        }

        function get_account_receivable_detail_data() {
            $response = array();
            //$customer_order_ids = sanitize_text_field($_POST['_customer_order_ids']);
            $response['html_contain'] = $this->print_account_receivable_detail_data($_POST['_customer_order_ids']);
            wp_send_json($response);
        }

        function print_account_receivable_detail_data($customer_order_ids=array()) {
            //$curtain_agent_number = get_post_meta($curtain_agent_id, 'curtain_agent_number', true);
            //$curtain_agent_name = get_post_meta($curtain_agent_id, 'curtain_agent_name', true);
            ob_start();
            $sum = 0;

            foreach ($customer_order_ids as $customer_order_id) {
                $customer_order_number = get_post_meta($customer_order_id, 'customer_order_number', true);
                $customer_order_date = wp_date(get_option('date_format'), $customer_order_number);

                ?>
                <h2 style="display:inline;"><?php echo __( '訂單號碼：', 'your-text-domain' ).$customer_order_number;?></h2>
                <h2 style="display:inline;"><?php echo __( '訂單日期：', 'your-text-domain' ).$customer_order_date;?></h2>
<?php

?>
                <table class="ui-widget" style="width:100%;">
                    <thead>
                        <tr>
                            <th><?php echo __( 'Item', 'your-text-domain' );?></th>
                            <th><?php echo __( 'Description', 'your-text-domain' );?></th>
                            <th><?php echo __( 'QTY', 'your-text-domain' );?></th>
                            <th><?php echo __( 'Amount', 'your-text-domain' );?></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    
                    $args = array(
                        'post_type'      => 'order-item',
                        'posts_per_page' => -1,
                        'meta_query'     => array(
                            array(
                                'key'     => 'customer_order_id',
                                'value'   => $customer_order_id,
                                'compare' => '=',
                            ),
                        ),
                    );
        
                    $query = new WP_Query($args);
/*        
                    if ($query->have_posts()) :
                        while ($query->have_posts()) : $query->the_post();
                            $curtain_category_id = get_post_meta(get_the_ID(), 'curtain_category_id', true);
                            $curtain_model_id = get_post_meta(get_the_ID(), 'curtain_model_id', true);
                            $order_item_qty = get_post_meta(get_the_ID(), 'order_item_qty', true);
                            $order_item_amount = get_post_meta(get_the_ID(), 'order_item_amount', true);
                            $sum += $order_item_amount;
                            ?>
                            <tr>
                                <td style="text-align:center;"><?php echo esc_html(get_the_title($curtain_category_id));?></td>
                                <td style="text-align:center;"><?php echo esc_html(get_the_title($curtain_model_id));?></td>
                                <td style="text-align:center;"><?php echo esc_html($order_item_qty);?></td>
                                <td style="text-align:center;"><?php echo number_format_i18n($order_item_amount);?></td>
                            </tr>
                            <?php
                        endwhile;                    
                        wp_reset_postdata();
                    endif;
*/
                    ?>
                    </tbody>
                </table>
    
                <?php

            }

            ?><div style="text-align:right;"><?php echo __( '總金額：', 'your-text-domain' );?><?php echo number_format_i18n($sum);?></div><?php
            return ob_get_clean();
        }

        function get_customer_order_dialog_data() {
            $response = array();
            if (isset($_POST['_customer_order_id'])) {
                $customer_order_id = sanitize_text_field($_POST['_customer_order_id']);
                $response['html_contain'] = $this->display_customer_order_dialog($customer_order_id);

                if (isset($_POST['_is_admin'])) {
                    $is_admin = sanitize_text_field($_POST['_is_admin']);
                    if (current_user_can('administrator') && $is_admin=="1") {
                        $response['html_contain'] = $this->display_customer_order_dialog($customer_order_id, $is_admin);
                    }
                }        
            }
            wp_send_json($response);
        }

        function set_quotation_dialog_data() {
            $response = array();
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
                update_post_meta( $post_id, 'curtain_agent_id', sanitize_text_field($_POST['_curtain_agent_id']));
                update_post_meta( $post_id, 'customer_name', 'New customer');
                update_post_meta( $post_id, 'customer_order_category', 1);
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

        function set_curtain_agent_id() {
            $response = array();
            if( isset($_POST['_agent_number']) ) {
                // Update the quotation data
                $agent_number = sanitize_text_field($_POST['_agent_number']);
                $agent_password = sanitize_text_field($_POST['_agent_password']);
                $display_name = sanitize_text_field($_POST['_display_name']);
                $user_email = sanitize_text_field($_POST['_user_email']);

                $args = array(
                    'post_type'      => 'curtain-agent',
                    'posts_per_page' => 1, // Assuming you only want to retrieve one post
                    'meta_query'     => array(
                        'relation' => 'AND',
                        array(
                            'key'     => 'curtain_agent_number',
                            'value'   => $agent_number,
                            'compare' => '=',
                        ),
                        array(
                            'key'     => 'curtain_agent_password',
                            'value'   => $agent_password,
                            'compare' => '=',
                        ),
                    ),
                );
            
                $query = new WP_Query($args);

                if ($query->have_posts()) {
                    while ($query->have_posts()) {
                        $query->the_post();
                        // Output or manipulate post data here
                        $current_user = wp_get_current_user();
                        update_user_meta($current_user->ID, 'curtain_agent_id', get_the_ID());
                        wp_update_user([
                            'ID' => $current_user->ID,
                            'display_name' => $display_name,
                            'user_email' => $user_email,
                        ]);

                    }
                    wp_reset_postdata(); // Restore global post data
                }
            }
            wp_send_json($response);
        }

        function proceed_customer_order_status() {
            $response = array();
            if( isset($_POST['_customer_order_id'])  && isset($_POST['_next_status']) ) {
                // Update the quotation data
                $customer_order_id = sanitize_text_field($_POST['_customer_order_id']);
                $customer_order_amount = sanitize_text_field($_POST['_customer_order_amount']);
                update_post_meta( $customer_order_id, 'customer_order_amount', $customer_order_amount);
                $current_status = get_post_meta($customer_order_id, 'customer_order_status', true);
                $current_status_code = get_post_meta($current_status, 'status_code', true);

                $next_status = sanitize_text_field($_POST['_next_status']);
                $next_status_code = get_post_meta($next_status, 'status_code', true);
                update_post_meta( $customer_order_id, 'customer_order_status', $next_status);
                if ($next_status>0) {
                    update_post_meta( $customer_order_id, 'customer_order_category', 2);
                    if ($next_status_code=="order01") {
                        update_post_meta( $customer_order_id, 'customer_order_number', time());

                        // Create new serial-number
                        $query = $this->retrieve_order_item_data($customer_order_id);
                        if ($query->have_posts()) {
                            while ($query->have_posts()) : $query->the_post();
                                $curtain_model_id = get_post_meta(get_the_ID(), 'curtain_model_id', true);
                                $curtain_model_name = get_the_title($curtain_model_id);
                                $curtain_specification_id = get_post_meta(get_the_ID(), 'curtain_specification_id', true);
                                $curtain_specification_name = get_the_title($curtain_specification_id);
                                $curtain_width = get_post_meta(get_the_ID(), 'curtain_width', true);
                                $order_item_qty = get_post_meta(get_the_ID(), 'order_item_qty', true);
                                $_x = 0;
                                while ($_x<$order_item_qty) {
                                    $qr_code_serial_no = $curtain_model_name . $curtain_specification_name. $curtain_width . time() . $_x;
        
                                    $new_post = array(
                                        'post_title'    => $qr_code_serial_no,
                                        'post_content'  => '',
                                        'post_status'   => 'publish',
                                        'post_author'   => get_current_user_id(),
                                        'post_type'     => 'serial-number',
                                    );    
                                    $post_id = wp_insert_post($new_post);
                                    update_post_meta( $post_id, 'order_item_id', get_the_ID() );
    
                                    $_x += 1;
                                }
                            endwhile;
                            wp_reset_postdata();
                        }
        
                        // Notice the administrators
                        $text_message = '訂單號碼「'.time().'」狀態已經從「報價單」被改成「採購單」了，你可以點擊下方按鍵，查看訂單明細。';
                        $link_uri = home_url().'/order/?_id='.$customer_order_id;

                        $args = array(
                            'role' => 'administrator',
                        );                        
                        $users = get_users($args);                        
                        foreach ($users as $user) {
                            $flexMessage = set_flex_message($user->display_name, $link_uri, $text_message);
                            $line_bot_api = new line_bot_api();
                            $line_bot_api->pushMessage([
                                'to' => get_user_meta($user->ID, 'line_user_id', true),
                                'messages' => [$flexMessage],
                            ]);
                        }

                        // Notice the current_user
                        $current_user_id = get_current_user_id();
                        $user_data = get_userdata($current_user_id);
                        $text_message = '我們已經收到你的「採購單」了，訂單號碼「'.time().'」，你可以點擊下方按鍵，查看訂單明細。';
                        $link_uri = home_url().'/order/?_id='.$customer_order_id;
                        $flexMessage = set_flex_message($user_data->display_name, $link_uri, $text_message);
                        $line_bot_api = new line_bot_api();
                        $line_bot_api->pushMessage([
                            'to' => get_user_meta($current_user_id, 'line_user_id', true),
                            'messages' => [$flexMessage],
                        ]);
                    }
                }
                if ($next_status==0) {
                    update_post_meta( $customer_order_id, 'customer_order_category', 1);
                }

                if ($current_status_code=="order01") {
                    $taobao_order_number = sanitize_text_field($_POST['_taobao_order_number']);
                    update_post_meta( $customer_order_id, 'taobao_order_number', $taobao_order_number);
                }
                if ($current_status_code=="order02") {
                    $taobao_ship_number = sanitize_text_field($_POST['_taobao_ship_number']);
                    update_post_meta( $customer_order_id, 'taobao_ship_number', $taobao_ship_number);
                }
                if ($current_status_code=="order03") {
                    $curtain_ship_number = sanitize_text_field($_POST['_curtain_ship_number']);
                    update_post_meta( $customer_order_id, 'curtain_ship_number', $curtain_ship_number);
                    update_post_meta( $customer_order_id, 'curtain_ship_date', time());
                }

            }
            wp_send_json($response);
        }

        function display_order_item_list($customer_order_id=false, $is_admin=false) {
            $customer_order_category = get_post_meta($customer_order_id, 'customer_order_category', true);
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
                    <tbody>
                        <?php
                        $query = $this->retrieve_order_item_data($customer_order_id);
                        if ($query->have_posts()) {
                            while ($query->have_posts()) : $query->the_post();
                                $curtain_category_id = get_post_meta(get_the_ID(), 'curtain_category_id', true);
                                $curtain_category_title = get_the_title($curtain_category_id);
                                $is_specification = get_post_meta($curtain_category_id, 'is_specification', true);
                                $is_height = get_post_meta($curtain_category_id, 'is_height', true);
                                $curtain_model_id = get_post_meta(get_the_ID(), 'curtain_model_id', true);
                                $curtain_model_description = get_post_field('post_content', $curtain_model_id);
                                $curtain_model_price = get_post_meta($curtain_model_id, 'curtain_model_price', true);
                                $curtain_model_price = ($curtain_model_price) ? $curtain_model_price : 0;
                                $curtain_specification_id = get_post_meta(get_the_ID(), 'curtain_specification_id', true);
                                $curtain_specification_description = get_post_field('post_content', $curtain_specification_id);
                                $curtain_specification_price = get_post_meta($curtain_specification_id, 'curtain_specification_price', true);
                                $curtain_specification_price = ($curtain_specification_price) ? $curtain_specification_price : 0;

                                $curtain_width = get_post_meta(get_the_ID(), 'curtain_width', true);
                                $curtain_width = ($curtain_width) ? $curtain_width : 1;
                                $curtain_height = get_post_meta(get_the_ID(), 'curtain_height', true);
                                $curtain_height = ($curtain_height) ? $curtain_height : 1;
                                $order_item_qty = get_post_meta(get_the_ID(), 'order_item_qty', true);
                                $order_item_qty = ($order_item_qty) ? $order_item_qty : 1;

                                $curtain_specification_description .= ' W:'.$curtain_width;
                                $order_item_description = $curtain_model_description.'('.get_the_title($curtain_model_id).')';                                
                                $order_item_amount = $order_item_qty * ($curtain_model_price + $curtain_specification_price * ($curtain_width/100) * ($curtain_height/100));
                                if ($is_height==1) $order_item_amount = $order_item_qty * ($curtain_model_price + $curtain_specification_price * ($curtain_width/100));
                                else $curtain_specification_description .= ' H:'.$curtain_height;
                                if ($is_specification==1) $order_item_amount = $order_item_qty * $curtain_model_price;
                                else $order_item_description .= '<br>'.$curtain_specification_description;
                                $customer_order_amount += $order_item_amount;

                                if ($customer_order_category<=1 || $is_admin==1) echo '<tr id="edit-order-item-'.esc_attr(get_the_ID()).'">';
                                else echo '<tr id="view-qr-code-'.esc_attr(get_the_ID()).'">';
                                echo '<td style="text-align:center;">'.esc_html($curtain_category_title).'</td>';
                                echo '<td>'.$order_item_description.'</td>';
                                echo '<td style="text-align:center;">'.esc_html($order_item_qty).'</td>';
                                echo '<td style="text-align:center;">'.number_format_i18n($order_item_amount).'</td>';
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
                            <td style="text-align:center;"><?php echo number_format_i18n($customer_order_amount);?></td>
                            <input type="hidden" id="customer-order-amount" value="<?php echo esc_attr($customer_order_amount);?>" />
                        </tr>
                    </tfoot>
                </table>
                <?php if ($customer_order_category<=1 || $is_admin==1) {?>
                <div id="new-order-item" class="custom-button" style="border:solid; margin:3px; text-align:center; border-radius:5px; font-size:small;">+</div>
                <?php }?>
            </fieldset>
            </div>
            <div id="qr-code-dialog" title="QR code dialog"></div>
            <div id="curtain-order-item-dialog" title="Order Item dialog"></div>
            <div id="new-order-item-dialog" title="Order Item dialog"></div>
            <?php
            return ob_get_clean();
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
                update_post_meta( $customer_order_id, 'customer_order_amount', sanitize_text_field($_POST['_customer_order_amount']));
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
                $order_item_id = wp_insert_post($new_post);
                $customer_order_id = sanitize_text_field($_POST['_customer_order_id']);
                update_post_meta( $order_item_id, 'customer_order_id', $customer_order_id);
                update_post_meta( $order_item_id, 'curtain_category_id', sanitize_text_field($_POST['_curtain_category_id']));
                update_post_meta( $order_item_id, 'curtain_model_id', sanitize_text_field($_POST['_curtain_model_id']));
                update_post_meta( $order_item_id, 'curtain_specification_id', sanitize_text_field($_POST['_curtain_specification_id']));
                update_post_meta( $order_item_id, 'curtain_width', sanitize_text_field($_POST['_curtain_width']));
                update_post_meta( $order_item_id, 'curtain_height', sanitize_text_field($_POST['_curtain_height']));
                update_post_meta( $order_item_id, 'order_item_qty', sanitize_text_field($_POST['_order_item_qty']));
                update_post_meta( $order_item_id, 'order_item_note', sanitize_text_field($_POST['_order_item_note']));
                update_post_meta( $customer_order_id, 'customer_order_amount', sanitize_text_field($_POST['_customer_order_amount']));
                //update_post_meta( $post_id, 'order_item_qty', 1);
                $response['html_contain'] = $this->display_order_item_list($customer_order_id);
            }
            wp_send_json($response);
        }

        function display_order_item_dialog($order_item_id=false, $curtain_category_id=false) {
            $curtain_agents = new curtain_agents();
            $curtain_categories = new curtain_categories();
            $curtain_models = new curtain_models();
            $curtain_specifications = new curtain_specifications();
            $customer_order_id = get_post_meta($order_item_id, 'customer_order_id', true);
            $customer_order_category = get_post_meta($customer_order_id, 'customer_order_category', true);
            if (!$curtain_category_id) $curtain_category_id = get_post_meta($order_item_id, 'curtain_category_id', true);
            $curtain_model_id = get_post_meta($order_item_id, 'curtain_model_id', true);
            $curtain_specification_id = get_post_meta($order_item_id, 'curtain_specification_id', true);
            $curtain_width = get_post_meta($order_item_id, 'curtain_width', true);
            $curtain_height = get_post_meta($order_item_id, 'curtain_height', true);
            $order_item_qty = get_post_meta($order_item_id, 'order_item_qty', true);
            $order_item_note = get_post_meta($order_item_id, 'order_item_note', true);
            $is_specification = get_post_meta($curtain_category_id, 'is_specification', true);
            $is_specification_hided = ($is_specification == 1) ? 'display:none;' : '';
            $is_height = get_post_meta($curtain_category_id, 'is_height', true);
            $is_height_hided = ($is_height == 1) ? 'display:none;' : '';
            $curtain_min_width = get_post_meta($curtain_category_id, 'curtain_min_width', true);
            $curtain_max_width = get_post_meta($curtain_category_id, 'curtain_max_width', true);
            $curtain_min_height = get_post_meta($curtain_category_id, 'curtain_min_height', true);
            $curtain_max_height = get_post_meta($curtain_category_id, 'curtain_max_height', true);

            ob_start();
            ?>
            <fieldset>
                <input type="hidden" id="order-item-id" value="<?php echo $order_item_id;?>" />
                <label for="curtain-category-id"><?php echo __( '類別', 'your-text-domain' );?></label>
                <select id="curtain-category-id" class="text ui-widget-content ui-corner-all"><?php echo $curtain_categories->select_curtain_category_options($curtain_category_id);?></select>
                <label id="curtain-model-label" for="curtain-model-id"><?php echo __( '型號', 'your-text-domain' );?></label>
                <select id="curtain-model-id" class="text ui-widget-content ui-corner-all"><?php echo $curtain_models->select_curtain_model_options($curtain_model_id, $curtain_category_id);?></select>
                <div id="spec-div" style="<?php echo $is_specification_hided;?>">
                    <label id="curtain-specification-label" for="curtain-specification-id"><?php echo __( '規格', 'your-text-domain' );?></label>
                    <select id="curtain-specification-id" class="text ui-widget-content ui-corner-all"><?php echo $curtain_specifications->select_curtain_specification_options($curtain_specification_id, $curtain_category_id);?></select>
                    <label id="curtain-width-label" for="curtain-width"><?php echo __( '寬', 'your-text-domain' );?>(min:<?php echo $curtain_min_width;?>/max:<?php echo $curtain_max_width;?>)</label>
                    <input type="number" id="curtain-width" min="<?php echo $curtain_min_width;?>" max="<?php echo $curtain_max_width;?>" value="<?php echo $curtain_width;?>" class="text ui-widget-content ui-corner-all" />
                    <div id="height-div" style="<?php echo $is_height_hided;?>">
                        <label id="curtain-height-label" for="curtain-height"><?php echo __( '高', 'your-text-domain' );?>(min:<?php echo $curtain_min_height;?>/max:<?php echo $curtain_max_height;?>)</label>
                        <input type="number" id="curtain-height" min="<?php echo $curtain_min_height;?>" max="<?php echo $curtain_max_height;?>" value="<?php echo $curtain_height;?>" class="text ui-widget-content ui-corner-all" />
                    </div>
                </div>
                <label for="order-item-qty"><?php echo __( '數量', 'your-text-domain' );?></label>
                <input type="text" id="order-item-qty" value="<?php echo $order_item_qty;?>" class="text ui-widget-content ui-corner-all" />
                <label for="order-item-note"><?php echo __( '備註', 'your-text-domain' );?></label>
                <textarea id="order-item-note" rows="2" style="width:100%;"><?php echo $order_item_note;?></textarea>
            </fieldset>
            <?php
            return ob_get_clean();
        }
        
        function display_qr_code_dialog($order_item_id = false) {
            if (!$order_item_id) {
                return '<p>Order item ID is required.</p>';
            }
        
            ob_start();
        
            $args = array(
                'post_type'      => 'serial-number',
                'posts_per_page' => -1,
                'meta_query'     => array(
                    array(
                        'key'   => 'order_item_id',
                        'value' => $order_item_id,
                    ),
                ),
            );        
            $query = new WP_Query($args);
        
            if ($query->have_posts()) {
                while ($query->have_posts()) {
                    $query->the_post();
                    ?>
                    <div id="qrcode" style="text-align:center;">
                    <div id="qrcode_content"><?php echo esc_url(home_url() . '/orders/?serial_no=' . get_the_title()); ?></div>
                    <div><?php echo esc_html(get_the_title());?></div>
                    </div>
                    <?php
                }
                wp_reset_postdata();
            } else {
                echo '<p>No serial numbers found for this order item ID.</p>';
            }
        
            return ob_get_clean();
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
            $order_item_id = sanitize_text_field($_POST['_order_item_id']);
            $curtain_category_id = sanitize_text_field($_POST['_curtain_category_id']);
            $response['html_contain'] = $this->display_order_item_dialog($order_item_id, $curtain_category_id);
            $response['qr_code_dialog'] = $this->display_qr_code_dialog($order_item_id);
            wp_send_json($response);
        }

        function data_migration() {
            // Customer Order Status Migration - 2024-04-30
            if (isset($_GET['_customer_order_status_migration'])) {
                $args = array(
                    'post_type'      => 'customer-order',
                    'posts_per_page' => -1, // Retrieve all matching posts
                );
                $query = new WP_Query($args);
                if ($query->have_posts()) {
                    while ($query->have_posts()) {
                        $query->the_post();
                        // Output or manipulate post data here
                        $order_item_id = get_the_ID();
                        $customer_order_status = get_post_meta($order_item_id, 'customer_order_status', true);
                        
                        // Query the "order-status" post based on the post content
                        $order_status_query = new WP_Query(array(
                            'post_type'      => 'order-status',
                            'posts_per_page' => 1,
                            's'              => $customer_order_status, // Search term to look for in post content
                        ));
                        
                        // Check if any posts were found
                        if ($order_status_query->have_posts()) {
                            $order_status_query->the_post();
                            $customer_order_status = get_the_ID();
                        }
                        wp_reset_postdata(); // Reset the post data
                        
                        // Update post meta
                        update_post_meta($order_item_id, 'customer_order_status', $customer_order_status);
                    }
                    wp_reset_postdata(); // Restore global post data
                }
            }

            // Curtain Model ID Migration - 2024-04-30
            if (isset($_GET['_migrate_model_id_part_3'])) {
                $args = array(
                    'post_type'      => 'order-item',
                    'posts_per_page' => -1, // Retrieve all matching posts
                );
                $query = new WP_Query($args);
                if ($query->have_posts()) {
                    while ($query->have_posts()) {
                        $query->the_post();
                        // Output or manipulate post data here
                        $order_item_id = get_the_ID();
                        $curtain_model_id = get_post_meta($order_item_id, 'curtain_model_id', true);
                        
                        // Curtain Model
                        global $wpdb;
                        $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}curtain_models WHERE curtain_model_id = %d", $curtain_model_id ), OBJECT );
                        $curtain_model_name = $row->curtain_model_name;
                        $curtain_model_post = get_page_by_title($curtain_model_name, OBJECT, 'curtain-model');
                        if ($curtain_model_post) {
                            $curtain_model_id = $curtain_model_post->ID;
                        }
                        update_post_meta($order_item_id, 'curtain_model_id', $curtain_model_id);
                    }
                    wp_reset_postdata(); // Restore global post data
                }
            }

            // curtain_category_id, curtain_specification_id migration 2024-4-30
            if (isset($_GET['_migrate_category_spec_id'])) {
                $args = array(
                    'post_type'      => 'order-item',
                    'posts_per_page' => -1, // Retrieve all matching posts
                );
                $query = new WP_Query($args);
                if ($query->have_posts()) {
                    while ($query->have_posts()) {
                        $query->the_post();
                        // Output or manipulate post data here
                        $order_item_id = get_the_ID();
                        $curtain_category_id = get_post_meta($order_item_id, 'curtain_category_id', true);
                        $curtain_model_id = get_post_meta($order_item_id, 'curtain_model_id', true);
                        $curtain_specification_id = get_post_meta($order_item_id, 'curtain_specification_id', true);
            
                        // Curtain Category
                        global $wpdb;
                        $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}curtain_categories WHERE curtain_category_id = %d", $curtain_category_id ), OBJECT );
                        $curtain_category_name = $row->curtain_category_name;
                        $curtain_category_post = get_page_by_title($curtain_category_name, OBJECT, 'curtain-category');
                        if ($curtain_category_post) {
                            $curtain_category_id = $curtain_category_post->ID;
                        }
            
                        // Curtain Specification
                        global $wpdb;
                        $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}curtain_specifications WHERE curtain_specification_id = %d", $curtain_specification_id ), OBJECT );
                        $curtain_specification_name = $row->curtain_specification_name;
                        $curtain_specification_post = get_page_by_title($curtain_specification_name, OBJECT, 'curtain-spec');
                        if ($curtain_specification_post) {
                            $curtain_specification_id = $curtain_specification_post->ID;
                        }
            
                        // Update post meta
                        update_post_meta($order_item_id, 'curtain_category_id', $curtain_category_id);
                        update_post_meta($order_item_id, 'curtain_model_id', $curtain_model_id);
                        update_post_meta($order_item_id, 'curtain_specification_id', $curtain_specification_id);
                    }
                    wp_reset_postdata(); // Restore global post data
                }
            }

            // order_items_table_to_post migration 2024-4-29
            if (isset($_GET['_migrate_order_items_table_to_post'])) {
                global $wpdb;
                $results = general_helps::get_search_results($wpdb->prefix.'order_items', $_POST['_where']);
                foreach ( $results as $result ) {
                    //$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}customer_orders WHERE customer_order_number = %s", $result->customer_order_number ), OBJECT );

                    $args = array(
                        'post_type'      => 'customer-order',
                        'posts_per_page' => -1, // Set to -1 to retrieve all matching posts
                        'meta_query'     => array(
                            array(
                                'key'     => 'customer_order_number',
                                'value'   => $result->customer_order_number,
                                'compare' => '=',
                            ),
                        ),
                    );
                
                    $filtered_query = new WP_Query($args);

                    $customer_order_id=0;
                    // Check if there are any posts found
                    if ($filtered_query->have_posts()) {
                        while ($filtered_query->have_posts()) {
                            $filtered_query->the_post();
                            // Output or manipulate post data here
                            $customer_order_id = get_the_ID();
                        }
                        wp_reset_postdata(); // Restore global post data
                    }
                    
                    $current_user_id = get_current_user_id();
                    $new_post = array(
                        'post_title'    => 'New item',
                        'post_content'  => 'Your post content goes here.',
                        'post_status'   => 'publish',
                        'post_author'   => $current_user_id,
                        'post_type'     => 'order-item',
                    );    
                    $post_id = wp_insert_post($new_post);
                    update_post_meta( $post_id, 'customer_order_id', $customer_order_id );
                    update_post_meta( $post_id, 'customer_order_number', $result->customer_order_number );
                    update_post_meta( $post_id, 'curtain_agent_id', $curtain_agent_id );
                    update_post_meta( $post_id, 'curtain_category_id', $result->curtain_category_id );
                    update_post_meta( $post_id, 'curtain_model_id', $result->curtain_model_id );
                    update_post_meta( $post_id, 'curtain_remote_id', $result->curtain_remote_id );
                    update_post_meta( $post_id, 'curtain_specification_id', $result->curtain_specification_id );
                    update_post_meta( $post_id, 'curtain_width', $result->curtain_width );
                    update_post_meta( $post_id, 'curtain_height', $result->curtain_height );
                    update_post_meta( $post_id, 'order_item_qty', $result->order_item_qty );
                    update_post_meta( $post_id, 'order_item_amount', $result->order_item_amount );
                    update_post_meta( $post_id, 'order_item_note', $result->order_item_note );
                    update_post_meta( $post_id, 'is_checkout', $result->is_checkout );
                   
                }
            }

            // customer_orders_table_to_post migration 2024-4-29
            if (isset($_GET['_migrate_customer_orders_table_to_post'])) {
                global $wpdb;
                $results = general_helps::get_search_results($wpdb->prefix.'customer_orders', $_POST['_where']);
                foreach ( $results as $result ) {
                    $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}curtain_agents WHERE curtain_agent_id = %d", $result->curtain_agent_id ), OBJECT );

                    $args = array(
                        'post_type'      => 'curtain-agent',
                        'posts_per_page' => -1, // Set to -1 to retrieve all matching posts
                        'meta_query'     => array(
                            array(
                                'key'     => 'curtain_agent_number',
                                'value'   => $row->agent_number,
                                'compare' => '=',
                            ),
                        ),
                    );
                
                    $filtered_query = new WP_Query($args);

                    $curtain_agent_id=0;
                    // Check if there are any posts found
                    if ($filtered_query->have_posts()) {
                        while ($filtered_query->have_posts()) {
                            $filtered_query->the_post();
                            // Output or manipulate post data here
                            $curtain_agent_id = get_the_ID();
                        }
                        wp_reset_postdata(); // Restore global post data
                    }
                    
                    $current_user_id = get_current_user_id();
                    $new_post = array(
                        'post_title'    => 'New order',
                        'post_content'  => 'Your post content goes here.',
                        'post_status'   => 'publish',
                        'post_author'   => $current_user_id,
                        'post_type'     => 'customer-order',
                    );    
                    $post_id = wp_insert_post($new_post);
                    update_post_meta( $post_id, 'customer_order_number', $result->customer_order_number );
                    update_post_meta( $post_id, 'curtain_agent_id', $curtain_agent_id );
                    update_post_meta( $post_id, 'customer_order_amount', $result->customer_order_amount );
                    update_post_meta( $post_id, 'customer_order_status', $result->customer_order_status );
                    update_post_meta( $post_id, 'customer_order_category', 2 );
           
                }
            }
        }

    }
    $orders_class = new curtain_orders();
}