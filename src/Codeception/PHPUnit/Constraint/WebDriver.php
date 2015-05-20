<?php
namespace Codeception\PHPUnit\Constraint;

use Codeception\Exception\ElementNotFound;
use Codeception\Lib\Console\Message;
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
            if (parent::matches(htmlspecialchars_decode($node->getText()))) return true;
        }
        return false;
    }

    protected function fail($nodes, $selector, \SebastianBergmann\Comparator\ComparisonFailure $comparisonFailure = NULL)
    {
        if (!count($nodes)) throw new ElementNotFound($selector, 'Element located either by name, CSS or XPath');

        $output = new Message("Failed asserting that any element by '$selector'");
        $output .= $this->uriMessage('on page');

        if (count($nodes) < 5) {
            $output .= "\nElements: ";
            $output .= $this->nodesList($nodes);
        } else {
            $message = new Message("[total %s elements]");
            $output .= $message->with(count($nodes))->style('debug');
        }
        $output .= "\ncontains text '".$this->string."'";

        throw new \PHPUnit_Framework_ExpectationFailedException(
          $output,
          $comparisonFailure
        );
    }

    protected function failureDescription($nodes)
    {
        $desc = '';
        foreach ($nodes as $node) {
            $desc .= parent::failureDescription($node->getText());
        }
        return $desc;
    }

    protected function nodesList($nodes, $contains = null)
    {
        $output = "";
        foreach ($nodes as $node)
        {
            if ($contains) {
                if (strpos($node->getText(), $contains) === false) {
                    continue;
                }
            }
            /** @var $node \WebDriverElement  **/
            $message = new Message("<%s> %s");
            $output .= $message->with($node->getTagName(), $node->getText())->style('info')->prepend("\n+ ");
        }
        return $output;

    }



}