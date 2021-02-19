<?php

declare(strict_types=1);

namespace Codeception\PHPUnit\Constraint;

use Codeception\Util\Locator;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverElement;
use PHPUnit\Framework\ExpectationFailedException;
use SebastianBergmann\Comparator\ComparisonFailure;
use function is_string;
use function strpos;

class WebDriverNot extends WebDriver
{
    protected function matches($nodes): bool
    {
        return !parent::matches($nodes);
    }

    /**
     * @param WebDriverElement[] $nodes
     * @param string|array|WebDriverBy $selector
     * @param ComparisonFailure|null $comparisonFailure
     */
    protected function fail($nodes, $selector, ComparisonFailure $comparisonFailure = null): void
    {
        if (!is_string($selector) || strpos($selector, "'") === false) {
            $selector = Locator::humanReadableString($selector);
        }
        if (!$this->string) {
            throw new ExpectationFailedException(
                "Element {$selector} was found",
                $comparisonFailure
            );
        }

        $output = "There was {$selector} element";
        $output .= $this->uriMessage("on page");
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
