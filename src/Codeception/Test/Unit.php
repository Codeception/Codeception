<?php

declare(strict_types=1);

namespace Codeception\Test;

use AllowDynamicProperties;
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
use LogicException;

use function lcfirst;
use function method_exists;

/**
 * Represents tests from PHPUnit compatible format.
 */
#[AllowDynamicProperties]
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
        $this->scenario = $this->scenario instanceof Scenario ? clone $this->scenario : null;
    }

    public function getMetadata(): Metadata
    {
        return $this->metadata ??= new Metadata();
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
        throw new LogicException('This method should not be called; use TestCaseWrapper instead.');
    }

    protected function _setUp()
    {
        $metadata = $this->getMetadata();
        if ($metadata->isBlocked()) {
            if ($skip = $metadata->getSkip()) {
                $this->markTestSkipped($skip);
            }
            if ($incomplete = $metadata->getIncomplete()) {
                $this->markTestIncomplete($incomplete);
            }
            return;
        }

        /** @var Di $di */
        $di = $metadata->getService('di');

        // Auto-inject $tester property
        if (
            ($actor = $this->getMetadata()->getCurrent('actor')) &&
            ($property = lcfirst((string) Configuration::config()['actor_suffix']))
        ) {
            $this->{$property} = $di->instantiate($actor);
        }

        $this->scenario = $di->get(Scenario::class);
        // Auto-inject into the _inject method
        $di->injectDependencies($this);
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

    public function getCurrent(?string $current): mixed
    {
        return $this->getMetadata()->getCurrent($current);
    }

    public function getReportFields(): array
    {
        return [
            'name'  => $this->getName(false),
            'class' => self::class,
            'file'  => $this->getMetadata()->getFilename(),
        ];
    }

    public function fetchDependencies(): array
    {
        return array_map(
            fn($dep): string => !str_contains((string)$dep, ':') && method_exists($this, $dep)
                ? self::class . ":{$dep}"
                : $dep,
            $this->getMetadata()->getDependencies()
        );
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
