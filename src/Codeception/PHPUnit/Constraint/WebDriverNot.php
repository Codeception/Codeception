<?php
namespace Codeception\PHPUnit\Constraint;

use SebastianBergmann\Comparator\ComparisonFailure;
use Codeception\Util\Locator;

class WebDriverNot extends WebDriver
{
    protected function matches($nodes)
    {
        return !parent::matches($nodes);
    }

    protected function fail($nodes, $selector, ComparisonFailure $comparisonFailure = null)
    {
        $selectorString = Locator::humanReadableString($selector);
        if (!$this->string) {
            throw new \PHPUnit_Framework_ExpectationFailedException("Element $selectorString was found", $comparisonFailure);
        }

        $output = "There was $selectorString element";
        $output .= $this->uriMessage("on page");
        $output .= str_replace($this->string, "<bold>{$this->string}</bold>", $this->nodesList($nodes, $this->string));
        $output .= "\ncontaining '{$this->string}'";

        throw new \PHPUnit_Framework_ExpectationFailedException(
            $output,
            $comparisonFailure
        );
    }

    public function toString()
    {
        if ($this->string) {
            return 'that contains text "' . $this->string . '"';
        }
    }
}
