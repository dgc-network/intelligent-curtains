<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('serial_number')) {
    class serial_number {

        public function __construct() {
            add_shortcode( 'serial-number-list', array( $this, 'display_shortcode' ) );
            //add_action( 'init', array( $this, 'register_serial_number_post_type' ) );

            add_action( 'wp_ajax_get_serial_number_dialog_data', array( $this, 'get_serial_number_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_get_serial_number_dialog_data', array( $this, 'get_serial_number_dialog_data' ) );
            add_action( 'wp_ajax_set_serial_number_dialog_data', array( $this, 'set_serial_number_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_set_serial_number_dialog_data', array( $this, 'set_serial_number_dialog_data' ) );
            add_action( 'wp_ajax_del_serial_number_dialog_data', array( $this, 'del_serial_number_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_del_serial_number_dialog_data', array( $this, 'del_serial_number_dialog_data' ) );
        }

        function display_shortcode() {
            if( isset($_GET['serial_no']) ) {
                $this->proceed_qr_code($_GET['serial_no']);
            } else {
                if (current_user_can('administrator')) {
                    $this->display_serial_number_list();
                } else {
                    ?>
                    <div style="text-align:center;">
                        <h4><?php echo __( '你沒有讀取目前網頁的權限!', 'textdomain' );?></h4>
                    </div>
                    <?php
                }    
            }
        }

        function send_message_to_agent() {
            $response = array();
            if( isset($_POST['_curtain_agent_id']) && isset($_POST['_curtain_user_id']) ) {
                $curtain_user_id = sanitize_text_field($_POST['_curtain_user_id']);
                $line_user_id = get_user_meta($curtain_user_id, 'line_user_id', true);

                $curtain_agent_id = sanitize_text_field($_POST['_curtain_agent_id']);
                $curtain_agent_name = get_post_meta($curtain_agent_id, 'curtain_agent_name', true);

                $text_message = $_POST['_chat_message'];
                $link_uri = 'line://nv/chat?userId=' . $line_user_id;
                //$flexMessage = set_flex_message($curtain_agent_name, $link_uri, $text_message);

                $args = array(
                    'meta_key'   => 'curtain_agent_id',  // The meta key to search by
                    'meta_value' => $curtain_agent_id,   // The meta value you're looking for
                );
                $user_query = new WP_User_Query($args);
                
                // Get the results
                $users = $user_query->get_results();
                // Check if any users were found
                if (!empty($users)) {
                    foreach ($users as $user) {
                        $line_bot_api = new line_bot_api();
                        $header_contents = array(
                            array(
                                'type' => 'text',
                                'text' => 'Hello, ' . $curtain_agent_name,
                                'size' => 'lg',
                                'weight' => 'bold',
                            ),
                        );
        
                        $body_contents = array(
                            array(
                                'type' => 'text',
                                'text' => $text_message,
                                'wrap' => true,
                            ),
                        );
        
                        $footer_contents = array(
                            array(
                                'type' => 'button',
                                'action' => array(
                                    'type' => 'uri',
                                    'label' => 'Click me!',
                                    'uri' => $link_uri, // Use the desired URI
                                ),
                                'style' => 'primary',
                                'margin' => 'sm',
                            ),
                        );
        
                        // Generate the Flex Message
                        $flexMessage = $line_bot_api->set_bubble_message([
                            'header_contents' => $header_contents,
                            'body_contents' => $body_contents,
                            'footer_contents' => $footer_contents,
                        ]);
                        // Send the Flex Message via LINE API
                        $line_bot_api->pushMessage([
                            'to' => get_user_meta($user->ID, 'line_user_id', true),
                            'messages' => [$flexMessage],
                        ]);
                    }
                } else {
                    echo 'No users found with curtain_agent_id: ' . $curtain_agent_id;
                }
            }
            wp_send_json($response);
        }

        function proceed_qr_code($qr_code_serial_no=false) {
            // Assign the User for the specified serial number(QR Code) and ask the question as well
            if (!is_user_logged_in()) user_is_not_logged_in();
            else {
                $user = wp_get_current_user();
                $args = array(
                    'post_type'   => 'serial-number',
                    'post_status' => 'publish', // Only look for published pages
                    'title'       => $qr_code_serial_no,
                    'numberposts' => 1,         // Limit the number of results to one
                );            
                $serial_number_post = get_posts($args);
    
                $order_item_id = get_post_meta($serial_number_post->ID, 'order_item_id', true);
                $customer_order_id = get_post_meta($order_item_id, 'customer_order_id', true);
                $curtain_agent_id = get_post_meta($customer_order_id, 'curtain_agent_id', true);
                update_post_meta( $serial_number_post->ID, 'curtain_user_id', get_current_user_id());
                ?>
                <div class="ui-widget" id="result-container">
                    <h4><?php echo __( 'Hi, ', 'textdomain' );?><?php echo $user->display_name;?></h4>
                    <h4><?php echo __( '感謝您選購我們的電動窗簾.', 'textdomain' );?></h4>
                    <label style="text-align:left;" for="chat-message">Question:</label>
                    <textarea id="chat-message" rows="10" cols="50"></textarea>
                    <input type="hidden" id="curtain-user-id" value="<?php echo $user->ID;?>" />
                    <input type="hidden" id="curtain-agent-id" value="<?php echo $curtain_agent_id;?>" />
                    <input type="submit" id="chat-submit" style="margin:3px;" value="Submit" />
                </div>
                <?php
            }
            if( isset($_POST['_chat_submit']) ) {
                $output = '<div style="text-align:center;">';
                $output .= $curtain_agents->get_name($_POST['_curtain_agent_id']);
                $results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}agent_operators WHERE curtain_agent_id = %d", $_POST['_curtain_agent_id'] ), OBJECT );
                foreach ( $results as $result ) {
                    $message_id = $this->insert_chat_message(
                        array(
                            'chat_from' => get_user_meta($_POST['_chat_user_id'], 'line_user_id', TRUE),
                            'chat_to' => get_user_meta($result->curtain_user_id, 'line_user_id', TRUE),
                            'chat_message'=> $_POST['_chat_message']
                        )
                    );                            
                    $link_uri = 'http://aihome.tw/service/?_chat_message='.$message_id;

                    $see_more["header"]["type"] = 'box';
                    $see_more["header"]["layout"] = 'vertical';
                    $see_more["header"]["backgroundColor"] = "#e3dee3";
                    $see_more["header"]["contents"][0]["type"] = 'text';
                    $see_more["header"]["contents"][0]["text"] = $user->display_name;
                    $see_more["body"]["contents"][0]["type"] = 'text';
                    $see_more["body"]["contents"][0]["text"] = $_POST['_chat_message'];
                    $see_more["body"]["contents"][0]["wrap"] = true;
                    $see_more["footer"]["type"] = 'box';
                    $see_more["footer"]["layout"] = 'vertical';
                    $see_more["footer"]["backgroundColor"] = "#e3dee3";
                    $see_more["footer"]["contents"][0]["type"] = 'button';
                    $see_more["footer"]["contents"][0]["action"]["type"] = 'uri';
                    $see_more["footer"]["contents"][0]["action"]["label"] = 'Reply message';
                    $see_more["footer"]["contents"][0]["action"]["uri"] = $link_uri;

                    $line_bot_api->pushMessage([
                        'to' => get_user_meta($result->curtain_user_id, 'line_user_id', TRUE),
                        'messages' => [
                            [
                                "type" => "flex",
                                "altText" => 'Chat message',
                                'contents' => $see_more
                            ]
                        ]
                    ]);
                }

                $output .= '<h3>Will reply the question to your Line chat box soon.</h3>';
                $output .= '</div>';
                return $output;    
            }
        }

        function register_serial_number_post_type() {
            $labels = array(
                'menu_name'     => _x('serial-number', 'admin menu', 'textdomain'),
            );
            $args = array(
                'labels'        => $labels,
                'public'        => true,
            );
            register_post_type( 'serial-number', $args );
        }

        function display_serial_number_list() {
            ?>
            <div class="ui-widget" id="result-container">
            <h2 style="display:inline;"><?php echo __( '序號列表', 'textdomain' );?></h2>
            <fieldset>
                <div style="display:flex; justify-content:space-between; margin:5px;">
                    <div>
                    </div>
                    <div style="text-align:right; display:flex;">
                        <input type="text" id="search-serial-number" style="display:inline" placeholder="Search..." />
                    </div>
                </div>
        
                <table class="ui-widget" style="width:100%;">
                    <thead>
                        <tr>
                            <th><?php echo __( 'serial_no', 'textdomain' );?></th>
                            <th><?php echo __( 'product', 'textdomain' );?></th>
                            <th><?php echo __( 'vendor', 'textdomain' );?></th>
                            <th><?php echo __( 'agent', 'textdomain' );?></th>
                            <th><?php echo __( 'user', 'textdomain' );?></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    // Define the custom pagination parameters
                    $posts_per_page = get_option('operation_row_counts');
                    $paged = max(1, get_query_var('paged')); // Get the current page number
                    $query = $this->retrieve_serial_number_data($paged);
                    $total_posts = $query->found_posts;
                    $total_pages = ceil($total_posts / $posts_per_page); // Calculate the total number of pages

                    if ($query->have_posts()) :
                        while ($query->have_posts()) : $query->the_post();
                            $qr_code_serial_no = get_the_title();
                            $curtain_specification = get_the_content();
                            $order_item_id = get_post_meta(get_the_ID(), 'order_item_id', true);
                            $product_item_id = get_post_meta($order_item_id, 'product_item_id', true);
                            $product_item_content = get_post_field('post_content', $product_item_id);
                            $production_order_id = get_post_meta($order_item_id, 'customer_order_id', true);
                            $production_order_vendor = get_post_meta($production_order_id, 'production_order_vendor', true);
                            $production_vendor_number = get_post_meta($production_order_vendor, 'curtain_agent_number', true);
                            $production_vendor_name = get_post_meta($production_order_vendor, 'curtain_agent_name', true);
                            $customer_order_id = get_post_meta($production_order_id, 'customer_order_id', true);
                            $curtain_agent_id = get_post_meta($customer_order_id, 'curtain_agent_id', true);
                            $curtain_agent_number = get_post_meta($curtain_agent_id, 'curtain_agent_number', true);
                            $curtain_agent_name = get_post_meta($curtain_agent_id, 'curtain_agent_name', true);
                            $curtain_user_id = get_post_meta($customer_order_id, 'curtain_user_id', true);
                            ?>
<?php /*                            
                            <tr id="edit-serial-number-<?php the_ID();?>">
*/?>
                            <tr>                            
                                <td style="text-align:center;"><?php echo esc_html($qr_code_serial_no);?></td>
                                <td><?php echo esc_html(get_the_title($product_item_id).'-'.$product_item_content);?></td>
                                <td><?php echo esc_html($production_vendor_name);?></td>
                                <td><?php echo esc_html($curtain_agent_name.'('.$curtain_agent_number.')');?></td>
                                <td style="text-align:center;"><?php echo esc_html($curtain_user_id);?></td>
                            </tr>
                            <?php
                        endwhile;
                        wp_reset_postdata();
                    endif;
                    ?>
                    </tbody>
                </table>
<?php /*                
                <div id="new-serial-number" class="custom-button" style="border:solid; margin:3px; text-align:center; border-radius:5px; font-size:small;">+</div>
*/?>                
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
            <div id="serial-number-dialog" title="Serial number dialog"></div>            
            <?php
        }

        function retrieve_serial_number_data($paged = 1) {
            // Define the custom pagination parameters
            $posts_per_page = get_option('operation_row_counts');
            $search_query = sanitize_text_field($_GET['_search']);
            $args = array(
                'post_type'      => 'serial-number',
                'posts_per_page' => $posts_per_page,
                'paged'          => $paged,
                's'              => $search_query,                
            );        
            $query = new WP_Query($args);
            return $query;
        }
        
        function display_serial_number_dialog($serial_number_id=false) {
            ob_start();            
            $qr_code_serial_no = get_the_title($serial_number_id);
            $curtain_specification = get_post_field('post_content', $serial_number_id);
            $curtain_model_id = get_post_meta($serial_number_id, 'curtain_model_id', true);
            $customer_order_number = get_post_meta($serial_number_id, 'customer_order_number', true);
            ?>
            <fieldset>
                <input type="hidden" id="serial-number-id" value="<?php echo esc_attr($serial_number_id);?>" />
                <label for="qrcode-serial-no"><?php echo __( 'Serial', 'textdomain' );?></label>
                <input type="text" id="qrcode-serial-no" value="<?php echo esc_html($qr_code_serial_no);?>" class="text ui-widget-content ui-corner-all" />
                <label for="curtain-modle-id"><?php echo __( 'Model', 'textdomain' );?></label>
                <input type="text" id="curtain-modle-id" value="<?php echo esc_html($curtain_model_id);?>" class="text ui-widget-content ui-corner-all" />
                <label for="curtain-specification"><?php echo __( 'Specification', 'textdomain' );?></label>
                <textarea id="curtain-specification" rows="3" style="width:100%;"><?php echo $curtain_specification;?></textarea>
                <label for="customer-order-number"><?php echo __( 'Order', 'textdomain' );?></label>
                <input type="text" id="customer-order-number" value="<?php echo esc_html($customer_order_number);?>" class="text ui-widget-content ui-corner-all" />
            </fieldset>
            <?php
            return ob_get_clean();
        }

        function get_serial_number_dialog_data() {
            $response = array();
            $serial_number_id = sanitize_text_field($_POST['_serial_number_id']);
            $response['html_contain'] = $this->display_serial_number_dialog($serial_number_id);
            wp_send_json($response);
        }

        function set_serial_number_dialog_data() {
            $response = array();
            if( isset($_POST['_serial_number_id']) ) {
                // Update the post title
                $updated_post = array(
                    'ID'         => $serial_number_id,
                    'post_title' => sanitize_text_field($_POST['_qr_code_serial_no']),
                );
                wp_update_post($updated_post);
                // Update the meta data
                $serial_number_id = sanitize_text_field($_POST['_serial_number_id']);
                update_post_meta( $serial_number_id, 'customer_order_number', sanitize_text_field($_POST['_customer_order_number']));
            } else {
                $qr_code_serial_no = sanitize_text_field($_POST['_qr_code_serial_no']);
                $new_post = array(
                    'post_title'    => $qr_code_serial_no,
                    'post_status'   => 'publish',
                    'post_author'   => get_current_user_id(),
                    'post_type'     => 'serial-number',
                );    
                $post_id = wp_insert_post($new_post);
                update_post_meta( $serial_number_id, 'customer_order_number', sanitize_text_field($_POST['_customer_order_number']));
            }
            wp_send_json($response);
        }

        function del_serial_number_dialog_data() {
            $response = array();
            $serial_number_id = sanitize_text_field($_POST['_serial_number_id']);
            wp_delete_post($serial_number_id, true);
            wp_send_json($response);
        }
    }
    $my_class = new serial_number();
}