<?php

namespace Behat\Mink\Driver;

use Behat\Mink\Driver\NodeJS\Server\ZombieServer;

use Behat\Mink\Session,
    Behat\Mink\Element\NodeElement,
    Behat\Mink\Exception\DriverException,
    Behat\Mink\Exception\UnsupportedDriverActionException;

/*
 * This file is part of the Behat\Mink.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Zombie (JS) driver.
 *
 * @author Pascal Cremer <b00gizm@gmail.com>
 */
class ZombieDriver implements DriverInterface
{
    private $started = false;
    private $nativeRefs = array();
    private $server = null;

    /**
     * Constructor.
     *
     * @param   mixed $v,...  Either the connection parameters for creating
     *                        the server
     *                          string  $host - The server's host
     *                          int     $port - The port to connect to
     *                        or a valid ZombieServer object instance
     */
    public function __construct()
    {
        $numArgs = func_num_args();
        if (0 === $numArgs) {
            throw new \InvalidArgumentException(
                "You must either provide connection parameters or a ZombieServer object"
            );
        }

        $argList = func_get_args();
        $first = array_shift($argList);
        if ($first instanceof ZombieServer) {
            $this->server = $first;

            return;
        } else if (is_object($first)) {
            throw new \InvalidArgumentException(
                "Invalid first argument"
            );
        }

        $params = array();
        $params['host'] = (string)$first;

        $second = array_shift($argList);
        if (null !== $second) {
            $params['port'] = intval($second);
        }

        $params = array_replace(array(
            'host' => '127.0.0.1',
            'port' => 8124,
        ), $params);

        $this->server = new ZombieServer($params['host'], $params['port']);
    }

    /**
     * Returns Zombie.js server.
     *
     * @return ZombieServer
     */
    public function getServer()
    {
        return $this->server;
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
        if ($this->server) {
            $this->server->start();
        }

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
        if ($this->server) {
            $this->server->stop();
        }

        $this->started = false;
    }

    /**
     * Resets driver.
     */
    public function reset()
    {
        // Cleanup cached references
        $this->nativeRefs = array();

        $js = <<<JS
browser.cookies(browser.window.location.hostname, '/').clear();
browser = null;
pointers = [];
stream.end();
JS;

        $this->server->evalJS($js);
    }

    /**
     * Visit specified URL.
     *
     * @param string $url url of the page
     *
     * @throws DriverException
     */
    public function visit($url)
    {
        // Cleanup cached references
        $this->nativeRefs = array();

        $js = <<<JS
pointers = [];
browser.visit("{$url}", function(err) {
  if (err) {
    stream.end(JSON.stringify(err.stack));
  } else {
    stream.end();
  }
});
JS;
        $out = $this->server->evalJS($js);

        if (!empty($out)) {
          throw new DriverException(sprintf("Could not load resource for URL '%s'", $url));
        }
    }

    /**
     * Returns current URL address.
     *
     * @return string
     */
    public function getCurrentUrl()
    {
        return $this->server->evalJS('browser.location.toString()', 'json');
    }

    /**
     * Reloads current page.
     */
    public function reload()
    {
        $this->visit($this->getCurrentUrl());
    }

    /**
     * Moves browser forward 1 page.
     */
    public function forward()
    {
        $this->server->evalJS("browser.window.history.forward(); browser.wait(function() { stream.end(); })");
    }

    /**
     * Moves browser backward 1 page.
     */
    public function back()
    {
        $this->server->evalJS("browser.window.history.back(); browser.wait(function() { stream.end(); })");
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
        $this->server->evalJS("browser.credentials = { credentials: { schema: 'basic', username: '{$user}', password: '{$password}'}};stream.end();");
    }

    /**
     * Sets specific request header on client.
     *
     * @param string $name
     * @param string $value
     *
     * @throws UnsupportedDriverActionException
     */
    public function setRequestHeader($name, $value)
    {
        if (strtolower($name) === 'user-agent') {
            $this->server->evalJS("browser.userAgent = '$value';stream.end();");
            return;
        }

        throw new UnsupportedDriverActionException('Request header "' . $name . '" manipulation is not supported by %s', $this);
    }

    /**
     * Returns last response headers.
     *
     * @return array
     */
    public function getResponseHeaders()
    {
        return (array)$this->server->evalJS('browser.lastResponse.headers', 'json');
    }

