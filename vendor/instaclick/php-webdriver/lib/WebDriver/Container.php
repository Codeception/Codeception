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

use WebDriver\Exception as WebDriverException;

/**
 * Abstract WebDriver\Container class
 *
 * @package WebDriver
 */
abstract class Container extends AbstractWebDriver
{
    /**
     * {@inheritdoc}
     */
    public function __construct($url = 'http://localhost:4444/wd/hub')
    {
        parent::__construct($url);

        $locatorStrategy = new \ReflectionClass('WebDriver\LocatorStrategy');
        $this->strategies  = $locatorStrategy->getConstants();
    }

    /**
     * Find element: /session/:sessionId/element (POST)
     * Find child element: /session/:sessionId/element/:id/element (POST)
     * Search for element on page, starting from the document root.
     *
     * @param string $using the locator strategy to use
     * @param string $value the search target
     *
     * @return \WebDriver\Element
     *
     * @throws \WebDriver\Exception if element not found, or invalid XPath
     */
    public function element($using = null, $value = null)
    {
        $locatorJson = $this->parseArgs('element', func_get_args());

        try {
            $results = $this->curl(
                'POST',
                '/element',
                $locatorJson
            );
        } catch (WebDriverException\NoSuchElement $e) {
            throw WebDriverException::factory(WebDriverException::NO_SUCH_ELEMENT,
                sprintf(
                    'Element not found with %s, %s',
                    $locatorJson['using'],
                    $locatorJson['value']) . "\n\n" . $e->getMessage(), $e
            );
        }

        return $this->webDriverElement($results['value']);
    }

    /**
     * Find elements: /session/:sessionId/elements (POST)
     * Find child elements: /session/:sessionId/element/:id/elements (POST)
     * Search for multiple elements on page, starting from the document root.
     *
     * @param string $using the locator strategy to use
     * @param string $value the search target
     *
     * @return array
     *
     * @throws \WebDriver\Exception if invalid XPath
     */
    public function elements($using = null, $value = null)
    {
        $locatorJson = $this->parseArgs('elements', func_get_args());

        $results = $this->curl(
            'POST',
            '/elements',
            $locatorJson
        );

        if (!is_array($results['value'])) {
            return array();
        }

        return array_filter(array_map(
            array($this, 'webDriverElement'), $results['value']
        ));
    }

    /**
     * Parse arguments allowing either separate $using and $value parameters, or
     * as an array containing the JSON parameters
     *
     * @param string $method method name
     * @param array  $argv   arguments
     *
     * @return array
     *
     * @throws \WebDriver\Exception if invalid number of arguments to the called method
     */
    private function parseArgs($method, $argv)
    {
        $argc = count($argv);

        switch ($argc) {
            case 2:
                $using = $argv[0];
                $value = $argv[1];
                break;

            case 1:
                $arg = $argv[0];
                if (is_array($arg)) {
                    $using = $arg['using'];
                    $value = $arg['value'];
                    break;
                }

            default:
                throw WebDriverException::factory(WebDriverException::JSON_PARAMETERS_EXPECTED,
                    sprintf('Invalid arguments to %s method: %s', $method, print_r($argv, true))
                );
        }

        return $this->locate($using, $value);
    }

    /**
     * Return JSON parameter for element / elements command
     *
     * @param string $using locator strategy
     * @param string $value search target
     *
     * @return array
     *
     * @throws \WebDriver\Exception if invalid locator strategy
     */
    public function locate($using, $value)
    {
        if (!in_array($using, $this->strategies)) {
            throw WebDriverException::factory(WebDriverException::UNKNOWN_LOCATOR_STRATEGY,
                sprintf('Invalid locator strategy %s', $using)
            );
        }

        return array(
            'using' => $using,
            'value' => $value,
        );
    }

    /**
     * Return WebDriver\Element wrapper for $value
     *
     * @param mixed $value
     *
     * @return \WebDriver\Element|null
     */
    protected function webDriverElement($value)
    {
        return array_key_exists('ELEMENT', (array) $value)
            ? new Element(
                $this->getElementPath($value['ELEMENT']), // url
                $value['ELEMENT'] // id
            )
            : null;
    }

    /**
     * {@inheritdoc}
     */
    public function __call($name, $arguments)
    {
        if (count($arguments) == 1 && in_array(str_replace('_', ' ', $name), $this->strategies)) {
            return $this->locate($name, $arguments[0]);
        }

        // fallback to executing WebDriver commands
        return parent::__call($name, $arguments);
    }

    /**
     * Get wire protocol URL for an element
     *
     * @param string $elementId
     *
     * @return string
     */
    abstract protected function getElementPath($elementId);
}
