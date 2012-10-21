<?php

namespace Behat\Mink\Driver;

use Behat\Mink\Session,
    Behat\Mink\Element\NodeElement,
    Behat\Mink\Exception\DriverException,
    Behat\Mink\Exception\UnsupportedDriverActionException;

use Symfony\Component\DomCrawler\Crawler;

use Selenium\Client as SeleniumClient,
    Selenium\Locator as SeleniumLocator,
    Selenium\Exception as SeleniumException;

/*
 * This file is part of the Behat\Mink.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Selenium driver.
 *
 * @author Alexandre Salomé <alexandre.salome@gmail.com>
 */
class SeleniumDriver implements DriverInterface
{
    const MODIFIER_CTRL  = 'ctrl';
    const MODIFIER_ALT   = 'alt';
    const MODIFIER_SHIFT = 'shift';
    const MODIFIER_META  = 'meta';

    /**
     * Default timeout for Selenium (in milliseconds)
     *
     * @var int
     */
    private $timeout = 60000;

    /**
     * The current session
     *
     * @var Behat\Mink\Session
     */
    private $session;

    /**
     * The selenium browser instance
     *
     * @var Selenium\Browser
     */
    private $browser;

    /**
     * Flag indicating if the browser is started
     *
     * @var boolean
     */
    private $started = false;

    /**
     * Instanciates the driver.
     *
     * @param string          $browser Browser name
     * @param string          $baseUrl Base URL for testing
     * @param Selenium\Client $client  The client for getting a browser
     */
    public function __construct($browser, $baseUrl, SeleniumClient $client)
    {
        $this->browser = $client->getBrowser($baseUrl, $browser);
    }

    /**
     * Returns Selenium browser instance.
     *
     * @return  Selenium\Browser
     */
    public function getBrowser()
    {
        return $this->browser;
    }

    /**
     * @see Behat\Mink\Driver\DriverInterface::setSession()
     */
    public function setSession(Session $session)
    {
        $this->session = $session;
    }

    /**
     * @see Behat\Mink\Driver\DriverInterface::start()
     */
    public function start()
    {
        $this->started = true;
        $this->browser->start();
    }

    /**
     * @see Behat\Mink\Driver\DriverInterface::isStarted()
     */
    public function isStarted()
    {
        return $this->started;
    }

    /**
     * @see Behat\Mink\Driver\DriverInterface::stop()
     */
    public function stop()
    {
        if (true === $this->started) {
            $this->browser->stop();
        }
        $this->started = false;
    }

    /**
     * @see Behat\Mink\Driver\DriverInterface::reset()
     */
    public function reset()
    {
        $this->browser->deleteAllVisibleCookies();
    }

    /**
     * @see Behat\Mink\Driver\DriverInterface::visit()
     */
    public function visit($url)
    {
        $this->browser
            ->open($url)
            ->waitForPageToLoad($this->timeout)
        ;
    }

    /**
     * @see Behat\Mink\Driver\DriverInterface::getCurrentUrl()
     */
    public function getCurrentUrl()
    {
        return $this->browser->getLocation();
    }

    /**
     * @see Behat\Mink\Driver\DriverInterface::reload()
     */
    public function reload()
    {
        $this->browser
            ->refresh()
            ->waitForPageToLoad($this->timeout)
        ;
    }

    /**
     * @see Behat\Mink\Driver\DriverInterface::forward()
     */
    public function forward()
    {
        $this->browser
            ->runScript('history.forward()')
            ->waitForPageToLoad($this->timeout)
        ;
    }

    /**
     * @see Behat\Mink\Driver\DriverInterface::back()
     */
    public function back()
    {
        $this->browser->goBack();
    }

    /**
     * Switches to specific browser window.
     *
     * @param string $name window name (null for switching back to main window)
     */
    public function switchToWindow($name = null)
    {
        $this->browser->selectWindow($name ? $name : 'null');
    }

    /**
     * Switches to specific iFrame.
     *
     * @param string $name iframe name (null for switching back)
     */
    public function switchToIFrame($name = null)
    {
        if ($name) {
            $this->browser->selectFrame('dom=window.frames["'.$name.'"]');
        } else {
            $this->browser->selectFrame('relative=top');
        }
    }

    /**
     * @see Behat\Mink\Driver\DriverInterface::setBasicAuth()
     */
    public function setBasicAuth($user, $password)
    {
        throw new UnsupportedDriverActionException('Basic Auth is not supported by %s', $this);
    }

