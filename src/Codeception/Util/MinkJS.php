<?php
namespace Codeception\Util;

class MinkJS extends Mink
{

    private $screenOrderCounter = 0;
    /**
     * Double clicks on link or button or any node found by css
     *
     * @param $link
     */
    public function doubleClick($link) {
        $el = $this->findEl($link);
        $el->doubleClick();
    }

    /**
     * Clicks with right button on link or button or any node found by css
     *
     * @param $link
     */
    public function clickWithRightButton($link) {
        $el = $this->findEl($link);
        $el->rightClick();

    }

    /**
     * Moves mouse over link or button or any node found by css
     *
     * @param $link
     */
    public function moveMouseOver($link) {
        $el = $this->findEl($link);
        $el->mouseOver();
    }

    /**
     * Moves focus to link or button or any node found by css
     *
     * @param $el
     */
    public function focus($el) {
        $el = $this->findEl($el);
        $el->focus();
    }

    /**
     * Removes focus from link or button or any node found by css
     *
     * @param $el
     */
    public function blur($el) {
        $el = $this->findEl($el);
        $el->blur();
    }

    /**
     * Drag first element to second
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
     *
     * @param $css
     */
    public function seeElement($css) {
        $el = $this->session->getPage()->find('css', $css);
        if (!$el) \PHPUnit_Framework_Assert::fail("Element $css not found");
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
        $nodes = $this->session->getPage()->findAll('css', $selector);
		$values = '';
		foreach ($nodes as $node) {
            if (!$this->session->getDriver()->isVisible($node->getXpath())) continue;

            $values .= '<!-- Merged Output -->'.$node->getText();
        }
		return array('contains', $this->escape($text), $values, "'$selector' selector For more details look for page snapshot in the log directory");
    }

    /**
     * Presses key on element found by css is focused
     * A char and modifier (ctrl, alt, shift, meta) can be provided.
     *
     * Example:
     *
     * ``` php
     * <?php
     * $I->pressKey('#page','u','ctrl');
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
     * Presses key up on element found by CSS.
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
     * Presses key down on element found by CSS.
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
     * Wait for x miliseconds
     *
     * @param $miliseconds
     */
    public function wait($miliseconds) {
        $this->session->getDriver()->wait($miliseconds, null);
    }

    /**
     * Waits for x miliseconds or until JS condition turns true.
     *
     * @param $miliseconds
     * @param $jsCondition
     */
    public function waitForJS($miliseconds, $jsCondition) {
        $this->session->getDriver()->wait($miliseconds, $jsCondition);
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
