<?php

declare(strict_types=1);

namespace Codeception\Lib\Connector\Shared;

/**
 * Converts BrowserKit\Request's request parameters and files into PHP-compatible structure
 *
 * @see https://bugs.php.net/bug.php?id=25589
 * @see https://bugs.php.net/bug.php?id=40000
 *
 * @package Codeception\Lib\Connector
 */
trait PhpSuperGlobalsConverter
{
    /**
     * Rearrange files array to match PHP $_FILES structure.
     * Handles nested arrays within files, ensuring compatibility with PHP's $_FILES superglobal.
     */
    protected function remapFiles(array $requestFiles): array
    {
        $normalizedFiles = $this->normalizeFilesArray($requestFiles);
        return $this->normalizeQueryParameters($normalizedFiles);
    }

    /**
     * Normalize request parameters by replacing spaces and special characters.
     * Ensures compatibility with PHP's handling of query parameters.
     */
    protected function remapRequestParameters(array $parameters): array
    {
        return $this->normalizeQueryParameters($parameters);
    }

    private function normalizeFilesArray(array $requestFiles): array
    {
        $normalizedFiles = [];
        foreach ($requestFiles as $fieldName => $fileInfo) {
            if (!is_array($fileInfo)) {
                continue;
            }

            // Check if the current file info has nested arrays within its keys
            $containsNestedArrays = count(array_filter($fileInfo, 'is_array'));

            if ($containsNestedArrays || !isset($fileInfo['tmp_name'])) {
                $nestedFiles = $this->remapFiles($fileInfo);
                // Convert from ['a' => ['tmp_name' => '/tmp/test.txt'] ]
                // to ['tmp_name' => ['a' => '/tmp/test.txt'] ]
                foreach ($nestedFiles as $nestedFieldName => $nestedFileInfo) {
                    $nestedFileInfo = array_map(
                        fn($value): array => [$nestedFieldName => $value],
                        $nestedFileInfo
                    );

                    $normalizedFiles[$fieldName] = array_replace_recursive(
                        $normalizedFiles[$fieldName] ?? [],
                        $nestedFileInfo
                    );
                }
            } else {
                $normalizedFiles[$fieldName] = $fileInfo;
            }
        }

        return $normalizedFiles;
    }

    /**
     * Normalize query parameters by replacing spaces and special characters.
     * Ensures compatibility with PHP's handling of query strings.
     */
    private function normalizeQueryParameters(array $parameters): array
    {
        parse_str(http_build_query($parameters), $normalizedParameters);
        return $normalizedParameters;
    }
}
