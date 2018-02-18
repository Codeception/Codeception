<?php
namespace Codeception\PHPUnit\Constraint;

use Codeception\Exception\ElementNotFound;
use Codeception\Lib\Console\Message;
use Codeception\Util\Locator;
use SebastianBergmann\Comparator\ComparisonFailure;

class WebDriver extends Page
{

    protected function matches($nodes)
    {
        if (!count($nodes)) {
            return false;
        }
        if ($this->string === '') {
            return true;
        }

        foreach ($nodes as $node) {
            /** @var $node \WebDriverElement  * */
            if (!$node->isDisplayed()) {
                continue;
            }
            if (parent::matches(htmlspecialchars_decode($node->getText()))) {
                return true;
            }
        }
        return false;
    }

    protected function fail($nodes, $selector, ComparisonFailure $comparisonFailure = null)
    {
        if (!count($nodes)) {
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

        throw new \PHPUnit\Framework\ExpectationFailedException(
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
        foreach ($nodes as $node) {
            if ($contains && strpos($node->getText(), $contains) === false) {
                continue;
            }
            /** @var $node \WebDriverElement  * */
            $message = new Message("\n+ <%s> %s");
            $output .= $message->with($node->getTagName(), $node->getText());
        }
        return $output;
    }
}