    /**
     * @see Behat\Mink\Driver\DriverInterface::setRequestHeader()
     */
    public function setRequestHeader($name, $value)
    {
        throw new UnsupportedDriverActionException('Request header is not supported by %s', $this);
    }

    /**
     * @see Behat\Mink\Driver\DriverInterface::getResponseHeaders()
     */
    public function getResponseHeaders()
    {
        throw new UnsupportedDriverActionException('Request header is not supported by %s', $this);
    }

    /**
     * @see Behat\Mink\Driver\DriverInterface::setCookie()
     */
    public function setCookie($name, $value = null)
    {
        if (null === $value) {
            $this->browser->deleteCookie($name, '');
        } else {
            $this->browser->createCookie($name.'='.$value, '');
        }
    }

    /**
     * @see Behat\Mink\Driver\DriverInterface::getCookie()
     */
    public function getCookie($name)
    {
        if ($this->browser->isCookiePresent($name)) {
            return $this->browser->getCookieByName($name);
        }

        return null;
    }

    /**
     * @see Behat\Mink\Driver\DriverInterface::getStatusCode()
     */
    public function getStatusCode()
    {
        throw new UnsupportedDriverActionException('Request header is not supported by %s', $this);
    }

    /**
     * @see Behat\Mink\Driver\DriverInterface::getContent()
     */
    public function getContent()
    {
        return $this->browser->getHtmlSource();
    }

    /**
     * @see Behat\Mink\Driver\DriverInterface::find()
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
     * @see Behat\Mink\Driver\DriverInterface::getTagName()
     */
    public function getTagName($xpath)
    {
        $nodes = $this->getCrawler()->filterXPath($xpath)->eq(0);
        $nodes->rewind();
        $node = $nodes->current();

        return $node->nodeName;
    }

    /**
     * @see Behat\Mink\Driver\DriverInterface::getText()
     */
    public function getText($xpath)
    {
        $result = $this->browser->getText(SeleniumLocator::xpath($xpath));

        return preg_replace("/[ \n]+/", " ", $result);
    }

    /**
     * @see Behat\Mink\Driver\DriverInterface::getHtml()
     */
    public function getHtml($xpath)
    {
        $nodes = $this->getCrawler()->filterXPath($xpath)->eq(0);

        $nodes->rewind();
        $node = $nodes->current();
        $text = $node->C14N();

        // cut the tag itself (making innerHTML out of outerHTML)
        $text = preg_replace('/^\<[^\>]+\>|\<[^\>]+\>$/', '', $text);

        return $text;
    }

    /**
     * @see Behat\Mink\Driver\DriverInterface::getAttribute()
     */
    public function getAttribute($xpath, $name)
    {
        $result = $this->getCrawler()->filterXPath($xpath)->attr($name);
        if ('' === $result) {
            $result = null;
        }
        return $result;
    }

    /**
     * @see Behat\Mink\Driver\DriverInterface::getValue()
     */
    public function getValue($xpath)
    {
        $xpathEscaped = str_replace('"', '\"', $xpath);
        $script = <<<JS
var node = this.browserbot.locateElementByXPath("$xpathEscaped", window.document);
    tagName = node.tagName.toUpperCase(),
    value = "null";
if (tagName == "INPUT") {
    var type = node.getAttribute('type').toLowerCase();
    if (type == "checkbox") {
        value = "boolean:" + node.checked;
    } else if (type == "radio") {
        var name = node.getAttribute('name');
        if (name) {
            var fields = window.document.getElementsByName(name);
            var i, l = fields.length;
            for (i = 0; i < l; i++) {
                var field = fields.item(i);
                if (field.checked) {
                    value = "string:" + field.value;
                }
            }
        }
    } else {
        value = "string:" + node.value;
    }
} else if (tagName == "TEXTAREA") {
  value = "string:" + node.text;
} else if (tagName == "SELECT") {
    if (node.getAttribute('multiple')) {
        options = [];
        for (var i = 0; i < node.options.length; i++) {
            if (node.options[ i ].selected) {
                options.push(node.options[ i ].value);
            }
        }
        value = "array:" + options.join(',');
    } else {
        var idx = node.selectedIndex;
        if (idx >= 0) {
            value = "string:" + node.options.item(idx).value;
        } else {
            value = null;
        }
    }
} else {
  value = "string:" + node.getAttribute('value');
}
value

JS;

        $value = $this->browser->getEval($script);

        if (null === $value) {
            return null;
        } elseif (preg_match('/^string:(.*)$/', $value, $vars)) {
            return $vars[1];
        } elseif (preg_match('/^boolean:(.*)$/', $value, $vars)) {
            return 'true' === strtolower($vars[1]);
        } elseif (preg_match('/^array:(.*)$/', $value, $vars)) {
            if ('' === trim($vars[1])) {
                return array();
            }
            return explode(',', $vars[1]);
        }
    }

