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
        if (!is_string($selector) || strpos($selector, "'") === false) {
            $selector = Locator::humanReadableString($selector);
        }
        if (!$this->string) {
            throw new \PHPUnit\Framework\ExpectationFailedException(
                "Element $selector was found",
                $comparisonFailure
            );
        }

        $output = "There was $selector element";
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
