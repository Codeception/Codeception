<?php
namespace Codeception\Util;

class MinkJS extends Mink
{

    public function checkOption($option)
    {
        $field = $this->findField($option);
        $isChecked = $field->isChecked();
        // overriding to use click
        if (!$isChecked) $field->click();
    }

    public function uncheckOption($option)
    {
        $field = $this->findField($option);
        $isChecked = $field->isChecked();
        // overriding to use click
        if ($isChecked) $field->click();
    }

    /**
     * Double clicks on link or button or any node found by CSS or XPath
     *
     * @param $link
     */
    public function doubleClick($link) {
        $el = $this->findEl($link);
        $el->doubleClick();
    }

    /**
     * Clicks with right button on link or button or any node found by CSS or XPath
     *
     * @param $link
     */
    public function clickWithRightButton($link) {
        $el = $this->findEl($link);
        $el->rightClick();

    }

    /**
     * Moves mouse over link or button or any node found by CSS or XPath
     *
     * @param $link
     */
    public function moveMouseOver($link) {
        $el = $this->findEl($link);
        $el->mouseOver();
    }

    /**
     * Moves focus to link or button or any node found by CSS or XPath
     *
     * @param $el
     */
    public function focus($el) {
        $el = $this->findEl($el);
        $el->focus();
    }

    /**
     * Removes focus from link or button or any node found by CSS or XPath
     * XPath or CSS selectors are accepted.
     *
     * @param $el
     */
    public function blur($el) {
        $el = $this->findEl($el);
        $el->blur();
    }

    /**
     * Drag first element to second
     * XPath or CSS selectors are accepted.
     *
     * @param $el1
     * @param $el2
     */
    public function dragAndDrop($el1, $el2) {
        $el1 = $this->findEl($el1);
        $el2 = $this->findEl($el2);
        $el1->dragTo($el2);
    }

    /**
     * Checks element visibility.
     * Fails if element exists but is invisible to user.
     * Eiter CSS or XPath can be used.
     *
     * Example:
     * 
     * ``` php
     * <?php
     * $I->seeElement("//input[@type='button']");
     * ?>
     * ``` 
     * 
     * @param $selector
     */
    public function seeElement($selector) {
        $el = $this->findEl($selector);

        if (!$el) \PHPUnit_Framework_Assert::fail("Element $selector not found");
        \PHPUnit_Framework_Assert::assertTrue($this->session->getDriver()->isVisible($el->getXpath()));
    }

    /**
     * We use 'see' command only on visible elements
     *
     * @param $text
     * @param null $selector
     * @return array
     */
    protected function proceedSee($text, $selector = null) {
        if (!$selector) return parent::proceedSee($this->escape($text), $selector);
        try {
            $nodes = $this->session->getPage()->findAll('css', $selector);
        } catch (\Symfony\Component\CssSelector\Exception\ParseException $e) {
            $nodes = @$this->session->getPage()->findAll('xpath', $selector);
        }

		$values = '';
		foreach ($nodes as $node) {
            if (!$this->session->getDriver()->isVisible($node->getXpath())) continue;

            $values .= '<!-- Merged Output -->'.$node->getText();
        }
		return array('contains', $this->escape($text), $values, "'$selector' selector For more details look for page snapshot in the log directory");
    }

    /**
     * Presses key on element found by css, xpath is focused
     * A char and modifier (ctrl, alt, shift, meta) can be provided.
     *
     * Example:
     *
     * ``` php
     * <?php
     * $I->pressKey('#page','u');
     * $I->pressKey('#page','u','ctrl');
     * $I->pressKey('descendant-or-self::*[@id='page']','u');
     * ?>
     * ```
     *
     * @param $element
     * @param $char char can be either char ('b') or char-code (98)
     * @param null $modifier keyboard modifier (could be 'ctrl', 'alt', 'shift' or 'meta')
     */
    public function pressKey($element, $char, $modifier = null)
    {
        $el = $this->findEl($element);
        $this->session->getDriver()->keyPress($el->getXpath(), $char, $modifier);
    }

    /**
     * Presses key up on element found by CSS or XPath.
     *
     * For example see 'pressKey'.
     *
     * @param $element
     * @param $char char can be either char ('b') or char-code (98)
     * @param null $modifier keyboard modifier (could be 'ctrl', 'alt', 'shift' or 'meta')
     */
    public function pressKeyUp($element, $char, $modifier = null) {
        $el = $this->findEl($element);
        $this->session->getDriver()->keyUp($el->getXpath(), $char, $modifier);
    }

    /**
     * Presses key down on element found by CSS or XPath.
     *
     * For example see 'pressKey'.
     *
     * @param $element
     * @param $char char can be either char ('b') or char-code (98)
     * @param null $modifier keyboard modifier (could be 'ctrl', 'alt', 'shift' or 'meta')
     */

    public function pressKeyDown($element, $char, $modifier = null) {
        $el = $this->findEl($element);
        $this->session->getDriver()->keyDown($el->getXpath(), $char, $modifier);
    }

    /**
     * Wait for x milliseconds
     *
     * Example:
     * 
     * ``` php
     * <?php
     * $I->wait(1000);	// waits 1000 milliseconds (one second)
     * ?>
     * ```
     * 
     * @param $milliseconds
     */
    public function wait($milliseconds) {
        $this->session->getDriver()->wait($milliseconds, null);
    }

    /**
     * Waits for x milliseconds or until a given JS condition turns true.
     * The function will keep asserting the javascript condition, but will
     * continue regardless of its validity once the x milliseconds time has
     * been passed.
     * 
     * See the example below on how to embed javascript functions as the
     * condition.
     *
     * Example:
     * 
     * ``` php
     * <?php
     * $I->waitForJS(1000, "(function myJavascriptFunction() {
     * 		// Javascript function code
     * 		if (some statement) {
     *			return true;	// waitForJS() function will finish
     *		} else {
     *			return false;	// keep asserting (some statement)
     *		}
     *	})()");
     * ?>
     * ```
     * 
     * @param $milliseconds
     * @param $jsCondition
     */
    public function waitForJS($milliseconds, $jsCondition) {
        $this->session->getDriver()->wait($milliseconds, $jsCondition);
    }

    /**
     * Executes any JS code.
     *
     * @param $jsCode
     */
    public function executeJs($jsCode) {
        $this->session->getDriver()->executeScript($jsCode);
    }
}
