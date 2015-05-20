<?php
namespace Codeception\PHPUnit\Constraint;

use Codeception\Exception\ElementNotFound;
use Codeception\Lib\Console\Message;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;

class Crawler extends Page {

    protected function matches($nodes)
    {
        /** @var $nodes DomCrawler  **/
        if (!$nodes->count()) return false;
        if ($this->string === '') return true;

        foreach ($nodes as $node)
        {
            if (parent::matches($node->nodeValue)) return true;
        }
        return false;
    }

    protected function fail($nodes, $selector, \SebastianBergmann\Comparator\ComparisonFailure $comparisonFailure = NULL)
    {
        /** @var $nodes DomCrawler  **/
        if (!$nodes->count()) throw new ElementNotFound($selector, 'Element located either by name, CSS or XPath');

        $output = "Failed asserting that any element by '$selector'";
        $output .= $this->uriMessage('on page');
        $output .= " ";

        if ($nodes->count() < 10) {
            $output .= $this->nodesList($nodes);
        } else {
            $message = new Message("[total %s elements]");
            $output .= $message->with($nodes->count())->style('debug')->getMessage();
        }
        $output .= "\ncontains text '{$this->string}'";

        throw new \PHPUnit_Framework_ExpectationFailedException(
          $output,
          $comparisonFailure
        );
    }

    protected function failureDescription($other)
    {
        $desc = '';
        foreach ($other as $o) {
            $desc .= parent::failureDescription($o->textContent);
        }
        return $desc;
    }

    protected function nodesList(DOMCrawler $nodes, $contains = null)
    {
        $output = "";
        foreach ($nodes as $node)
        {
            if ($contains) {
                if (strpos($node->nodeValue, $contains) === false) {
                    continue;
                }
            }
            $output .= "\n+ <info>" . $node->C14N()."</info>";
        }
        return $output;
    }


}