    /**
     * Sets cookie.
     *
     * @param string $name
     * @param string $value
     */
    public function setCookie($name, $value = null)
    {
        $js = "browser.cookies(browser.window.location.hostname, '/')";
        $js .= (null === $value) ? ".remove('{$name}')" : ".set('{$name}', '{$value}')";
        $this->server->evalJS($js, 'json');
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
        $js = <<<JS
var cookieVal = browser.cookies(browser.window.location.hostname, '/').get('{$name}');
if (cookieVal) {
    stream.end(unescape(cookieVal))
} else {
    stream.end();
}
JS;

        $res = $this->server->evalJS($js);
        if (empty($res)) {
            return null;
        }

        return $res;
    }

    /**
     * Returns last response status code.
     *
     * @return integer
     */
    public function getStatusCode()
    {
        return (int)$this->server->evalJS('browser.statusCode', 'json');
    }

    /**
     * Returns last response content.
     *
     * @return string
     */
    public function getContent()
    {
        return html_entity_decode($this->server->evalJS('browser.html()', 'json'));
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
        $xpathEncoded = json_encode($xpath);
        $js = <<<JS
var refs = [];
browser.xpath("{$xpath}").value.forEach(function(node) {
  if (node.nodeType !== 10) {
    pointers.push(node);
    refs.push(pointers.length - 1);
  }
});
stream.end(JSON.stringify(refs));
JS;
        $refs = (array)json_decode($this->server->evalJS($js));

        $elements = array();
        foreach ($refs as $i => $ref) {
            $subXpath = sprintf('(%s)[%d]', $xpath, $i + 1);
            $this->nativeRefs[md5($subXpath)] = $ref;
            $elements[] = new NodeElement($subXpath, $this->session);

            // first node ref also matches the original xpath
            if (0 === $i) {
                $this->nativeRefs[md5($xpath)] = $ref;
            }
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
        if (!$ref = $this->getNativeRefForXPath($xpath)) {
            return null;
        }

        return strtolower($this->server->evalJS("{$ref}.tagName", 'json'));
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
        if (!$ref = $this->getNativeRefForXPath($xpath)) {
            return null;
        }

        return trim($this->server->evalJS("{$ref}.textContent.replace(/\s+/g, ' ')", 'json'));
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
        if (!$ref = $this->getNativeRefForXPath($xpath)) {
            return null;
        }

        return $this->server->evalJS("{$ref}.innerHTML", 'json');
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
        if (!$ref = $this->getNativeRefForXPath($xpath)) {
            return null;
        }

        $out = $this->server->evalJS("{$ref}.getAttribute('{$name}')", 'json');

        return empty($out) ? null : $out;
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
        if (!$ref = $this->getNativeRefForXPath($xpath)) {
            return null;
        }

        $js = <<<JS
var node = {$ref},
    tagName = node.tagName,
    value = null;
if (tagName == "INPUT") {
  var type = node.getAttribute('type').toLowerCase();
  if (type == "checkbox") {
    value = node.checked;
  } else if (type == "radio") {
    var name = node.getAttribute('name');
    if (name) {
      var field = browser.field("input[type='radio'][name='" + name + "']:checked");
      if (field) {
        value = field.value;
      }
    }
  } else {
    value = node.value;
  }
} else if (tagName == "TEXTAREA") {
  value = node.text;
} else if (tagName == "SELECT") {
  if (node.getAttribute('multiple')) {
    value = [];
    for (var i = 0; i < node.options.length; i++) {
      if (node.options[ i ].selected) {
        value.push(node.options[ i ].value);
      }
    }
  } else {
    var idx = node.selectedIndex;
    if (idx >= 0) {
      value = node.options.item(idx).value;
    } else {
      value = null;
    }
  }
} else {
  value = node.getAttribute('value');
}
stream.end(JSON.stringify(value));
JS;
        return json_decode($this->server->evalJS($js));
    }

    /**
     * Sets element's value by it's XPath query.
     *
     * @param string $xpath
     * @param string $value
     */
    public function setValue($xpath, $value)
    {
        if (!$ref = $this->getNativeRefForXPath($xpath)) {
            return;
        }

        $value = json_encode($value);

        $js = <<<JS
var node = {$ref},
    tagName = node.tagName;
if (tagName == "TEXTAREA") {
  node.textContent = {$value};
} else {
  var type = node.getAttribute('type');
  if (type == "checkbox") {
    {$value} ? browser.check(node) : browser.uncheck(node);
  } else if (type == "radio") {
    browser.choose(node);
  } else {
    browser.fill(node, {$value});
  }
}
stream.end();
JS;
        $this->server->evalJS($js);
    }

    /**
     * Checks checkbox by it's XPath query.
     *
     * @param string $xpath
     */
    public function check($xpath)
    {
        if (!$ref = $this->getNativeRefForXPath($xpath)) {
            return;
        }

        $this->server->evalJS("browser.check({$ref});stream.end();");
    }

    /**
     * Unchecks checkbox by it's XPath query.
     *
     * @param string $xpath
     */
    public function uncheck($xpath)
    {
        if (!$ref = $this->getNativeRefForXPath($xpath)) {
            return;
        }

        $this->server->evalJS("browser.uncheck({$ref});stream.end();");
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
        if (!$ref = $this->getNativeRefForXPath($xpath)) {
            return false;
        }

        return (boolean)$this->server->evalJS("{$ref}.checked", 'json');
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
        if (!$ref = $this->getNativeRefForXPath($xpath)) {
            return;
        }

        $value = json_encode($value);
        $js = <<<JS
var node = {$ref},
    tagName = node.tagName;
if (tagName == "SELECT") {
  browser.select(node, {$value});
} else if (tagName == "INPUT") {
  var type = node.getAttribute('type');
  if (type == "radio") {
    browser.choose(node);
  }
}
stream.end();
JS;
        $this->server->evalJS($js);
    }

    /**
     * Clicks button or link located by it's XPath query.
     *
     * @param string $xpath
     *
     * @throws DriverException
     */
    public function click($xpath)
    {
        if (!$ref = $this->getNativeRefForXPath($xpath)) {
            return;
        }

        $js = <<<JS
var node    = {$ref},
    tagName = node.tagName.toLowerCase();
    type    = (node.getAttribute('type') || '').toLowerCase();
if (tagName == "button" || (tagName == "input" && (type == "button" || type == "submit"))) {
  if (node.getAttribute('disabled')) {
    stream.end('This button is disabled');
  }
}
browser.fire("click", node, function(err) {
  if (err) {
    stream.end(JSON.stringify(err.stack));
  } else {
    stream.end();
  }
});
JS;
        $out = $this->server->evalJS($js);
        if (!empty($out)) {
            throw new DriverException('Error while clicking button: [%s]', $out);
        }
    }

    /**
     * Double-clicks button or link located by it's XPath query.
     *
     * @param string $xpath
     */
    public function doubleClick($xpath)
    {
        $this->triggerBrowserEvent("dblclick", $xpath);
    }

    /**
     * Right-clicks button or link located by it's XPath query.
     *
     * @param string $xpath
     */
    public function rightClick($xpath)
    {
        $this->triggerBrowserEvent("contextmenu", $xpath);
    }

    /**
     * Attaches file path to file field located by it's XPath query.
     *
     * @param string $xpath
     * @param string $path
     */
    public function attachFile($xpath, $path)
    {
        if (!$ref = $this->getNativeRefForXPath($xpath)) {
            return;
        }

        $path = json_encode($path);
        $this->server->evalJS("browser.attach({$ref}, {$path});stream.end();");
    }

    /**
     * Checks whether element visible located by it's XPath query.
     *
     * @param string $xpath
     *
     * @return Boolean
     */
    public function isVisible($xpath)
    {
        if (!$ref = $this->getNativeRefForXPath($xpath)) {
            return;
        }

        // This is kind of a workaround, because the current version of
        // Zombie.js does not fully support the DOMElement's style attribute
        $hiddenXpath = json_encode("./ancestor-or-self::*[contains(@style, 'display:none') or contains(@style, 'display: none')]");
        return (0 == (int)$this->server->evalJS("browser.xpath({$hiddenXpath}, {$ref}).value.length", 'json'));
    }

    /**
     * Simulates a mouse over on the element.
     *
     * @param string $xpath
     */
    public function mouseOver($xpath)
    {
        $this->triggerBrowserEvent("mouseover", $xpath);
    }

    /**
     * Brings focus to element.
     *
     * @param string $xpath
     */
    public function focus($xpath)
    {
        $this->triggerBrowserEvent("focus", $xpath);
    }

    /**
     * Removes focus from element.
     *
     * @param string $xpath
     */
    public function blur($xpath)
    {
        $this->triggerBrowserEvent("blur", $xpath);
    }

    /**
     * Presses specific keyboard key.
     *
     * @param string $xpath
     * @param mixed  $char     could be either char ('b') or char-code (98)
     * @param string $modifier keyboard modifier (could be 'ctrl', 'alt', 'shift' or 'meta')
     */
    public function keyPress($xpath, $char, $modifier = null)
    {
        $this->triggerKeyEvent("keypress", $xpath, $char, $modifier);
    }

    /**
     * Pressed down specific keyboard key.
     *
     * @param string $xpath
     * @param mixed  $char     could be either char ('b') or char-code (98)
     * @param string $modifier keyboard modifier (could be 'ctrl', 'alt', 'shift' or 'meta')
     */
    public function keyDown($xpath, $char, $modifier = null)
    {
        $this->triggerKeyEvent("keydown", $xpath, $char, $modifier);
    }

    /**
     * Pressed up specific keyboard key.
     *
     * @param string $xpath
     * @param mixed  $char     could be either char ('b') or char-code (98)
     * @param string $modifier keyboard modifier (could be 'ctrl', 'alt', 'shift' or 'meta')
     */
    public function keyUp($xpath, $char, $modifier = null)
    {
        $this->triggerKeyEvent("keyup", $xpath, $char, $modifier);
    }

    /**
     * Drag one element onto another.
     *
     * @param string $sourceXpath
     * @param string $destinationXpath
     */
    public function dragTo($sourceXpath, $destinationXpath)
    {
        throw new UnsupportedDriverActionException('Dragging is not supported by %s', $this);
    }

    /**
     * Executes JS script.
     *
     * @param string $script
     */
    public function executeScript($script)
    {
        $script = json_encode($script);
        $this->server->evalJS("browser.evaluate({$script})");
    }

    /**
     * Evaluates JS script.
     *
     * @param string $script
     *
     * @return mixed
     */
    public function evaluateScript($script)
    {
        $script = json_encode($script);
        return $this->server->evalJS("browser.evaluate({$script})", 'json');
    }

    /**
     * Waits some time or until JS condition turns true.
     *
     * @param integer $time      time in milliseconds
     * @param string  $condition JS condition
     */
    public function wait($time, $condition)
    {
        $js = <<<JS
browser.waitFor = {$time};
browser.wait(function(window) {
    with(window) {
        return {$condition};
    }
}, function() {
    stream.end();
});
JS;
        $this->server->evalJS($js);
    }

    /**
     * Triggers (fires) a Zombie.js browser event.
     *
     * @param string $event The name of the event
     * @param string $xpath The xpath of the element to trigger this event
     *
     * @throws DriverException
     */
    protected function triggerBrowserEvent($event, $xpath)
    {
        if (!$ref = $this->getNativeRefForXPath($xpath)) {
            return;
        }

        $js = <<<JS
browser.fire("{$event}", {$ref}, function(err) {
  if (err) {
    stream.end(JSON.stringify(err.stack));
  } else {
    stream.end();
  }
});
JS;
        $out = $this->server->evalJS($js);
        if (!empty($out)) {
            throw new DriverException(sprintf("Error while processing event '%s'", $event));
        }
    }

    /**
     * Triggers a keyboard event
     *
     * @param string $name     The event name
     * @param string $xpath    The xpath of the element to trigger this event on
     * @param mixed  $char     could be either char ('b') or char-code (98)
     * @param string $modifier keyboard modifier (could be 'ctrl', 'alt', 'shift' or 'meta')
     */
    protected function triggerKeyEvent($name, $xpath, $char, $modifier)
    {
        if (!$ref = $this->getNativeRefForXPath($xpath)) {
            return;
        }

        $char = is_numeric($char) ? $char : ord($char);

        $isCtrlKeyArg  = ($modifier == 'ctrl')  ? "true" : "false";
        $isAltKeyArg   = ($modifier == 'alt')   ? "true" : "false";
        $isShiftKeyArg = ($modifier == 'shift') ? "true" : "false";
        $isMetaKeyArg  = ($modifier == 'meta')  ? "true" : "false";

        $js = <<<JS
var node = {$ref},
    window = browser.window,
    e = window.document.createEvent("UIEvents");
e.initUIEvent("{$name}", true, true, window, 1);
e.ctrlKey = {$isCtrlKeyArg};
e.altKey = {$isAltKeyArg};
e.shiftKey = {$isShiftKeyArg};
e.metaKey = {$isMetaKeyArg};
e.keyCode = {$char};
node.dispatchEvent(e);
stream.end();
JS;
        $this->server->evalJS($js);
    }

    /**
     * Tries to fetch a native reference to a node that might have been cached
     * by the server. If it can't be found, the method performs a search.
     *
     * Searching the native reference by the MD5 hash of its xpath feels kinda
     * hackish, but it'll boost performance and prevents a lot of boilerplate
     * Javascript code.
     *
     * @param string $xpath
     *
     * @return string|null
     */
    protected function getNativeRefForXPath($xpath)
    {
        $hash = md5($xpath);
        if (!isset($this->nativeRefs[$hash])) {
            $res = $this->find($xpath);
            if (1 > count($res)) {
                return null;
            }
        }

        return sprintf('pointers[%s]', $this->nativeRefs[$hash]);
    }
}

