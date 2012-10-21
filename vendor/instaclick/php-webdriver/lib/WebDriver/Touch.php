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
 * WebDriver\Touch class
 *
 * @package WebDriver
 *
 * @method void click($jsonElement) Single tap on the touch enabled device.
 * @method void down($jsonCoordinates) Finger down on the screen.
 * @method void up($jsonCoordinates) Finger up on the screen.
 * @method void move($jsonCoordinates) Finger move on the screen.
 * @method void scroll($jsonCoordinates) Scroll on the touch screen using finger based motion events.  Coordinates are either absolute, or relative to a element (if specified).
 * @method void doubleclick($jsonElement) Double tap on the touch screen using finger motion events.
 * @method void longclick($jsonElement) Long press on the touch screen using finger motion events.
 * @method void flick($json) Flick on the touch screen using finger motion events.
 */
final class Touch extends AbstractWebDriver
{
    /**
     * {@inheritdoc}
     */
    protected function methods()
    {
        return array(
            'click' => array('POST'),
            'down' => array('POST'),
            'up' => array('POST'),
            'move' => array('POST'),
            'scroll' => array('POST'),
            'doubleclick' => array('POST'),
            'longclick' => array('POST'),
            'flick' => array('POST'),
        );
    }
}
