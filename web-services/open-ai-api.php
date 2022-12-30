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
class open_ai {

    /** @var string */
    private $channelAccessToken;
    /** @var string */
    private $channelSecret;

    /**
     * @param string $channelAccessToken
     * @param string $channelSecret
     */
/*    
    public function __construct($channelAccessToken, $channelSecret)
    {
        $this->channelAccessToken = $channelAccessToken;
        $this->channelSecret = $channelSecret;
    }
*/
    public function __construct($channelAccessToken='', $channelSecret='') {

        if ($channelAccessToken==''||$channelSecret=='') {
            if (file_exists(dirname( __FILE__ ) . '/config.ini')) {
                $config = parse_ini_file(dirname( __FILE__ ) . '/config.ini', true);
                if ($config['OpenAI']['API_KEY'] == null || $config['OpenAI']['Orgnazation'] == null) {
                    error_log("config.ini uncompleted!", 0);
                } else {
                    $channelAccessToken = $config['OpenAI']['API_KEY'];
                    $channelSecret = $config['OpenAI']['Orgnazation'];
                }
            }    
        } 
        $this->channelAccessToken = $channelAccessToken;
        $this->channelSecret = $channelSecret;
    }

    /**
     * @param array<string, mixed> $param
     * @return void
     */
    public function createCompletion($param) {

        $header = array(
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->channelAccessToken,
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
}