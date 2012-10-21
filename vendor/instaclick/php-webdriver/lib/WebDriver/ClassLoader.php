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
 * WebDriver\ClassLoader (autoloader) class
 *
 * @package WebDriver
 */
final class ClassLoader
{
    /**
     * Load class
     *
     * @param string $class Class name
     */
    public static function loadClass($class)
    {
        $file = strpos($class, '\\') !== false
            ? str_replace('\\', DIRECTORY_SEPARATOR, $class)
            : str_replace('_', DIRECTORY_SEPARATOR, $class);

        $path = dirname(__DIR__) . DIRECTORY_SEPARATOR . $file . '.php';

        if (file_exists($path)) {
            include_once $path;
        }
    }

    /**
     * Autoloader
     *
     * @param string $class Class name
     */
    public static function autoload($class)
    {
        try {
            self::loadClass($class);
        } catch (Exception $e) {
        }
    }
}

// Note: only one __autoload per PHP instance
if(function_exists('spl_autoload_register'))
{
    // use the SPL autoload stack
    spl_autoload_register(array('WebDriver\ClassLoader', 'autoload'));

    // preserve any existing __autoload
    if(function_exists('__autoload'))
    {
        spl_autoload_register('__autoload');
    }
}
else
{
    function __autoload($class)
    {
        ClassLoader::autoload($class);
    }
}
