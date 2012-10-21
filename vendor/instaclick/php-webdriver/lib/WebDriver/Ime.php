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

/**
 * WebDriver\Ime class
 *
 * @package WebDriver
 *
 * @method array available_engines() List all available engines on the machines.
 * @method string active_engine() Get the name of the active IME engine.
 * @method boolean activated() Indicates whether IME input is active at the momeent.
 * @method void deactivate() De-activates the curently active IME engine.
 * @method void activate($json) Make an engine that is available active.
 */
final class Ime extends AbstractWebDriver
{
    /**
     * {@inheritdoc}
     */
    protected function methods()
    {
        return array(
            'available_engines' => array('GET'),
            'active_engine' => array('GET'),
            'activated' => array('GET'),
            'deactivate' => array('POST'),
            'activate' => array('POST'),
        );
    }
}
