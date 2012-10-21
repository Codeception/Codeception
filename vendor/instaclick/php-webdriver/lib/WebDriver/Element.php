<?php
/**
 * Copyright 2004-2012 Facebook. All Rights Reserved.
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
 * @author Justin Bishop <jubishop@gmail.com>
 * @author Anthon Pang <apang@softwaredevelopment.ca>
 * @author Fabrizio Branca <mail@fabrizio-branca.de>
 */

namespace WebDriver;

/**
 * WebDriver\Element class
 *
 * @package WebDriver
 *
 * @method void click() Click on an element.
 * @method void submit() Submit a FORM element.
 * @method string text() Returns the visible text for the element.
 * @method void postValue($json) Send a sequence of key strokes to an element.
 * @method string name() Query for an element's tag name.
 * @method void clear() Clear a TEXTAREA or text INPUT element's value.
 * @method boolean selected() Determine if an OPTION element, or an INPUT element of type checkbox or radiobutton is currently selected.
 * @method boolean enabled() Determine if an element is currently enabled.
 * @method string attribute($attributeName) Get the value of an element's attribute.
 * @method boolean equals($otherId) Test if two element IDs refer to the same DOM element.
 * @method boolean displayed() Determine if an element is currently displayed.
 * @method array location() Determine an element's location on the page.
 * @method array location_in_view() Determine an element's location on the screen once it has been scrolled into view.
 * @method array size() Determine an element's size in pixels.
 * @method string css($propertyName) Query the value of an element's computed CSS property.
 */
final class Element extends Container
{
    /**
     * {@inheritdoc}
     */
    protected function methods()
    {
        return array(
            'click' => array('POST'),
            'submit' => array('POST'),
            'text' => array('GET'),
            'value' => array('POST'),
            'name' => array('GET'),
            'clear' => array('POST'),
            'selected' => array('GET'),
            'enabled' => array('GET'),
            'attribute' => array('GET'),
            'equals' => array('GET'),
            'displayed' => array('GET'),
            'location' => array('GET'),
            'location_in_view' => array('GET'),
            'size' => array('GET'),
            'css' => array('GET'),
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function obsoleteMethods()
    {
        return array(
            'value' => array('GET'),
            'selected' => array('POST'),
            'toggle' => array('POST'),
            'hover' => array('POST'),
            'drag' => array('POST'),
        );
    }

    /**
     * Element ID
     *
     * @var string
     */
    private $id;

    /**
     * Constructor
     *
     * @param string $url URL
     * @param string $id  element ID
     */
    public function __construct($url, $id)
    {
        parent::__construct($url);

        $this->id = $id;
    }

    /**
     * Get element ID
     *
     * @return string
     */
    public function getID()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    protected function getElementPath($elementId)
    {
        return preg_replace(sprintf('/%s$/', $this->id), $elementId, $this->url);
    }
}
