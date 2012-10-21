<?php
/**
 * Copyright 2011-2012 Anthon Pang. All Rights Reserved.
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

use WebDriver\Exception as WebDriverException;

/**
 * WebDriver\Timeouts class
 *
 * @package WebDriver
 *
 * @method void async_script($json) Set the amount of time, in milliseconds, that asynchronous scripts (executed by execute_async) are permitted to run before they are aborted and a timeout error is returned to the client.
 * @method void implicit_wait($json) Set the amount of time the driver should wait when searching for elements.
 */
final class Timeouts extends AbstractWebDriver
{
    /**
     * {@inheritdoc}
     */
    protected function methods()
    {
        return array(
            'async_script' => array('POST'),
            'implicit_wait' => array('POST'),
        );
    }

    /**
     * helper method to wait until user-defined condition is met
     *
     * @param function $callback      callback which returns non-false result if wait condition was met
     * @param integer  $maxIterations maximum number of iterations
     * @param integer  $sleep         sleep duration in seconds between iterations
     * @param array    $args          optional args; if the callback needs $this, then pass it here
     *
     * @return mixed result from callback function
     *
     * @throws \Exception if thrown by callback, or \WebDriver\Exception\Timeout if helper times out
     */
    public function wait($callback, $maxIterations = 1, $sleep = 0, $args = array())
    {
        $i = max(1, $maxIterations);

        while ($i-- > 0) {
            $result = call_user_func_array($callback, $args);

            if ($result !== false) {
                return $result;
            }

            // don't sleep on the last iteration
            $i && sleep($sleep);
        }

        throw WebDriverException::factory(WebDriverException::TIMEOUT, 'wait() method timed out');
    }
}
