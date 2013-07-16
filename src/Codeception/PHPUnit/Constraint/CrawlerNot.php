<?php
namespace Codeception\PHPUnit\Constraint;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;

class CrawlerNot extends Crawler {

    protected function matches(DomCrawler $nodes)
    {
        return !parent::matches($nodes);
    }

    protected function fail(DomCrawler $nodes, $selector, PHPUnit_Framework_ComparisonFailure $comparisonFailure = NULL)
    {
        $output = "Element '$selector' was found ";

        if (!$this->string) throw new \PHPUnit_Framework_ExpectationFailedException($output, $comparisonFailure);

        foreach ($nodes as $node)
        {
            if (strpos($node->nodeValue, $this->string)) {
                $output .= $this->failureDescription($node->C14N());
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