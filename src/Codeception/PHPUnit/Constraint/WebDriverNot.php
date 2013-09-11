<?php
namespace Codeception\PHPUnit\Constraint;

class WebDriverNot extends WebDriver {

    protected function matches($nodes)
    {
        return !parent::matches($nodes);
    }

    protected function fail($nodes, $selector, \PHPUnit_Framework_ComparisonFailure $comparisonFailure = NULL)
    {
        $output = "Element '$selector' was found ";

        if (!$this->string) throw new \PHPUnit_Framework_ExpectationFailedException($output, $comparisonFailure);

        foreach ($nodes as $node)
        {
            /** @var $node \WebDriverElement  **/
            if (strpos($node->getText(), $this->string)) {
                $output .= $this->failureDescription($node->getTagName().'['.$node->getText().']');
                break;
            }
        }

        throw new \PHPUnit_Framework_ExpectationFailedException(
          $output,
          $comparisonFailure
        );

    }

    public function toString()
    {
        if ($this->string) return 'that contains text "'.$this->string.'"';
    }
}