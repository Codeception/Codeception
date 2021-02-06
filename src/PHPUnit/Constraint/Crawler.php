<?php

declare(strict_types=1);

namespace Codeception\PHPUnit\Constraint;

use Codeception\Exception\ElementNotFound;
use Codeception\Lib\Console\Message;
use Facebook\WebDriver\WebDriverBy;
use PHPUnit\Framework\ExpectationFailedException;
use SebastianBergmann\Comparator\ComparisonFailure;
use Symfony\Component\DomCrawler\Crawler as SymfonyDomCrawler;
use function strpos;

class Crawler extends Page
{
    protected function matches($nodes) : bool
    {
        /** @var SymfonyDomCrawler $nodes **/
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

    /**
     * @param mixed $nodes
     * @param string|array|WebDriverBy $selector
     * @param ComparisonFailure|null $comparisonFailure
     */
    protected function fail($nodes, $selector, ComparisonFailure $comparisonFailure = null):void
    {
        /** @var SymfonyDomCrawler $nodes **/
        if (!$nodes->count()) {
            throw new ElementNotFound($selector, 'Element located either by name, CSS or XPath');
        }

        $output = "Failed asserting that any element by '{$selector}'";
        $output .= $this->uriMessage('on page');
        $output .= " ";

        if ($nodes->count() < 10) {
            $output .= $this->nodesList($nodes);
        } else {
            $message = new Message("[total %s elements]");
            $output .= $message->with($nodes->count())->getMessage();
        }
        $output .= "\ncontains text '{$this->string}'";

        throw new ExpectationFailedException(
            $output,
            $comparisonFailure
        );
    }

    protected function failureDescription($other) : string
    {
        $description = '';
        foreach ($other as $o) {
            $description .= parent::failureDescription($o->textContent);
        }
        return $description;
    }

    protected function nodesList(SymfonyDomCrawler $domCrawler, $contains = null): string
    {
        $output = '';
        foreach ($domCrawler as $node) {
            if ($contains && strpos($node->nodeValue, (string) $contains) === false) {
                continue;
            }
            $output .= "\n+ " . $node->C14N();
        }
        return $output;
    }
}
