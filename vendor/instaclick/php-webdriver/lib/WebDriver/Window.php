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
 * @author Fabrizio Branca <mail@fabrizio-branca.de>
 */

namespace WebDriver;

/**
 * WebDriver\Window class
 *
 * @package WebDriver
 *
 * @method array getSize() Get size of the window.
 * @method void postSize($json) Change the size of the window.
 * @method array getPosition() Get position of the window.
 * @method void postPosition($json) Change position of the window.
 * @method void maximize() Maximize the window if not already maximized.
 */
final class Window extends AbstractWebDriver
{
    /**
     * Window handle
     *
     * @var string
     */
    private $windowHandle;

    /**
     * {@inheritdoc}
     */
    protected function methods()
    {
        return array(
            'size' => array('GET', 'POST'),
            'position' => array('GET', 'POST'),
            'maximize' => array('POST'),
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function obsoleteMethods()
    {
        return array(
            'restore' => array('POST'),
        );
    }

    /**
     * Get window handle
     *
     * @return string
     */
    public function getHandle()
    {
        return $this->windowHandle;
    }

    /**
     * Constructor
     *
     * @param string $url          URL
     * @param string $windowHandle Window handle
     */
    public function __construct($url, $windowHandle)
    {
        $this->windowHandle = $windowHandle;

        parent::__construct($url . '/' . $windowHandle);
    }
}
