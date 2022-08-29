<?php

declare(strict_types=1);

namespace Codeception\Lib\Connector\Shared;

/**
 * Converts BrowserKit\Request's request parameters and files into PHP-compatible structure
 *
 * @see https://bugs.php.net/bug.php?id=25589
 * @see https://bugs.php.net/bug.php?id=25589
 *
 * @package Codeception\Lib\Connector
 */
trait PhpSuperGlobalsConverter
{
    /**
     * Rearrange files array to be compatible with PHP $_FILES superglobal structure
     * @see https://bugs.php.net/bug.php?id=25589
     */
    protected function remapFiles(array $requestFiles): array
    {
        $files = $this->rearrangeFiles($requestFiles);

        return $this->replaceSpaces($files);
    }

    /**
     * Escape high-level variable name with dots, underscores and other "special" chars
     * to be compatible with PHP "bug"
     * @see https://bugs.php.net/bug.php?id=40000
     */
    protected function remapRequestParameters(array $parameters): array
    {
        return $this->replaceSpaces($parameters);
    }

    private function rearrangeFiles(array $requestFiles): array
    {
        $files = [];
        foreach ($requestFiles as $name => $info) {
            if (!is_array($info)) {
                continue;
            }

            /**
             * If we have a form with fields like
             * ```
             * <input type="file" name="foo" />
             * <input type="file" name="foo[bar]" />
             * ```
             * then only array variable will be used while simple variable will be ignored in php $_FILES
             * (eg $_FILES = [
             *                 foo => [
             *                     tmp_name => [
             *                         'bar' => 'asdf'
             *                     ],
             *                     //...
             *                ]
             *              ]
             * )
             * (notice there is no entry for file "foo", only for file "foo[bar]"
             * this will check if current element contains inner arrays within it's keys
             * so we can ignore element itself and only process inner files
             */
            $hasInnerArrays = count(array_filter($info, 'is_array'));

            if ($hasInnerArrays || !isset($info['tmp_name'])) {
                $inner = $this->remapFiles($info);
                foreach ($inner as $innerName => $innerInfo) {
                    /**
                     * Convert from ['a' => ['tmp_name' => '/tmp/test.txt'] ]
                     * to ['tmp_name' => ['a' => '/tmp/test.txt'] ]
                     */
                    $innerInfo = array_map(
                        fn ($v) => [$innerName => $v],
                        $innerInfo
                    );

                    if (empty($files[$name])) {
                        $files[$name] = [];
                    }

                    $files[$name] = array_replace_recursive($files[$name], $innerInfo);
                }
            } else {
                $files[$name] = $info;
            }
        }

        return $files;
    }

    /**
     * Replace spaces and dots and other chars in high-level query parameters for
     * compatibility with PHP bug (or not a bug)
     * @see https://bugs.php.net/bug.php?id=40000
     *
     * @param array $parameters Array of request parameters to be converted
     */
    private function replaceSpaces(array $parameters): array
    {
        $qs = http_build_query($parameters);
        parse_str($qs, $output);

        return $output;
    }
}
