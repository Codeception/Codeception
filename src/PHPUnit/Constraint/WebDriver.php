<?php

declare(strict_types=1);

namespace Codeception\PHPUnit\Constraint;

use Codeception\Exception\ElementNotFound;
use Codeception\Lib\Console\Message;
use Codeception\Util\Locator;
use Facebook\WebDriver\WebDriverBy;
use PHPUnit\Framework\ExpectationFailedException;
use SebastianBergmann\Comparator\ComparisonFailure;
use function count;
use function htmlspecialchars_decode;
use function strpos;

class WebDriver extends Page
{
    protected function matches($nodes): bool
    {
        if (count($nodes) === 0) {
            return false;
        }
        if ($this->string === '') {
            return true;
        }

        foreach ($nodes as $node) {
            /** @var \WebDriverElement $node **/
            if (!$node->isDisplayed()) {
                continue;
            }
            if (parent::matches(htmlspecialchars_decode($node->getText()))) {
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
    protected function fail($nodes, $selector, ComparisonFailure $comparisonFailure = null): void
    {
        if (count($nodes) === 0) {
            throw new ElementNotFound($selector, 'Element located either by name, CSS or XPath');
        }

        $output = "Failed asserting that any element by " . Locator::humanReadableString($selector);
        $output .= $this->uriMessage('on page');

        if (count($nodes) < 5) {
            $output .= "\nElements: ";
            $output .= $this->nodesList($nodes);
        } else {
            $message = new Message("[total %s elements]");
            $output .= $message->with(count($nodes));
        }
        $output .= "\ncontains text '" . $this->string . "'";

        throw new ExpectationFailedException(
            $output,
            $comparisonFailure
        );
    }

    protected function failureDescription($nodes): string
    {
        $desc = '';
        foreach ($nodes as $node) {
            $desc .= parent::failureDescription($node->getText());
        }
        return $desc;
    }

    protected function nodesList($nodes, $contains = null): string
    {
        $output = "";
        foreach ($nodes as $node) {
            if ($contains && strpos($node->getText(), (string) $contains) === false) {
                continue;
            }
            /** @var \WebDriverElement $node **/
            $message = new Message("\n+ <%s> %s");
            $output .= $message->with($node->getTagName(), $node->getText());
        }
        return $output;
    }
}
