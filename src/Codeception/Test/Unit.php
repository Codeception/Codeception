<?php

declare(strict_types=1);

namespace Codeception\Test;

use Codeception\Configuration;
use Codeception\Exception\ModuleException;
use Codeception\Lib\Di;
use Codeception\Lib\PauseShell;
use Codeception\Module;
use Codeception\PHPUnit\TestCase;
use Codeception\ResultAggregator;
use Codeception\Scenario;
use Codeception\Test\Feature\Stub;
use Codeception\TestInterface;
use Codeception\Util\Debug;

use function get_class;
use function lcfirst;
use function method_exists;

/**
 * Represents tests from PHPUnit compatible format.
 */
class Unit extends TestCase implements
    Interfaces\Reported,
    Interfaces\Dependent,
    TestInterface
{
    use Stub;

    private ?Metadata $metadata = null;

    private ?Scenario $scenario = null;

    public function __clone(): void
    {
        if ($this->scenario !== null) {
            $this->scenario = clone $this->scenario;
        }
    }

    public function getMetadata(): Metadata
    {
        if (!$this->metadata) {
            $this->metadata = new Metadata();
        }
        return $this->metadata;
    }

    public function getScenario(): ?Scenario
    {
        return $this->scenario;
    }

    public function setMetadata(?Metadata $metadata): void
    {
        $this->metadata = $metadata;
    }

    public function getResultAggregator(): ResultAggregator
    {
        throw new \LogicException('This method should not be called, TestCaseWrapper class must be used instead');
    }

    protected function _setUp()
    {
        if ($this->getMetadata()->isBlocked()) {
            if ($this->getMetadata()->getSkip() !== null) {
                $this->markTestSkipped($this->getMetadata()->getSkip());
            }
            if ($this->getMetadata()->getIncomplete() !== null) {
                $this->markTestIncomplete($this->getMetadata()->getIncomplete());
            }
            return;
        }

        /** @var Di $di */
        $di = $this->getMetadata()->getService('di');
        // auto-inject $tester property
        if (($this->getMetadata()->getCurrent('actor')) && ($property = lcfirst(Configuration::config()['actor_suffix']))) {
            $this->$property = $di->instantiate($this->getMetadata()->getCurrent('actor'));
        }

        $this->scenario = $di->get(Scenario::class);

        // Auto inject into the _inject method
        $di->injectDependencies($this); // injecting dependencies
        $this->_before();
    }

    /**
     * @Override
     */
    protected function _before()
    {
    }

    protected function _tearDown()
    {
        $this->_after();
    }

    /**
     * @Override
     */
    protected function _after()
    {
    }

    public function getModule(string $module): Module
    {
        $modules = $this->getMetadata()->getCurrent('modules');
        if (!isset($modules[$module])) {
            throw new ModuleException($module, "Module can't be accessed");
        }
        return $modules[$module];
    }

    /**
     * Starts interactive pause in this test
     *
     * @param array<string, mixed> $vars
     * @return void
     */
    public function pause(array $vars = []): void
    {
        if (!Debug::isEnabled()) {
            return;
        }
        $psy = (new PauseShell())->getShell();
        $psy->setBoundObject($this);
        $psy->setScopeVariables($vars);
        $psy->run();
    }

    /**
     * Returns current values
     */
    public function getCurrent(?string $current): mixed
    {
        return $this->getMetadata()->getCurrent($current);
    }

    public function getReportFields(): array
    {
        return [
            'name'    => $this->getName(false),
            'class'   => get_class($this),
            'file'    => $this->getMetadata()->getFilename()
        ];
    }

    public function fetchDependencies(): array
    {
        $names = [];
        foreach ($this->getMetadata()->getDependencies() as $required) {
            if (!str_contains($required, ':') && method_exists($this, $required)) {
                $required = get_class($this) . ":{$required}";
            }
            $names[] = $required;
        }
        return $names;
    }

    public function getFileName(): string
    {
        return $this->getMetadata()->getFilename();
    }

    public function getSignature(): string
    {
        return $this->getName(false);
    }
}
