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
                    //$this->do_migration();
                    $this->display_serial_number_list();
                } else {
                    ?>
                    <div style="text-align:center;">
                        <h4><?php echo __( '你沒有讀取目前網頁的權限!', 'your-text-domain' );?></h4>
                    </div>
                    <?php
                }    
            }
        }

        function proceed_qr_code($_serial_no=false) {
            // Assign the User for the specified serial number(QR Code) and ask the question as well
            if (!is_user_logged_in()) user_is_not_logged_in();
            else {
                $user = wp_get_current_user();
                $serial_number_post = get_page_by_title($_serial_no);
                $order_item_id = get_post_meta($serial_number_post->ID, 'order_item_id', true);
                $customer_order_id = get_post_meta($order_item_id, 'customer_order_id', true);
                $curtain_agent_id = get_post_meta($customer_order_id, 'curtain_agent_id', true);
                ?>
                <div class="ui-widget" id="result-container">
                    <h4><?php echo __( 'Hi, ', 'your-text-domain' );?><?php echo $user->display_name;?></h4>
                    <h4><?php echo __( '感謝您選購我們的電動窗簾.', 'your-text-domain' );?></h4>
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
/*                
            $output = '<div style="text-align:center;">';
            $qr_code_serial_no = $_GET['serial_no'];
            $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}serial_number WHERE qr_code_serial_no = %s", $qr_code_serial_no ), OBJECT );            
            //** incorrect QR-code then display the admin link
            if (is_null($row) || !empty($wpdb->last_error)) {                        
                $output .= '<div style="font-weight:700; font-size:xx-large;">Wrong Code</div>';

            //** registration for QR-code
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
            //return $output;        
*/
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
            <h2 style="display:inline;"><?php echo __( '序號列表', 'your-text-domain' );?></h2>
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
                            <th><?php echo __( 'serial_no', 'your-text-domain' );?></th>
                            <th><?php echo __( 'model', 'your-text-domain' );?></th>
                            <th><?php echo __( 'specification', 'your-text-domain' );?></th>
                            <th><?php echo __( 'agent', 'your-text-domain' );?></th>
                            <th><?php echo __( 'user', 'your-text-domain' );?></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    // Define the custom pagination parameters
                    $posts_per_page = get_option('operation_row_counts');
                    $current_page = max(1, get_query_var('paged')); // Get the current page number
                    $query = $this->retrieve_serial_number_data($current_page);
                    $total_posts = $query->found_posts;
                    $total_pages = ceil($total_posts / $posts_per_page); // Calculate the total number of pages
        
                    if ($query->have_posts()) :
                        while ($query->have_posts()) : $query->the_post();
                            $qr_code_serial_no = get_the_title();
                            $curtain_specification = get_the_content();
                            $curtain_model_id = get_post_meta(get_the_ID(), 'curtain_model_id', true);
                            $curtain_agent_id = get_post_meta(get_the_ID(), 'curtain_agent_id', true);
                            $curtain_user_id = get_post_meta(get_the_ID(), 'curtain_user_id', true);
                            $curtain_agent_id = get_post_meta(get_the_ID(), 'curtain_specification_id', true);
                            $curtain_user_id = get_post_meta(get_the_ID(), 'customer_order_number', true);
                            ?>
                            <tr id="edit-serial-number-<?php the_ID();?>">
                                <td style="text-align:center;"><?php echo esc_html($qr_code_serial_no);?></td>
                                <td style="text-align:center;"><?php echo esc_html($curtain_model_id);?></td>
                                <td><?php echo esc_html($curtain_specification);?></td>
                                <td style="text-align:center;"><?php echo esc_html($curtain_agent_id);?></td>
                                <td style="text-align:center;"><?php echo esc_html($curtain_user_id);?></td>
                            </tr>
                            <?php
                        endwhile;
                        wp_reset_postdata();
                    endif;
                    ?>
                    </tbody>
                </table>
                <div id="disabled-new-serial-number" class="custom-button" style="border:solid; margin:3px; text-align:center; border-radius:5px; font-size:small;">+</div>
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
            <div id="serial-number-dialog" title="Serial number dialog"></div>            
            <?php
        }

        function retrieve_serial_number_data($current_page = 1) {
            // Define the custom pagination parameters
            $posts_per_page = get_option('operation_row_counts');
            $search_query = sanitize_text_field($_GET['_search']);
            $args = array(
                'post_type'      => 'serial-number',
                'posts_per_page' => $posts_per_page,
                'paged'          => $current_page,
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
                <label for="qrcode-serial-no"><?php echo __( 'Serial', 'your-text-domain' );?></label>
                <input type="text" id="qrcode-serial-no" value="<?php echo esc_html($qr_code_serial_no);?>" class="text ui-widget-content ui-corner-all" />
                <label for="curtain-modle-id"><?php echo __( 'Model', 'your-text-domain' );?></label>
                <input type="text" id="curtain-modle-id" value="<?php echo esc_html($curtain_model_id);?>" class="text ui-widget-content ui-corner-all" />
                <label for="curtain-specification"><?php echo __( 'Specification', 'your-text-domain' );?></label>
                <textarea id="curtain-specification" rows="3" style="width:100%;"><?php echo $curtain_specification;?></textarea>
                <label for="customer-order-number"><?php echo __( 'Order', 'your-text-domain' );?></label>
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
                // Update the meta data
                $serial_number_id = sanitize_text_field($_POST['_serial_number_id']);
                update_post_meta( $serial_number_id, 'curtain_model_id', sanitize_text_field($_POST['_curtain_model_id']));
                update_post_meta( $serial_number_id, 'curtain_agent_id', sanitize_text_field($_POST['_curtain_agent_id']));
                update_post_meta( $serial_number_id, 'curtain_user_id', sanitize_text_field($_POST['_curtain_user_id']));
                // Update the post title
                $updated_post = array(
                    'ID'         => $serial_number_id,
                    'post_title' => sanitize_text_field($_POST['_qr_code_serial_no']),
                    'post_content' => $_POST['_curtain_specification'],
                );
                wp_update_post($updated_post);
            } else {
                $current_user_id = get_current_user_id();
                $qr_code_serial_no = $model->curtain_model_name . $data['specification'] . time() . $_x;

                $new_post = array(
                    'post_title'    => $qr_code_serial_no,
                    'post_content'  => sanitize_text_field($_POST['_curtain_specification']),
                    'post_status'   => 'publish',
                    'post_author'   => get_current_user_id(),
                    'post_type'     => 'serial-number',
                );    
                $post_id = wp_insert_post($new_post);
                //update_post_meta( $post_id, 'status_code', 'order0');
            }
            wp_send_json($response);
        }

        function del_serial_number_dialog_data() {
            $response = array();
            $serial_number_id = sanitize_text_field($_POST['_serial_number_id']);
            wp_delete_post($serial_number_id, true);
            wp_send_json($response);
        }
/*
        function do_migration() {
            // delete serial-number post 2024-6-18
            if (isset($_GET['_model_specification_migration'])) {

                $query = new WP_Query( array(
                    'post_type' => 'serial-number',
                    'posts_per_page' => -1,
                    'post_status' => 'any'
                ) );
            
                while ( $query->have_posts() ) {
                    $query->the_post();
                    $curtain_model_id = get_post_meta(get_the_ID(), 'curtain_model_id', true);
                    $curtain_specification = get_post_field('post_content', get_the_ID());
                    $curtain_specification_post = get_page_by_title($curtain_specification, OBJECT, 'curtain-spec');
                    update_post_meta( get_the_ID(), 'old_model_id', $curtain_model_id );
                    update_post_meta( get_the_ID(), 'curtain_specification_id', $curtain_specification_post->ID );

                }
            
                wp_reset_postdata();
                ?><script>window.location.replace("https://aihome.tw/serials/");</script><?php

                // Get the current URL without any query parameters
                $current_url = remove_query_arg( array_keys( $_GET ) );
                // Redirect to the URL without any query parameters
                wp_redirect( $current_url );
                exit();                

            }

            // serial_number_table_to_post migration 2024-6-18
            if (isset($_GET['_migrate_serial_number_table_to_post'])) {
                global $wpdb;
                $results = general_helps::get_search_results($wpdb->prefix.'serial_number', $_POST['_where']);
                foreach ( $results as $result ) {
                    $current_user_id = get_current_user_id();
                    $new_post = array(
                        'post_title'    => $result->qr_code_serial_no,
                        'post_content'  => $result->specification,
                        'post_status'   => 'publish',
                        'post_author'   => get_current_user_id(),
                        'post_type'     => 'serial-number',
                    );    
                    $post_id = wp_insert_post($new_post);
                    update_post_meta( $post_id, 'customer_order_number', $result->customer_order_number );
                    update_post_meta( $post_id, 'curtain_model_id', $result->curtain_model_id );
                }
                ?><script>window.location.replace("https://aihome.tw/serials/");</script><?php
                                
                // Get the current URL without any query parameters
                $current_url = remove_query_arg( array_keys( $_GET ) );
                // Redirect to the URL without any query parameters
                wp_redirect( $current_url );
                exit();                
                
            }
        }



        private $_wp_page_title;
        private $_wp_page_postid;
        public function __construct_backup() {
            $this->_wp_page_title = 'Serials';
            $this->_wp_page_postid = general_helps::create_page($this->_wp_page_title, 'serial-number-list');
            add_shortcode( 'serial-number-list', array( $this, 'list_serial_number' ) );
            $this->create_tables();
        }

        public function list_serial_number() {
            global $wpdb;
            $curtain_models = new curtain_models();
            $curtain_agents = new curtain_agents();
            //** Check the permission
            if ( !is_user_logged_in() ) return '<div style="text-align:center;"><h3>You did not login the system. Please login first.</h3></div>';
            $user = wp_get_current_user();
            //if ( !$user->has_cap('manage_options') ) return '<div style="text-align:center;"><h3>You did not have the cpability to access this system.<br>Please contact the administrator.</h3></div>';

            //** Post the result
            if( isset($_POST['_create']) ) {
                $this->insert_serial_number(
                    array(
                        'customer_order_number'=>$_POST['_customer_order_number'],
                        'curtain_model_id'=>$_POST['_curtain_model_id'],
                        'specification'=>$_POST['_specification'],
                        'curtain_agent_id'=>$_POST['_curtain_agent_id']
                    )
                );
            }

            if( isset($_POST['_update']) ) {
                $this->update_serial_number(
                    array(
                        'customer_order_number'=>$_POST['_customer_order_number'],
                        'order_item_id'=>$_POST['_order_item_id'],
                        //'curtain_model_id'=>$_POST['_curtain_model_id'],
                        //'curtain_agent_id'=>$_POST['_curtain_agent_id']
                    ),
                    array(
                        'serial_number_id'=>$_POST['_serial_number_id']
                    )
                );
            }

            if( isset($_GET['_delete']) ) {
                $this->delete_serial_number(
                    array(
                        'serial_number_id'=>$_GET['_delete']
                    )
                );
            }

            //** List
            $output  = '<h2>Serial Number</h2>';
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
            $output .= '<table class="ui-widget ui-widget-content">';
            $output .= '<thead><tr class="ui-widget-header ">';
            $output .= '<th></th>';
            $output .= '<th>serial_no</th>';
            $output .= '<th>model</th>';
            $output .= '<th>spec</th>';
            $output .= '<th>agent</th>';
            $output .= '<th>user</th>';
            $output .= '<th>update_time</th>';
            $output .= '<th></th>';
            $output .= '</tr></thead>';
            
            $output .= '<tbody>';
            
            //if( isset($_GET['_customer_order_number']) ) {
            //    $customer_order_number = $_GET['_customer_order_number'];
            //    $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}serial_number WHERE customer_order_number={$customer_order_number}", OBJECT );
            
            if( isset($_GET['_order_item_id']) ) {
                $order_item_id = $_GET['_order_item_id'];
                $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}serial_number WHERE order_item_id={$order_item_id}", OBJECT );
            } else {
                $results = general_helps::get_search_results($wpdb->prefix.'serial_number', $_POST['_where']);
            }
/*
            if( isset($_GET['_curtain_agent_id']) ) {
                $curtain_agent_id = $_GET['_curtain_agent_id'];
                $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}serial_number WHERE curtain_agent_id={$curtain_agent_id}", OBJECT );
            } else {
                $results = general_helps::get_search_results($wpdb->prefix.'serial_number', $_POST['_where']);
            }
            foreach ( $results as $index=>$result ) {
                $output .= '<tr>';
                $output .= '<td style="text-align: center;">';
                $output .= '<span id="btn-qrcode-'.$result->qr_code_serial_no.'"><i class="fa-solid fa-qrcode"></i></span>';
                $output .= '</td>';
                $output .= '<td>'.$result->qr_code_serial_no.'</td>';
                //$output .= '<td>'.$curtain_models->get_name($result->curtain_model_id).'</td>';
                $output .= '<td>'.get_the_title($result->curtain_model_id).'</td>';
                $output .= '<td>'.$result->specification.'</td>';
                //$output .= '<td>'.$curtain_agents->get_name($result->curtain_agent_id).'</td>';
                $curtain_agent_name = get_post_meta($result->curtain_agent_id, 'curtain_agent_name', true);
                $output .= '<td>'.$curtain_agent_name.'</td>';
                $user = get_userdata( $result->curtain_user_id );
                $output .= '<td>'.$user->display_name.'</td>';
                $output .= '<td>'.wp_date( get_option('date_format'), $result->update_timestamp ).' '.wp_date( get_option('time_format'), $result->update_timestamp ).'</td>';
                if(wp_get_current_user()->has_cap('manage_options')){
                    $output .= '<td style="text-align: center;">';
                    $output .= '<span id="btn-del-'.$result->serial_number_id.'"><i class="fa-regular fa-trash-can"></i></span>';
                    $output .= '<span style="margin-left:5px;" id="btn-edit-'.$result->serial_number_id.'"><i class="fa-regular fa-pen-to-square"></i></span>';
                    $output .= '</td>';
                }
                $output .= '</tr>';
            }
            $output .= '</tbody></table></div>';

            if( isset($_POST['_add']) ) {
                $output .= '<div id="dialog" title="Create new serial_no">';
                $output .= '<form method="post">';
                $output .= '<fieldset>';
                $output .= '<label for="curtain_model_id">Model</label>';                    
                //$output .= '<select name="_curtain_model_id" id="curtain_model_id">'.$curtain_models->select_options().'</select>';
                $output .= '<select name="_curtain_model_id" id="curtain_model_id">'.$curtain_models->select_curtain_model_options().'</select>';
                $output .= '<label for="specification">Specification</label>';
                $output .= '<input type="text" name="_specification" id="specification" class="text ui-widget-content ui-corner-all">';
                $output .= '<label for="curtain_agent_id">Agent</label>';
                //$output .= '<select name="_curtain_agent_id" id="curtain_agent_id">'.$curtain_agents->select_options().'</select>';
                $output .= '<select name="_curtain_agent_id" id="curtain_agent_id">'.$curtain_agents->select_curtain_agent_options().'</select>';
                $output .= '</fieldset>';
                $output .= '<input class="wp-block-button__link" type="submit" value="Create" name="_create">';
                $output .= '</form>';
                $output .= '</div>';
            }

            if( isset($_GET['_edit']) ) {
                $_id = $_GET['_edit'];
                $row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}serial_number WHERE serial_number_id={$_id}", OBJECT );
                $output .= '<div id="dialog" title="Serial_no update">';
                $output .= '<form method="post">';
                $output .= '<fieldset>';
                $output .= '<input type="hidden" name="_serial_number_id" value="'.$row->serial_number_id.'">';
                $output .= '<label for="customer_order_number">Customer Order Number</label>';
                $output .= '<input type="text" name="_customer_order_number" id="customer_order_number" value="'.$row->customer_order_number.'" class="text ui-widget-content ui-corner-all">';
                $output .= '<label for="order_item_id">Order Item ID</label>';
                $output .= '<input type="text" name="_order_item_id" id="order_item_id" value="'.$row->order_item_id.'" class="text ui-widget-content ui-corner-all">';
/*                
                $output .= '<label for="curtain_model_id">Model</label>';
                $output .= '<select name="_curtain_model_id" id="curtain_model_id">'.$curtain_models->select_options($row->curtain_model_id).'</select>';
                $output .= '<label for="curtain_agent_id">Agent</label>';
                $output .= '<select name="_curtain_agent_id" id="curtain_agent_id">'.$curtain_agents->select_options($row->curtain_agent_id).'</select>';

                $output .= '</fieldset>';
                $output .= '<input class="wp-block-button__link" type="submit" value="Update" name="_update">';
                $output .= '</form>';
                $output .= '</div>';
            }

            if( isset($_GET['_qrcode']) ) {
                $_id = $_GET['_qrcode'];
                $output .= '<div id="dialog" title="QR Code">';
                $output .= '<div id="qrcode">';
                $output .= '<div id="qrcode_content">';
                $output .= get_option('Service').'?serial_no='.$_id;
                $output .= '</div>';
                $output .= '</div>';
                $output .= '<div style="display: flex;">';
                $print_me = do_shortcode('[print-me target=".print-me-'.$_id.'"/]');
                $output .= $print_me;
                $output .= '<span> </span>';
                $output .= '<span>'.$_id.'</span>';
                $output .= '</div>';
                $output .= '</div>';
                
                $output .= '<br><br><br><br><br>';                
                $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}serial_number WHERE qr_code_serial_no = %s", $_id ), OBJECT );            
                $output .= '<div class="print-me-'.$_id.'">';
                //$output .= '<div id="qrcode1" style="display: inline-block; margin-left: 100px;">';
                $output .= '<div id="qrcode1">';
                $output .= '<div id="qrcode_content">';
                $output .= get_option('Service').'?serial_no='.$_id;
                $output .= '</div>';
                $output .= '</div>';
                $output .= '<p><h1 style="margin-left: 25px;">'.wp_date( get_option('date_format'), $row->create_timestamp ).'</h1></p><br><br><br>';
                //$output .= '<div id="qrcode2" style="display: inline-block;; margin-left: 200px;">';
                $output .= '<div id="qrcode2" style="margin-top: 100px;">';
                $output .= '<div id="qrcode_content">';
                $output .= get_option('Service').'?serial_no='.$_id;
                $output .= '</div>';
                $output .= '</div>';
                $output .= '<p><h1 style="margin-left: 25px;">'.wp_date( get_option('date_format'), $row->create_timestamp ).'</h1></p>';
                $output .= '</div>';                
            }
            return $output;
        }

        public function insert_serial_number($data=[], $_x='') {
            global $wpdb;
            $curtain_models = new curtain_models();
            $curtain_model_id = $data['curtain_model_id'];
            $model = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}curtain_models WHERE curtain_model_id = {$curtain_model_id}", OBJECT );
            if (!(is_null($model) || !empty($wpdb->last_error))) {
                $qr_code_serial_no = $model->curtain_model_name . $data['specification'] . time() . $_x;
                $data['qr_code_serial_no'] = $qr_code_serial_no;
                $data['create_timestamp'] = time();
                $data['update_timestamp'] = time();
                $table = $wpdb->prefix.'serial_number';
                $wpdb->insert($table, $data);
                return $wpdb->insert_id;
            }
        }

        public function update_serial_number($data=[], $where=[]) {
            global $wpdb;
            $table = $wpdb->prefix.'serial_number';
            $data['update_timestamp'] = time();
            $wpdb->update($table, $data, $where);
        }

        public function delete_serial_number($where=[]) {
            global $wpdb;
            $table = $wpdb->prefix.'serial_number';
            $wpdb->delete($table, $where);
        }

        public function create_tables() {
            global $wpdb;
            $charset_collate = $wpdb->get_charset_collate();
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
            $sql = "CREATE TABLE `{$wpdb->prefix}serial_number` (
                serial_number_id int NOT NULL AUTO_INCREMENT,
                qr_code_serial_no varchar(50) UNIQUE,
                customer_order_number varchar(20),
                order_item_id int,
                curtain_model_id int,
                specification varchar(10),
                curtain_agent_id int,
                curtain_user_id int,
                one_time_password int,
                create_timestamp int(10),
                update_timestamp int(10),
                PRIMARY KEY (serial_number_id)
            ) $charset_collate;";
            dbDelta($sql);
        }
*/            
    }
    $my_class = new serial_number();
}