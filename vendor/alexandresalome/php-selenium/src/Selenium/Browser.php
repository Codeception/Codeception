<?php
/*
 * This file is part of PHP Selenium Library.
 * (c) Alexandre Salomé <alexandre.salome@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Selenium;

/**
 * Browser class containing all methods of Selenium Server, with documentation.
 *
 * This class was generated, do not modify it.
 *
 * @author Alexandre Salomé <alexandre.salome@gmail.com>
 */
class Browser extends BaseBrowser
{
    /**
     * Clicks on a link, button, checkbox or radio button. If the click action
     * causes a new page to load (like a link usually does), call
     * waitForPageToLoad.
     * 
     * @param string $locator an element locator
     * 
     * @return Selenium\Browser Fluid interface
     */
    public function click($locator)
    {
        $this->driver->action("click", $locator);
        
        return $this;
    }

    /**
     * Double clicks on a link, button, checkbox or radio button. If the double
     * click action
     * causes a new page to load (like a link usually does), call
     * waitForPageToLoad.
     * 
     * @param string $locator an element locator
     * 
     * @return Selenium\Browser Fluid interface
     */
    public function doubleClick($locator)
    {
        $this->driver->action("doubleClick", $locator);
        
        return $this;
    }

    /**
     * Simulates opening the context menu for the specified element (as might
     * happen if the user "right-clicked" on the element).
     * 
     * @param string $locator an element locator
     * 
     * @return Selenium\Browser Fluid interface
     */
    public function contextMenu($locator)
    {
        $this->driver->action("contextMenu", $locator);
        
        return $this;
    }

    /**
     * Clicks on a link, button, checkbox or radio button. If the click action
     * causes a new page to load (like a link usually does), call
     * waitForPageToLoad.
     * 
     * @param string $locator an element locator
     * 
     * @param string $coordString specifies the x,y position (i.e. - 10,20) of
     * the mouse      event relative to the element returned by the locator.
     * 
     * @return Selenium\Browser Fluid interface
     */
    public function clickAt($locator, $coordString)
    {
        $this->driver->action("clickAt", $locator, $coordString);
        
        return $this;
    }

    /**
     * Doubleclicks on a link, button, checkbox or radio button. If the action
     * causes a new page to load (like a link usually does), call
     * waitForPageToLoad.
     * 
     * @param string $locator an element locator
     * 
     * @param string $coordString specifies the x,y position (i.e. - 10,20) of
     * the mouse      event relative to the element returned by the locator.
     * 
     * @return Selenium\Browser Fluid interface
     */
    public function doubleClickAt($locator, $coordString)
    {
        $this->driver->action("doubleClickAt", $locator, $coordString);
        
        return $this;
    }

    /**
     * Simulates opening the context menu for the specified element (as might
     * happen if the user "right-clicked" on the element).
     * 
     * @param string $locator an element locator
     * 
     * @param string $coordString specifies the x,y position (i.e. - 10,20) of
     * the mouse      event relative to the element returned by the locator.
     * 
     * @return Selenium\Browser Fluid interface
     */
    public function contextMenuAt($locator, $coordString)
    {
        $this->driver->action("contextMenuAt", $locator, $coordString);
        
        return $this;
    }

    /**
     * Explicitly simulate an event, to trigger the corresponding
     * "on<em>event</em>"
     * handler.
     * 
     * @param string $locator an <a href="#locators">element locator</a>
     * 
     * @param string $eventName the event name, e.g. "focus" or "blur"
     * 
     * @return Selenium\Browser Fluid interface
     */
    public function fireEvent($locator, $eventName)
    {
        $this->driver->action("fireEvent", $locator, $eventName);
        
        return $this;
    }

    /**
     * Move the focus to the specified element; for example, if the element is
     * an input field, move the cursor to that field.
     * 
     * @param string $locator an <a href="#locators">element locator</a>
     * 
     * @return Selenium\Browser Fluid interface
     */
    public function focus($locator)
    {
        $this->driver->action("focus", $locator);
        
        return $this;
    }

    /**
     * Simulates a user pressing and releasing a key.
     * 
     * @param string $locator an <a href="#locators">element locator</a>
     * 
     * @param string $keySequence Either be a string("\" followed by the numeric
     * keycode  of the key to be pressed, normally the ASCII value of that key),
     * or a single  character. For example: "w", "\119".
     * 
     * @return Selenium\Browser Fluid interface
     */
    public function keyPress($locator, $keySequence)
    {
        $this->driver->action("keyPress", $locator, $keySequence);
        
        return $this;
    }

    /**
     * Press the shift key and hold it down until doShiftUp() is called or a new
     * page is loaded.
     * 
     * @return Selenium\Browser Fluid interface
     */
    public function shiftKeyDown()
    {
        $this->driver->action("shiftKeyDown");
        
        return $this;
    }

    /**
     * Release the shift key.
     * 
     * @return Selenium\Browser Fluid interface
     */
    public function shiftKeyUp()
    {
        $this->driver->action("shiftKeyUp");
        
        return $this;
    }

    /**
     * Press the meta key and hold it down until doMetaUp() is called or a new
     * page is loaded.
     * 
     * @return Selenium\Browser Fluid interface
     */
    public function metaKeyDown()
    {
        $this->driver->action("metaKeyDown");
        
        return $this;
    }

    /**
     * Release the meta key.
     * 
     * @return Selenium\Browser Fluid interface
     */
    public function metaKeyUp()
    {
        $this->driver->action("metaKeyUp");
        
        return $this;
    }

    /**
     * Press the alt key and hold it down until doAltUp() is called or a new
     * page is loaded.
     * 
     * @return Selenium\Browser Fluid interface
     */
    public function altKeyDown()
    {
        $this->driver->action("altKeyDown");
        
        return $this;
    }

    /**
     * Release the alt key.
     * 
     * @return Selenium\Browser Fluid interface
     */
    public function altKeyUp()
    {
        $this->driver->action("altKeyUp");
        
        return $this;
    }

    /**
     * Press the control key and hold it down until doControlUp() is called or a
     * new page is loaded.
     * 
     * @return Selenium\Browser Fluid interface
     */
    public function controlKeyDown()
    {
        $this->driver->action("controlKeyDown");
        
        return $this;
    }

    /**
     * Release the control key.
     * 
     * @return Selenium\Browser Fluid interface
     */
    public function controlKeyUp()
    {
        $this->driver->action("controlKeyUp");
        
        return $this;
    }

    /**
     * Simulates a user pressing a key (without releasing it yet).
     * 
     * @param string $locator an <a href="#locators">element locator</a>
     * 
     * @param string $keySequence Either be a string("\" followed by the numeric
     * keycode  of the key to be pressed, normally the ASCII value of that key),
     * or a single  character. For example: "w", "\119".
     * 
     * @return Selenium\Browser Fluid interface
     */
    public function keyDown($locator, $keySequence)
    {
        $this->driver->action("keyDown", $locator, $keySequence);
        
        return $this;
    }

    /**
     * Simulates a user releasing a key.
     * 
     * @param string $locator an <a href="#locators">element locator</a>
     * 
     * @param string $keySequence Either be a string("\" followed by the numeric
     * keycode  of the key to be pressed, normally the ASCII value of that key),
     * or a single  character. For example: "w", "\119".
     * 
     * @return Selenium\Browser Fluid interface
     */
    public function keyUp($locator, $keySequence)
    {
        $this->driver->action("keyUp", $locator, $keySequence);
        
        return $this;
    }

    /**
     * Simulates a user hovering a mouse over the specified element.
     * 
     * @param string $locator an <a href="#locators">element locator</a>
     * 
     * @return Selenium\Browser Fluid interface
     */
    public function mouseOver($locator)
    {
        $this->driver->action("mouseOver", $locator);
        
        return $this;
    }

    /**
     * Simulates a user moving the mouse pointer away from the specified
     * element.
     * 
     * @param string $locator an <a href="#locators">element locator</a>
     * 
     * @return Selenium\Browser Fluid interface
     */
    public function mouseOut($locator)
    {
        $this->driver->action("mouseOut", $locator);
        
        return $this;
    }

    /**
     * Simulates a user pressing the left mouse button (without releasing it
     * yet) on
     * the specified element.
     * 
     * @param string $locator an <a href="#locators">element locator</a>
     * 
     * @return Selenium\Browser Fluid interface
     */
    public function mouseDown($locator)
    {
        $this->driver->action("mouseDown", $locator);
        
        return $this;
    }

    /**
     * Simulates a user pressing the right mouse button (without releasing it
     * yet) on
     * the specified element.
     * 
     * @param string $locator an <a href="#locators">element locator</a>
     * 
     * @return Selenium\Browser Fluid interface
     */
    public function mouseDownRight($locator)
    {
        $this->driver->action("mouseDownRight", $locator);
        
        return $this;
    }

    /**
     * Simulates a user pressing the left mouse button (without releasing it
     * yet) at
     * the specified location.
     * 
     * @param string $locator an <a href="#locators">element locator</a>
     * 
     * @param string $coordString specifies the x,y position (i.e. - 10,20) of
     * the mouse      event relative to the element returned by the locator.
     * 
     * @return Selenium\Browser Fluid interface
     */
    public function mouseDownAt($locator, $coordString)
    {
        $this->driver->action("mouseDownAt", $locator, $coordString);
        
        return $this;
    }

