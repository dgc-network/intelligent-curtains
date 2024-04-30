<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('curtain_models')) {
    class curtain_models {
        private $_wp_page_title;
        private $_wp_page_postid;
        /**
         * Class constructor
         */
        public function __construct() {
            $this->create_tables();
            $this->_wp_page_title = 'Models';
            $this->_wp_page_postid = general_helps::create_page($this->_wp_page_title, 'curtain-model-list');
            //add_shortcode( 'curtain-model-list', array( $this, 'list_curtain_models' ) );
            add_action( 'wp_ajax_model_dialog_get_data', array( $this, 'model_dialog_get_data' ) );
            add_action( 'wp_ajax_nopriv_model_dialog_get_data', array( $this, 'model_dialog_get_data' ) );
            add_action( 'wp_ajax_model_dialog_save_data', array( $this, 'model_dialog_save_data' ) );
            add_action( 'wp_ajax_nopriv_model_dialog_save_data', array( $this, 'model_dialog_save_data' ) );

            //add_shortcode( 'curtain-model-list', array( $this, 'display_curtain_model_list' ) );
            add_shortcode( 'shopping-item-list', array( $this, 'display_shortcode' ) );
            add_action( 'init', array( $this, 'register_curtain_model_post_type' ) );
            add_action( 'wp_ajax_get_curtain_model_dialog_data', array( $this, 'get_curtain_model_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_get_curtain_model_dialog_data', array( $this, 'get_curtain_model_dialog_data' ) );
            add_action( 'wp_ajax_set_curtain_model_dialog_data', array( $this, 'set_curtain_model_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_set_curtain_model_dialog_data', array( $this, 'set_curtain_model_dialog_data' ) );
            add_action( 'wp_ajax_del_curtain_model_dialog_data', array( $this, 'del_curtain_model_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_del_curtain_model_dialog_data', array( $this, 'del_curtain_model_dialog_data' ) );

        }

        function register_curtain_model_post_type() {
            $labels = array(
                'menu_name'     => _x('curtain-model', 'admin menu', 'textdomain'),
            );
            $args = array(
                'labels'        => $labels,
                'public'        => true,
                'rewrite'       => array('slug' => 'curtain-models'),
                'supports'      => array('title', 'editor', 'custom-fields'),
                'has_archive'   => true,
                'show_in_menu'  => false,
            );
            register_post_type( 'curtain-model', $args );
        }

        function display_shortcode() {
            if (is_user_logged_in()) {
                $this->display_curtain_model_list();
                $this->list_curtain_models();
            }
        }

        function display_curtain_model_list() {
            $curtain_categories = new curtain_categories();
            ?>
            <div class="ui-widget" id="result-container">
            <h2 style="display:inline;"><?php echo __( 'Curtain models', 'your-text-domain' );?></h2>
            <fieldset>
                <div style="display:flex; justify-content:space-between; margin:5px;">
                    <div>
                        <select id="select-category-in-model"><?php echo $curtain_categories->select_curtain_category_options($_GET['_category']);?></select>
                    </div>
                    <div style="text-align:right; display:flex;">
                        <input type="text" id="search-model" style="display:inline" placeholder="Search..." />
                    </div>
                </div>
        
                <table class="ui-widget" style="width:100%;">
                    <thead>
                        <tr>
                            <th><?php echo __( 'Model', 'your-text-domain' );?></th>
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
                    $query = $this->retrieve_curtain_model_data($current_page);
                    $total_posts = $query->found_posts;
                    $total_pages = ceil($total_posts / $posts_per_page); // Calculate the total number of pages
        
                    if ($query->have_posts()) :
                        while ($query->have_posts()) : $query->the_post();
                            $curtain_model_title = get_the_title();
                            $curtain_model_description = get_post_field('post_content', get_the_ID());
                            $curtain_category_id = get_post_meta(get_the_ID(), 'curtain_category_id', true);
                            $curtain_model_price = get_post_meta(get_the_ID(), 'curtain_model_price', true);
                            $curtain_model_vendor = get_post_meta(get_the_ID(), 'curtain_model_vendor', true);
                            ?>
                            <tr id="edit-curtain-model-<?php the_ID();?>">
                                <td style="text-align:center;"><?php echo esc_html(get_the_title());?></td>
                                <td><?php echo esc_html($curtain_model_description);?></td>
                                <td style="text-align:center;"><?php echo esc_html($curtain_model_price);?></td>
                                <td style="text-align:center;"><?php echo esc_html($curtain_model_vendor);?></td>
                            </tr>
                            <?php
                        endwhile;
                        wp_reset_postdata();
                    endif;
                    ?>
                    </tbody>
                </table>
                <div id="new-curtain-model" class="custom-button" style="border:solid; margin:3px; text-align:center; border-radius:5px; font-size:small;">+</div>
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
            <?php
            echo $this->display_curtain_model_dialog();
        }

        function retrieve_curtain_model_data($current_page = 1) {
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
                'post_type'      => 'curtain-model',
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

        function display_curtain_model_dialog($curtain_model_id=false) {            
            $curtain_categories = new curtain_categories();
            $curtain_model_title = get_the_title($curtain_model_id);
            $curtain_model_description = get_post_field('post_content', $curtain_model_id);
            $curtain_category_id = get_post_meta($curtain_model_id, 'curtain_category_id', true);
            $curtain_model_price = get_post_meta($curtain_model_id, 'curtain_model_price', true);
            $curtain_model_vendor = get_post_meta($curtain_model_id, 'curtain_model_vendor', true);
            ob_start();
            ?>
            <div id="curtain-model-dialog" title="Model dialog">
            <fieldset>
                <input type="hidden" id="curtain-model-id" value="<?php echo esc_attr($curtain_model_id);?>" />
                <label for="curtain-model-title"><?php echo __( 'Title', 'your-text-domain' );?></label>
                <input type="text" id="curtain-model-title" value="<?php echo esc_html($curtain_model_title);?>" class="text ui-widget-content ui-corner-all" />
                <label for="curtain-model-description"><?php echo __( 'Description', 'your-text-domain' );?></label>
                <input type="text" id="curtain-model-description" value="<?php echo esc_html($curtain_model_description);?>" class="text ui-widget-content ui-corner-all" />
                <label for="curtain-category-id"><?php echo __( 'Category', 'your-text-domain' );?></label>
                <select id="curtain-category-id"><?php echo $curtain_categories->select_curtain_category_options($curtain_category_id);?></select>
                <label for="curtain-model-price"><?php echo __( 'Price', 'your-text-domain' );?></label>
                <input type="text" id="curtain-model-price" value="<?php echo esc_html($curtain_model_price);?>" class="text ui-widget-content ui-corner-all" />
                <label for="curtain-model-vendor"><?php echo __( 'Vendor', 'your-text-domain' );?></label>
                <input type="text" id="curtain-model-vendor" value="<?php echo esc_html($curtain_model_vendor);?>" class="text ui-widget-content ui-corner-all" />
            </fieldset>
            </div>
            <?php
            $html = ob_get_clean();
            return $html;        
        }

        function get_curtain_model_dialog_data() {
            $response = array();
            if (isset($_POST['_curtain_model_id'])) {
                $curtain_model_id = sanitize_text_field($_POST['_curtain_model_id']);
                $response['html_contain'] = $this->display_curtain_model_dialog($curtain_model_id);
            } else {
                $response['html_contain'] = 'Invalid AJAX request!';
            }
            wp_send_json($response);
        }

        function set_curtain_model_dialog_data() {
            $response = array();
            if( isset($_POST['_curtain_model_id']) ) {
                // Update the meta data
                $curtain_model_id = sanitize_text_field($_POST['_curtain_model_id']);
                update_post_meta( $curtain_model_id, 'curtain_category_id', sanitize_text_field($_POST['_curtain_category_id']));
                update_post_meta( $curtain_model_id, 'curtain_model_price', sanitize_text_field($_POST['_curtain_model_price']));
                update_post_meta( $curtain_model_id, 'curtain_model_vendor', sanitize_text_field($_POST['_curtain_model_vendor']));
                // Update the post title
                $updated_post = array(
                    'ID'         => $curtain_model_id,
                    'post_title' => sanitize_text_field($_POST['_curtain_model_title']),
                    'post_content' => sanitize_text_field($_POST['_curtain_model_description']),
                );
                wp_update_post($updated_post);
            } else {
                $current_user_id = get_current_user_id();
                $new_post = array(
                    'post_title'    => 'New model',
                    'post_content'  => 'Your post content goes here.',
                    'post_status'   => 'publish',
                    'post_author'   => $current_user_id,
                    'post_type'     => 'curtain-model',
                );    
                $post_id = wp_insert_post($new_post);
            }
            wp_send_json($response);
        }

        function del_curtain_model_dialog_data() {
            $response = array();
            if( isset($_POST['_curtain_model_id']) ) {
                $curtain_model_id = sanitize_text_field($_POST['_curtain_model_id']);
                wp_delete_post($curtain_model_id, true);
            }
            wp_send_json($response);
        }

        function select_curtain_model_options($selected_option=0, $curtain_category_id=0) {
            $args = array(
                'post_type'      => 'curtain-model',
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
        


        public function list_curtain_models() {
            // 2024-4-26 Modify the curtain-model as the post type
            $this->display_curtain_model_list();


            global $wpdb;
            $curtain_categories = new curtain_categories();
            /** Check the permission */
            if ( !is_user_logged_in() ) return '<div style="text-align:center;"><h3>You did not login the system. Please login first.</h3></div>';
            $user = wp_get_current_user();
            if ( !$user->has_cap('manage_options') ) return '<div style="text-align:center;"><h3>You did not have the cpability to access this system.<br>Please contact the administrator.</h3></div>';

            /** Post the result */
            if( isset($_POST['_create']) ) {
                $this->insert_curtain_model(
                    array(
                        'curtain_model_name'=>$_POST['_curtain_model_name'],
                        'model_description'=>$_POST['_model_description'],
                        'model_price'=>$_POST['_model_price'],
                        'curtain_category_id'=>$_POST['_curtain_category_id'],
                        'curtain_vendor_name'=>$_POST['_curtain_vendor_name']
                    )
                );
            }
            
            if( isset($_POST['_update']) ) {
                $this->update_curtain_models(
                    array(
                        'curtain_model_name'=>$_POST['_curtain_model_name'],
                        'model_description'=>$_POST['_model_description'],
                        'model_price'=>$_POST['_model_price'],
                        'curtain_category_id'=>$_POST['_curtain_category_id'],
                        'curtain_vendor_name'=>$_POST['_curtain_vendor_name']
                    ),
                    array(
                        'curtain_model_id'=>$_POST['_curtain_model_id'],
                    )
                );
                ?><script>window.location.replace("?_update=");</script><?php
            }

            if( isset($_GET['_delete']) ) {
                $this->delete_curtain_models(
                    array(
                        'curtain_model_id'=>$_GET['_delete']
                    )
                );
            }

            /** List */
            $output  = '<h2>Curtain Models</h2>';
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
            $output .= '<table id="models" class="ui-widget ui-widget-content">';
            $output .= '<thead><tr class="ui-widget-header ">';
            $output .= '<th></th>';
            $output .= '<th>model</th>';
            $output .= '<th>description</th>';
            $output .= '<th>price</th>';
            $output .= '<th>vendor</th>';
            $output .= '<th>update_time</th>';
            $output .= '<th></th>';
            $output .= '</tr></thead>';

            if( isset($_GET['_curtain_category_id']) ) {
                $curtain_category_id = $_GET['_curtain_category_id'];
                $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}curtain_models WHERE curtain_category_id={$curtain_category_id}", OBJECT );
            } else {
                $results = general_helps::get_search_results($wpdb->prefix.'curtain_models', $_POST['_where']);
            }
            $output .= '<tbody>';
            foreach ( $results as $index=>$result ) {
                $output .= '<tr>';
                $output .= '<td style="text-align: center;">';
                //$output .= '<span id="btn-edit-'.$result->curtain_model_id.'"><i class="fa-regular fa-pen-to-square"></i></span>';
                $output .= '<span id="btn-model-'.$result->curtain_model_id.'"><i class="fa-regular fa-pen-to-square"></i></span>';
                $output .= '</td>';
                $output .= '<td style="text-align: center;">'.$result->curtain_model_name.':'.$result->curtain_model_id.'</td>';
                $output .= '<td>'.$result->model_description.'</td>';
                $output .= '<td style="text-align: center;">'.$result->model_price.'</td>';
                $output .= '<td>'.$result->curtain_vendor_name.'</td>';
                $output .= '<td>'.wp_date( get_option('date_format'), $result->update_timestamp ).' '.wp_date( get_option('time_format'), $result->update_timestamp ).'</td>';
                $output .= '<td style="text-align: center;">';
                $output .= '<span id="btn-del-'.$result->curtain_model_id.'"><i class="fa-regular fa-trash-can"></i></span>';
                $output .= '</td>';
                $output .= '</tr>';
            }
            $output .= '<tr><td colspan="7"><div id="btn-model" style="border:solid; margin:3px; text-align:center; border-radius:5px">+</div></td></tr>';
            $output .= '</tbody></table></div>';

            /** Model Dialog */
            $output .= '<div id="model-dialog" title="Model dialog">';
            $output .= '<fieldset>';
            $output .= '<input type="hidden" id="curtain-model-id" />';
            $output .= '<label for="curtain-model-name">Model Name</label>';
            $output .= '<input type="text" id="curtain-model-name" />';
            $output .= '<label for="model-description">Description</label>';
            $output .= '<input type="text" id="model-description" />';
            $output .= '<label for="model-price">Price</label>';
            $output .= '<input type="text" id="model-price" />';
            $output .= '<label for="curtain-category-id">Curtain Category</label>';
            $output .= '<select id="curtain-category-id"></select>';
            $output .= '<label for="curtain-vendor-name">Curtain Vendor</label>';
            $output .= '<input type="text" id="curtain-vendor-name" />';
            $output .= '</fieldset>';
            $output .= '</div>';

            if( isset($_GET['_edit']) ) {
                $_id = $_GET['_edit'];
                $row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}curtain_models WHERE curtain_model_id={$_id}", OBJECT );
                $output .= '<div id="dialog" title="Curtain model update">';
                $output .= '<form method="post">';
                $output .= '<fieldset>';
                $output .= '<input type="hidden" value="'.$row->curtain_model_id.'" name="_curtain_model_id">';
                $output .= '<label for="curtain-model-name">Model Name</label>';
                $output .= '<input type="text" name="_curtain_model_name" value="'.$row->curtain_model_name.'" id="curtain-model-name" class="text ui-widget-content ui-corner-all">';
                $output .= '<label for="model-description">Description</label>';
                $output .= '<input type="text" name="_model_description" value="'.$row->model_description.'" id="model-description" class="text ui-widget-content ui-corner-all">';
                $output .= '<label for="model-price">Price</label>';
                $output .= '<input type="text" name="_model_price" value="'.$row->model_price.'" id="model-price" class="text ui-widget-content ui-corner-all">';
                $output .= '<label for="curtain-category-id">Curtain Category</label>';
                $output .= '<select name="_curtain_category_id" id="curtain-category-id">'.$curtain_categories->select_options($row->curtain_category_id).'</select>';
                $output .= '<label for="curtain-vendor-name">Curtain Vendor</label>';
                $output .= '<input type="text" name="_curtain_vendor_name" value="'.$row->curtain_vendor_name.'" id="curtain-vendor-name" class="text ui-widget-content ui-corner-all">';
                $output .= '</fieldset>';
                $output .= '<input class="wp-block-button__link" type="submit" value="Update" name="_update">';
                $output .= '</form>';
                $output .= '</div>';
            }

            if( isset($_POST['_add']) ) {
                $output .= '<div id="dialog" title="Create new model">';
                $output .= '<form method="post">';
                $output .= '<fieldset>';
                $output .= '<label for="curtain-model-name">Model Name</label>';
                $output .= '<input type="text" name="_curtain_model_name" id="curtain-model-name" class="text ui-widget-content ui-corner-all">';
                $output .= '<label for="model-description">Description</label>';
                $output .= '<input type="text" name="_model_description" id="model-description" class="text ui-widget-content ui-corner-all">';
                $output .= '<label for="model-price">Price</label>';
                $output .= '<input type="text" name="_model_price" id="model-price" class="text ui-widget-content ui-corner-all">';
                $output .= '<label for="curtain-category-id">Curtain Category</label>';
                $output .= '<select name="_curtain_category_id" id="curtain-category-id">'.$curtain_categories->select_options().'</select>';
                $output .= '<label for="curtain-vendor-name">Curtain Vendor</label>';
                $output .= '<input type="text" name="_curtain_vendor_name" id="curtain-vendor-name" class="text ui-widget-content ui-corner-all">';
                $output .= '</fieldset>';
                $output .= '<input class="wp-block-button__link" type="submit" value="Create" name="_create">';
                $output .= '</form>';
                $output .= '</div>';
            }
            return $output;
        }

        function model_dialog_get_data() {
            global $wpdb;
            $curtain_categories = new curtain_categories();
            $_id = $_POST['_id'];
            $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}curtain_models WHERE curtain_model_id = %d", $_id ), OBJECT );
            $response = array();
            $response["curtain_model_name"] = $row->curtain_model_name;
            $response["model_description"] = $row->model_description;
            $response["model_price"] = $row->model_price;
            $response["curtain_category_id"] = $curtain_categories->select_options($row->curtain_category_id);
            $response["curtain_vendor_name"] = $row->curtain_vendor_name;
            echo json_encode( $response );
            wp_die();
        }

        function model_dialog_save_data() {
            if( $_POST['_curtain_model_id']=='' ) {
                $this->insert_curtain_model(
                    array(
                        'curtain_model_name'=>$_POST['_curtain_model_name'],
                        'model_description'=>$_POST['_model_description'],
                        'model_price'=>$_POST['_model_price'],
                        'curtain_category_id'=>$_POST['_curtain_category_id'],
                        'curtain_vendor_name'=>$_POST['_curtain_vendor_name']
                    )
                );
            } else {
                $this->update_curtain_models(
                    array(
                        'curtain_model_name'=>$_POST['_curtain_model_name'],
                        'model_description'=>$_POST['_model_description'],
                        'model_price'=>$_POST['_model_price'],
                        'curtain_category_id'=>$_POST['_curtain_category_id'],
                        'curtain_vendor_name'=>$_POST['_curtain_vendor_name']
                    ),
                    array(
                        'curtain_model_id'=>$_POST['_curtain_model_id']
                    )
                );
            }

            $response = array();
            echo json_encode( $response );
            wp_die();
        }

        public function insert_curtain_model($data=[]) {
            global $wpdb;
            $table = $wpdb->prefix.'curtain_models';
            $data['create_timestamp'] = time();
            $data['update_timestamp'] = time();
            $wpdb->insert($table, $data);        
            return $wpdb->insert_id;
        }

        public function update_curtain_models($data=[], $where=[]) {
            global $wpdb;
            $table = $wpdb->prefix.'curtain_models';
            $data['update_timestamp'] = time();
            $wpdb->update($table, $data, $where);
        }

        public function delete_curtain_models($where=[]) {
            global $wpdb;
            $table = $wpdb->prefix.'curtain_models';
            $wpdb->delete($table, $where);
        }

        public function get_name( $_id=0 ) {
            global $wpdb;
            $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}curtain_models WHERE curtain_model_id = %d", $_id ), OBJECT );
            return $row->curtain_model_name;
        }

        public function get_description( $_id=0 ) {
            global $wpdb;
            $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}curtain_models WHERE curtain_model_id = %d", $_id ), OBJECT );
            return $row->model_description.'('.$row->curtain_model_name.')';
        }

        public function get_price( $_id=0 ) {
            global $wpdb;
            $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}curtain_models WHERE curtain_model_id = %d", $_id ), OBJECT );
            return $row->model_price;
        }

        public function select_options( $_category_id=0, $_id=0 ) {
            global $wpdb;
            $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}curtain_models WHERE curtain_category_id={$_category_id}", OBJECT );
            $output = '<option value="0">-- Select an option --</option>';
            foreach ($results as $index => $result) {
                if ( $result->curtain_model_id == $_id ) {
                    $output .= '<option value="'.$result->curtain_model_id.'" selected>';
                } else {
                    $output .= '<option value="'.$result->curtain_model_id.'">';
                }
                $output .= $result->curtain_model_name.'('.$result->model_description.')';
                $output .= '</option>';        
            }
            $output .= '<option value="0">-- Remove this --</option>';
            return $output;
        }

        public function create_tables() {
            global $wpdb;
            $charset_collate = $wpdb->get_charset_collate();
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
            $sql = "CREATE TABLE `{$wpdb->prefix}curtain_models` (
                curtain_model_id int NOT NULL AUTO_INCREMENT,
                curtain_model_name varchar(5) UNIQUE,
                model_description varchar(50),
                model_price decimal(10,2),
                curtain_vendor_name varchar(50),
                curtain_category_id int(10),
                create_timestamp int(10),
                update_timestamp int(10),
                PRIMARY KEY (curtain_model_id)
            ) $charset_collate;";
            dbDelta($sql);
        }
    }
    $my_class = new curtain_models();
}