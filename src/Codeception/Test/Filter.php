<?php

declare(strict_types=1);

namespace Codeception\Test;

class Filter
{
    private ?string $namePattern = null;
    private ?int $filterMin = null;
    private ?int $filterMax = null;

    /**
     * @param string[] $includeGroups
     * @param string[] $excludeGroups
     * @param string $namePattern
     */
    public function __construct(
        private ?array $includeGroups,
        private ?array $excludeGroups,
        ?string $namePattern
    ) {
        if ($namePattern === null) {
            return;
        }

        if (@preg_match($namePattern, '') === false) {
            // Handles:
            //  * :testAssertEqualsSucceeds#4
            //  * "testAssertEqualsSucceeds#4-8
            if (preg_match('/^(.*?)#(\d+)(?:-(\d+))?$/', $namePattern, $matches)) {
                if (isset($matches[3]) && $matches[2] < $matches[3]) {
                    $namePattern = sprintf(
                        '%s.*with data set #(\d+)$',
                        $matches[1]
                    );

                    $this->filterMin = (int)$matches[2];
                    $this->filterMax = (int)$matches[3];
                } else {
                    $namePattern = sprintf(
                        '%s.*with data set #%s$',
                        $matches[1],
                        $matches[2]
                    );
                }
            } elseif (preg_match('/^(.*?)@(.+)$/', $namePattern, $matches)) {
                // Handles:
                //  * :testDetermineJsonError@JSON_ERROR_NONE
                //  * :testDetermineJsonError@JSON.*
                $namePattern = sprintf(
                    '%s.*with data set "%s"$',
                    $matches[1],
                    $matches[2]
                );
            }

            // Escape delimiters in regular expression. Do NOT use preg_quote,
            // to keep magic characters.
            $namePattern = sprintf(
                '/%s/i',
                str_replace(
                    '/',
                    '\\/',
                    $namePattern
                )
            );
        }

        $this->namePattern = $namePattern;
    }

    public function isNameAccepted(Test $test): bool
    {
        if ($this->namePattern === null) {
            return true;
        }

        $name = Descriptor::getTestSignature($test) . Descriptor::getTestDataSetIndex($test);

        $accepted = preg_match($this->namePattern, $name, $matches);

        if ($accepted && $this->filterMax !== null) {
            $set = end($matches);
            $accepted = $set >= $this->filterMin && $set <= $this->filterMax;
        }
        return (bool)$accepted;
    }

    public function isGroupAccepted(Test $test, array $groups): bool
    {
        if ($this->includeGroups !== null && $this->includeGroups !== [] && count(\array_intersect($groups, $this->includeGroups)) === 0) {
            return false;
        }
        if ($this->excludeGroups !== null && $this->excludeGroups !== [] && count(\array_intersect($groups, $this->excludeGroups)) > 0) {
            return false;
        }

        return true;
    }
}