    /**
     * Simulates a user pressing the right mouse button (without releasing it
     * yet) at
     * the specified location.
     * 
     * @param string $locator an <a href="#locators">element locator</a>
     * 
     * @param string $coordString specifies the x,y position (i.e. - 10,20) of
     * the mouse      event relative to the element returned by the locator.
     * 
     * @return Selenium\Browser Fluid interface
     */
    public function mouseDownRightAt($locator, $coordString)
    {
        $this->driver->action("mouseDownRightAt", $locator, $coordString);
        
        return $this;
    }

    /**
     * Simulates the event that occurs when the user releases the mouse button
     * (i.e., stops
     * holding the button down) on the specified element.
     * 
     * @param string $locator an <a href="#locators">element locator</a>
     * 
     * @return Selenium\Browser Fluid interface
     */
    public function mouseUp($locator)
    {
        $this->driver->action("mouseUp", $locator);
        
        return $this;
    }

    /**
     * Simulates the event that occurs when the user releases the right mouse
     * button (i.e., stops
     * holding the button down) on the specified element.
     * 
     * @param string $locator an <a href="#locators">element locator</a>
     * 
     * @return Selenium\Browser Fluid interface
     */
    public function mouseUpRight($locator)
    {
        $this->driver->action("mouseUpRight", $locator);
        
        return $this;
    }

    /**
     * Simulates the event that occurs when the user releases the mouse button
     * (i.e., stops
     * holding the button down) at the specified location.
     * 
     * @param string $locator an <a href="#locators">element locator</a>
     * 
     * @param string $coordString specifies the x,y position (i.e. - 10,20) of
     * the mouse      event relative to the element returned by the locator.
     * 
     * @return Selenium\Browser Fluid interface
     */
    public function mouseUpAt($locator, $coordString)
    {
        $this->driver->action("mouseUpAt", $locator, $coordString);
        
        return $this;
    }

    /**
     * Simulates the event that occurs when the user releases the right mouse
     * button (i.e., stops
     * holding the button down) at the specified location.
     * 
     * @param string $locator an <a href="#locators">element locator</a>
     * 
     * @param string $coordString specifies the x,y position (i.e. - 10,20) of
     * the mouse      event relative to the element returned by the locator.
     * 
     * @return Selenium\Browser Fluid interface
     */
    public function mouseUpRightAt($locator, $coordString)
    {
        $this->driver->action("mouseUpRightAt", $locator, $coordString);
        
        return $this;
    }

    /**
     * Simulates a user pressing the mouse button (without releasing it yet) on
     * the specified element.
     * 
     * @param string $locator an <a href="#locators">element locator</a>
     * 
     * @return Selenium\Browser Fluid interface
     */
    public function mouseMove($locator)
    {
        $this->driver->action("mouseMove", $locator);
        
        return $this;
    }

    /**
     * Simulates a user pressing the mouse button (without releasing it yet) on
     * the specified element.
     * 
     * @param string $locator an <a href="#locators">element locator</a>
     * 
     * @param string $coordString specifies the x,y position (i.e. - 10,20) of
     * the mouse      event relative to the element returned by the locator.
     * 
     * @return Selenium\Browser Fluid interface
     */
    public function mouseMoveAt($locator, $coordString)
    {
        $this->driver->action("mouseMoveAt", $locator, $coordString);
        
        return $this;
    }

    /**
     * Sets the value of an input field, as though you typed it in.
     * 
     * <p>Can also be used to set the value of combo boxes, check boxes, etc. In
     * these cases,
     * value should be the value of the option selected, not the visible
     * text.</p>
     * 
     * @param string $locator an <a href="#locators">element locator</a>
     * 
     * @param string $value the value to type
     * 
     * @return Selenium\Browser Fluid interface
     */
    public function type($locator, $value)
    {
        $this->driver->action("type", $locator, $value);
        
        return $this;
    }

    /**
     * Simulates keystroke events on the specified element, as though you typed
     * the value key-by-key.
     * 
     * <p>This is a convenience method for calling keyDown, keyUp, keyPress for
     * every character in the specified string;
     * this is useful for dynamic UI widgets (like auto-completing combo boxes)
     * that require explicit key events.</p>
     * 
     * <p>Unlike the simple "type" command, which forces the specified value
     * into the page directly, this command
     * may or may not have any visible effect, even in cases where typing keys
     * would normally have a visible effect.
     * For example, if you use "typeKeys" on a form element, you may or may not
     * see the results of what you typed in
     * the field.</p>
     * <p>In some cases, you may need to use the simple "type" command to set
     * the value of the field and then the "typeKeys" command to
     * send the keystroke events corresponding to what you just typed.</p>
     * 
     * @param string $locator an <a href="#locators">element locator</a>
     * 
     * @param string $value the value to type
     * 
     * @return Selenium\Browser Fluid interface
     */
    public function typeKeys($locator, $value)
    {
        $this->driver->action("typeKeys", $locator, $value);
        
        return $this;
    }

    /**
     * Set execution speed (i.e., set the millisecond length of a delay which
     * will follow each selenium operation).  By default, there is no such
     * delay, i.e.,
     * the delay is 0 milliseconds.
     * 
     * @param string $value the number of milliseconds to pause after operation
     * 
     * @return Selenium\Browser Fluid interface
     */
    public function setSpeed($value)
    {
        $this->driver->action("setSpeed", $value);
        
        return $this;
    }

    /**
     * Get execution speed (i.e., get the millisecond length of the delay
     * following each selenium operation).  By default, there is no such delay,
     * i.e.,
     * the delay is 0 milliseconds.
     * 
     * See also setSpeed.
     * 
     * @return string the execution speed in milliseconds.
     */
    public function getSpeed()
    {
        return $this->driver->getString("getSpeed");
    }

    /**
     * Check a toggle-button (checkbox/radio)
     * 
     * @param string $locator an <a href="#locators">element locator</a>
     * 
     * @return Selenium\Browser Fluid interface
     */
    public function check($locator)
    {
        $this->driver->action("check", $locator);
        
        return $this;
    }

    /**
     * Uncheck a toggle-button (checkbox/radio)
     * 
     * @param string $locator an <a href="#locators">element locator</a>
     * 
     * @return Selenium\Browser Fluid interface
     */
    public function uncheck($locator)
    {
        $this->driver->action("uncheck", $locator);
        
        return $this;
    }

    /**
     * Select an option from a drop-down using an option locator.
     * 
     * <p>
     * Option locators provide different ways of specifying options of an HTML
     * Select element (e.g. for selecting a specific option, or for asserting
     * that the selected option satisfies a specification). There are several
     * forms of Select Option Locator.
     * </p>
     * <ul>
     * <li><strong>label</strong>=<em>labelPattern</em>:
     * matches options based on their labels, i.e. the visible text. (This
     * is the default.)
     * <ul class="first last simple">
     * <li>label=regexp:^[Oo]ther</li>
     * </ul>
     * </li>
     * <li><strong>value</strong>=<em>valuePattern</em>:
     * matches options based on their values.
     * <ul class="first last simple">
     * <li>value=other</li>
     * </ul>
     * 
     * 
     * </li>
     * <li><strong>id</strong>=<em>id</em>:
     * 
     * matches options based on their ids.
     * <ul class="first last simple">
     * <li>id=option1</li>
     * </ul>
     * </li>
     * <li><strong>index</strong>=<em>index</em>:
     * matches an option based on its index (offset from zero).
     * <ul class="first last simple">
     * 
     * <li>index=2</li>
     * </ul>
     * </li>
     * </ul>
     * <p>
     * If no option locator prefix is provided, the default behaviour is to
     * match on <strong>label</strong>.
     * </p>
     * 
     * @param string $selectLocator an <a href="#locators">element locator</a>
     * identifying a drop-down menu
     * 
     * @param string $optionLocator an option locator (a label by default)
     * 
     * @return Selenium\Browser Fluid interface
     */
    public function select($selectLocator, $optionLocator)
    {
        $this->driver->action("select", $selectLocator, $optionLocator);
        
        return $this;
    }

    /**
     * Add a selection to the set of selected options in a multi-select element
     * using an option locator.
     * 
     * @see #doSelect for details of option locators
     * 
     * @param string $locator an <a href="#locators">element locator</a>
     * identifying a multi-select box
     * 
     * @param string $optionLocator an option locator (a label by default)
     * 
     * @return Selenium\Browser Fluid interface
     */
    public function addSelection($locator, $optionLocator)
    {
        $this->driver->action("addSelection", $locator, $optionLocator);
        
        return $this;
    }

    /**
     * Remove a selection from the set of selected options in a multi-select
     * element using an option locator.
     * 
     * @see #doSelect for details of option locators
     * 
     * @param string $locator an <a href="#locators">element locator</a>
     * identifying a multi-select box
     * 
     * @param string $optionLocator an option locator (a label by default)
     * 
     * @return Selenium\Browser Fluid interface
     */
    public function removeSelection($locator, $optionLocator)
    {
        $this->driver->action("removeSelection", $locator, $optionLocator);
        
        return $this;
    }

    /**
     * Unselects all of the selected options in a multi-select element.
     * 
     * @param string $locator an <a href="#locators">element locator</a>
     * identifying a multi-select box
     * 
     * @return Selenium\Browser Fluid interface
     */
    public function removeAllSelections($locator)
    {
        $this->driver->action("removeAllSelections", $locator);
        
        return $this;
    }

