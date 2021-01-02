<?php
namespace Codeception\PHPUnit\Constraint;

use Codeception\Exception\ElementNotFound;
use Codeception\Lib\Console\Message;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;
use SebastianBergmann\Comparator\ComparisonFailure;

class Crawler extends Page
{
    protected function matches($nodes) : bool
    {
        /** @var $nodes DomCrawler  * */
        if (!$nodes->count()) {
            return false;
        }
        if ($this->string === '') {
            return true;
        }

        foreach ($nodes as $node) {
            if (parent::matches($node->nodeValue)) {
                return true;
            }
        }
        return false;
    }

    protected function fail($nodes, $selector, ComparisonFailure $comparisonFailure = null):void
    {
        /** @var $nodes DomCrawler  * */
        if (!$nodes->count()) {
            throw new ElementNotFound($selector, 'Element located either by name, CSS or XPath');
        }

        $output = "Failed asserting that any element by '$selector'";
        $output .= $this->uriMessage('on page');
        $output .= " ";

        if ($nodes->count() < 10) {
            $output .= $this->nodesList($nodes);
        } else {
            $message = new Message("[total %s elements]");
            $output .= $message->with($nodes->count())->getMessage();
        }
        $output .= "\ncontains text '{$this->string}'";

        throw new \PHPUnit\Framework\ExpectationFailedException(
            $output,
            $comparisonFailure
        );
    }

    protected function failureDescription($other) : string
    {
        $desc = '';
        foreach ($other as $o) {
            $desc .= parent::failureDescription($o->textContent);
        }
        return $desc;
    }

    protected function nodesList(DomCrawler $nodes, $contains = null)
    {
        $output = "";
        foreach ($nodes as $node) {
            if ($contains && strpos($node->nodeValue, $contains) === false) {
                continue;
            }
            $output .= "\n+ " . $node->C14N();
        }
        return $output;
    }
}
