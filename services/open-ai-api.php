<?php
/**
 * Copyright 2022 dgc.network
 *
 * dgc.network licenses this file to you under the Apache License,
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
if (!class_exists('open_ai_api')) {
    class open_ai_api {

        /** @var string */
        private $openai_api_key;
    
        /**
         * @param string $openai_api_key
         */
        public function __construct($openai_api_key='') {
    
            if ($openai_api_key=='') {
                if (file_exists(dirname( __FILE__ ) . '/config.ini')) {
                    $config = parse_ini_file(dirname( __FILE__ ) . '/config.ini', true);
                    if ($config['OpenAI']['API_KEY'] == null) {
                        error_log("config.ini uncompleted!", 0);
                    } else {
                        $openai_api_key = $config['OpenAI']['API_KEY'];
                    }
                }    
            } 
            $this->openai_api_key = $openai_api_key;

            $this->openai_api_key = get_option('open_ai_api_key');

        }
    
        /**
         * @param array<string, mixed> $param
         * @return void
         */
        public function createCompletion($param) {
    
            $header = array(
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->openai_api_key,
            );
    
            $context = stream_context_create([
                'http' => [
                    'ignore_errors' => true,
                    'method' => 'POST',
                    'header' => implode("\r\n", $header),
                    'content' => json_encode($param),
                ],
            ]);
    
            $response = file_get_contents('https://api.openai.com/v1/completions', false, $context);
            if (strpos($http_response_header[0], '200') === false) {
                error_log('Request failed: ' . $response);
            }
    
            //return $response;
            $data = json_decode($response, true);
            return $data['choices'][0];
        }

        /**
         * @param array<string, mixed> $param
         * @return void
         */
        public function createChatCompletion($userMessage) {
            $param = array(
                'model' => 'gpt-3.5-turbo',
                'messages' => array(
                    // Fixed system role for maintaining the subject
                    array('role' => 'system', 'content' => 'intelligent-curtains'),
                    // User's message
                    array('role' => 'user', 'content' => $userMessage),
                ),
                'temperature' => 1.0,
                'max_tokens' => 4000,
                'frequency_penalty' => 0,
                'presence_penalty' => 0,
            );
        
            $header = array(
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->openai_api_key,
            );
        
            $context = stream_context_create([
                'http' => [
                    'ignore_errors' => true,
                    'method' => 'POST',
                    'header' => implode("\r\n", $header),
                    'content' => json_encode($param),
                ],
            ]);
        
            $response = file_get_contents('https://api.openai.com/v1/chat/completions', false, $context);
            if (strpos($http_response_header[0], '200') === false) {
                error_log('Request failed: ' . $response);
            }
        
            $data = json_decode($response, true);
            $responseContent = $data['choices'][0]['message']['content'];
        
            return $responseContent;
        }

        function generate_openai_proposal($prompt) {
            // Get all attachment post IDs
            $attachment_ids = get_posts(array(
                'post_type' => 'attachment',
                'numberposts' => -1,
                'post_status' => null,
                'fields' => 'ids',
            ));
        
            // Initialize an empty array to store the content of attachments
            $attachment_contents = array();
        
            // Loop through each attachment
            foreach ($attachment_ids as $attachment_id) {
                // Get the attachment content
                $attachment_content = wp_get_attachment_url($attachment_id);
                // Add the attachment content to the prompt if it can be retrieved
                if ($attachment_content) {
                    // Attempt to retrieve the attachment content
                    $content = file_get_contents($attachment_content);
                    if ($content !== false) {
                        // Add the attachment content to the prompt
                        $prompt .= "\n" . $content;
                    } else {
                        // Log error or handle the case where the attachment cannot be read
                        error_log('Unable to read attachment: ' . $attachment_content);
                    }
                }
            }
        
            // Prepare OpenAI API parameters
            $param = array(
                'model' => 'gpt-3.5-turbo',
                'messages' => array(
                    // Fixed system role for maintaining the subject
                    array('role' => 'system', 'content' => 'iso-helper'),
                    // User's message
                    array('role' => 'user', 'content' => $prompt),
                ),
                'temperature' => 1.0,
                'max_tokens' => 4000,
                'frequency_penalty' => 0,
                'presence_penalty' => 0,
            );
        
            // Set up request headers
            $header = array(
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->openai_api_key,
            );
        
            // Create the request context
            $context = stream_context_create([
                'http' => [
                    'ignore_errors' => true,
                    'method' => 'POST',
                    'header' => implode("\r\n", $header),
                    'content' => json_encode($param),
                ],
            ]);
        
            // Call the OpenAI API
            $response = file_get_contents('https://api.openai.com/v1/chat/completions', false, $context);
            if (strpos($http_response_header[0], '200') === false) {
                error_log('Request failed: ' . $response);
            }
        
            // Parse the response
            $data = json_decode($response, true);
            $responseContent = $data['choices'][0]['message']['content'];
        
            // Return the generated response
            return $responseContent;
        }
    }
}

