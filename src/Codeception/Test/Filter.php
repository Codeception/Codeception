<?php

declare(strict_types=1);

namespace Codeception\Test;

use function array_intersect;

class Filter
{
    private ?string $namePattern = null;
    private ?int $filterMin      = null;
    private ?int $filterMax      = null;

    /**
     * @param string[] $includeGroups
     * @param string[] $excludeGroups
     * @param string|null $namePattern
     */
    public function __construct(
        private readonly ?array $includeGroups,
        private readonly ?array $excludeGroups,
        ?string $namePattern
    ) {
        if ($namePattern !== null) {
            $this->namePattern = $this->preparePattern($namePattern);
        }
    }

    private function preparePattern(string $namePattern): string
    {
        if (@preg_match($namePattern, '') !== false) {
            return $namePattern;
        }

        if (preg_match('/^(.*?)#(\d+)(?:-(\d+))?$/', $namePattern, $matches)) {
            if (isset($matches[3]) && (int)$matches[2] < (int)$matches[3]) {
                $this->filterMin = (int) $matches[2];
                $this->filterMax = (int) $matches[3];

                $namePattern = sprintf(
                    '%s.*with data set #(\\d+)$',
                    $matches[1]
                );
            } else {
                $namePattern = sprintf(
                    '%s.*with data set #%s$',
                    $matches[1],
                    $matches[2]
                );
            }
        } elseif (preg_match('/^(.*?)@(.+)$/', $namePattern, $matches)) {
            $namePattern = sprintf(
                '%s.*with data set "%s"$',
                $matches[1],
                $matches[2]
            );
        }

        $escaped = str_replace('/', '\\/', $namePattern);
        return "/{$escaped}/i";
    }

    public function isNameAccepted(Test $test): bool
    {
        if ($this->namePattern === null) {
            return true;
        }

        $name    = Descriptor::getTestSignature($test) . Descriptor::getTestDataSetIndex($test);
        $matches = [];
        if (preg_match($this->namePattern, $name, $matches) === 0) {
            return false;
        }

        if ($this->filterMax !== null) {
            $set = (int) end($matches);
            return $set >= $this->filterMin && $set <= $this->filterMax;
        }

        return true;
    }

    public function isGroupAccepted(Test $test, array $groups): bool
    {
        if ($this->includeGroups && array_intersect($groups, $this->includeGroups) === []) {
            return false;
        }
        return !($this->excludeGroups && array_intersect($groups, $this->excludeGroups));
    }
}
