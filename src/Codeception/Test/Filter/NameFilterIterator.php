<?php

declare(strict_types=1);

namespace Codeception\Test\Filter;

use Codeception\Test\Descriptor;
use Exception;
use PHPUnit\Framework\ErrorTestCase;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Framework\WarningTestCase;
use PHPUnit\Util\RegularExpression;
use RecursiveFilterIterator;
use RecursiveIterator;

use function end;
use function preg_match;

/**
 * Filter tests by name
 */
class NameFilterIterator extends RecursiveFilterIterator
{
    private ?string $filter = null;
    private ?int $filterMin = null;
    private ?int $filterMax = null;

    /**
     * @throws Exception
     */
    public function __construct(RecursiveIterator $iterator, string $filter)
    {
        parent::__construct($iterator);

        $this->setFilter($filter);
    }

    /**
     * @throws Exception
     */
    protected function setFilter(string $filter): void
    {
        if (RegularExpression::safeMatch($filter, '') === false) {
            // Handles:
            //  * :testAssertEqualsSucceeds#4
            //  * "testAssertEqualsSucceeds#4-8
            if (preg_match('/^(.*?)#(\d+)(?:-(\d+))?$/', $filter, $matches)) {
                if (isset($matches[3]) && $matches[2] < $matches[3]) {
                    $filter = sprintf(
                        '%s.*with data set #(\d+)$',
                        $matches[1]
                    );

                    $this->filterMin = (int)$matches[2];
                    $this->filterMax = (int)$matches[3];
                } else {
                    $filter = sprintf(
                        '%s.*with data set #%s$',
                        $matches[1],
                        $matches[2]
                    );
                }
            } elseif (preg_match('/^(.*?)@(.+)$/', $filter, $matches)) {
                // Handles:
                //  * :testDetermineJsonError@JSON_ERROR_NONE
                //  * :testDetermineJsonError@JSON.*
                $filter = sprintf(
                    '%s.*with data set "%s"$',
                    $matches[1],
                    $matches[2]
                );
            }

            // Escape delimiters in regular expression. Do NOT use preg_quote,
            // to keep magic characters.
            $filter = sprintf(
                '/%s/i',
                str_replace(
                    '/',
                    '\\/',
                    $filter
                )
            );
        }

        $this->filter = $filter;
    }

    public function accept(): bool
    {
        $test = $this->getInnerIterator()->current();

        if ($test instanceof TestSuite) {
            return true;
        }

        // This fix the issue when an invalid DataProvider method generates error or warning
        // See https://github.com/Codeception/Codeception/issues/4888
        if ($test instanceof ErrorTestCase || $test instanceof WarningTestCase) {
            $name = $test->getMessage();
        } else {
            $name = Descriptor::getTestSignature($test) . Descriptor::getTestDataSetIndex($test);
        }

        $accepted = preg_match($this->filter, $name, $matches);

        if ($accepted && $this->filterMax !== null) {
            $set = end($matches);
            $accepted = $set >= $this->filterMin && $set <= $this->filterMax;
        }
        return (bool)$accepted;
    }
}
