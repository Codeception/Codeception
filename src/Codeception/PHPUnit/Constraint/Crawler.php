<?php
namespace Codeception\PHPUnit\Constraint;

use Codeception\Exception\ElementNotFound;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;

class Crawler extends Page {

    protected function matches(DomCrawler $nodes)
    {
        if (!$nodes->count()) return false;
        $res = false;
        foreach ($nodes as $node)
        {
            if (parent::matches($node->nodeValue)) return true;
        }
        return false;
    }

    protected function fail(DomCrawler $nodes, $selector, PHPUnit_Framework_ComparisonFailure $comparisonFailure = NULL)
    {
        if (!$nodes->count()) throw new ElementNotFound($selector, 'Element located either by name, CSS or XPath');
        $s = $nodes->count() > 1 ? 's' : '';
        $output = "Failed asserting that any element matched by selector (('$selector')) ";
        if ($nodes->count() < 10) {
            $output .= "(listed below)";
            foreach ($nodes as $node)
            {
                $output .= "\n--> ".$node->nodeValue;
            }
        } else {
            $output .= "(total {$nodes->count()} elements)";
        }
        $s = $nodes->count() > 1 ? '' : 's';
        $output .= "\ncontain$s text '".$this->string."'";

        throw new \PHPUnit_Framework_ExpectationFailedException(
          $output,
          $comparisonFailure
        );
    }

}