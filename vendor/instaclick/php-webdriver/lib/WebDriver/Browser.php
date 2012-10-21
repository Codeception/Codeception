<?php
/**
 * Copyright 2011-2012 Fabrizio Branca. All Rights Reserved.
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
 * @author Fabrizio Branca <mail@fabrizio-branca.de>
 * @author Anthon Pang <apang@softwaredevelopment.ca>
 */

namespace WebDriver;

/**
 * WebDriver\Browser class
 *
 * @package WebDriver
 */
final class Browser
{
    /**
     * Check browser names used in static functions in the selenium source:
     * @see http://code.google.com/p/selenium/source/browse/trunk/java/client/src/org/openqa/selenium/remote/DesiredCapabilities.java
     *
     * Note: Capability array takes these browserNames and not the "browserTypes"
     *
     * Also check
     * @see http://code.google.com/p/selenium/wiki/JsonWireProtocol#Capabilities_JSON_Object
     */
    const ANDROID           = 'android';
    const CHROME            = 'chrome';
    const FIREFOX           = 'firefox';
    const HTMLUNIT          = 'htmlunit';
    const INTERNET_EXPLORER = 'internet explorer';
    const IPHONE            = 'iPhone';
    const IPAD              = 'iPad';
    const OPERA             = 'opera';
}
