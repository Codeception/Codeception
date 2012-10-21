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

namespace Test\WebDriver;

use WebDriver\Exception;

/**
 * Test WebDriver\Exception class
 *
 * @package WebDriver
 */
class ExceptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * test factory()
     */
    public function testFactory()
    {
        $out = Exception::factory(255, 'wtf');
        $this->assertTrue(get_class($out) === 'Exception');
        $this->assertTrue($out->getMessage() === 'wtf');

        $out = Exception::factory(Exception::SUCCESS);
        $this->assertTrue(get_class($out) === 'Exception');
        $this->assertTrue($out->getMessage() === 'Unknown Error');

        $out = Exception::factory(Exception::CURL_EXEC);
        $this->assertTrue($out instanceof Exception\CurlExec);
        $this->assertTrue($out->getMessage() === 'curl_exec() error.');
    }
}