    /**
     * Submit the specified form. This is particularly useful for forms without
     * submit buttons, e.g. single-input "Search" forms.
     * 
     * @param string $formLocator an <a href="#locators">element locator</a> for
     * the form you want to submit
     * 
     * @return Selenium\Browser Fluid interface
     */
    public function submit($formLocator)
    {
        $this->driver->action("submit", $formLocator);
        
        return $this;
    }

    /**
     * Opens an URL in the test frame. This accepts both relative and absolute
     * URLs.
     * 
     * The "open" command waits for the page to load before proceeding,
     * ie. the "AndWait" suffix is implicit.
     * 
     * <em>Note</em>: The URL must be on the same domain as the runner HTML
     * due to security restrictions in the browser (Same Origin Policy). If you
     * need to open an URL on another domain, use the Selenium Server to start a
     * new browser session on that domain.
     * 
     * @param string $url the URL to open; may be relative or absolute
     * 
     * @return Selenium\Browser Fluid interface
     */
    public function open($url)
    {
        $this->driver->action("open", $url);
        
        return $this;
    }

    /**
     * Opens a popup window (if a window with that ID isn't already open).
     * After opening the window, you'll need to select it using the selectWindow
     * command.
     * 
     * <p>This command can also be a useful workaround for bug SEL-339.  In some
     * cases, Selenium will be unable to intercept a call to window.open (if the
     * call occurs during or before the "onLoad" event, for example).
     * In those cases, you can force Selenium to notice the open window's name
     * by using the Selenium openWindow command, using
     * an empty (blank) url, like this: openWindow("", "myFunnyWindow").</p>
     * 
     * @param string $url the URL to open, which can be blank
     * 
     * @param string $windowID the JavaScript window ID of the window to select
     * 
     * @return Selenium\Browser Fluid interface
     */
    public function openWindow($url, $windowID)
    {
        $this->driver->action("openWindow", $url, $windowID);
        
        return $this;
    }

    /**
     * Selects a popup window using a window locator; once a popup window has
     * been selected, all
     * commands go to that window. To select the main window again, use null
     * as the target.
     * 
     * <p>
     * 
     * Window locators provide different ways of specifying the window object:
     * by title, by internal JavaScript "name," or by JavaScript variable.
     * </p>
     * <ul>
     * <li><strong>title</strong>=<em>My Special Window</em>:
     * Finds the window using the text that appears in the title bar.  Be
     * careful;
     * two windows can share the same title.  If that happens, this locator will
     * just pick one.
     * </li>
     * <li><strong>name</strong>=<em>myWindow</em>:
     * Finds the window using its internal JavaScript "name" property.  This is
     * the second 
     * parameter "windowName" passed to the JavaScript method window.open(url,
     * windowName, windowFeatures, replaceFlag)
     * (which Selenium intercepts).
     * </li>
     * <li><strong>var</strong>=<em>variableName</em>:
     * Some pop-up windows are unnamed (anonymous), but are associated with a
     * JavaScript variable name in the current
     * application window, e.g. "window.foo = window.open(url);".  In those
     * cases, you can open the window using
     * "var=foo".
     * </li>
     * </ul>
     * <p>
     * If no window locator prefix is provided, we'll try to guess what you mean
     * like this:</p>
     * <p>1.) if windowID is null, (or the string "null") then it is assumed the
     * user is referring to the original window instantiated by the
     * browser).</p>
     * <p>2.) if the value of the "windowID" parameter is a JavaScript variable
     * name in the current application window, then it is assumed
     * that this variable contains the return value from a call to the
     * JavaScript window.open() method.</p>
     * <p>3.) Otherwise, selenium looks in a hash it maintains that maps string
     * names to window "names".</p>
     * <p>4.) If <em>that</em> fails, we'll try looping over all of the known
     * windows to try to find the appropriate "title".
     * Since "title" is not necessarily unique, this may have unexpected
     * behavior.</p>
     * 
     * <p>If you're having trouble figuring out the name of a window that you
     * want to manipulate, look at the Selenium log messages
     * which identify the names of windows created via window.open (and
     * therefore intercepted by Selenium).  You will see messages
     * like the following for each window as it is opened:</p>
     * 
     * <p><code>debug: window.open call intercepted; window ID (which you can
     * use with selectWindow()) is "myNewWindow"</code></p>
     * 
     * <p>In some cases, Selenium will be unable to intercept a call to
     * window.open (if the call occurs during or before the "onLoad" event, for
     * example).
     * (This is bug SEL-339.)  In those cases, you can force Selenium to notice
     * the open window's name by using the Selenium openWindow command, using
     * an empty (blank) url, like this: openWindow("", "myFunnyWindow").</p>
     * 
     * @param string $windowID the JavaScript window ID of the window to select
     * 
     * @return Selenium\Browser Fluid interface
     */
    public function selectWindow($windowID)
    {
        $this->driver->action("selectWindow", $windowID);
        
        return $this;
    }

    /**
     * Simplifies the process of selecting a popup window (and does not offer
     * functionality beyond what <code>selectWindow()</code> already provides).
     * <ul>
     * <li>If <code>windowID</code> is either not specified, or specified as
     * "null", the first non-top window is selected. The top window is the one
     * that would be selected by <code>selectWindow()</code> without providing a
     * <code>windowID</code> . This should not be used when more than one popup
     * window is in play.</li>
     * <li>Otherwise, the window will be looked up considering
     * <code>windowID</code> as the following in order: 1) the "name" of the
     * window, as specified to <code>window.open()</code>; 2) a javascript
     * variable which is a reference to a window; and 3) the title of the
     * window. This is the same ordered lookup performed by
     * <code>selectWindow</code> .</li>
     * </ul>
     * 
     * @param string $windowID an identifier for the popup window, which can
     * take on a                  number of different meanings
     * 
     * @return Selenium\Browser Fluid interface
     */
    public function selectPopUp($windowID)
    {
        $this->driver->action("selectPopUp", $windowID);
        
        return $this;
    }

    /**
     * Selects the main window. Functionally equivalent to using
     * <code>selectWindow()</code> and specifying no value for
     * <code>windowID</code>.
     * 
     * @return Selenium\Browser Fluid interface
     */
    public function deselectPopUp()
    {
        $this->driver->action("deselectPopUp");
        
        return $this;
    }

    /**
     * Selects a frame within the current window.  (You may invoke this command
     * multiple times to select nested frames.)  To select the parent frame, use
     * "relative=parent" as a locator; to select the top frame, use
     * "relative=top".
     * You can also select a frame by its 0-based index number; select the first
     * frame with
     * "index=0", or the third frame with "index=2".
     * 
     * <p>You may also use a DOM expression to identify the frame you want
     * directly,
     * like this: <code>dom=frames["main"].frames["subframe"]</code></p>
     * 
     * @param string $locator an <a href="#locators">element locator</a>
     * identifying a frame or iframe
     * 
     * @return Selenium\Browser Fluid interface
     */
    public function selectFrame($locator)
    {
        $this->driver->action("selectFrame", $locator);
        
        return $this;
    }

    /**
     * Determine whether current/locator identify the frame containing this
     * running code.
     * 
     * <p>This is useful in proxy injection mode, where this code runs in every
     * browser frame and window, and sometimes the selenium server needs to
     * identify
     * the "current" frame.  In this case, when the test calls selectFrame, this
     * routine is called for each frame to figure out which one has been
     * selected.
     * The selected frame will return true, while all others will return
     * false.</p>
     * 
     * @param string $currentFrameString starting frame
     * 
     * @param string $target new frame (which might be relative to the current
     * one)
     * 
     * @return boolean true if the new frame is this code's window
     */
    public function getWhetherThisFrameMatchFrameExpression($currentFrameString, $target)
    {
        return $this->driver->getBoolean("getWhetherThisFrameMatchFrameExpression", $currentFrameString, $target);
    }

    /**
     * Determine whether currentWindowString plus target identify the window
     * containing this running code.
     * 
     * <p>This is useful in proxy injection mode, where this code runs in every
     * browser frame and window, and sometimes the selenium server needs to
     * identify
     * the "current" window.  In this case, when the test calls selectWindow,
     * this
     * routine is called for each window to figure out which one has been
     * selected.
     * The selected window will return true, while all others will return
     * false.</p>
     * 
     * @param string $currentWindowString starting window
     * 
     * @param string $target new window (which might be relative to the current
     * one, e.g., "_parent")
     * 
     * @return boolean true if the new window is this code's window
     */
    public function getWhetherThisWindowMatchWindowExpression($currentWindowString, $target)
    {
        return $this->driver->getBoolean("getWhetherThisWindowMatchWindowExpression", $currentWindowString, $target);
    }

    /**
     * Waits for a popup window to appear and load up.
     * 
     * @param string $windowID the JavaScript window "name" of the window that
     * will appear (not the text of the title bar)                 If
     * unspecified, or specified as "null", this command will                
     * wait for the first non-top window to appear (don't rely                
     * on this if you are working with multiple popups                
     * simultaneously).
     * 
     * @param string $timeout a timeout in milliseconds, after which the action
     * will return with an error.                If this value is not specified,
     * the default Selenium                timeout will be used. See the
     * setTimeout() command.
     * 
     * @return Selenium\Browser Fluid interface
     */
    public function waitForPopUp($windowID, $timeout)
    {
        $this->driver->action("waitForPopUp", $windowID, $timeout);
        
        return $this;
    }

