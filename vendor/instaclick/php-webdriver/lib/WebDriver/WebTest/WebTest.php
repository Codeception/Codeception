<?php
/**
 * Copyright 2011-2012 Anthon Pang. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @package WebDriver
 *
 * @author Anthon Pang <apang@softwaredevelopment.ca>
 */

namespace WebDriver\WebTest;

use WebDriver\WebDriver;
use WebDriver\Exception as WebDriverException;

/**
 * WebDriver\WebTest\WebTest class - test runner
 *
 * WebDriver-based web test runner, outputing results in TAP format.
 *
 * @package WebDriver
 *
 * @link    http://testanything.org/wiki/index.php/TAP_version_13_specification
 */
class WebTest
{
    /**
     * List of magic methods
     *
     * @var array
     */
    private static $magicMethods = array(
        '__construct',
        '__destruct',
        '__call',
        '__callStatic',
        '__get',
        '__set',
        '__isset',
        '__unset',
        '__sleep',
        '__wakeup',
        '__toString',
        '__invoke',
        '__set_state',
        '__clone',
    );

    /**
     * Error handler to instead throw exceptions
     *
     * @param integer $errno   Error number
     * @param string  $errstr  Error string
     * @param string  $errfile Source file of error
     * @param integer $errline Line number in source file
     *
     * @throws \ErrorException
     */
    static public function exception_error_handler($errno, $errstr, $errfile, $errline)
    {
        throw new \ErrorException($errstr, $errno, 0, $errfile, $errline);
    }

    /**
     * Assertion handler to instead throw exceptions
     *
     * @param string  $file Source file
     * @param integer $line Line number
     * @param string  $code Error code
     *
     * @throws \WebDriver\Exception\WebTestAssertion
     */
    static public function assert_handler($file, $line, $code)
    {
        throw WebDriverException::factory(WebDriverException::WEBTEST_ASSERTION, "assertion failed: $file:$line: $code");
    }

    /**
     * Get classes declared in the target file
     *
     * @param string $file File name
     *
     * @return array Array of class names
     */
    public function getClasses($file)
    {
        $classes = get_declared_classes();

        include_once($file);

        return array_diff(get_declared_classes(), $classes);
    }

    /**
     * Dump comment block
     *
     * Note: Reflection extension expects phpdocs style comments
     *
     * @param string $comment
     */
    public function dumpComment($comment)
    {
        if ($comment) {
            $lines = preg_replace(
                array('~^\s*/[*]+\s*~', '~^\s*[*]+/?\s*~'),
                '# ',
                explode("\n", trim($comment))
            );

            echo implode("\n", $lines) . "\n";
        }
    }

    /**
     * Dump diagnostic block (YAML format)
     *
     * @param mixed $diagnostic
     */
    public function dumpDiagnostic($diagnostic)
    {
        if ($diagnostic) {
            if (function_exists('yaml_emit')) {
                $diagnostic = trim(yaml_emit($diagnostic));
            } else {
                $diagnostic = "---\n" . trim($diagnostic) . "\n...";
            }

            if (is_string($diagnostic)) {
                $lines = explode("\n", $diagnostic);
                $lines = preg_replace('/^/', '  ', $lines);
                echo implode("\n", $lines) . "\n";
            }
        }
    }

    /**
     * Parse TODO/SKIP directives (if any) from comment block
     *
     * @param string $comment Comment
     *
     * @return string|null
     */
    public function getDirective($comment)
    {
        if ($comment) {
            if (preg_match('~\b(SKIP|TODO)(\s+([\S \t]+))?~', $comment, $matches)) {
                return $matches[0];
            }
        }

        return null;
    }

    /**
     * Is this a testable method?
     *
     * @param string             $className        Class name
     * @param \RefelectionMethod $reflectionMethod Reflection method
     *
     * @return boolean False if method should not be counted
     */
    protected function isTestableMethod($className, $reflectionMethod)
    {
        $method    = $reflectionMethod->getName();
        $modifiers = $reflectionMethod->getModifiers();

        if ($method === $className
            || $modifiers !== \ReflectionMethod::IS_PUBLIC
            || in_array($method, self::$magicMethods)
        ) {
            return false;
        }

        return true;
    }

