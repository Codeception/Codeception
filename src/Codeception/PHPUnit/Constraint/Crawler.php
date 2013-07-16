<?php
namespace Codeception\PHPUnit\Constraint;

use Codeception\Exception\ElementNotFound;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;

class Crawler extends Page {

    protected function matches(DomCrawler $nodes)
    {
        if (!$nodes->count()) return false;
        if ($this->string === '') return true;

        foreach ($nodes as $node)
        {
            if (parent::matches($node->nodeValue)) return true;
        }
        return false;
    }

    protected function fail(DomCrawler $nodes, $selector, PHPUnit_Framework_ComparisonFailure $comparisonFailure = NULL)
    {
        if (!$nodes->count()) throw new ElementNotFound($selector, 'Element located either by name, CSS or XPath');

        $output = "Failed asserting that any element found by selector '$selector' ";
        if ($this->uri) $output .= "on page '{$this->uri}' ";
        if ($nodes->count() < 10) {
            $output .= "(listed below)";
            foreach ($nodes as $node)
            {
                $output .= "\n--> ".$node->C14N();
            }
        } else {
            $output .= "(total {$nodes->count()} elements)";
        }
        $output .= "\ncontains text '".$this->string."'";

        throw new \PHPUnit_Framework_ExpectationFailedException(
          $output,
          $comparisonFailure
        );
    }

    protected function failureDescription(DOMCrawler $other)
    {
        $desc = '';
        foreach ($other as $o) {
            $desc .= parent::failureDescription($o->textContent);
        }
        return $desc;
    }


}