<?php
namespace Codeception\Util;

class MinkJS extends Mink
{

    public function doubleClick($link) {
        $el = $this->findEl($link);
        $el->doubleClick();
    }

    public function clickWithRightButton($link) {
        $el = $this->findEl($link);
        $el->rightClick();

    }

    public function moveMouseOver($link) {
        $el = $this->findEl($link);
        $el->mouseOver();
    }

    public function focus($el) {
        $el = $this->findEl($el);
        $el->focus();
    }

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
		$values = array();
		foreach ($nodes as $node) {
            if (!$this->session->getDriver()->isVisible($node->getXpath())) continue;

            $values .= '<!-- Merged Output -->'.$node->getText();
        }
		return array('contains', $this->escape($text), $values, "'$selector' selector For more details look for page snapshot in the log directory");
    }

    public function pressKey($element, $char, $modifier = null)
    {
        $el = $this->findEl($element);
        $this->session->getDriver()->keyPress($el->getXpath(), $char, $modifier);
    }
    
    public function pressKeyUp($element, $char, $modifier = null) {
        $el = $this->findEl($element);
        $this->session->getDriver()->keyUp($el->getXpath(), $char, $modifier);
    }

    public function pressKeyDown($element, $char, $modifier = null) {
        $el = $this->findEl($element);
        $this->session->getDriver()->keyDown($el->getXpath(), $char, $modifier);
    }

    public function wait($miliseconds) {
        $this->session->getDriver()->wait($miliseconds, null);
    }
    
    public function waitForJS($miliseconds, $jsCondition) {
        $this->session->getDriver()->wait($miliseconds, $jsCondition);
    }
    
    public function executeJs($jsCode) {
        $this->session->getDriver()->executeScript($jsCode);
    }

}
