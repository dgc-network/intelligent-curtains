<?php
/**
 * Copyright 2016 LINE Corporation
 *
 * LINE Corporation licenses this file to you under the Apache License,
 * version 2.0 (the "License"); you may not use this file except in compliance
 * with the License. You may obtain a copy of the License at:
 *
 *   https://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations
 * under the License.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if (!class_exists('line_login_api')) {
    class line_login_api {
        private $channel_id;
        private $channel_access_token;

        public function __construct() {
            $this->channel_id = get_option('line_login_channel_id');
            $this->channel_access_token = get_option('line_login_token_option');
            add_action( 'admin_init', array( $this, 'line_login_register_settings' ) );
            add_action('init', array( $this, 'handle_line_callback'));
            add_shortcode( 'line-login', array( $this, 'display_message' ));
        }

        function line_login_register_settings() {
            // Register Line login section
            add_settings_section(
                'line-login-section-settings',
                'Line login Settings',
                array( $this, 'line_login_section_settings_callback' ),
                'web-service-settings'
            );

            // Register fields for Line login section
            add_settings_field(
                'line_login_redirect_uri',
                'Line login redirect uri',
                array( $this, 'line_login_redirect_uri_callback' ),
                'web-service-settings',
                'line-login-section-settings'
            );
            register_setting('web-service-settings', 'line_login_redirect_uri');

            add_settings_field(
                'line_login_client_id',
                'Line login client id',
                array( $this, 'line_login_client_id_callback' ),
                'web-service-settings',
                'line-login-section-settings'
            );
            register_setting('web-service-settings', 'line_login_client_id');

            add_settings_field(
                'line_login_client_secret',
                'Line login client secret',
                array( $this, 'line_login_client_secret_callback' ),
                'web-service-settings',
                'line-login-section-settings'
            );
            register_setting('web-service-settings', 'line_login_client_secret');
        }

        function line_login_section_settings_callback() {
            echo '<p>Settings for Line login.</p>';
        }

        function line_login_redirect_uri_callback() {
            $value = get_option('line_login_redirect_uri');
            echo '<input type="text" name="line_login_redirect_uri" style="width:100%;" value="' . esc_attr($value) . '" />';
        }

        function line_login_client_id_callback() {
            $value = get_option('line_login_client_id');
            echo '<input type="text" name="line_login_client_id" style="width:100%;" value="' . esc_attr($value) . '" />';
        }

        function line_login_client_secret_callback() {
            $value = get_option('line_login_client_secret');
            echo '<input type="text" name="line_login_client_secret" style="width:100%;" value="' . esc_attr($value) . '" />';
        }

        // login callback
        function handle_line_callback() {
            if (isset($_GET['code']) && isset($_GET['state'])) {
                // Sanitize inputs
                $code = sanitize_text_field($_GET['code']);
                $state = sanitize_text_field($_GET['state']);
        
                // Validate the state parameter (important to prevent CSRF)
                $stored_state = get_transient('line_login_state');
                if ($state !== $stored_state) {
                    wp_die('Invalid state parameter. Possible CSRF attack.');
                }
                // Remove the transient as it's no longer needed
                delete_transient('line_login_state');
        
                // Exchange authorization code for access token
                $response = wp_remote_post('https://api.line.me/oauth2/v2.1/token', array(
                    'body' => array(
                        'grant_type'    => 'authorization_code',
                        'code'          => $code,
                        'redirect_uri'  => get_option('line_login_redirect_uri'),
                        'client_id'     => get_option('line_login_client_id'),
                        'client_secret' => get_option('line_login_client_secret'),
                    ),
                ));
                if (is_wp_error($response)) {
                    wp_die('Failed to contact LINE API: ' . $response->get_error_message());
                }
                $body = wp_remote_retrieve_body($response);
                $json = json_decode($body, true);
        
                if (isset($json['access_token'])) {
                    $access_token = $json['access_token'];
                    // Use the access token to get the user's profile
                    $profile_response = wp_remote_get('https://api.line.me/v2/profile', array(
                        'headers' => array(
                            'Authorization' => 'Bearer ' . $access_token,
                        ),
                    ));
                    if (is_wp_error($profile_response)) {
                        wp_die('Failed to retrieve LINE profile: ' . $profile_response->get_error_message());
                    }
                    $profile = json_decode(wp_remote_retrieve_body($profile_response), true);
        
                    if (isset($profile['userId'])) {
                        // You now have the user's LINE ID
                        $line_user_id = $profile['userId'];
                        $user_login = $profile['userId'];
                        $display_name = isset($profile['displayName']) ? $profile['displayName'] : '';
                        error_log('Found the LINE profile and display the name: ' . $display_name);
        
                        // Check if user exists
                        global $wpdb;
                        $user_id = $wpdb->get_var($wpdb->prepare(
                            "SELECT user_id FROM $wpdb->usermeta WHERE meta_key = 'line_user_id' AND meta_value = %s",
                            $line_user_id
                        ));
                        if ($user_id) {
                            error_log('Found the user in WordPress and the user ID is ' . $user_id);
                        } else {
                            // Register a new user
                            $random_password = wp_generate_password();
                            $user_id = wp_insert_user(array(
                                'user_login' => $user_login,
                                'user_pass'  => $random_password,
                                'display_name' => $display_name // Adding display name to the registration process
                            ));
                            // Check if user registration failed
                            if (is_wp_error($user_id)) {
                                error_log('User registration failed: ' . $user_id->get_error_message());
                                wp_die('User registration failed: ' . $user_id->get_error_message());
                            } else {
                                add_user_meta($user_id, 'line_user_id', $line_user_id);
                                error_log('Registered a new user in WordPress and the user ID is ' . $user_id);
                            }
                        }
        
                        // Check if headers have already been sent
                        if (headers_sent()) {
                            wp_die('Headers already sent. Cannot set cookie.');
                        } else {
                            //clean_user_cache($user_id);
                            //wp_clear_auth_cookie();
                            wp_set_current_user($user_id);
                            wp_set_auth_cookie($user_id, true);
                            error_log('Auth Cookie: ' . print_r($_COOKIE, true));
                            // Log the user login action for consistency with WP behavior
                            $user = get_user_by('id', $user_id);
                            do_action('wp_login', $user->user_login, $user);
                            error_log('User object: ' . print_r($user, true));
                            error_log('Completed setting the auth cookie for the user ID: ' . $user_id);
                            // Redirect the user after setting the cookie
                            wp_redirect(home_url());
                            exit;
                        }
                    } else {
                        wp_die('Failed to retrieve LINE user profile.');
                    }
                } else {
                    wp_die('Failed to get access token from LINE.');
                }
            }
        }
        
        function display_line_login_button() {
            $state = bin2hex(random_bytes(16)); // Generate a random string
            set_transient('line_login_state', $state, 3600); // Save it for 1 hour
            $line_auth_url = "https://access.line.me/oauth2/v2.1/authorize?response_type=code&client_id=" . urlencode(get_option('line_login_client_id')) .
                 "&redirect_uri=" . urlencode(get_option('line_login_redirect_uri')) .
                 "&state=" . urlencode($state) .
                 "&scope=profile";
            ?>
            <div style="display: flex; justify-content: center; align-items: center; height: 100vh; flex-direction: column;">
                <a href="<?php echo $line_auth_url;?>">    
                    <img src="https://s3.ap-southeast-1.amazonaws.com/app-assets.easystore.co/apps/154/icon.png" alt="LINE Login">
                </a><br>
                <p style="text-align: center;">
                    <?php echo __( 'You are not logged in.', 'textdomain' );?><br>
                    <?php echo __( 'Please click the above button to log in.', 'textdomain' );?><br>
                </p>
            </div>
            <?php            
        }

        function display_message() {
            echo '<pre>';
            echo 'Auth Cookie: ' . print_r($_COOKIE, true) . "\n\n";
            $user = wp_get_current_user();
            echo 'User object: ' . print_r($user, true);
            echo '</pre>';
            if (is_user_logged_in()) {
            } else {
                //user_is_not_logged_in();
                $this->display_line_login_button();
            }
        }
    }
    $line_login_api = new line_login_api();
}