    /**
     * <p>
     * By default, Selenium's overridden window.confirm() function will
     * return true, as if the user had manually clicked OK; after running
     * this command, the next call to confirm() will return false, as if
     * the user had clicked Cancel.  Selenium will then resume using the
     * default behavior for future confirmations, automatically returning 
     * true (OK) unless/until you explicitly call this command for each
     * confirmation.
     * </p>
     * <p>
     * Take note - every time a confirmation comes up, you must
     * consume it with a corresponding getConfirmation, or else
     * the next selenium operation will fail.
     * </p>
     * 
     * @return Selenium\Browser Fluid interface
     */
    public function chooseCancelOnNextConfirmation()
    {
        $this->driver->action("chooseCancelOnNextConfirmation");
        
        return $this;
    }

    /**
     * <p>
     * Undo the effect of calling chooseCancelOnNextConfirmation.  Note
     * that Selenium's overridden window.confirm() function will normally
     * automatically
     * return true, as if the user had manually clicked OK, so you shouldn't
     * need to use this command unless for some reason you need to change
     * your mind prior to the next confirmation.  After any confirmation,
     * Selenium will resume using the
     * default behavior for future confirmations, automatically returning 
     * true (OK) unless/until you explicitly call chooseCancelOnNextConfirmation
     * for each
     * confirmation.
     * </p>
     * <p>
     * Take note - every time a confirmation comes up, you must
     * consume it with a corresponding getConfirmation, or else
     * the next selenium operation will fail.
     * </p>
     * 
     * @return Selenium\Browser Fluid interface
     */
    public function chooseOkOnNextConfirmation()
    {
        $this->driver->action("chooseOkOnNextConfirmation");
        
        return $this;
    }

    /**
     * Instructs Selenium to return the specified answer string in response to
     * the next JavaScript prompt [window.prompt()].
     * 
     * @param string $answer the answer to give in response to the prompt pop-up
     * 
     * @return Selenium\Browser Fluid interface
     */
    public function answerOnNextPrompt($answer)
    {
        $this->driver->action("answerOnNextPrompt", $answer);
        
        return $this;
    }

    /**
     * Simulates the user clicking the "back" button on their browser.
     * 
     * @return Selenium\Browser Fluid interface
     */
    public function goBack()
    {
        $this->driver->action("goBack");
        
        return $this;
    }

    /**
     * Simulates the user clicking the "Refresh" button on their browser.
     * 
     * @return Selenium\Browser Fluid interface
     */
    public function refresh()
    {
        $this->driver->action("refresh");
        
        return $this;
    }

    /**
     * Simulates the user clicking the "close" button in the titlebar of a popup
     * window or tab.
     * 
     * @return Selenium\Browser Fluid interface
     */
    public function close()
    {
        $this->driver->action("close");
        
        return $this;
    }

    /**
     * Has an alert occurred?
     * 
     * <p>
     * This function never throws an exception
     * </p>
     * 
     * @return boolean true if there is an alert
     */
    public function isAlertPresent()
    {
        return $this->driver->getBoolean("isAlertPresent");
    }

    /**
     * Has a prompt occurred?
     * 
     * <p>
     * This function never throws an exception
     * </p>
     * 
     * @return boolean true if there is a pending prompt
     */
    public function isPromptPresent()
    {
        return $this->driver->getBoolean("isPromptPresent");
    }

    /**
     * Has confirm() been called?
     * 
     * <p>
     * This function never throws an exception
     * </p>
     * 
     * @return boolean true if there is a pending confirmation
     */
    public function isConfirmationPresent()
    {
        return $this->driver->getBoolean("isConfirmationPresent");
    }

    /**
     * Retrieves the message of a JavaScript alert generated during the previous
     * action, or fail if there were no alerts.
     * 
     * <p>Getting an alert has the same effect as manually clicking OK. If an
     * alert is generated but you do not consume it with getAlert, the next
     * Selenium action
     * will fail.</p>
     * 
     * <p>Under Selenium, JavaScript alerts will NOT pop up a visible alert
     * dialog.</p>
     * 
     * <p>Selenium does NOT support JavaScript alerts that are generated in a
     * page's onload() event handler. In this case a visible dialog WILL be
     * generated and Selenium will hang until someone manually clicks OK.</p>
     * 
     * @return string The message of the most recent JavaScript alert
     */
    public function getAlert()
    {
        return $this->driver->getString("getAlert");
    }

    /**
     * Retrieves the message of a JavaScript confirmation dialog generated
     * during
     * the previous action.
     * 
     * <p>
     * By default, the confirm function will return true, having the same effect
     * as manually clicking OK. This can be changed by prior execution of the
     * chooseCancelOnNextConfirmation command. 
     * </p>
     * <p>
     * If an confirmation is generated but you do not consume it with
     * getConfirmation,
     * the next Selenium action will fail.
     * </p>
     * 
     * <p>
     * NOTE: under Selenium, JavaScript confirmations will NOT pop up a visible
     * dialog.
     * </p>
     * 
     * <p>
     * NOTE: Selenium does NOT support JavaScript confirmations that are
     * generated in a page's onload() event handler. In this case a visible
     * dialog WILL be generated and Selenium will hang until you manually click
     * OK.
     * </p>
     * 
     * @return string the message of the most recent JavaScript confirmation
     * dialog
     */
    public function getConfirmation()
    {
        return $this->driver->getString("getConfirmation");
    }

    /**
     * Retrieves the message of a JavaScript question prompt dialog generated
     * during
     * the previous action.
     * 
     * <p>Successful handling of the prompt requires prior execution of the
     * answerOnNextPrompt command. If a prompt is generated but you
     * do not get/verify it, the next Selenium action will fail.</p>
     * 
     * <p>NOTE: under Selenium, JavaScript prompts will NOT pop up a visible
     * dialog.</p>
     * 
     * <p>NOTE: Selenium does NOT support JavaScript prompts that are generated
     * in a
     * page's onload() event handler. In this case a visible dialog WILL be
     * generated and Selenium will hang until someone manually clicks OK.</p>
     * 
     * @return string the message of the most recent JavaScript question prompt
     */
    public function getPrompt()
    {
        return $this->driver->getString("getPrompt");
    }

    /**
     * Gets the absolute URL of the current page.
     * 
     * @return string the absolute URL of the current page
     */
    public function getLocation()
    {
        return $this->driver->getString("getLocation");
    }

    /**
     * Gets the title of the current page.
     * 
     * @return string the title of the current page
     */
    public function getTitle()
    {
        return $this->driver->getString("getTitle");
    }

    /**
     * Gets the entire text of the page.
     * 
     * @return string the entire text of the page
     */
    public function getBodyText()
    {
        return $this->driver->getString("getBodyText");
    }

    /**
     * Gets the (whitespace-trimmed) value of an input field (or anything else
     * with a value parameter).
     * For checkbox/radio elements, the value will be "on" or "off" depending on
     * whether the element is checked or not.
     * 
     * @param string $locator an <a href="#locators">element locator</a>
     * 
     * @return string the element value, or "on/off" for checkbox/radio elements
     */
    public function getValue($locator)
    {
        return $this->driver->getString("getValue", $locator);
    }

    /**
     * Gets the text of an element. This works for any element that contains
     * text. This command uses either the textContent (Mozilla-like browsers) or
     * the innerText (IE-like browsers) of the element, which is the rendered
     * text shown to the user.
     * 
     * @param string $locator an <a href="#locators">element locator</a>
     * 
     * @return string the text of the element
     */
    public function getText($locator)
    {
        return $this->driver->getString("getText", $locator);
    }

    /**
     * Briefly changes the backgroundColor of the specified element yellow. 
     * Useful for debugging.
     * 
     * @param string $locator an <a href="#locators">element locator</a>
     * 
     * @return Selenium\Browser Fluid interface
     */
    public function highlight($locator)
    {
        $this->driver->action("highlight", $locator);
        
        return $this;
    }

    /**
     * Gets the result of evaluating the specified JavaScript snippet.  The
     * snippet may
     * have multiple lines, but only the result of the last line will be
     * returned.
     * 
     * <p>Note that, by default, the snippet will run in the context of the
     * "selenium"
     * object itself, so <code>this</code> will refer to the Selenium object. 
     * Use <code>window</code> to
     * refer to the window of your application, e.g.
     * <code>window.document.getElementById('foo')</code></p>
     * 
     * <p>If you need to use
     * a locator to refer to a single element in your application page, you can
     * use <code>this.browserbot.findElement("id=foo")</code> where "id=foo" is
     * your locator.</p>
     * 
     * @param string $script the JavaScript snippet to run
     * 
     * @return string the results of evaluating the snippet
     */
    public function getEval($script)
    {
        return $this->driver->getString("getEval", $script);
    }

    /**
     * Gets whether a toggle-button (checkbox/radio) is checked.  Fails if the
     * specified element doesn't exist or isn't a toggle-button.
     * 
     * @param string $locator an <a href="#locators">element locator</a>
     * pointing to a checkbox or radio button
     * 
     * @return boolean true if the checkbox is checked, false otherwise
     */
    public function isChecked($locator)
    {
        return $this->driver->getBoolean("isChecked", $locator);
    }

