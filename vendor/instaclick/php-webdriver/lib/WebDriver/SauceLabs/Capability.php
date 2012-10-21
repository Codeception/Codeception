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

namespace WebDriver\SauceLabs;

use WebDriver\Capability as BaseCapability;

/**
 * WebDriver\SauceLabs\Capability class
 *
 * @package WebDriver
 */
class Capability extends BaseCapability
{
    /**
     * Desired capabilities - SauceLabs
     *
     * @see http://saucelabs.com/docs/ondemand/additional-config#desired-capabilities
     */

    // Job Annotation
    const NAME                  = 'name';                  // Name the job
    const BUILD                 = 'build';                 // Record the build number
    const TAGS                  = 'tags';                  // Tag your jobs
    const PASSED                = 'passed';                // Record pass/fail status
    const CUSTOM_DATA           = 'custom-data';           // Record custom data

    // Performance improvements and data collection
    const RECORD_VIDEO          = 'record-video';          // Video recording
    const VIDEO_UPLOAD_ON_PASS  = 'video-upload-on-pass';  // Video upload on pass
    const RECORD_SCREENSHOTS    = 'record-screenshots';    // Record step-by-step screenshots
    const CAPTURE_HTML          = 'capture-html';          // HTML source capture
    const STRIP_SE2_SCREENSHOTS = 'strip-se2-screenshots'; // Disable Selenium 2's automatic screenshots
    const SAUCE_ADVISOR         = 'sauce-advisor';         // Sauce Advisor

    // Selenium specific
    const SELENIUM_VERSION      = 'selenium-version';      // Use a specific Selenium version

    // Timeouts
    const MAX_DURATION          = 'max-duration';          // Set maximum test duration
    const COMMAND_TIMEOUT       = 'command-timeout';       // Set command timeout
    const IDLE_TIMEOUT          = 'idle-timeout';          // Set idle test timeout

    // Sauce OnDemand specific
    const AVOID_PROXY           = 'avoid-proxy';           // Avoid proxy
    const DISABLE_POPUP_HANDLER = 'disable-popup-handler'; // Disable popup handler

    // Job Sharing
    const PUBLIC_RESULTS        = 'public';                // Share job's result page, video, and logs
    const RESTRICTED_PUBLIC     = 'restricted-public';     // Share job's result page and video only
}