    /**
     * Run tests
     *
     * @param string $file File
     *
     * @return boolean True if success; false otherwise
     */
    public function runTests($file)
    {
        $webdriver = new WebDriver();
        $session   = $webdriver->session();
        $classes   = $this->getClasses($file);
        $success   = true;

        /*
         * count the number of testable methods
         */
        $totalMethods = 0;

        foreach ($classes as $class) {
            $parents = class_parents($class, false);

            if ($parents && in_array('WebDriver\WebTest\Script', $parents)) {
                $reflectionClass   = new \ReflectionClass($class);
                $reflectionMethods = $reflectionClass->getMethods();

                foreach ($reflectionMethods as $reflectionMethod) {
                    if ($this->isTestableMethod($class, $reflectionMethod)) {
                        $totalMethods++;
                    }
                }
            }
        }

        if ($totalMethods) {
            $i = 0;
            echo "1..$totalMethods\n";

            foreach ($classes as $class) {
                $parents = class_parents($class, false);

                if ($parents && in_array('WebDriver\WebTest\Script', $parents)) {
                    $class = '\\' . $class;

                    $objectUnderTest = new $class($session);

                    $reflectionClass = new \ReflectionClass($class);

                    $comment = $reflectionClass->getDocComment();
                    $this->dumpComment($comment);

                    $reflectionMethods = $reflectionClass->getMethods();

                    foreach ($reflectionMethods as $reflectionMethod) {
                        if (!$this->isTestableMethod($class, $reflectionMethod)) {
                            continue;
                        }

                        $comment = $reflectionMethod->getDocComment();
                        $this->dumpComment($comment);

                        $directive = $this->getDirective($comment);

                        $description = $method;
                        $reflectionParameters = $reflectionMethod->getParameters();

                        foreach ($reflectionParameters as $reflectionParameter) {
                            if ($reflectionParameter->getName() == 'description'
                                && $reflectionParameter->isDefaultValueAvailable()
                            ) {
                                $defaultValue = $reflectionParameter->getDefaultValue();

                                if (is_string($defaultValue)) {
                                    $description = $defaultValue;
                                    break;
                                }
                            }
                        }

                        $diagnostic = null;
                        $rc = false;
                        $i++;

                        try {
                            $objectUnderTest->$method();
                            $rc = true;
                        } catch (WebDriverException\Curl $e) {
                            $success = false;
                            echo 'Bail out! ' . $e->getMessage() . "\n";
                            break 2;
                        } catch (\Exception $e) {
                            $success    = false;
                            $diagnostic = $e->getMessage();

                            // @todo check driver capability for screenshot

                            $screenshot = $session->screenshot();

                            if (!empty($screenshot)) {
                                $imageName = basename($file) . ".$i.png";
                                file_put_contents($imageName, base64_decode($screenshot));
                            }
                        }

                        echo ($rc ? 'ok' : 'not ok') . " $i - $description" . ($directive ? " # $directive" : '') . "\n";

                        $this->dumpDiagnostic($diagnostic);
                    }

                    unset($objectUnderTest);
                }
            }
        } else {
            echo "0..0\n";
        }

        if ($session) {
            $session->close();
        }

        return $success;
    }

    /**
     * Main dispatch routine
     *
     * @param integer $argc number of arguments
     * @param array   $argv arguments
     *
     * @return boolean True if success; false otherwise
     */
    static public function main($argc, $argv)
    {
        set_error_handler(array('WebDriver\WebTest\WebTest', 'exception_error_handler'));

        assert_options(ASSERT_ACTIVE, 1);
        assert_options(ASSERT_WARNING, 0);
        assert_options(ASSERT_CALLBACK, array('WebDriver\WebTest\WebTest', 'assert_handler'));

        /*
         * parse command line options
         */
        if ($argc == 1) {
            $argc++;
            array_push($argv, '-h');
        }

        for ($i = 1; $i < $argc; $i++) {
            $opt = $argv[$i];
            $optValue = '';

            if (preg_match('~([-]+[^=]+)=(.+)~', $opt, $matches)) {
                $opt = $matches[1];
                $optValue = $matches[2];
            }

            switch ($opt) {
                case '-h':
                case '--help':
                    echo $argv[0] . " [-d directory] [--tap] [--xml] [--disable-screenshot] test.php\n";
                    exit(1);

                case '-d':
                case '--output-directory':

                case '--format':
                case '--tap':
                case '--xml':

                case '--disable-screenshot':

                default:
            }
        }

        echo "TAP version 13\n";

        $success = false;

        try {
            $webtest = new self;
            $success = $webtest->runTests($argv[1]);
        } catch (\Exception $e) {
            echo 'Bail out! ' . $e->getMessage() . "\n";
        }

        return $success;
    }
}
