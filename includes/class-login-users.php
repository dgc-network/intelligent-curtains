<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('login_users')) {
    class login_users {

        public function __construct() {

            add_shortcode( 'login-user-list', array( $this, 'display_shortcode' ) );

            add_action( 'wp_ajax_get_login_user_dialog_data', array( $this, 'get_login_user_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_get_login_user_dialog_data', array( $this, 'get_login_user_dialog_data' ) );
            add_action( 'wp_ajax_set_login_user_dialog_data', array( $this, 'set_login_user_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_set_login_user_dialog_data', array( $this, 'set_login_user_dialog_data' ) );
            add_action( 'wp_ajax_del_login_user_dialog_data', array( $this, 'del_login_user_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_del_login_user_dialog_data', array( $this, 'del_login_user_dialog_data' ) );

        }

        function display_shortcode() {
            if (current_user_can('administrator')) {
                $this->display_login_user_list();
            } else {
                ?>
                <div style="text-align:center;">
                    <h4><?php echo __( '你沒有讀取目前網頁的權限!', 'your-text-domain' );?></h4>
                </div>
                <?php
            }
        }

        function display_login_user_list() {
            ?>
            <div class="ui-widget" id="result-container">
            <h2 style="display:inline;"><?php echo __( '使用者列表', 'your-text-domain' );?></h2>
            <fieldset>
                <div style="display:flex; justify-content:space-between; margin:5px;">
                    <div>
                    </div>
                    <div style="text-align:right; display:flex;">
                        <input type="text" id="search-user" style="display:inline" placeholder="Search..." />
                    </div>
                </div>
        
                <table class="ui-widget" style="width:100%;">
                    <thead>
                        <tr>
                            <th><?php echo __( 'Name', 'your-text-domain' );?></th>
                            <th><?php echo __( 'Email', 'your-text-domain' );?></th>
                            <th><?php echo __( '倉管人員', 'your-text-domain' );?></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $users = get_users();
                    foreach ($users as $user) {
                        $curtain_agent_id = get_user_meta($current_user_id, 'curtain_agent_id', true);
                        $is_warehouse_personnel = get_user_meta($user->ID, 'is_warehouse_personnel', true);
                        $is_checked = ($is_warehouse_personnel) ? 'checked' : '';
                        ?>
                        <tr id="edit-login-user-<?php echo esc_attr($user->ID);?>">
                            <td style="text-align:center;"><?php echo esc_html($user->display_name);?></td>
                            <td><?php echo esc_html($user->user_email);?></td>
                            <td style="text-align:center;"><input type="checkbox" <?php echo $is_checked;?> /></td>
                        </tr>
                        <?php
                    }            
                    ?>
                    </tbody>
                </table>
                <div id="new-login-user" class="custom-button" style="border:solid; margin:3px; text-align:center; border-radius:5px; font-size:small;">+</div>
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
            <div id="new-user-dialog" title="User dialog"></div>
            <div id="login-user-dialog" title="User dialog"></div>
            <?php
        }

        function display_login_user_dialog($login_user_id=false) {
            
            $user_data = get_userdata($login_user_id);
            $display_name = $user_data->display_name;
            $user_email = $user_data->user_email;
            $is_warehouse_personnel = get_user_meta($login_user_id, 'is_warehouse_personnel', true);
            $is_checked = ($is_warehouse_personnel==1) ? 'checked' : '';
            ob_start();
            ?>
            <fieldset>
                <input type="hidden" id="login-user-id" value="<?php echo esc_attr($login_user_id);?>" />
                <label for="display-name"><?php echo __( '姓名', 'your-text-domain' );?></label>
                <input type="text" id="display-name" value="<?php echo esc_attr($display_name);?>" class="text ui-widget-content ui-corner-all" />
                <label for="user-email"><?php echo __( 'Email', 'your-text-domain' );?></label>
                <input type="text" id="user-email" value="<?php echo esc_attr($user_email);?>" class="text ui-widget-content ui-corner-all" />
                <input type="checkbox" id="is-warehouse-personnel" <?php echo $is_checked;?> />
                <label for="is-warehouse-personnel"><?php echo __( '倉管人員', 'your-text-domain' );?></label>
            </fieldset>
            <?php
            $html = ob_get_clean();
            return $html;        
        }

        function get_login_user_dialog_data() {
            $response = array();
            $login_user_id = sanitize_text_field($_POST['_login_user_id']);
            $response['html_contain'] = $this->display_login_user_dialog($login_user_id);
            wp_send_json($response);
        }

        function set_login_user_dialog_data() {
            // Initialize the response array
            $response = array();
        
            // Check if the necessary POST variables are set
            if (isset($_POST['_login_user_id'])) {
                // Sanitize and assign the user ID
                $login_user_id = intval($_POST['_login_user_id']);
        
                // Update user meta data
                if (isset($_POST['_is_warehouse_personnel'])) {
                    update_user_meta($login_user_id, 'is_warehouse_personnel', sanitize_text_field($_POST['_is_warehouse_personnel']));
                }
        
                // Update the user data
                if (isset($_POST['_user_email']) || isset($_POST['_display_name'])) {
                    $updated_user = array('ID' => $login_user_id);
        
                    if (isset($_POST['_user_email'])) {
                        $updated_user['user_email'] = sanitize_email($_POST['_user_email']);
                    }
                    
                    if (isset($_POST['_display_name'])) {
                        $updated_user['display_name'] = sanitize_text_field($_POST['_display_name']);
                    }
        
                    // Update the user
                    $user_id = wp_update_user($updated_user);
        
                    // Check for errors during user update
                    if (is_wp_error($user_id)) {
                        $response['error'] = $user_id->get_error_message();
                    } else {
                        $response['success'] = 'User updated successfully.';
                    }
                }
            } else {
                // Create a new user
                $new_user = array();
        
                if (isset($_POST['_user_email'])) {
                    $new_user['user_email'] = sanitize_email($_POST['_user_email']);
                }
        
                if (isset($_POST['_display_name'])) {
                    $new_user['display_name'] = sanitize_text_field($_POST['_display_name']);
                }
        
                // Check if the email is provided for new user creation
                if (!empty($new_user['user_email'])) {
                    $user_id = wp_insert_user($new_user);
        
                    // Check for errors during user insertion
                    if (is_wp_error($user_id)) {
                        $response['error'] = $user_id->get_error_message();
                    } else {
                        $response['success'] = 'New user created successfully.';
                    }
                } else {
                    $response['error'] = 'Email is required for new user creation.';
                }
            }
        
            // Send the JSON response
            wp_send_json($response);
        }

        function del_login_user_dialog_data() {
            $response = array();
        
            // Check if the necessary POST variable is set and sanitize it
            if (isset($_POST['_login_user_id'])) {
                $login_user_id = intval($_POST['_login_user_id']);
        
                // Check if the user exists before attempting to delete
                if (get_userdata($login_user_id)) {
                    // Delete the user
                    $deleted = wp_delete_user($login_user_id);
        
                    // Check if the deletion was successful
                    if ($deleted) {
                        $response['success'] = 'User deleted successfully.';
                    } else {
                        $response['error'] = 'Failed to delete the user.';
                    }
                } else {
                    $response['error'] = 'User not found.';
                }
            } else {
                $response['error'] = 'User ID is not set.';
            }
        
            // Send the JSON response
            wp_send_json($response);
        }
   }
    $user_class = new login_users();
}