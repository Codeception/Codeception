<?php
namespace Codeception\PHPUnit\Constraint;

use SebastianBergmann\Comparator\ComparisonFailure;

class CrawlerNot extends Crawler
{
    protected function matches($nodes)
    {
        return !parent::matches($nodes);
    }

    protected function fail($nodes, $selector, ComparisonFailure $comparisonFailure = null)
    {
        if (!$this->string) {
            throw new \PHPUnit_Framework_ExpectationFailedException(
                "Element '$selector' was found",
                $comparisonFailure
            );
        }
        /** @var $nodes DomCrawler  * */

        $output = "There was '$selector' element";
        $output .= $this->uriMessage('on page');
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