    /**
     * Gets the text from a cell of a table. The cellAddress syntax
     * tableLocator.row.column, where row and column start at 0.
     * 
     * @param string $tableCellAddress a cell address, e.g. "foo.1.4"
     * 
     * @return string the text from the specified cell
     */
    public function getTable($tableCellAddress)
    {
        return $this->driver->getString("getTable", $tableCellAddress);
    }

    /**
     * Gets all option labels (visible text) for selected options in the
     * specified select or multi-select element.
     * 
     * @param string $selectLocator an <a href="#locators">element locator</a>
     * identifying a drop-down menu
     * 
     * @return string[] an array of all selected option labels in the specified
     * select drop-down
     */
    public function getSelectedLabels($selectLocator)
    {
        return $this->driver->getStringArray("getSelectedLabels", $selectLocator);
    }

    /**
     * Gets option label (visible text) for selected option in the specified
     * select element.
     * 
     * @param string $selectLocator an <a href="#locators">element locator</a>
     * identifying a drop-down menu
     * 
     * @return string the selected option label in the specified select
     * drop-down
     */
    public function getSelectedLabel($selectLocator)
    {
        return $this->driver->getString("getSelectedLabel", $selectLocator);
    }

    /**
     * Gets all option values (value attributes) for selected options in the
     * specified select or multi-select element.
     * 
     * @param string $selectLocator an <a href="#locators">element locator</a>
     * identifying a drop-down menu
     * 
     * @return string[] an array of all selected option values in the specified
     * select drop-down
     */
    public function getSelectedValues($selectLocator)
    {
        return $this->driver->getStringArray("getSelectedValues", $selectLocator);
    }

    /**
     * Gets option value (value attribute) for selected option in the specified
     * select element.
     * 
     * @param string $selectLocator an <a href="#locators">element locator</a>
     * identifying a drop-down menu
     * 
     * @return string the selected option value in the specified select
     * drop-down
     */
    public function getSelectedValue($selectLocator)
    {
        return $this->driver->getString("getSelectedValue", $selectLocator);
    }

    /**
     * Gets all option indexes (option number, starting at 0) for selected
     * options in the specified select or multi-select element.
     * 
     * @param string $selectLocator an <a href="#locators">element locator</a>
     * identifying a drop-down menu
     * 
     * @return string[] an array of all selected option indexes in the specified
     * select drop-down
     */
    public function getSelectedIndexes($selectLocator)
    {
        return $this->driver->getStringArray("getSelectedIndexes", $selectLocator);
    }

    /**
     * Gets option index (option number, starting at 0) for selected option in
     * the specified select element.
     * 
     * @param string $selectLocator an <a href="#locators">element locator</a>
     * identifying a drop-down menu
     * 
     * @return string the selected option index in the specified select
     * drop-down
     */
    public function getSelectedIndex($selectLocator)
    {
        return $this->driver->getString("getSelectedIndex", $selectLocator);
    }

    /**
     * Gets all option element IDs for selected options in the specified select
     * or multi-select element.
     * 
     * @param string $selectLocator an <a href="#locators">element locator</a>
     * identifying a drop-down menu
     * 
     * @return string[] an array of all selected option IDs in the specified
     * select drop-down
     */
    public function getSelectedIds($selectLocator)
    {
        return $this->driver->getStringArray("getSelectedIds", $selectLocator);
    }

    /**
     * Gets option element ID for selected option in the specified select
     * element.
     * 
     * @param string $selectLocator an <a href="#locators">element locator</a>
     * identifying a drop-down menu
     * 
     * @return string the selected option ID in the specified select drop-down
     */
    public function getSelectedId($selectLocator)
    {
        return $this->driver->getString("getSelectedId", $selectLocator);
    }

    /**
     * Determines whether some option in a drop-down menu is selected.
     * 
     * @param string $selectLocator an <a href="#locators">element locator</a>
     * identifying a drop-down menu
     * 
     * @return boolean true if some option has been selected, false otherwise
     */
    public function isSomethingSelected($selectLocator)
    {
        return $this->driver->getBoolean("isSomethingSelected", $selectLocator);
    }

    /**
     * Gets all option labels in the specified select drop-down.
     * 
     * @param string $selectLocator an <a href="#locators">element locator</a>
     * identifying a drop-down menu
     * 
     * @return string[] an array of all option labels in the specified select
     * drop-down
     */
    public function getSelectOptions($selectLocator)
    {
        return $this->driver->getStringArray("getSelectOptions", $selectLocator);
    }

    /**
     * Gets the value of an element attribute. The value of the attribute may
     * differ across browsers (this is the case for the "style" attribute, for
     * example).
     * 
     * @param string $attributeLocator an element locator followed by an @ sign
     * and then the name of the attribute, e.g. "foo@bar"
     * 
     * @return string the value of the specified attribute
     */
    public function getAttribute($attributeLocator)
    {
        return $this->driver->getString("getAttribute", $attributeLocator);
    }

    /**
     * Verifies that the specified text pattern appears somewhere on the
     * rendered page shown to the user.
     * 
     * @param string $pattern a <a href="#patterns">pattern</a> to match with
     * the text of the page
     * 
     * @return boolean true if the pattern matches the text, false otherwise
     */
    public function isTextPresent($pattern)
    {
        return $this->driver->getBoolean("isTextPresent", $pattern);
    }

    /**
     * Verifies that the specified element is somewhere on the page.
     * 
     * @param string $locator an <a href="#locators">element locator</a>
     * 
     * @return boolean true if the element is present, false otherwise
     */
    public function isElementPresent($locator)
    {
        return $this->driver->getBoolean("isElementPresent", $locator);
    }

    /**
     * Determines if the specified element is visible. An
     * element can be rendered invisible by setting the CSS "visibility"
     * property to "hidden", or the "display" property to "none", either for the
     * element itself or one if its ancestors.  This method will fail if
     * the element is not present.
     * 
     * @param string $locator an <a href="#locators">element locator</a>
     * 
     * @return boolean true if the specified element is visible, false otherwise
     */
    public function isVisible($locator)
    {
        return $this->driver->getBoolean("isVisible", $locator);
    }

    /**
     * Determines whether the specified input element is editable, ie hasn't
     * been disabled.
     * This method will fail if the specified element isn't an input element.
     * 
     * @param string $locator an <a href="#locators">element locator</a>
     * 
     * @return boolean true if the input element is editable, false otherwise
     */
    public function isEditable($locator)
    {
        return $this->driver->getBoolean("isEditable", $locator);
    }

    /**
     * Returns the IDs of all buttons on the page.
     * 
     * <p>If a given button has no ID, it will appear as "" in this array.</p>
     * 
     * @return string[] the IDs of all buttons on the page
     */
    public function getAllButtons()
    {
        return $this->driver->getStringArray("getAllButtons");
    }

    /**
     * Returns the IDs of all links on the page.
     * 
     * <p>If a given link has no ID, it will appear as "" in this array.</p>
     * 
     * @return string[] the IDs of all links on the page
     */
    public function getAllLinks()
    {
        return $this->driver->getStringArray("getAllLinks");
    }

    /**
     * Returns the IDs of all input fields on the page.
     * 
     * <p>If a given field has no ID, it will appear as "" in this array.</p>
     * 
     * @return string[] the IDs of all field on the page
     */
    public function getAllFields()
    {
        return $this->driver->getStringArray("getAllFields");
    }

    /**
     * Returns an array of JavaScript property values from all known windows
     * having one.
     * 
     * @param string $attributeName name of an attribute on the windows
     * 
     * @return string[] the set of values of this attribute from all known
     * windows.
     */
    public function getAttributeFromAllWindows($attributeName)
    {
        return $this->driver->getStringArray("getAttributeFromAllWindows", $attributeName);
    }

    /**
     * deprecated - use dragAndDrop instead
     * 
     * @param string $locator an element locator
     * 
     * @param string $movementsString offset in pixels from the current location
     * to which the element should be moved, e.g., "+70,-300"
     * 
     * @return Selenium\Browser Fluid interface
     */
    public function dragdrop($locator, $movementsString)
    {
        $this->driver->action("dragdrop", $locator, $movementsString);
        
        return $this;
    }

    /**
     * Configure the number of pixels between "mousemove" events during
     * dragAndDrop commands (default=10).
     * <p>Setting this value to 0 means that we'll send a "mousemove" event to
     * every single pixel
     * in between the start location and the end location; that can be very
     * slow, and may
     * cause some browsers to force the JavaScript to timeout.</p>
     * 
     * <p>If the mouse speed is greater than the distance between the two
     * dragged objects, we'll
     * just send one "mousemove" at the start location and then one final one at
     * the end location.</p>
     * 
     * @param string $pixels the number of pixels between "mousemove" events
     * 
     * @return Selenium\Browser Fluid interface
     */
    public function setMouseSpeed($pixels)
    {
        $this->driver->action("setMouseSpeed", $pixels);
        
        return $this;
    }

    /**
     * Returns the number of pixels between "mousemove" events during
     * dragAndDrop commands (default=10).
     * 
     * @return number the number of pixels between "mousemove" events during
     * dragAndDrop commands (default=10)
     */
    public function getMouseSpeed()
    {
        return $this->driver->getNumber("getMouseSpeed");
    }

