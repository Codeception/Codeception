<?php
namespace Codeception\PHPUnit\Constraint;

use SebastianBergmann\Comparator\ComparisonFailure;
use Codeception\Util\Locator;

class WebDriverNot extends WebDriver
{
    protected function matches($nodes) : bool
    {
        return !parent::matches($nodes);
    }

    protected function fail($nodes, $selector, ComparisonFailure $comparisonFailure = null) : void
    {
        $selectorString = Locator::humanReadableString($selector);
        if (!$this->string) {
            throw new \PHPUnit\Framework\ExpectationFailedException(
                "Element $selectorString was found",
                $comparisonFailure
            );
        }

        $output = "There was $selectorString element";
        $output .= $this->uriMessage("on page");
        $output .= $this->nodesList($nodes, $this->string);
        $output .= "\ncontaining '{$this->string}'";

        throw new \PHPUnit\Framework\ExpectationFailedException(
            $output,
            $comparisonFailure
        );
    }

    public function toString() : string
    {
        if ($this->string) {
            return 'that contains text "' . $this->string . '"';
        }
    }
}