    /**
     * @see Behat\Mink\Driver\DriverInterface::setValue()
     */
    public function setValue($xpath, $value)
    {
        $this->browser->type(SeleniumLocator::xpath($xpath), $value);
    }

    /**
     * @see Behat\Mink\Driver\DriverInterface::check()
     */
    public function check($xpath)
    {
        $this->browser->check(SeleniumLocator::xpath($xpath));
    }

    /**
     * @see Behat\Mink\Driver\DriverInterface::uncheck()
     */
    public function uncheck($xpath)
    {
        $this->browser->uncheck(SeleniumLocator::xpath($xpath));
    }

    /**
     * @see Behat\Mink\Driver\DriverInterface::selectOption()
     */
    public function selectOption($xpath, $value, $multiple = false)
    {
        $xpathEscaped = str_replace('"', '\"', $xpath);
        $valueEscaped = str_replace('"', '\"', $value);
        $multipleJS   = $multiple ? 'true' : 'false';

        $script = <<<JS
var node = this.browserbot.locateElementByXPath("$xpathEscaped", window.document);
if (node.tagName == 'SELECT') {
    var i, l = node.length;
    for (i = 0; i < l; i++) {
        if (node[i].value == "$valueEscaped") {
            node[i].selected = true;
        } else if (!$multipleJS) {
            node[i].selected = false;
        }
    }
} else {
    var nodes = window.document.getElementsByName(node.getAttribute('name'));
    var i, l = nodes.length;
    for (i = 0; i < l; i++) {
        if (nodes[i].getAttribute('value') == "$valueEscaped") {
            nodes[i].checked = true;
            break;
        }
    }
}
JS;

        $this->browser->getEval($script);

    }

    /**
     * @see Behat\Mink\Driver\DriverInterface::click()
     */
    public function click($xpath)
    {
        $this->browser->click(SeleniumLocator::xpath($xpath));
        $readyState = $this->browser->getEval('window.document.readyState');

        if ($readyState == 'loading' || $readyState == 'interactive') {
            $this->browser->waitForPageToLoad($this->timeout);
        }
    }

    /**
     * @see Behat\Mink\Driver\DriverInterface::isChecked()
     */
    public function isChecked($xpath)
    {
        return $this->browser->isChecked(SeleniumLocator::xpath($xpath));
    }

    /**
     * @see Behat\Mink\Driver\DriverInterface::attachFile()
     */
    public function attachFile($xpath, $path)
    {
        $this->browser->attachFile(SeleniumLocator::xpath($xpath), 'file://'.$path);
    }

    /**
     * @see Behat\Mink\Driver\DriverInterface::doubleClick()
     */
    public function doubleClick($xpath)
    {
        $this->browser->doubleClick(SeleniumLocator::xpath($xpath));
    }

    /**
     * @see Behat\Mink\Driver\DriverInterface::rightClick()
     *
     * @throws  Behat\Mink\Exception\UnsupportedDriverActionException   action is not supported by this driver
     */
    public function rightClick($xpath)
    {
        throw new UnsupportedDriverActionException('Right click is not supported by %s', $this);
    }

    /**
     * @see Behat\Mink\Driver\DriverInterface::mouseOver()
     */
    public function mouseOver($xpath)
    {
        $this->browser->mouseOver(SeleniumLocator::xpath($xpath));
    }

    /**
     * @see Behat\Mink\Driver\DriverInterface::focus()
     *
     * @throws  Behat\Mink\Exception\UnsupportedDriverActionException   action is not supported by this driver
     */
    public function focus($xpath)
    {
        throw new UnsupportedDriverActionException('Focus is not supported by %s', $this);
    }

    /**
     * @see Behat\Mink\Driver\DriverInterface::blur()
     *
     * @throws  Behat\Mink\Exception\UnsupportedDriverActionException   action is not supported by this driver
     */
    public function blur($xpath)
    {
        throw new UnsupportedDriverActionException('Blur is not supported by %s', $this);
    }

    /**
     * @see Behat\Mink\Driver\DriverInterface::keyPress()
     *
     * @throws  Behat\Mink\Exception\UnsupportedDriverActionException   action is not supported by this driver
     */
    public function keyPress($xpath, $char, $modifier = null)
    {
        $this->keyDownModifier($modifier);
        $this->browser->keyPress(SeleniumLocator::xpath($xpath), $char);
        $this->keyUpModifier($modifier);
    }

