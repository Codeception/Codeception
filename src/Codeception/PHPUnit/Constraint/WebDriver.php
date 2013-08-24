<?php
namespace Codeception\PHPUnit\Constraint;

use Codeception\Exception\ElementNotFound;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;

class WebDriver extends Page {

    protected function matches($nodes)
    {
        if (!count($nodes)) return false;
        if ($this->string === '') return true;

        foreach ($nodes as $node)
        {
            /** @var $node \WebDriverElement  **/
            if (!$node->isDisplayed()) continue;
            if (parent::matches($node->getText())) return true;
        }
        return false;
    }

    protected function fail($nodes, $selector, \PHPUnit_Framework_ComparisonFailure $comparisonFailure = NULL)
    {
        if (!count($nodes)) throw new ElementNotFound($selector, 'Element located either by name, CSS or XPath');

        $output = "Failed asserting that any element found by selector '$selector' ";
        if ($this->uri) $output .= "on page '{$this->uri}' ";
        if (count($nodes) < 5) {
            $output .= "\nElements: ";
            foreach ($nodes as $node)
            {
                /** @var $node \WebDriverElement  **/
                $output .= $node->getTagName().'['.$node->getText().'] ';
            }
        } else {
            $output .= sprintf("(total %s elements)", count($nodes));
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