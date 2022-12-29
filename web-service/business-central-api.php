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
class business_central {

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
                if ($config['BusinessCentral']['Token'] == null || $config['BusinessCentral']['Secret'] == null) {
                    error_log("config.ini uncompleted!", 0);
                } else {
                    $channelAccessToken = $config['BusinessCentral']['Token'];
                    $channelSecret = $config['BusinessCentral']['Secret'];
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
    //public function getItems($param) {
    public function getItems() {

        $header = array(
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->channelAccessToken,
        );

        $context = stream_context_create([
            'http' => [
                'ignore_errors' => true,
                //'method' => 'POST',
                'method' => 'GET',
                'header' => implode("\r\n", $header),
                //'content' => json_encode($param),
            ],
        ]);

        $end_point = 'https://api.businesscentral.dynamics.com/v2.0/6431b284-21b0-4f5d-9bff-8f963418794e/Development/ODataV4/Company("DG")/Items';

        $response = file_get_contents($end_point, false, $context);
        if (strpos($http_response_header[0], '200') === false) {
            error_log('Request failed: ' . $response);
        }

        return $response;
        //$data = json_decode($response, true);
        //return $data['choices'][0];
    }
}