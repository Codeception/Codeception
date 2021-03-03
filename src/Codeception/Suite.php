<?php

declare(strict_types=1);

namespace Codeception;

use Codeception\Test\Descriptor;
use Codeception\Test\Interfaces\Dependent;
use PHPUnit\Framework\SelfDescribing;
use PHPUnit\Framework\TestSuite;

class Suite extends TestSuite
{
    /**
     * @var array
     */
    protected $modules;
    /**
     * @var string
     */
    protected $baseName;

    public function reorderDependencies()
    {
        $tests = [];
        foreach ($this->tests as $test) {
            $tests = array_merge($tests, $this->getDependencies($test));
        }

        $queue = [];
        $hashes = [];
        foreach ($tests as $test) {
            if (in_array(spl_object_hash($test), $hashes)) {
                continue;
            }
            $hashes[] = spl_object_hash($test);
            $queue[] = $test;
        }
        $this->tests = $queue;
    }

    /**
     * @param Dependent|SelfDescribing $test
     * @return array
     */
    protected function getDependencies($test): array
    {
        if (!$test instanceof Dependent) {
            return [$test];
        }
        $tests = [];
        foreach ($test->fetchDependencies() as $requiredTestName) {
            $required = $this->findMatchedTest($requiredTestName);
            if (!$required) {
                continue;
            }
            $tests = array_merge($tests, $this->getDependencies($required));
        }
        $tests[] = $test;
        return $tests;
    }

    protected function findMatchedTest($testSignature): SelfDescribing
    {
        /** @var SelfDescribing $test */
        foreach ($this->tests as $test) {
            $signature = Descriptor::getTestSignature($test);
            if ($signature === $testSignature) {
                return $test;
            }
        }
    }

    public function getModules(): array
    {
        return $this->modules;
    }

    public function setModules(array $modules)
    {
        $this->modules = $modules;
    }

    public function getBaseName(): string
    {
        return $this->baseName;
    }

    public function setBaseName(string $baseName): void
    {
        $this->baseName = $baseName;
    }
}