    /**
     * Drags an element a certain distance and then drops it
     * 
     * @param string $locator an element locator
     * 
     * @param string $movementsString offset in pixels from the current location
     * to which the element should be moved, e.g., "+70,-300"
     * 
     * @return Selenium\Browser Fluid interface
     */
    public function dragAndDrop($locator, $movementsString)
    {
        $this->driver->action("dragAndDrop", $locator, $movementsString);
        
        return $this;
    }

    /**
     * Drags an element and drops it on another element
     * 
     * @param string $locatorOfObjectToBeDragged an element to be dragged
     * 
     * @param string $locatorOfDragDestinationObject an element whose location
     * (i.e., whose center-most pixel) will be the point where
     * locatorOfObjectToBeDragged  is dropped
     * 
     * @return Selenium\Browser Fluid interface
     */
    public function dragAndDropToObject($locatorOfObjectToBeDragged, $locatorOfDragDestinationObject)
    {
        $this->driver->action("dragAndDropToObject", $locatorOfObjectToBeDragged, $locatorOfDragDestinationObject);
        
        return $this;
    }

    /**
     * Gives focus to the currently selected window
     * 
     * @return Selenium\Browser Fluid interface
     */
    public function windowFocus()
    {
        $this->driver->action("windowFocus");
        
        return $this;
    }

    /**
     * Resize currently selected window to take up the entire screen
     * 
     * @return Selenium\Browser Fluid interface
     */
    public function windowMaximize()
    {
        $this->driver->action("windowMaximize");
        
        return $this;
    }

    /**
     * Returns the IDs of all windows that the browser knows about in an array.
     * 
     * @return string[] Array of identifiers of all windows that the browser
     * knows about.
     */
    public function getAllWindowIds()
    {
        return $this->driver->getStringArray("getAllWindowIds");
    }

    /**
     * Returns the names of all windows that the browser knows about in an
     * array.
     * 
     * @return string[] Array of names of all windows that the browser knows
     * about.
     */
    public function getAllWindowNames()
    {
        return $this->driver->getStringArray("getAllWindowNames");
    }

    /**
     * Returns the titles of all windows that the browser knows about in an
     * array.
     * 
     * @return string[] Array of titles of all windows that the browser knows
     * about.
     */
    public function getAllWindowTitles()
    {
        return $this->driver->getStringArray("getAllWindowTitles");
    }

    /**
     * Returns the entire HTML source between the opening and
     * closing "html" tags.
     * 
     * @return string the entire HTML source
     */
    public function getHtmlSource()
    {
        return $this->driver->getString("getHtmlSource");
    }

    /**
     * Moves the text cursor to the specified position in the given input
     * element or textarea.
     * This method will fail if the specified element isn't an input element or
     * textarea.
     * 
     * @param string $locator an <a href="#locators">element locator</a>
     * pointing to an input element or textarea
     * 
     * @param string $position the numerical position of the cursor in the
     * field; position should be 0 to move the position to the beginning of the
     * field.  You can also set the cursor to -1 to move it to the end of the
     * field.
     * 
     * @return Selenium\Browser Fluid interface
     */
    public function setCursorPosition($locator, $position)
    {
        $this->driver->action("setCursorPosition", $locator, $position);
        
        return $this;
    }

    /**
     * Get the relative index of an element to its parent (starting from 0). The
     * comment node and empty text node
     * will be ignored.
     * 
     * @param string $locator an <a href="#locators">element locator</a>
     * pointing to an element
     * 
     * @return number of relative index of the element to its parent (starting
     * from 0)
     */
    public function getElementIndex($locator)
    {
        return $this->driver->getNumber("getElementIndex", $locator);
    }

    /**
     * Check if these two elements have same parent and are ordered siblings in
     * the DOM. Two same elements will
     * not be considered ordered.
     * 
     * @param string $locator1 an <a href="#locators">element locator</a>
     * pointing to the first element
     * 
     * @param string $locator2 an <a href="#locators">element locator</a>
     * pointing to the second element
     * 
     * @return boolean true if element1 is the previous sibling of element2,
     * false otherwise
     */
    public function isOrdered($locator1, $locator2)
    {
        return $this->driver->getBoolean("isOrdered", $locator1, $locator2);
    }

    /**
     * Retrieves the horizontal position of an element
     * 
     * @param string $locator an <a href="#locators">element locator</a>
     * pointing to an element OR an element itself
     * 
     * @return number of pixels from the edge of the frame.
     */
    public function getElementPositionLeft($locator)
    {
        return $this->driver->getNumber("getElementPositionLeft", $locator);
    }

    /**
     * Retrieves the vertical position of an element
     * 
     * @param string $locator an <a href="#locators">element locator</a>
     * pointing to an element OR an element itself
     * 
     * @return number of pixels from the edge of the frame.
     */
    public function getElementPositionTop($locator)
    {
        return $this->driver->getNumber("getElementPositionTop", $locator);
    }

    /**
     * Retrieves the width of an element
     * 
     * @param string $locator an <a href="#locators">element locator</a>
     * pointing to an element
     * 
     * @return number width of an element in pixels
     */
    public function getElementWidth($locator)
    {
        return $this->driver->getNumber("getElementWidth", $locator);
    }

    /**
     * Retrieves the height of an element
     * 
     * @param string $locator an <a href="#locators">element locator</a>
     * pointing to an element
     * 
     * @return number height of an element in pixels
     */
    public function getElementHeight($locator)
    {
        return $this->driver->getNumber("getElementHeight", $locator);
    }

    /**
     * Retrieves the text cursor position in the given input element or
     * textarea; beware, this may not work perfectly on all browsers.
     * 
     * <p>Specifically, if the cursor/selection has been cleared by JavaScript,
     * this command will tend to
     * return the position of the last location of the cursor, even though the
     * cursor is now gone from the page.  This is filed as <a
     * href="http://jira.openqa.org/browse/SEL-243">SEL-243</a>.</p>
     * This method will fail if the specified element isn't an input element or
     * textarea, or there is no cursor in the element.
     * 
     * @param string $locator an <a href="#locators">element locator</a>
     * pointing to an input element or textarea
     * 
     * @return number the numerical position of the cursor in the field
     */
    public function getCursorPosition($locator)
    {
        return $this->driver->getNumber("getCursorPosition", $locator);
    }

    /**
     * Returns the specified expression.
     * 
     * <p>This is useful because of JavaScript preprocessing.
     * It is used to generate commands like assertExpression and
     * waitForExpression.</p>
     * 
     * @param string $expression the value to return
     * 
     * @return string the value passed in
     */
    public function getExpression($expression)
    {
        return $this->driver->getString("getExpression", $expression);
    }

    /**
     * Returns the number of nodes that match the specified xpath, eg. "//table"
     * would give
     * the number of tables.
     * 
     * @param string $xpath the xpath expression to evaluate. do NOT wrap this
     * expression in a 'count()' function; we will do that for you.
     * 
     * @return number the number of nodes that match the specified xpath
     */
    public function getXpathCount($xpath)
    {
        return $this->driver->getNumber("getXpathCount", $xpath);
    }

    /**
     * Temporarily sets the "id" attribute of the specified element, so you can
     * locate it in the future
     * using its ID rather than a slow/complicated XPath.  This ID will
     * disappear once the page is
     * reloaded.
     * 
     * @param string $locator an <a href="#locators">element locator</a>
     * pointing to an element
     * 
     * @param string $identifier a string to be used as the ID of the specified
     * element
     * 
     * @return Selenium\Browser Fluid interface
     */
    public function assignId($locator, $identifier)
    {
        $this->driver->action("assignId", $locator, $identifier);
        
        return $this;
    }

    /**
     * Specifies whether Selenium should use the native in-browser
     * implementation
     * of XPath (if any native version is available); if you pass "false" to
     * this function, we will always use our pure-JavaScript xpath library.
     * Using the pure-JS xpath library can improve the consistency of xpath
     * element locators between different browser vendors, but the pure-JS
     * version is much slower than the native implementations.
     * 
     * @param string $allow boolean, true means we'll prefer to use native
     * XPath; false means we'll only use JS XPath
     * 
     * @return Selenium\Browser Fluid interface
     */
    public function allowNativeXpath($allow)
    {
        $this->driver->action("allowNativeXpath", $allow);
        
        return $this;
    }

    /**
     * Specifies whether Selenium will ignore xpath attributes that have no
     * value, i.e. are the empty string, when using the non-native xpath
     * evaluation engine. You'd want to do this for performance reasons in IE.
     * However, this could break certain xpaths, for example an xpath that looks
     * for an attribute whose value is NOT the empty string.
     * 
     * The hope is that such xpaths are relatively rare, but the user should
     * have the option of using them. Note that this only influences xpath
     * evaluation when using the ajaxslt engine (i.e. not "javascript-xpath").
     * 
     * @param string $ignore boolean, true means we'll ignore attributes without
     * value                        at the expense of xpath "correctness"; false
     * means                        we'll sacrifice speed for correctness.
     * 
     * @return Selenium\Browser Fluid interface
     */
    public function ignoreAttributesWithoutValue($ignore)
    {
        $this->driver->action("ignoreAttributesWithoutValue", $ignore);
        
        return $this;
    }

