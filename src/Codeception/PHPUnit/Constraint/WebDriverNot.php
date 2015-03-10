<?php
namespace Codeception\PHPUnit\Constraint;

class WebDriverNot extends WebDriver
{

    protected function matches($nodes)
    {
        return !parent::matches($nodes);
    }

    protected function fail($nodes, $selector, \SebastianBergmann\Comparator\ComparisonFailure $comparisonFailure = null)
    {
        if (!$this->string) {
            throw new \PHPUnit_Framework_ExpectationFailedException("Element '$selector' was found", $comparisonFailure);
        }

        $output = "There was '$selector' element";
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