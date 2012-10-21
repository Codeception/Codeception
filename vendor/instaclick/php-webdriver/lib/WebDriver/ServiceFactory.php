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

namespace WebDriver;

/**
 * WebDriver\ServiceFactory class
 *
 * A service factory
 *
 * @package WebDriver
 */
final class ServiceFactory
{
    /**
     * singleton
     *
     * @var \WebDriver\ServiceFactory
     */
    private static $instance;

    /**
     * @var array
     */
    protected $services;

    /**
     * @var array
     */
    protected $serviceClasses;

    /**
     * Private constructor
     */
    private function __construct()
    {
        $this->services = array();

        $this->serviceClasses = array(
            'service.curl' => '\\WebDriver\\Service\\CurlService',
        );
    }

    /**
     * Get singleton instance
     *
     * @return \WebDriver\ServiceFactory
     */
    static public function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Get service
     *
     * @param string $serviceName Name of service
     *
     * @return object
     */
    public function getService($serviceName)
    {
        if (!isset($this->services[$serviceName])) {
            $className = $this->serviceClasses[$serviceName];

            $this->services[$serviceName] = new $className;
        }

        return $this->services[$serviceName];
    }

    /**
     * Override default service class
     *
     * @param string $serviceName Name of service
     * @param string $className   Name of service class
     */
    public function setServiceClass($serviceName, $className)
    {
        if (substr($className, 0, 1) !== '\\') {
            $className = '\\' . $className;
        }

        $this->serviceClasses[$serviceName] = $className;
    }
}