    /**
     * @see Behat\Mink\Driver\DriverInterface::keyPress()
     *
     * @throws  Behat\Mink\Exception\UnsupportedDriverActionException   action is not supported by this driver
     */
    public function keyDown($xpath, $char, $modifier = null)
    {
        $this->keyDownModifier($modifier);
        $this->browser->keyDown(SeleniumLocator::xpath($xpath), $char);
        $this->keyUpModifier($modifier);
    }

    /**
     * @see Behat\Mink\Driver\DriverInterface::keyPress()
     *
     * @throws  Behat\Mink\Exception\UnsupportedDriverActionException   action is not supported by this driver
     */
    public function keyUp($xpath, $char, $modifier = null)
    {
        $this->keyDownModifier($modifier);
        $this->browser->keyUp(SeleniumLocator::xpath($xpath), $char);
        $this->keyUpModifier($modifier);
    }

    /**
     * @see Behat\Mink\Driver\DriverInterface::executeScript()
     */
    public function executeScript($script)
    {
        $this->browser->runScript($script);
    }

    /**
     * @see Behat\Mink\Driver\DriverInterface::evaluateScript()
     *
     * @throws  Behat\Mink\Exception\UnsupportedDriverActionException   action is not supported by this driver
     */
    public function evaluateScript($script)
    {
        throw new UnsupportedDriverActionException('Evaluate script is not supported by %s', $this);
    }

    /**
     * @see Behat\Mink\Driver\DriverInterface::wait()
     */
    public function wait($time, $condition)
    {
        try {
            $this->browser->waitForCondition('with (selenium.browserbot.getCurrentWindow()) { '."\n".$condition."\n }", $time);
        } catch (SeleniumException $e) {}
    }

    /**
     * @see Behat\Mink\Driver\DriverInterface::isVisible()
     *
     * @throws  Behat\Mink\Exception\UnsupportedDriverActionException   action is not supported by this driver
     */
    public function isVisible($xpath)
    {
        return $this->browser->isVisible(SeleniumLocator::xpath($xpath));
    }

    /**
     * @see Behat\Mink\Driver\DriverInterface::dragTo()
     *
     * @throws  Behat\Mink\Exception\UnsupportedDriverActionException   action is not supported by this driver
     */
    public function dragTo($sourceXpath, $destinationXpath)
    {
        $this->browser->dragAndDropToObject(SeleniumLocator::xpath($sourceXpath), SeleniumLocator::xpath($destinationXpath));
    }

    /**
     * Returns crawler instance (got from client).
     *
     * @return  Symfony\Component\DomCrawler\Crawler
     *
     * @throws  Behat\Mink\Exception\DriverException    if can't init crawler (no page is opened)
     */
    private function getCrawler()
    {
        $content = '<html>'.$this->browser->getHtmlSource().'</html>';

        $contentType = null;
        // get content-type from meta tag
        if (preg_match('/\<meta[^\>]+charset *= *["\']?([a-zA-Z\-0-9]+)/i', $content, $matches)) {
            $contentType = 'text/html;charset='.$matches[1];
        }

        $crawler = new Crawler();
        $crawler->addContent($content, $contentType);

        return $crawler;
    }

    /**
     * Handles the key down of a keyboard modifier
     *
     * @param string $modifier The modifier to handle (see self::MODIFIER_*)
     */
    protected function keyDownModifier($modifier)
    {
        switch ($modifier) {
            case self::MODIFIER_CTRL:
                throw new UnsupportedDriverActionException('Ctrl key is not supported by %s', $this);
            case self::MODIFIER_ALT:
                $this->browser->altKeyDown();
                break;
            case self::MODIFIER_SHIFT:
                $this->browser->shiftKeyDown();
                break;
            case self::MODIFIER_META:
                $this->browser->metaKeyDown();
                break;
        }
    }

    /**
     * Handles the key up of a keyboard modifier
     *
     * @param string $modifier The modifier to handle (see self::MODIFIER_*)
     */
    protected function keyUpModifier($modifier)
    {
        switch ($modifier) {
            case self::MODIFIER_CTRL:
                throw new UnsupportedDriverActionException('Ctrl key is not supported by %s', $this);
            case self::MODIFIER_ALT:
                $this->browser->altKeyUp();
                break;
            case self::MODIFIER_SHIFT:
                $this->browser->shiftKeyUp();
                break;
            case self::MODIFIER_META:
                $this->browser->metaKeyUp();
                break;
        }
    }
}