    /**
     * Runs the specified JavaScript snippet repeatedly until it evaluates to
     * "true".
     * The snippet may have multiple lines, but only the result of the last line
     * will be considered.
     * 
     * <p>Note that, by default, the snippet will be run in the runner's test
     * window, not in the window
     * of your application.  To get the window of your application, you can use
     * the JavaScript snippet
     * <code>selenium.browserbot.getCurrentWindow()</code>, and then
     * run your JavaScript in there</p>
     * 
     * @param string $script the JavaScript snippet to run
     * 
     * @param string $timeout a timeout in milliseconds, after which this
     * command will return with an error
     * 
     * @return Selenium\Browser Fluid interface
     */
    public function waitForCondition($script, $timeout)
    {
        $this->driver->action("waitForCondition", $script, $timeout);
        
        return $this;
    }

    /**
     * Specifies the amount of time that Selenium will wait for actions to
     * complete.
     * 
     * <p>Actions that require waiting include "open" and the "waitFor*"
     * actions.</p>
     * The default timeout is 30 seconds.
     * 
     * @param string $timeout a timeout in milliseconds, after which the action
     * will return with an error
     * 
     * @return Selenium\Browser Fluid interface
     */
    public function setTimeout($timeout)
    {
        $this->driver->action("setTimeout", $timeout);
        
        return $this;
    }

    /**
     * Waits for a new page to load.
     * 
     * <p>You can use this command instead of the "AndWait" suffixes,
     * "clickAndWait", "selectAndWait", "typeAndWait" etc.
     * (which are only available in the JS API).</p>
     * 
     * <p>Selenium constantly keeps track of new pages loading, and sets a
     * "newPageLoaded"
     * flag when it first notices a page load.  Running any other Selenium
     * command after
     * turns the flag to false.  Hence, if you want to wait for a page to load,
     * you must
     * wait immediately after a Selenium command that caused a page-load.</p>
     * 
     * @param string $timeout a timeout in milliseconds, after which this
     * command will return with an error
     * 
     * @return Selenium\Browser Fluid interface
     */
    public function waitForPageToLoad($timeout)
    {
        $this->driver->action("waitForPageToLoad", $timeout);
        
        return $this;
    }

    /**
     * Waits for a new frame to load.
     * 
     * <p>Selenium constantly keeps track of new pages and frames loading, 
     * and sets a "newPageLoaded" flag when it first notices a page load.</p>
     * 
     * See waitForPageToLoad for more information.
     * 
     * @param string $frameAddress FrameAddress from the server side
     * 
     * @param string $timeout a timeout in milliseconds, after which this
     * command will return with an error
     * 
     * @return Selenium\Browser Fluid interface
     */
    public function waitForFrameToLoad($frameAddress, $timeout)
    {
        $this->driver->action("waitForFrameToLoad", $frameAddress, $timeout);
        
        return $this;
    }

    /**
     * Return all cookies of the current page under test.
     * 
     * @return string all cookies of the current page under test
     */
    public function getCookie()
    {
        return $this->driver->getString("getCookie");
    }

    /**
     * Returns the value of the cookie with the specified name, or throws an
     * error if the cookie is not present.
     * 
     * @param string $name the name of the cookie
     * 
     * @return string the value of the cookie
     */
    public function getCookieByName($name)
    {
        return $this->driver->getString("getCookieByName", $name);
    }

    /**
     * Returns true if a cookie with the specified name is present, or false
     * otherwise.
     * 
     * @param string $name the name of the cookie
     * 
     * @return boolean true if a cookie with the specified name is present, or
     * false otherwise.
     */
    public function isCookiePresent($name)
    {
        return $this->driver->getBoolean("isCookiePresent", $name);
    }

    /**
     * Create a new cookie whose path and domain are same with those of current
     * page
     * under test, unless you specified a path for this cookie explicitly.
     * 
     * @param string $nameValuePair name and value of the cookie in a format
     * "name=value"
     * 
     * @param string $optionsString options for the cookie. Currently supported
     * options include 'path', 'max_age' and 'domain'.      the optionsString's
     * format is "path=/path/, max_age=60, domain=.foo.com". The order of
     * options are irrelevant, the unit      of the value of 'max_age' is
     * second.  Note that specifying a domain that isn't a subset of the current
     * domain will      usually fail.
     * 
     * @return Selenium\Browser Fluid interface
     */
    public function createCookie($nameValuePair, $optionsString)
    {
        $this->driver->action("createCookie", $nameValuePair, $optionsString);
        
        return $this;
    }

    /**
     * Delete a named cookie with specified path and domain.  Be careful; to
     * delete a cookie, you
     * need to delete it using the exact same path and domain that were used to
     * create the cookie.
     * If the path is wrong, or the domain is wrong, the cookie simply won't be
     * deleted.  Also
     * note that specifying a domain that isn't a subset of the current domain
     * will usually fail.
     * 
     * Since there's no way to discover at runtime the original path and domain
     * of a given cookie,
     * we've added an option called 'recurse' to try all sub-domains of the
     * current domain with
     * all paths that are a subset of the current path.  Beware; this option can
     * be slow.  In
     * big-O notation, it operates in O(n*m) time, where n is the number of dots
     * in the domain
     * name and m is the number of slashes in the path.
     * 
     * @param string $name the name of the cookie to be deleted
     * 
     * @param string $optionsString options for the cookie. Currently supported
     * options include 'path', 'domain'      and 'recurse.' The optionsString's
     * format is "path=/path/, domain=.foo.com, recurse=true".      The order of
     * options are irrelevant. Note that specifying a domain that isn't a subset
     * of      the current domain will usually fail.
     * 
     * @return Selenium\Browser Fluid interface
     */
    public function deleteCookie($name, $optionsString)
    {
        $this->driver->action("deleteCookie", $name, $optionsString);
        
        return $this;
    }

    /**
     * Calls deleteCookie with recurse=true on all cookies visible to the
     * current page.
     * As noted on the documentation for deleteCookie, recurse=true can be much
     * slower
     * than simply deleting the cookies using a known domain/path.
     * 
     * @return Selenium\Browser Fluid interface
     */
    public function deleteAllVisibleCookies()
    {
        $this->driver->action("deleteAllVisibleCookies");
        
        return $this;
    }

    /**
     * Sets the threshold for browser-side logging messages; log messages
     * beneath this threshold will be discarded.
     * Valid logLevel strings are: "debug", "info", "warn", "error" or "off".
     * To see the browser logs, you need to
     * either show the log window in GUI mode, or enable browser-side logging in
     * Selenium RC.
     * 
     * @param string $logLevel one of the following: "debug", "info", "warn",
     * "error" or "off"
     * 
     * @return Selenium\Browser Fluid interface
     */
    public function setBrowserLogLevel($logLevel)
    {
        $this->driver->action("setBrowserLogLevel", $logLevel);
        
        return $this;
    }

    /**
     * Creates a new "script" tag in the body of the current test window, and 
     * adds the specified text into the body of the command.  Scripts run in
     * this way can often be debugged more easily than scripts executed using
     * Selenium's "getEval" command.  Beware that JS exceptions thrown in these
     * script
     * tags aren't managed by Selenium, so you should probably wrap your script
     * in try/catch blocks if there is any chance that the script will throw
     * an exception.
     * 
     * @param string $script the JavaScript snippet to run
     * 
     * @return Selenium\Browser Fluid interface
     */
    public function runScript($script)
    {
        $this->driver->action("runScript", $script);
        
        return $this;
    }

    /**
     * Defines a new function for Selenium to locate elements on the page.
     * For example,
     * if you define the strategy "foo", and someone runs click("foo=blah"),
     * we'll
     * run your function, passing you the string "blah", and click on the
     * element 
     * that your function
     * returns, or throw an "Element not found" error if your function returns
     * null.
     * 
     * We'll pass three arguments to your function:
     * <ul>
     * <li>locator: the string the user passed in</li>
     * <li>inWindow: the currently selected window</li>
     * <li>inDocument: the currently selected document</li>
     * </ul>
     * The function must return null if the element can't be found.
     * 
     * @param string $strategyName the name of the strategy to define; this
     * should use only   letters [a-zA-Z] with no spaces or other punctuation.
     * 
     * @param string $functionDefinition a string defining the body of a
     * function in JavaScript.   For example: <code>return
     * inDocument.getElementById(locator);</code>
     * 
     * @return Selenium\Browser Fluid interface
     */
    public function addLocationStrategy($strategyName, $functionDefinition)
    {
        $this->driver->action("addLocationStrategy", $strategyName, $functionDefinition);
        
        return $this;
    }

