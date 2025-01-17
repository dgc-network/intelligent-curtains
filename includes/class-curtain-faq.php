<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('curtain_faq')) {
    class curtain_faq {

        public function __construct() {
            add_shortcode( 'curtain-faq-list', array( $this, 'display_shortcode' ) );
            //add_action( 'init', array( $this, 'register_curtain_faq_post_type' ) );

            add_action( 'wp_ajax_get_curtain_faq_dialog_data', array( $this, 'get_curtain_faq_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_get_curtain_faq_dialog_data', array( $this, 'get_curtain_faq_dialog_data' ) );
            add_action( 'wp_ajax_set_curtain_faq_dialog_data', array( $this, 'set_curtain_faq_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_set_curtain_faq_dialog_data', array( $this, 'set_curtain_faq_dialog_data' ) );
            add_action( 'wp_ajax_del_curtain_faq_dialog_data', array( $this, 'del_curtain_faq_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_del_curtain_faq_dialog_data', array( $this, 'del_curtain_faq_dialog_data' ) );
        }

        function register_curtain_faq_post_type() {
            $labels = array(
                'menu_name'     => _x('curtain-faq', 'admin menu', 'textdomain'),
            );
            $args = array(
                'labels'        => $labels,
                'public'        => true,
            );
            register_post_type( 'curtain-faq', $args );
        }

        function display_shortcode() {
            if (current_user_can('administrator')) {
                $this->display_curtain_faq_list();
            } else {
                ?>
                <div style="text-align:center;">
                    <h4><?php echo __( '你沒有讀取目前網頁的權限!', 'text-domain' );?></h4>
                </div>
                <?php
            }
        }

        function display_curtain_faq_list() {
            ?>
            <div class="ui-widget" id="result-container">
            <h2 style="display:inline;"><?php echo __( 'Q&A', 'text-domain' );?></h2>
            <fieldset>
                <div style="display:flex; justify-content:space-between; margin:5px;">
                    <div>
                    </div>
                    <div style="text-align:right; display:flex;">
                        <input type="text" id="search-faq" style="display:inline" placeholder="Search..." />
                    </div>
                </div>
        
                <table class="ui-widget" style="width:100%;">
                    <thead>
                        <tr>
                            <th><?php echo __( '#', 'text-domain' );?></th>
                            <th><?php echo __( 'Question', 'text-domain' );?></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    // Define the custom pagination parameters
                    $posts_per_page = get_option('operation_row_counts');
                    $paged = max(1, get_query_var('paged')); // Get the current page number
                    $query = $this->retrieve_curtain_faq_data($paged);
                    $total_posts = $query->found_posts;
                    $total_pages = ceil($total_posts / $posts_per_page); // Calculate the total number of pages
        
                    if ($query->have_posts()) :
                        while ($query->have_posts()) : $query->the_post();
                            $faq_code = get_post_meta(get_the_ID(), 'faq_code', true);
                            $faq_question = get_the_title();
                            $faq_answer = get_the_content();
                            $toolbox_uri = get_post_meta(get_the_ID(), 'toolbox_uri', true);
                            //$status_color = get_post_meta(get_the_ID(), 'status_color', true);
                            //$next_status = get_post_meta(get_the_ID(), 'next_status', true);
                            ?>
                            <tr id="edit-curtain-faq-<?php the_ID();?>">
                                <td style="text-align:center;"><?php echo esc_html($faq_code);?></td>
                                <td><?php echo esc_html($faq_question);?></td>
                            </tr>
                            <?php
                        endwhile;
                        wp_reset_postdata();
                    endif;
                    ?>
                    </tbody>
                </table>
                <div id="new-curtain-faq" class="custom-button" style="border:solid; margin:3px; text-align:center; border-radius:5px; font-size:small;">+</div>
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
            <div id="curtain-faq-dialog" title="Status dialog"></div>            
            <?php
        }

        function retrieve_curtain_faq_data($paged=1, $search_query=false) {
            // Define the custom pagination parameters
            $posts_per_page = get_option('operation_row_counts');
            if (!$search_query) $search_query = sanitize_text_field($_GET['_search']);
            $args = array(
                'post_type'      => 'curtain-faq',
                'posts_per_page' => $posts_per_page,
                'paged'          => $paged,
                's'              => $search_query,                
                'meta_key'       => 'faq_code', // Specify the meta key to order by
                'orderby'        => 'meta_value',  // Order by the meta value
                'order'          => 'ASC',         // Order direction (ASC or DESC)
            );        
            if ($paged==0) $args['posts_per_page'] = -1;
            $query = new WP_Query($args);
            return $query;
        }
        
        function display_curtain_faq_dialog($curtain_faq_id=false) {            
            ob_start();
            $faq_code = get_post_meta($curtain_faq_id, 'faq_code', true);
            $faq_question = get_the_title($curtain_faq_id);
            $faq_answer = get_post_field('post_content', $curtain_faq_id);
            $toolbox_uri = get_post_meta($curtain_faq_id, 'toolbox_uri', true);
            ?>
            <fieldset>
                <input type="hidden" id="curtain-faq-id" value="<?php echo esc_attr($curtain_faq_id);?>" />
                <label for="faq-code"><?php echo __( '代碼', 'text-domain' );?></label>
                <input type="text" id="faq-code" value="<?php echo esc_html($faq_code);?>" class="text ui-widget-content ui-corner-all" />
                <label for="faq-question"><?php echo __( '問題', 'text-domain' );?></label>
                <textarea id="faq-question" rows="3" style="width:100%;"><?php echo $faq_question;?></textarea>
                <label for="faq-answer"><?php echo __( '回答', 'text-domain' );?></label>
                <textarea id="faq-answer" rows="3" style="width:100%;"><?php echo $faq_answer;?></textarea>
                <label for="toolbox-uri"><?php echo __( '連結', 'text-domain' );?></label>
                <input type="text" id="toolbox-uri" value="<?php echo esc_html($toolbox_uri);?>" class="text ui-widget-content ui-corner-all" />
            </fieldset>
            <?php
            return ob_get_clean();
        }

        function get_curtain_faq_dialog_data() {
            $response = array();
            $curtain_faq_id = sanitize_text_field($_POST['_curtain_faq_id']);
            $response['html_contain'] = $this->display_curtain_faq_dialog($curtain_faq_id);
            wp_send_json($response);
        }

        function set_curtain_faq_dialog_data() {
            $response = array();
            if( isset($_POST['_curtain_faq_id']) ) {
                $curtain_faq_id = sanitize_text_field($_POST['_curtain_faq_id']);
                // Update the post title
                if (isset($_POST['_faq_question'])) {
                    $updated_post = array(
                        'ID'         => $curtain_faq_id,
                        'post_title' => $_POST['_faq_question'],
                        'post_content' => $_POST['_faq_answer'],
                    );
                    wp_update_post($updated_post);
                }
                // Update the meta data
                update_post_meta( $curtain_faq_id, 'faq_code', sanitize_text_field($_POST['_faq_code']));
                update_post_meta( $curtain_faq_id, 'toolbox_uri', sanitize_text_field($_POST['_toolbox_uri']));
            } else {
                $current_user_id = get_current_user_id();
                $new_post = array(
                    'post_title'    => 'New question',
                    'post_content'  => 'Your answer.',
                    'post_status'   => 'publish',
                    'post_author'   => $current_user_id,
                    'post_type'     => 'curtain-faq',
                );    
                $post_id = wp_insert_post($new_post);
                update_post_meta( $post_id, 'faq_code', time());
            }
            wp_send_json($response);
        }

        function del_curtain_faq_dialog_data() {
            $response = array();
            $curtain_faq_id = sanitize_text_field($_POST['_curtain_faq_id']);
            wp_delete_post($curtain_faq_id, true);
            wp_send_json($response);
        }

        function select_curtain_faq_options($selected_option=0) {
            $args = array(
                'post_type'      => 'curtain-faq',
                'posts_per_page' => -1,
                'meta_key'       => 'faq_code', // Meta key for sorting
                'orderby'        => 'meta_value', // Sort by meta value
                'order'          => 'ASC', // Sorting order (ascending)
            );
            $query = new WP_Query($args);

            $options = '<option value="">Select status</option>';
            while ($query->have_posts()) : $query->the_post();
                $selected = ($selected_option == get_the_ID()) ? 'selected' : '';
                $faq_code = get_post_meta(get_the_ID(), 'status_code', true);
                $faq_question = get_the_title();
                $faq_answer = get_the_content();
                $faq_question .= '('.$faq_code.')';
                $options .= '<option value="' . esc_attr(get_the_ID()) . '" '.$selected.' />' . esc_html($faq_question) . '</option>';
            endwhile;
            wp_reset_postdata();
            return $options;
        }        
    }
    $curtain_faq = new curtain_faq();
}