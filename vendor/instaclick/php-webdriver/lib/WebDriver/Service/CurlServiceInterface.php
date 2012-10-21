<?php
/**
 * Copyright 2012 Anthon Pang. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @package WebDriver
 *
 * @author Anthon Pang <apang@softwaredevelopment.ca>
 */

namespace WebDriver\Service;

/**
 * WebDriver\Service\CurlServiceInterface class
 *
 * @package WebDriver
 */
interface CurlServiceInterface
{
    /**
     * Send protocol request to WebDriver server using curl extension API.
     *
     * @param string $requestMethod HTTP request method, e.g., 'GET', 'POST', or 'DELETE'
     * @param string $url           Request URL
     * @param array  $parameters    If an array(), they will be posted as JSON parameters
     *                              If a number or string, "/$params" is appended to url
     * @param array  $extraOptions  key=>value pairs of curl options to pass to curl_setopt()
     *
     * @return array
     *
     * @throws \WebDriver\Exception if error
     */
    public function execute($requestMethod, $url, $parameters = null, $extraOptions = array());
}