    /**
     * Saves the entire contents of the current window canvas to a PNG file.
     * Contrast this with the captureScreenshot command, which captures the
     * contents of the OS viewport (i.e. whatever is currently being displayed
     * on the monitor), and is implemented in the RC only. Currently this only
     * works in Firefox when running in chrome mode, and in IE non-HTA using
     * the EXPERIMENTAL "Snapsie" utility. The Firefox implementation is mostly
     * borrowed from the Screengrab! Firefox extension. Please see
     * http://www.screengrab.org and http://snapsie.sourceforge.net/ for
     * details.
     * 
     * @param string $filename the path to the file to persist the screenshot
     * as. No                  filename extension will be appended by default.  
     *                Directories will not be created if they do not exist,     
     *               and an exception will be thrown, possibly by native        
     *          code.
     * 
     * @param string $kwargs a kwargs string that modifies the way the
     * screenshot                  is captured. Example: "background=#CCFFDD" . 
     *                 Currently valid options:                  <dl>           
     *        <dt>background</dt>                     <dd>the background CSS for
     * the HTML document. This                     may be useful to set for
     * capturing screenshots of                     less-than-ideal layouts, for
     * example where absolute                     positioning causes the
     * calculation of the canvas                     dimension to fail and a
     * black background is exposed                     (possibly obscuring black
     * text).</dd>                  </dl>
     * 
     * @return Selenium\Browser Fluid interface
     */
    public function captureEntirePageScreenshot($filename, $kwargs)
    {
        $this->driver->action("captureEntirePageScreenshot", $filename, $kwargs);
        
        return $this;
    }

    /**
     * Executes a command rollup, which is a series of commands with a unique
     * name, and optionally arguments that control the generation of the set of
     * commands. If any one of the rolled-up commands fails, the rollup is
     * considered to have failed. Rollups may also contain nested rollups.
     * 
     * @param string $rollupName the name of the rollup command
     * 
     * @param string $kwargs keyword arguments string that influences how the   
     *                 rollup expands into commands
     * 
     * @return Selenium\Browser Fluid interface
     */
    public function rollup($rollupName, $kwargs)
    {
        $this->driver->action("rollup", $rollupName, $kwargs);
        
        return $this;
    }

    /**
     * Loads script content into a new script tag in the Selenium document. This
     * differs from the runScript command in that runScript adds the script tag
     * to the document of the AUT, not the Selenium document. The following
     * entities in the script content are replaced by the characters they
     * represent:
     * 
     *     &lt;
     *     &gt;
     *     &amp;
     * 
     * The corresponding remove command is removeScript.
     * 
     * @param string $scriptContent the Javascript content of the script to add
     * 
     * @param string $scriptTagId (optional) the id of the new script tag. If   
     *                    specified, and an element with this id already        
     *               exists, this operation will fail.
     * 
     * @return Selenium\Browser Fluid interface
     */
    public function addScript($scriptContent, $scriptTagId)
    {
        $this->driver->action("addScript", $scriptContent, $scriptTagId);
        
        return $this;
    }

    /**
     * Removes a script tag from the Selenium document identified by the given
     * id. Does nothing if the referenced tag doesn't exist.
     * 
     * @param string $scriptTagId the id of the script element to remove.
     * 
     * @return Selenium\Browser Fluid interface
     */
    public function removeScript($scriptTagId)
    {
        $this->driver->action("removeScript", $scriptTagId);
        
        return $this;
    }

    /**
     * Allows choice of one of the available libraries.
     * 
     * @param string $libraryName name of the desired library Only the following
     * three can be chosen: <ul>   <li>"ajaxslt" - Google's library</li>  
     * <li>"javascript-xpath" - Cybozu Labs' faster library</li>   <li>"default"
     * - The default library.  Currently the default library is "ajaxslt" .</li>
     * </ul> If libraryName isn't one of these three, then  no change will be
     * made.
     * 
     * @return Selenium\Browser Fluid interface
     */
    public function useXpathLibrary($libraryName)
    {
        $this->driver->action("useXpathLibrary", $libraryName);
        
        return $this;
    }

    /**
     * Writes a message to the status bar and adds a note to the browser-side
     * log.
     * 
     * @param string $context the message to be sent to the browser
     * 
     * @return Selenium\Browser Fluid interface
     */
    public function setContext($context)
    {
        $this->driver->action("setContext", $context);
        
        return $this;
    }

    /**
     * Sets a file input (upload) field to the file listed in fileLocator
     * 
     * @param string $fieldLocator an <a href="#locators">element locator</a>
     * 
     * @param string $fileLocator a URL pointing to the specified file. Before
     * the file  can be set in the input field (fieldLocator), Selenium RC may
     * need to transfer the file    to the local machine before attaching the
     * file in a web page form. This is common in selenium  grid configurations
     * where the RC server driving the browser is not the same  machine that
     * started the test.   Supported Browsers: Firefox ("*chrome") only.
     * 
     * @return Selenium\Browser Fluid interface
     */
    public function attachFile($fieldLocator, $fileLocator)
    {
        $this->driver->action("attachFile", $fieldLocator, $fileLocator);
        
        return $this;
    }

    /**
     * Captures a PNG screenshot to the specified file.
     * 
     * @param string $filename the absolute path to the file to be written, e.g.
     * "c:\blah\screenshot.png"
     * 
     * @return Selenium\Browser Fluid interface
     */
    public function captureScreenshot($filename)
    {
        $this->driver->action("captureScreenshot", $filename);
        
        return $this;
    }

    /**
     * Capture a PNG screenshot.  It then returns the file as a base 64 encoded
     * string.
     * 
     * @return string The base 64 encoded string of the screen shot (PNG file)
     */
    public function captureScreenshotToString()
    {
        return $this->driver->getString("captureScreenshotToString");
    }

    /**
     * Downloads a screenshot of the browser current window canvas to a 
     * based 64 encoded PNG file. The <em>entire</em> windows canvas is
     * captured,
     * including parts rendered outside of the current view port.
     * 
     * Currently this only works in Mozilla and when running in chrome mode.
     * 
     * @param string $kwargs A kwargs string that modifies the way the
     * screenshot is captured. Example: "background=#CCFFDD". This may be useful
     * to set for capturing screenshots of less-than-ideal layouts, for example
     * where absolute positioning causes the calculation of the canvas dimension
     * to fail and a black background is exposed  (possibly obscuring black
     * text).
     * 
     * @return string The base 64 encoded string of the page screenshot (PNG
     * file)
     */
    public function captureEntirePageScreenshotToString($kwargs)
    {
        return $this->driver->getString("captureEntirePageScreenshotToString", $kwargs);
    }

    /**
     * Kills the running Selenium Server and all browser sessions.  After you
     * run this command, you will no longer be able to send
     * commands to the server; you can't remotely start the server once it has
     * been stopped.  Normally
     * you should prefer to run the "stop" command, which terminates the current
     * browser session, rather than 
     * shutting down the entire server.
     * 
     * @return Selenium\Browser Fluid interface
     */
    public function shutDownSeleniumServer()
    {
        $this->driver->action("shutDownSeleniumServer");
        
        return $this;
    }

    /**
     * Retrieve the last messages logged on a specific remote control. Useful
     * for error reports, especially
     * when running multiple remote controls in a distributed environment. The
     * maximum number of log messages
     * that can be retrieve is configured on remote control startup.
     * 
     * @return Selenium\Browser Fluid interface
     */
    public function retrieveLastRemoteControlLogs()
    {
        $this->driver->action("retrieveLastRemoteControlLogs");
        
        return $this;
    }

    /**
     * Simulates a user pressing a key (without releasing it yet) by sending a
     * native operating system keystroke.
     * This function uses the java.awt.Robot class to send a keystroke; this
     * more accurately simulates typing
     * a key on the keyboard.  It does not honor settings from the shiftKeyDown,
     * controlKeyDown, altKeyDown and
     * metaKeyDown commands, and does not target any particular HTML element. 
     * To send a keystroke to a particular
     * element, focus on the element first before running this command.
     * 
     * @param string $keycode an integer keycode number corresponding to a
     * java.awt.event.KeyEvent; note that Java keycodes are NOT the same thing
     * as JavaScript keycodes!
     * 
     * @return Selenium\Browser Fluid interface
     */
    public function keyDownNative($keycode)
    {
        $this->driver->action("keyDownNative", $keycode);
        
        return $this;
    }

    /**
     * Simulates a user releasing a key by sending a native operating system
     * keystroke.
     * This function uses the java.awt.Robot class to send a keystroke; this
     * more accurately simulates typing
     * a key on the keyboard.  It does not honor settings from the shiftKeyDown,
     * controlKeyDown, altKeyDown and
     * metaKeyDown commands, and does not target any particular HTML element. 
     * To send a keystroke to a particular
     * element, focus on the element first before running this command.
     * 
     * @param string $keycode an integer keycode number corresponding to a
     * java.awt.event.KeyEvent; note that Java keycodes are NOT the same thing
     * as JavaScript keycodes!
     * 
     * @return Selenium\Browser Fluid interface
     */
    public function keyUpNative($keycode)
    {
        $this->driver->action("keyUpNative", $keycode);
        
        return $this;
    }

    /**
     * Simulates a user pressing and releasing a key by sending a native
     * operating system keystroke.
     * This function uses the java.awt.Robot class to send a keystroke; this
     * more accurately simulates typing
     * a key on the keyboard.  It does not honor settings from the shiftKeyDown,
     * controlKeyDown, altKeyDown and
     * metaKeyDown commands, and does not target any particular HTML element. 
     * To send a keystroke to a particular
     * element, focus on the element first before running this command.
     * 
     * @param string $keycode an integer keycode number corresponding to a
     * java.awt.event.KeyEvent; note that Java keycodes are NOT the same thing
     * as JavaScript keycodes!
     * 
     * @return Selenium\Browser Fluid interface
     */
    public function keyPressNative($keycode)
    {
        $this->driver->action("keyPressNative", $keycode);
        
        return $this;
    }

}
