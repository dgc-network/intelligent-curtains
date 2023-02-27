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

add_action('init', 'line_bot_api::instance');
//line_bot_api::instance();
if (!class_exists('line_bot_api')) {
    class line_bot_api {

        /**
         * Actions that the Plugin runs before WordPress finishes loading and sending headers
         */
        static function instance() {
            return new self();
        }

        static function init() {
            if (false === ($channel_access_token = get_transient(self::TRANSIENT_KEY__TEMP_CHANNEL_ACCESS_TOKEN))) {
                // If not, get it from the options table
                $channel_access_token = general_helps::decrypt(get_option(self::OPTION_KEY__CHANNEL_ACCESS_TOKEN), self::ENCRYPT_PASSWORD);
            }
            $this->channel_access_token = esc_html($channel_access_token);
            // Add the Menu page at the top of the management interface
            add_action('admin_menu', [$this, 'set_plugin_menu']);
            // Operations performed at the beginning of each admin page, 
            // before the page is rendered, the function for the Plugin to save preferences
            add_action('admin_init', [$this, 'save_settings']);
            //return new self();
        }

        /**
         * Plunin Version
         */        
        const VERSION = '1.0.7';
        
        /**
         * Plugin ID
         */
        const PLUGIN_ID = 'glap';

        /**
         * CREDENTIAL PREFIX
         */
        const CREDENTIAL_PREFIX = self::PLUGIN_ID . '-nonce-action_';

        /**
         * CredentialAction：set
         */
        const CREDENTIAL_ACTION__SETTINGS_FORM = self::PLUGIN_ID . '-nonce-action_settings-form';

        /**
         * CredentialAction：post
         */
        const CREDENTIAL_ACTION__POST = self::PLUGIN_ID . '-nonce-action_post';

        /**
         * CredentialName: set
         */
        const CREDENTIAL_NAME__SETTINGS_FORM = self::PLUGIN_ID . '-nonce-name_settings-form';

        /**
         * CredentialName: Post
         */
        const CREDENTIAL_NAME__POST = self::PLUGIN_ID . '-nonce-name_post';

        /**
         * PLUGIN PREFIX
         */
        const PLUGIN_PREFIX = self::PLUGIN_ID . '_';

        /**
         * OPTIONS KEY：ChannelAccessToken
         */
        const OPTION_KEY__CHANNEL_ACCESS_TOKEN = self::PLUGIN_PREFIX . 'channel-access-token';

        /**
         * SLUG: top
         */
        const SLUG__SETTINGS_FORM = self::PLUGIN_ID . '-settings-form';

        /**
         * SLUG: default
         */
        const SLUG__INITIAL_CONFIG_FORM = self::PLUGIN_PREFIX . 'initial-config-form';

        /**
         * Parameter name: ChannelAccessToken
         */
        const PARAMETER__CHANNEL_ACCESS_TOKEN = self::PLUGIN_PREFIX . 'channel-access-token';

        /**
         * Parameter name: LINE message sending check box
         */
        const PARAMETER__SEND_CHECKBOX = self::PLUGIN_PREFIX . 'send-checkbox';

        /**
         * TRANSIENT key (temporary input value): ChannelAccessToken *4 characters + 41 characters or less
         */
        const TRANSIENT_KEY__TEMP_CHANNEL_ACCESS_TOKEN = self::PLUGIN_PREFIX . 'temp-channel-access-token';

        /**
         * TRANSIENT key (illegal message): ChannelAccessToken
         */
        const TRANSIENT_KEY__INVALID_CHANNEL_ACCESS_TOKEN = self::PLUGIN_PREFIX . 'invalid-channel-access-token';

        /**
         * TRANSIENT key (error): Failed to send LINE message
         */
        const TRANSIENT_KEY__ERROR_SEND_TO_LINE = self::PLUGIN_PREFIX . 'error-send-to-line';

        /**
         * TRANSIENT key (success): LINE message sent successfully
         */
        const TRANSIENT_KEY__SUCCESS_SEND_TO_LINE = self::PLUGIN_PREFIX . 'success-send-to-line';

        /**
         * TRANSIENT key (save complete message): set
         */
        const TRANSIENT_KEY__SAVE_SETTINGS = self::PLUGIN_PREFIX . 'save-settings';

        /**
         * TRANSIENT time limit: 5 seconds
         */
        const TRANSIENT_TIME_LIMIT = 5;

        /**
         * Notification Type: Error
         */
        const NOTICE_TYPE__ERROR = 'error';

        /**
         * Notification Type: Warning
         */
        const NOTICE_TYPE__WARNING = 'warning';

        /**
         * Notification Type: Success
         */
        const NOTICE_TYPE__SUCCESS = 'success';

        /**
         * Notification Type: Information
         */
        const NOTICE_TYPE__INFO = 'info';

        /**
         * Encryption password: the public key and secret key used to decrypt STRIPE
         */
        const ENCRYPT_PASSWORD = 's9YQReXd';

        /**
         * Formal performance: ChannelAccessToken
         */
        const REGEXP_CHANNEL_ACCESS_TOKEN = '/^[a-zA-Z0-9+\/=]{100,}$/';

        /**
         * It is hooked to the operation to be performed,
         * After the basic structure of the management Menu is arranged, 
         * Added functionality to the Menu page at the top of the admin interface
         */
        function set_plugin_menu() {
            add_options_page(
                __( 'Web Services', 'textdomain' ), // The text to be displayed in the title tags of the page when the menu is selected.
                __( 'Web Services', 'textdomain' ), // The text to be used for the menu.
                'manage_options',                   // The capability required for this menu to be displayed to the user.
                'web-services-page',                // The slug name to refer to this menu by (should be unique for this menu).
                [$this, 'show_settings'],           // The function to be called to output the content for this page.
            );
        }

        /**
         * show initial settings
         */
        function show_settings() {        
            // Save completion information by default
            if (false !== ($complete_message = get_transient(self::TRANSIENT_KEY__SAVE_SETTINGS))) {
                $complete_message = general_helps::getNotice($complete_message, self::NOTICE_TYPE__SUCCESS);
            }
            // Error message for ChannelAccessToken
            if (false !== ($invalid_channel_access_token = get_transient(self::TRANSIENT_KEY__INVALID_CHANNEL_ACCESS_TOKEN))) {
                $invalid_channel_access_token = general_helps::getNotice($invalid_channel_access_token, self::NOTICE_TYPE__ERROR);
            }        
            // The parameter name of ChannelAccessToken
            $param_channel_access_token = self::PARAMETER__CHANNEL_ACCESS_TOKEN;
            // Retrieve ChannelAccessToken from TRANSIENT
            if (false === ($channel_access_token = get_transient(self::TRANSIENT_KEY__TEMP_CHANNEL_ACCESS_TOKEN))) {
                // If not, get it from the options table
                $channel_access_token = general_helps::decrypt(get_option(self::OPTION_KEY__CHANNEL_ACCESS_TOKEN), self::ENCRYPT_PASSWORD);
            }
            $channel_access_token = esc_html($channel_access_token);
            // Generation/acquisition of random number fields
            $nonce_field = wp_nonce_field(self::CREDENTIAL_ACTION__SETTINGS_FORM, self::CREDENTIAL_NAME__SETTINGS_FORM, true, false);
            // Generation/acquisition of the submit button
            $submit_button = get_submit_button('Save');
            // Output HTML
            echo <<< EOM
                <div class="wrap">
                <h3>Line Bot API Setting</h3>
                {$complete_message}
                {$invalid_channel_access_token}
                <form action="" method='post' id="line-auto-post-settings-form">
                    {$nonce_field}
                    <p>
                        <label for="{$param_channel_access_token}">Channel Access Token：</label>
                        <input type="text" size="80" name="{$param_channel_access_token}" value="{$channel_access_token}"/>
                    </p>
                    {$submit_button}
                </form>
                </div>
            EOM;
        }
    
        /**
         * The callback function to save the initial settings
         */
        function save_settings() {
            // When the credentials set by the nonce are received by POST
            if (isset($_POST[self::CREDENTIAL_ACTION__SETTINGS_FORM]) && $_POST[self::CREDENTIAL_NAME__SETTINGS_FORM]) {
                // If there is no problem with the verification result of the certificate set by the nonce
                if (check_admin_referer(self::CREDENTIAL_ACTION__SETTINGS_FORM, self::CREDENTIAL_NAME__SETTINGS_FORM)) {
                    // Get ChannelAccessToken from POST
                    $channel_access_token = trim(sanitize_text_field($_POST[self::PARAMETER__CHANNEL_ACCESS_TOKEN]));
                    $valid = true;                
                    // If ChannelAccessToken is incorrect
                    if (!preg_match(self::REGEXP_CHANNEL_ACCESS_TOKEN, $channel_access_token)) {
                        // Hold a message in TRANSIENT for 5 seconds asking you to reset
                        set_transient(self::TRANSIENT_KEY__INVALID_CHANNEL_ACCESS_TOKEN, "Incorrect Channel Access Token。", self::TRANSIENT_TIME_LIMIT);                    
                        // enable flag is False
                        $valid = false;
                    }
                    // If valid flag is true (if ChannelAccessToken is entered)
                    if ($valid) {
                        // save process
                        // Store the ChannelAccessToken in the options table
                        update_option(self::OPTION_KEY__CHANNEL_ACCESS_TOKEN, general_helps::encrypt($channel_access_token, self::ENCRYPT_PASSWORD));
                        // After the save is complete, leave the completion message in TRANSIENT for 5 seconds
                        set_transient(self::TRANSIENT_KEY__SAVE_SETTINGS, "Initial settings saved", self::TRANSIENT_TIME_LIMIT);
                        // (temporarily) remove ChannelAccessToken's invalid message from TRANSIENT
                        delete_transient(self::TRANSIENT_KEY__INVALID_CHANNEL_ACCESS_TOKEN);
                        // (Temporarily) remove user-entered ChannelAccessToken from TRANSIENT
                        delete_transient(self::TRANSIENT_KEY__TEMP_CHANNEL_ACCESS_TOKEN);
                    }
                    // If valid flag is False (if ChannelAccessToken is not entered)
                    else {
                        // Hold the ChannelAccessToken entered by the user in TRANSIENT for 5 seconds
                        set_transient(self::TRANSIENT_KEY__TEMP_CHANNEL_ACCESS_TOKEN, $channel_access_token, self::TRANSIENT_TIME_LIMIT);
                        // (temporarily) remove default save complete message
                        delete_transient(self::TRANSIENT_KEY__SAVE_SETTINGS);
                    }
                    // Redirect to settings screen
                    wp_safe_redirect(menu_page_url(self::SLUG__SETTINGS_FORM), 303);
                }
            }
        }

        /** @var string */
        public $channel_access_token;

        public function __construct() {
            //$this->channel_access_token = general_helps::decrypt(get_option(self::OPTION_KEY__CHANNEL_ACCESS_TOKEN), self::ENCRYPT_PASSWORD);
            if (false === ($channel_access_token = get_transient(self::TRANSIENT_KEY__TEMP_CHANNEL_ACCESS_TOKEN))) {
                // If not, get it from the options table
                $channel_access_token = general_helps::decrypt(get_option(self::OPTION_KEY__CHANNEL_ACCESS_TOKEN), self::ENCRYPT_PASSWORD);
            }
            $this->channel_access_token = esc_html($channel_access_token);
            // Add the Menu page at the top of the management interface
            add_action('admin_menu', [$this, 'set_plugin_menu']);
            // Operations performed at the beginning of each admin page, 
            // before the page is rendered, the function for the Plugin to save preferences
            add_action('admin_init', [$this, 'save_settings']);
        }

        /**
         * @return mixed
         */
        public static function parseEvents() {
         
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                error_log('Method not allowed');
            }
    
            $entityBody = file_get_contents('php://input');
                
            if ($entityBody === false || strlen($entityBody) === 0) {
                http_response_code(400);
                error_log('Missing request body');
            }

            $data = json_decode($entityBody, true);

            return $data['events'];
       
        }

        /**
         * @param string $userId
         * @return object
         */
        public static function getProfile($userId) {
    
            $header = array(
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->channel_access_token,
            );
    
            $context = stream_context_create([
                'http' => [
                    'ignore_errors' => true,
                    'method' => 'GET',
                    'header' => implode("\r\n", $header),
                    //'content' => json_encode($userId),
                ],
            ]);
    
            $response = file_get_contents('https://api.line.me/v2/bot/profile/'.$userId, false, $context);
            if (strpos($http_response_header[0], '200') === false) {
                error_log('Request failed: ' . $response);
            }
    
            $response = stripslashes($response);
            $response = json_decode($response, true);
            
            return $response;
        }
    
        /**
         * @param array<string, mixed> $message
         * @return void
         */
        public static function broadcastMessage($message) {
    
            $header = array(
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->channel_access_token,
            );
    
            $context = stream_context_create([
                'http' => [
                    'ignore_errors' => true,
                    'method' => 'POST',
                    'header' => implode("\r\n", $header),
                    'content' => json_encode($message),
                ],
            ]);
    
            $response = file_get_contents('https://api.line.me/v2/bot/message/broadcast', false, $context);
            if (strpos($http_response_header[0], '200') === false) {
                error_log('Request failed: ' . $response);
            }
        }
    
        /**
         * @param array<string, mixed> $message
         * @return void
         */
        public static function replyMessage($message) {
    
            $header = array(
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->channel_access_token,
            );
    
            $context = stream_context_create([
                'http' => [
                    'ignore_errors' => true,
                    'method' => 'POST',
                    'header' => implode("\r\n", $header),
                    'content' => json_encode($message),
                ],
            ]);
    
            $response = file_get_contents('https://api.line.me/v2/bot/message/reply', false, $context);
            if (strpos($http_response_header[0], '200') === false) {
                error_log('Request failed: ' . $response);
            }
        }
    
        /**
         * @param array<string, mixed> $message
         * @return void
         */
        public static function pushMessage($message) {
    
            $header = array(
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->channel_access_token,
            );
    
            $context = stream_context_create([
                'http' => [
                    'ignore_errors' => true,
                    'method' => 'POST',
                    'header' => implode("\r\n", $header),
                    'content' => json_encode($message),
                ],
            ]);
    
            $response = file_get_contents('https://api.line.me/v2/bot/message/push', false, $context);
            if (strpos($http_response_header[0], '200') === false) {
                error_log('Request failed: ' . $response);
            }
        }
    
        /**
         * @param array<string, mixed> $content
         * @return void
         */
        public function createRichMenu($content) {
    
            $header = array(
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->channel_access_token,
            );
    
            $context = stream_context_create([
                'http' => [
                    'ignore_errors' => true,
                    'method' => 'POST',
                    'header' => implode("\r\n", $header),
                    'content' => json_encode($content),
                ],
            ]);
    
            $response = file_get_contents('https://api.line.me/v2/bot/richmenu', false, $context);
            if (strpos($http_response_header[0], '200') === false) {
                error_log('Request failed: ' . $response);
            }
        }
    
        /**
         * @param string $richMenuId
         * @return void
         */
        public function uploadImageToRichMenu($richMenuId, $imagePath, $content) {
    
            $header = array(
                'Content-Type: image/png',
                'Authorization: Bearer ' . $this->channel_access_token,
            );
    
            $context = stream_context_create([
                'http' => [
                    'ignore_errors' => true,
                    'method' => 'POST',
                    'header' => implode("\r\n", $header),
                    //'content' => json_encode($content),
                    'content' => $imagePath,
                ],
            ]);
    
            $response = file_get_contents('https://api-data.line.me/v2/bot/richmenu/'.$richMenuId.'/content', false, $context);
            if (strpos($http_response_header[0], '200') === false) {
                error_log('Request failed: ' . $response);
            }
        }
    
        /**
         * @param array<string, mixed> $content
         * $content['richMenuId']
         * $content['userIds']
         * @return void
         */
        public function setDefaultRichMenu($content) {
    
            $header = array(
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->channel_access_token,
            );
    
            $context = stream_context_create([
                'http' => [
                    'ignore_errors' => true,
                    'method' => 'POST',
                    'header' => implode("\r\n", $header),
                    'content' => json_encode($content),
                ],
            ]);
    
            if (is_null($content['userIds'])) {
                $response = file_get_contents('https://api.line.me/v2/bot/user/all/richmenu/'.$content['richMenuId'], false, $context);
            } else {
                $response = file_get_contents('https://api.line.me/v2/bot/richmenu/bulk/link', false, $context);
            }
            
            if (strpos($http_response_header[0], '200') === false) {
                error_log('Request failed: ' . $response);
            }
        }
    
        /**
         * @param string $groupId
         * @return object
         */
        public function getGroupSummary($groupId) {
    
            $header = array(
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->channel_access_token,
            );
    
            $context = stream_context_create([
                'http' => [
                    'ignore_errors' => true,
                    'method' => 'GET',
                    'header' => implode("\r\n", $header),
                ],
            ]);
    
            $response = file_get_contents('https://api.line.me/v2/bot/group/'.$groupId.'/summary', false, $context);
            if (strpos($http_response_header[0], '200') === false) {
                error_log('Request failed: ' . $response);
            }
    
            $response = stripslashes($response);
            $response = json_decode($response, true);
            
            return $response;
        }
    
        /**
         * @param string $groupId, $userId
         * @return object
         */
        public function getGroupMemberProfile($groupId, $userId) {
    
            $header = array(
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->channel_access_token,
            );
    
            $context = stream_context_create([
                'http' => [
                    'ignore_errors' => true,
                    'method' => 'GET',
                    'header' => implode("\r\n", $header),
                ],
            ]);
    
            $response = file_get_contents('https://api.line.me/v2/bot/group/'.$groupId.'/member'.'/'.$userId, false, $context);
            if (strpos($http_response_header[0], '200') === false) {
                error_log('Request failed: ' . $response);
            }
    
            $response = stripslashes($response);
            $response = json_decode($response, true);
            
            return $response;
        }
    
        /**
         * @param string $messageId
         * @return object
         */
        public function getContent($messageId) {
    
            $header = array(
                //'Content-Type: application/octet-stream',
                'Authorization: Bearer ' . $this->channel_access_token,
            );
    
            $context = stream_context_create([
                'http' => [
                    'ignore_errors' => true,
                    'method' => 'GET',
                    'header' => implode("\r\n", $header),
                ],
            ]);
            $response = file_get_contents('https://api-data.line.me/v2/bot/message/'.$messageId.'/content', false, $context);
            if (strpos($http_response_header[0], '200') === false) {
                error_log('Request failed: ' . $response);
            }
            return $this->save_temp_image($response);
            //return var_dump($response);
            //return $response;
    
        }
    
        /**
         * Save the submitted image as a temporary file.
         *
         * @todo Revisit file handling.
         *
         * @param string $img Base64 encoded image.
         * @return false|string File name on success, false on failure.
         */
        protected function save_temp_image($img) {
    
            // Strip the "data:image/png;base64," part and decode the image.
            $img = explode(',', $img);
            $img = isset($img[1]) ? base64_decode($img[1]) : base64_decode($img[0]);
            if (!$img) {
                return false;
            }
            // Upload to tmp folder.
            $filename = 'user-feedback-' . date('Y-m-d-H-i-s');
            $tempfile = wp_tempnam($filename, sys_get_temp_dir());
            if (!$tempfile) {
                return false;
            }
            // WordPress adds a .tmp file extension, but we want .png.
            if (rename($tempfile, $filename . '.png')) {
                $tempfile = $filename . '.png';
            }
            if (!WP_Filesystem(request_filesystem_credentials(''))) {
                return false;
            }
            /**
             * WordPress Filesystem API.
             *
             * @var \WP_Filesystem_Base $wp_filesystem
             */
            global $wp_filesystem;
            //$wp_filesystem->chdir(get_temp_dir());
            $success = $wp_filesystem->put_contents($tempfile, $img);
            if (!$success) {
                return false;
            }
            //return $tempfile;
            $upload = wp_get_upload_dir();
            $url = '<img src="'.$upload['url'].'/'.$filename. '.png">';
            $url = '<img src="'.sys_get_temp_dir().$filename. '.png">';
            //$url = $wp_filesystem->wp_content_dir().'/'.$filename;
            return $url;
        }
      
        /**
         * @param string $body
         * @return string
         */
        private function sign($body) {
    
            $hash = hash_hmac('sha256', $body, $this->channelSecret, true);
            $signature = base64_encode($hash);
            return $signature;
        }
    }
}
