<?php

declare(strict_types=1);

namespace Codeception\Command;

use Codeception\Configuration;
use Codeception\Event\SuiteEvent;
use Codeception\Event\TestEvent;
use Codeception\Events;
use Codeception\Lib\Generator\Actions;
use Codeception\Lib\ModuleContainer;
use Codeception\Stub;
use Codeception\Subscriber\Bootstrap as BootstrapLoader;
use Codeception\Subscriber\Console as ConsolePrinter;
use Codeception\SuiteManager;
use Codeception\Test\Interfaces\ScenarioDriven;
use Codeception\Test\Test;
use Exception;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionIntersectionType;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionUnionType;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

use function ini_set;
use function preg_match;
use function str_replace;

/**
 * Shows step-by-step execution process for scenario driven tests without actually running them.
 *
 * * `codecept dry-run acceptance`
 * * `codecept dry-run acceptance MyCest`
 * * `codecept dry-run acceptance checkout.feature`
 * * `codecept dry-run tests/acceptance/MyCest.php`
 *
 */
class DryRun extends Command
{
    use Shared\ConfigTrait;
    use Shared\StyleTrait;

    protected function configure(): void
    {
        $this->setDefinition(
            [
                new InputArgument('suite', InputArgument::REQUIRED, 'suite to scan for feature files'),
                new InputArgument('test', InputArgument::OPTIONAL, 'tests to be loaded'),
            ]
        );
        parent::configure();
    }

    public function getDescription(): string
    {
        return 'Prints step-by-step scenario-driven test or a feature';
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->addStyles($output);
        $suite = (string)$input->getArgument('suite');
        $test = $input->getArgument('test');

        $config = $this->getGlobalConfig();
        ini_set(
            'memory_limit',
            $config['settings']['memory_limit'] ?? '1024M'
        );
        if (!Configuration::isEmpty() && !$test && str_starts_with($suite, (string)$config['paths']['tests'])) {
            [, $suite, $test] = $this->matchTestFromFilename($suite, $config['paths']['tests']);
        }
        $settings = $this->getSuiteConfig($suite);

        $eventDispatcher = new EventDispatcher();
        $eventDispatcher->addSubscriber(new ConsolePrinter([
            'colors'    => (!$input->hasParameterOption('--no-ansi') xor $input->hasParameterOption('ansi')),
            'steps'     => true,
            'verbosity' => OutputInterface::VERBOSITY_VERBOSE,
        ]));
        $eventDispatcher->addSubscriber(new BootstrapLoader());

        $suiteManager = new SuiteManager($eventDispatcher, $suite, $settings, []);
        $moduleContainer = $suiteManager->getModuleContainer();
        foreach (Configuration::modules($settings) as $module) {
            $this->mockModule($module, $moduleContainer);
        }
        $suiteManager->loadTests($test);
        $tests = $suiteManager->getSuite()->getTests();

        $eventDispatcher->dispatch(new SuiteEvent($suiteManager->getSuite(), $settings), Events::SUITE_INIT);
        $eventDispatcher->dispatch(new SuiteEvent($suiteManager->getSuite(), $settings), Events::SUITE_BEFORE);

        foreach ($tests as $test) {
            if ($test instanceof Test && $test instanceof ScenarioDriven) {
                $this->dryRunTest($output, $eventDispatcher, $test);
            }
        }
        $eventDispatcher->dispatch(new SuiteEvent($suiteManager->getSuite()), Events::SUITE_AFTER);
        return 0;
    }

    protected function matchTestFromFilename($filename, $testsPath): array
    {
        $filename = str_replace(['//', '\/', '\\'], '/', $filename);
        $res = preg_match("#^{$testsPath}/(.*?)/(.*)$#", $filename, $matches);
        if (!$res) {
            throw new InvalidArgumentException("Test file can't be matched");
        }

        return $matches;
    }

    protected function dryRunTest(OutputInterface $output, EventDispatcher $eventDispatcher, Test $test): void
    {
        $eventDispatcher->dispatch(new TestEvent($test), Events::TEST_START);
        $eventDispatcher->dispatch(new TestEvent($test), Events::TEST_BEFORE);
        try {
            $test->test();
        } catch (Exception) {
        }
        $eventDispatcher->dispatch(new TestEvent($test), Events::TEST_AFTER);
        $eventDispatcher->dispatch(new TestEvent($test), Events::TEST_END);

        if ($test->getMetadata()->isBlocked()) {
            $output->writeln('');
            if ($skip = $test->getMetadata()->getSkip()) {
                $output->writeln("<warning> SKIPPED </warning>" . $skip);
            }
            if ($incomplete = $test->getMetadata()->getIncomplete()) {
                $output->writeln("<warning> INCOMPLETE </warning>" . $incomplete);
            }
        }
        $output->writeln('');
    }

    private function mockModule(string $moduleName, ModuleContainer $moduleContainer): void
    {
        $module = $moduleContainer->getModule($moduleName);
        $class = new ReflectionClass($module);
        $methodResults = [];
        foreach ($class->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            if ($method->isConstructor()) {
                continue;
            }
            $methodResults[$method->getName()] = $this->getDefaultResultForMethod($class, $method);
        }

        $moduleContainer->mock($moduleName, Stub::makeEmpty($module, $methodResults));
    }

    private function getDefaultResultForMethod(ReflectionClass $class, ReflectionMethod $method): mixed
    {
        $returnType = $method->getReturnType();

        if ($returnType === null || $returnType->allowsNull()) {
            return null;
        }

        if ($returnType instanceof ReflectionUnionType) {
            return $this->getDefaultValueOfUnionType($returnType);
        }
        if ($returnType instanceof ReflectionIntersectionType) {
            return $this->returnDefaultValueForIntersectionType($returnType);
        }
        if ($returnType->isBuiltin()) {
            return $this->getDefaultValueForBuiltinType($returnType);
        }

        $typeName = Actions::stringifyNamedType($returnType, $class);
        return Stub::makeEmpty($typeName);
    }

    private function getDefaultValueForBuiltinType(ReflectionNamedType $returnType): mixed
    {
        return match ($returnType->getName()) {
            'mixed', 'void' => null,
            'string' => '',
            'int' => 0,
            'float' => 0.0,
            'bool' => false,
            'array' => [],
            'resource' => fopen('data://text/plain;base64,', 'r'),
            default => throw new Exception('Unsupported return type ' . $returnType->getName()),
        };
    }

    private function getDefaultValueOfUnionType(ReflectionUnionType $returnType): mixed
    {
        $unionTypes = $returnType->getTypes();
        foreach ($unionTypes as $type) {
            if ($type->isBuiltin()) {
                return $this->getDefaultValueForBuiltinType($type);
            }
        }

        return Stub::makeEmpty($unionTypes[0]);
    }

    private function returnDefaultValueForIntersectionType(ReflectionIntersectionType $returnType): mixed
    {
        $extends = null;
        $implements = [];
        foreach ($returnType->getTypes() as $type) {
            if (class_exists($type->getName())) {
                $extends = $type;
            } else {
                $implements [] = $type;
            }
        }
        $className = uniqid('anonymous_class_');
        $code = "abstract class $className";
        if ($extends !== null) {
            $code .= " extends \\$extends";
        }
        if ($implements !== []) {
            $code .= ' implements ' . implode(', ', $implements);
        }
        $code .= ' {}';
        eval($code);

        return Stub::makeEmpty($className);
    }
}
