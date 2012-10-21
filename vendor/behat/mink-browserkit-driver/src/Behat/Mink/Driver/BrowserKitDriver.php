<?php

namespace Behat\Mink\Driver;

use Symfony\Component\BrowserKit\Client,
    Symfony\Component\BrowserKit\Cookie,
    Symfony\Component\DomCrawler\Crawler,
    Symfony\Component\DomCrawler\Form,
    Symfony\Component\DomCrawler\Field,
    Symfony\Component\DomCrawler\Field\FormField;

use Behat\Mink\Session,
    Behat\Mink\Element\NodeElement,
    Behat\Mink\Exception\DriverException,
    Behat\Mink\Exception\UnsupportedDriverActionException,
    Behat\Mink\Exception\ElementNotFoundException;

/*
 * This file is part of the Behat\Mink.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Symfony2 BrowserKit driver.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class BrowserKitDriver implements DriverInterface
{
    private $session;
    private $client;
    private $forms = array();
    private $started = false;

    /**
     * Initializes Goutte driver.
     *
     * @param Client $client BrowserKit client instance
     */
    public function __construct(Client $client = null)
    {
        $this->client = $client;
        $this->client->followRedirects(true);
    }

    /**
     * Returns BrowserKit HTTP client instance.
     *
     * @return Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Sets driver's current session.
     *
     * @param Session $session
     */
    public function setSession(Session $session)
    {
        $this->session = $session;
    }

    /**
     * Starts driver.
     */
    public function start()
    {
        $this->started = true;
    }

    /**
     * Checks whether driver is started.
     *
     * @return Boolean
     */
    public function isStarted()
    {
        return $this->started;
    }

    /**
     * Stops driver.
     */
    public function stop()
    {
        $this->client->restart();
        $this->started = false;
        $this->forms = array();
    }

    /**
     * Resets driver.
     */
    public function reset()
    {
        $this->client->restart();
        $this->forms = array();
    }

    /**
     * Visit specified URL.
     *
     * @param string $url url of the page
     */
    public function visit($url)
    {
        $this->client->request('GET', $this->prepareUrl($url));
        $this->forms = array();
    }

    /**
     * Returns current URL address.
     *
     * @return string
     */
    public function getCurrentUrl()
    {
        return $this->client->getRequest()->getUri();
    }

    /**
     * Reloads current page.
     */
    public function reload()
    {
        $this->client->reload();
        $this->forms = array();
    }

    /**
     * Moves browser forward 1 page.
     */
    public function forward()
    {
        $this->client->forward();
        $this->forms = array();
    }

    /**
     * Moves browser backward 1 page.
     */
    public function back()
    {
        $this->client->back();
        $this->forms = array();
    }

    /**
     * Switches to specific browser window.
     *
     * @param string $name window name (null for switching back to main window)
     *
     * @throws UnsupportedDriverActionException
     */
    public function switchToWindow($name = null)
    {
        throw new UnsupportedDriverActionException('Window management is not supported by %s', $this);
    }

    /**
     * Switches to specific iFrame.
     *
     * @param string $name iframe name (null for switching back)
     *
     * @throws UnsupportedDriverActionException
     */
    public function switchToIFrame($name = null)
    {
        throw new UnsupportedDriverActionException('iFrame management is not supported by %s', $this);
    }

    /**
     * Sets HTTP Basic authentication parameters
     *
     * @param string|Boolean $user     user name or false to disable authentication
     * @param string         $password password
     */
    public function setBasicAuth($user, $password)
    {
        $this->client->setServerParameter('PHP_AUTH_USER', $user);
        $this->client->setServerParameter('PHP_AUTH_PW', $password);
    }

    /**
     * Sets specific request header on client.
     *
     * @param string $name
     * @param string $value
     */
    public function setRequestHeader($name, $value)
    {
        switch (strtolower($name)) {
            case 'accept':
                $name = 'HTTP_ACCEPT';
                break;
            case 'accept-charset':
                $name = 'HTTP_ACCEPT_CHARSET';
                break;
            case 'accept-encoding':
                $name = 'HTTP_ACCEPT_ENCODING';
                break;
            case 'accept-language':
                $name = 'HTTP_ACCEPT_LANGUAGE';
                break;
            case 'connection':
                $name = 'HTTP_CONNECTION';
                break;
            case 'host':
                $name = 'HTTP_HOST';
                break;
            case 'user-agent':
                $name = 'HTTP_USER_AGENT';
                break;
            case 'authorization':
                $name = 'PHP_AUTH_DIGEST';
                break;
        }

        $this->client->setServerParameter($name, $value);
    }

    /**
     * Returns last response headers.
     *
     * @return array
     */
    public function getResponseHeaders()
    {
        $headers         = array();
        $responseHeaders = trim($this->client->getResponse()->headers->__toString());

        foreach (explode("\r\n", $responseHeaders) as $header) {
            list($name, $value) = array_map('trim', explode(':', $header, 2));

            if (isset($headers[$name])) {
                $headers[$name]   = array($headers[$name]);
                $headers[$name][] = $value;
            } else {
                $headers[$name] = $value;
            }
        }

        return $headers;
    }

    /**
     * Sets cookie.
     *
     * @param string $name
     * @param string $value
     */
    public function setCookie($name, $value = null)
    {
        $jar = $this->client->getCookieJar();

        if (null === $value) {
            if (null !== $jar->get($name)) {
                $jar->expire($name);
            }

            return;
        }

        $jar->set(new Cookie($name, $value));
    }

    /**
     * Returns cookie by name.
     *
     * @param string $name
     *
     * @return string|null
     */
    public function getCookie($name)
    {
        // Note that the following doesn't work well because
        // Symfony\Component\BrowserKit\CookieJar stores cookies by name,
        // path, AND domain and if you don't fill them all in correctly then
        // you won't get the value that you're expecting.
        //
        // $jar = $this->client->getCookieJar();
        //
        // if (null !== $cookie = $jar->get($name)) {
        //     return $cookie->getValue();
        // }

        $allValues = $this->client->getCookieJar()->allValues($this->getCurrentUrl());

        if (isset($allValues[$name])) {
            return $allValues[$name];
        } else {
            return null;
        }
    }

    /**
     * Returns last response status code.
     *
     * @return integer
     */
    public function getStatusCode()
    {
        return $this->client->getResponse()->getStatusCode();
    }

    /**
     * Returns last response content.
     *
     * @return string
     */
    public function getContent()
    {
        return $this->client->getResponse()->getContent();
    }

    /**
     * Finds elements with specified XPath query.
     *
     * @param string $xpath
     *
     * @return array array of NodeElements
     */
    public function find($xpath)
    {
        $nodes = $this->getCrawler()->filterXPath($xpath);

        $elements = array();
        foreach ($nodes as $i => $node) {
            $elements[] = new NodeElement(sprintf('(%s)[%d]', $xpath, $i + 1), $this->session);
        }

        return $elements;
    }

    /**
     * Returns element's tag name by it's XPath query.
     *
     * @param string $xpath
     *
     * @return string
     */
    public function getTagName($xpath)
    {
        return $this->getCrawlerNode($this->getCrawler()->filterXPath($xpath)->eq(0))->nodeName;
    }

    /**
     * Returns element's text by it's XPath query.
     *
     * @param string $xpath
     *
     * @return string
     */
    public function getText($xpath)
    {
        $text = $this->getCrawler()->filterXPath($xpath)->eq(0)->text();
        $text = str_replace("\n", ' ', $text);
        $text = preg_replace('/ {2,}/', ' ', $text);

        return trim($text);
    }

    /**
     * Returns element's html by it's XPath query.
     *
     * @param string $xpath
     *
     * @return string
     */
    public function getHtml($xpath)
    {
        $node = $this->getCrawlerNode($this->getCrawler()->filterXPath($xpath)->eq(0));
        $text = $node->ownerDocument->saveXML($node);

        // cut the tag itself (making innerHTML out of outerHTML)
        $text = preg_replace('/^\<[^\>]+\>|\<[^\>]+\>$/', '', $text);

        return $text;
    }

    /**
     * Returns element's attribute by it's XPath query.
     *
     * @param string $xpath
     * @param string $name
     *
     * @return mixed
     */
    public function getAttribute($xpath, $name)
    {
        $value = $this->getCrawler()->filterXPath($xpath)->eq(0)->attr($name);

        return '' !== $value ? $value : null;
    }

    /**
     * Returns element's value by it's XPath query.
     *
     * @param string $xpath
     *
     * @return mixed
     */
    public function getValue($xpath)
    {
        if (in_array($this->getAttribute($xpath, 'type'), array('submit', 'image', 'button'))) {
            return $this->getAttribute($xpath, 'value');
        }

        try {
            $field = $this->getFormField($xpath);
        } catch (\InvalidArgumentException $e) {
            return $this->getAttribute($xpath, 'value');
        }

        $value = $field->getValue();

        if ($field instanceof Field\ChoiceFormField && 'checkbox' === $field->getType()) {
            $value = '1' == $value;
        }

        return $value;
    }

    /**
     * Sets element's value by it's XPath query.
     *
     * @param string $xpath
     * @param string $value
     */
    public function setValue($xpath, $value)
    {
        $this->getFormField($xpath)->setValue($value);
    }

    /**
     * Checks checkbox by it's XPath query.
     *
     * @param string $xpath
     */
    public function check($xpath)
    {
        $this->getFormField($xpath)->tick();
    }

    /**
     * Unchecks checkbox by it's XPath query.
     *
     * @param string $xpath
     */
    public function uncheck($xpath)
    {
        $this->getFormField($xpath)->untick();
    }

    /**
     * Selects option from select field located by it's XPath query.
     *
     * @param string  $xpath
     * @param string  $value
     * @param Boolean $multiple
     */
    public function selectOption($xpath, $value, $multiple = false)
    {
        $field = $this->getFormField($xpath);

        if ($multiple) {
            $oldValue   = (array) $field->getValue();
            $oldValue[] = $value;
            $value      = $oldValue;
        }

        $field->select($value);
    }

    /**
     * Clicks button or link located by it's XPath query.
     *
     * @param string $xpath
     *
     * @throws ElementNotFoundException
     * @throws DriverException
     */
    public function click($xpath)
    {
        if (!count($nodes = $this->getCrawler()->filterXPath($xpath))) {
            throw new ElementNotFoundException(
                $this->session, 'link or button', 'xpath', $xpath
            );
        }
        $node = $nodes->eq(0);
        $type = $this->getCrawlerNode($node)->nodeName;

        if ('a' === $type) {
            $this->client->click($node->link());
        } elseif('input' === $type || 'button' === $type) {
            $form   = $node->form();
            $formId = $this->getFormNodeId($form->getFormNode());

            if (isset($this->forms[$formId])) {
                $this->mergeForms($form, $this->forms[$formId]);
            }

            $this->client->submit($form);
        } else {
            throw new DriverException(sprintf(
                'Goutte driver supports clicking on inputs and links only. But "%s" provided', $type
            ));
        }

        $this->forms = array();
    }

    /**
     * Checks whether checkbox checked located by it's XPath query.
     *
     * @param string $xpath
     *
     * @return Boolean
     */
    public function isChecked($xpath)
    {
        return (bool) $this->getValue($xpath);
    }

    /**
     * Attaches file path to file field located by it's XPath query.
     *
     * @param string $xpath
     * @param string $path
     */
    public function attachFile($xpath, $path)
    {
        $this->getFormField($xpath)->upload($path);
    }

    /**
     * Double-clicks button or link located by it's XPath query.
     *
     * @param string $xpath
     *
     * @throws UnsupportedDriverActionException
     */
    public function doubleClick($xpath)
    {
        throw new UnsupportedDriverActionException('Double-clicking is not supported by %s', $this);
    }

    /**
     * Right-clicks button or link located by it's XPath query.
     *
     * @param string $xpath
     *
     * @throws UnsupportedDriverActionException
     */
    public function rightClick($xpath)
    {
        throw new UnsupportedDriverActionException('Right-clicking is not supported by %s', $this);
    }

    /**
     * Simulates a mouse over on the element.
     *
     * @param string $xpath
     *
     * @throws UnsupportedDriverActionException
     */
    public function mouseOver($xpath)
    {
        throw new UnsupportedDriverActionException('Mouse moving is not supported by %s', $this);
    }

    /**
     * Brings focus to element.
     *
     * @param string $xpath
     *
     * @throws UnsupportedDriverActionException
     */
    public function focus($xpath)
    {
        throw new UnsupportedDriverActionException('Focus actions are not supported by %s', $this);
    }

    /**
     * Removes focus from element.
     *
     * @param string $xpath
     *
     * @throws UnsupportedDriverActionException
     */
    public function blur($xpath)
    {
        throw new UnsupportedDriverActionException('Focus actions are not supported by %s', $this);
    }

    /**
     * Presses specific keyboard key.
     *
     * @param string $xpath
     * @param mixed  $char     could be either char ('b') or char-code (98)
     * @param string $modifier keyboard modifier (could be 'ctrl', 'alt', 'shift' or 'meta')
     *
     * @throws UnsupportedDriverActionException
     */
    public function keyPress($xpath, $char, $modifier = null)
    {
        throw new UnsupportedDriverActionException('Keyboard actions are not supported by %s', $this);
    }

    /**
     * Pressed down specific keyboard key.
     *
     * @param string $xpath
     * @param mixed  $char     could be either char ('b') or char-code (98)
     * @param string $modifier keyboard modifier (could be 'ctrl', 'alt', 'shift' or 'meta')
     *
     * @throws UnsupportedDriverActionException
     */
    public function keyDown($xpath, $char, $modifier = null)
    {
        throw new UnsupportedDriverActionException('Keyboard actions are not supported by %s', $this);
    }

    /**
     * Pressed up specific keyboard key.
     *
     * @param string $xpath
     * @param mixed  $char     could be either char ('b') or char-code (98)
     * @param string $modifier keyboard modifier (could be 'ctrl', 'alt', 'shift' or 'meta')
     *
     * @throws UnsupportedDriverActionException
     */
    public function keyUp($xpath, $char, $modifier = null)
    {
        throw new UnsupportedDriverActionException('Keyboard actions are not supported by %s', $this);
    }

    /**
     * Executes JS script.
     *
     * @param string $script
     *
     * @throws UnsupportedDriverActionException
     */
    public function executeScript($script)
    {
        throw new UnsupportedDriverActionException('JS scripts execution is not supported by %s', $this);
    }

    /**
     * Evaluates JS script.
     *
     * @param string $script
     *
     * @throws UnsupportedDriverActionException
     */
    public function evaluateScript($script)
    {
        throw new UnsupportedDriverActionException('JS scripts execution is not supported by %s', $this);
    }

    /**
     * Waits some time or until JS condition turns true.
     *
     * @param integer $time      time in milliseconds
     * @param string  $condition JS condition
     *
     * @throws UnsupportedDriverActionException
     */
    public function wait($time, $condition)
    {
        throw new UnsupportedDriverActionException('JS scripts execution is not supported by %s', $this);
    }

    /**
     * Checks whether element visible located by it's XPath query.
     *
     * @param string $xpath
     *
     * @return Boolean
     *
     * @throws UnsupportedDriverActionException
     */
    public function isVisible($xpath)
    {
        throw new UnsupportedDriverActionException('Element visibility check is not supported by %s', $this);
    }

    /**
     * Drag one element onto another.
     *
     * @param string $sourceXpath
     * @param string $destinationXpath
     *
     * @throws UnsupportedDriverActionException
     */
    public function dragTo($sourceXpath, $destinationXpath)
    {
        throw new UnsupportedDriverActionException('Element dragging is not supported by %s', $this);
    }

    /**
     * Prepares URL for visiting.
     * Removes "*.php/" from urls and then passes it to GoutteDriver::visit().
     *
     * @param string $url
     *
     * @return string
     */
    protected function prepareUrl($url)
    {
        return preg_replace('/^(https?\:\/\/[^\/]+)(\/[^\/]+\.php)?/', '$1', $url);
    }

    /**
     * Returns form field from XPath query.
     *
     * @param string $xpath
     *
     * @return FormField
     *
     * @throws ElementNotFoundException
     * @throws \LogicException
     */
    private function getFormField($xpath)
    {
        if (!count($crawler = $this->getCrawler()->filterXPath($xpath))) {
            throw new ElementNotFoundException(
                $this->session, 'form field', 'xpath', $xpath
            );
        }

        $fieldNode = $this->getCrawlerNode($crawler);
        $fieldName = str_replace('[]', '', $fieldNode->getAttribute('name'));
        $formNode  = $fieldNode;

        do {
            // use the ancestor form element
            if (null === $formNode = $formNode->parentNode) {
                throw new \LogicException('The selected node does not have a form ancestor.');
            }
        } while ('form' != $formNode->nodeName);

        $formId = $this->getFormNodeId($formNode);

        // check if form already exists
        if (isset($this->forms[$formId])) {
            return $this->forms[$formId][$fieldName];
        }

        // find form button
        if (null === $buttonNode = $this->findFormButton($formNode)) {
            throw new ElementNotFoundException(
                $this->session, 'form submit button for field with xpath "'.$xpath.'"'
            );
        }

        $this->forms[$formId] = new Form($buttonNode, $this->client->getRequest()->getUri());

        return $this->forms[$formId][$fieldName];
    }

    /**
     * Returns form node unique identifier.
     *
     * @param \DOMElement $form
     *
     * @return mixed
     */
    private function getFormNodeId(\DOMElement $form)
    {
        return md5($form->getLineNo() . $form->getNodePath() . $form->nodeValue);
    }

    /**
     * Finds form submit button inside form node.
     *
     * @param \DOMElement $form
     *
     * @return \DOMElement
     */
    private function findFormButton(\DOMElement $form)
    {
        $document = new \DOMDocument('1.0', 'UTF-8');
        $node     = $document->importNode($form, true);
        $root     = $document->appendChild($document->createElement('_root'));

        $root->appendChild($node);
        $xpath = new \DOMXPath($document);

        foreach ($xpath->query('descendant::input | descendant::button', $root) as $node) {
            if ('button' == $node->nodeName || in_array($node->getAttribute('type'), array('submit', 'button', 'image'))) {
                return $node;
            }
        }

        return null;
    }

    /**
     * Merges second form values into first one.
     *
     * @param Form $to   merging target
     * @param Form $from merging source
     */
    private function mergeForms(Form $to, Form $from)
    {
        foreach ($from->all() as $name => $field) {
            $fieldReflection = new \ReflectionObject($field);
            $nodeReflection  = $fieldReflection->getProperty('node');
            $valueReflection = $fieldReflection->getProperty('value');

            $nodeReflection->setAccessible(true);
            $valueReflection->setAccessible(true);

            if (!($field instanceof Field\InputFormField && in_array(
                $nodeReflection->getValue($field)->getAttribute('type'),
                array('submit', 'button', 'image')
            ))) {
                $valueReflection->setValue($to[$name], $valueReflection->getValue($field));
            }
        }
    }

    /**
     * Returns DOMNode from crawler instance.
     *
     * @param Crawler $crawler
     * @param integer $num     number of node from crawler
     *
     * @return \DOMNode
     */
    private function getCrawlerNode(Crawler $crawler, $num = 0)
    {
        foreach ($crawler as $i => $node) {
            if ($num == $i) {
                return $node;
            }
        }

        return null;
    }

    /**
     * Returns crawler instance (got from client).
     *
     * @return Crawler
     *
     * @throws DriverException
     */
    private function getCrawler()
    {
        $crawler = $this->client->getCrawler();

        if (null === $crawler) {
            throw new DriverException('Crawler can\'t be initialized. Did you started driver?');
        }

        return $crawler;
    }
}
