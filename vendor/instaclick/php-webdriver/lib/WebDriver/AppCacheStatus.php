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
 * WebDriver\AppCacheStatus class
 *
 * @package WebDriver
 */
final class AppCacheStatus
{
    /**
     * Application cache status
     *
     * @see https://github.com/Selenium2/Selenium2/blob/master/java/client/src/org/openqa/selenium/html5/AppCacheStatus.java
     */
    const UNCACHED     = 0;
    const IDLE         = 1;
    const CHECKING     = 2;
    const DOWNLOADING  = 3;
    const UPDATE_READY = 4;
    const OBSOLETE     = 5;
}
