<?php

declare(strict_types=1);

namespace Codeception\PHPUnit\Constraint;

use Facebook\WebDriver\WebDriverBy;
use PHPUnit\Framework\ExpectationFailedException;
use SebastianBergmann\Comparator\ComparisonFailure;
use Symfony\Component\DomCrawler\Crawler as SymfonyCrawler;

class CrawlerNot extends Crawler
{
    /**
     * @param SymfonyCrawler $nodes
     * @return bool
     */
    protected function matches($nodes): bool
    {
        return !parent::matches($nodes);
    }

    /**
     * @param SymfonyCrawler $nodes
     * @param string|array|WebDriverBy $selector
     * @param ComparisonFailure|null $comparisonFailure
     */
    protected function fail($nodes, $selector, ComparisonFailure $comparisonFailure = null): void
    {
        if (!$this->string) {
            throw new ExpectationFailedException(
                "Element '{$selector}' was found",
                $comparisonFailure
            );
        }

        $output = "There was '{$selector}' element";
        $output .= $this->uriMessage('on page');
        $output .= $this->nodesList($nodes, $this->string);
        $output .= "\ncontaining '{$this->string}'";

        throw new ExpectationFailedException(
            $output,
            $comparisonFailure
        );
    }

    public function toString(): string
    {
        if ($this->string) {
            return 'that contains text "' . $this->string . '"';
        }
    }
}
