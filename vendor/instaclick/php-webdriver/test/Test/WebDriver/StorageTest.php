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

use WebDriver\Storage;

/**
 * Test WebDriver\Storage class
 *
 * @package WebDriver
 */
class StorageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * test factory()
     */
    public function testFactory()
    {
        $out = Storage::factory('Local', '/');
        $this->assertTrue($out instanceof Storage\Local);

        $out = Storage::factory('sEsSiOn', '/');
        $this->assertTrue($out instanceof Storage\Session);
    }
}
