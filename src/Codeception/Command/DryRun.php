<?php
namespace Codeception\Command;

use Codeception\Configuration;
use Codeception\Event\DispatcherWrapper;
use Codeception\Event\SuiteEvent;
use Codeception\Event\TestEvent;
use Codeception\Events;
use Codeception\Lib\Generator\Actions;
use Codeception\Lib\ModuleContainer;
use Codeception\Module;
use Codeception\Step;
use Codeception\Stub;
use Codeception\Subscriber\Bootstrap as BootstrapLoader;
use Codeception\Subscriber\Console as ConsolePrinter;
use Codeception\SuiteManager;
use Codeception\Test\Interfaces\ScenarioDriven;
use Codeception\Test\Test;
use Codeception\Util\Maybe;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionIntersectionType;
use ReflectionMethod;
use ReflectionType;
use ReflectionUnionType;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Shows step by step execution process for scenario driven tests without actually running them.
 *
 * * `codecept dry-run acceptance`
 * * `codecept dry-run acceptance MyCest`
 * * `codecept dry-run acceptance checkout.feature`
 * * `codecept dry-run tests/acceptance/MyCest.php`
 *
 */
class DryRun extends Command
{
    use DispatcherWrapper;
    use Shared\Config;
    use Shared\Style;

    protected function configure()
    {
        $this->setDefinition(
            [
                new InputArgument('suite', InputArgument::REQUIRED, 'suite to scan for feature files'),
                new InputArgument('test', InputArgument::OPTIONAL, 'tests to be loaded'),
            ]
        );
        parent::configure();
    }

    public function getDescription()
    {
        return 'Prints step-by-step scenario-driven test or a feature';
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->addStyles($output);
        $suite = $input->getArgument('suite');
        $test = $input->getArgument('test');

        $config = $this->getGlobalConfig();
        ini_set(
            'memory_limit',
            isset($config['settings']['memory_limit']) ? $config['settings']['memory_limit'] : '1024M'
        );
        if (! Configuration::isEmpty() && ! $test && strpos($suite, $config['paths']['tests']) === 0) {
            list(, $suite, $test) = $this->matchTestFromFilename($suite, $config['paths']['tests']);
        }
        $settings = $this->getSuiteConfig($suite);

        $dispatcher = new EventDispatcher();
        $dispatcher->addSubscriber(new ConsolePrinter([
            'colors'    => (!$input->hasParameterOption('--no-ansi') xor $input->hasParameterOption('ansi')),
            'steps'     => true,
            'verbosity' => OutputInterface::VERBOSITY_VERBOSE,
        ]));
        $dispatcher->addSubscriber(new BootstrapLoader());

        $suiteManager = new SuiteManager($dispatcher, $suite, $settings);
        $moduleContainer = $suiteManager->getModuleContainer();
        foreach (Configuration::modules($settings) as $module) {
            $this->mockModule($module, $moduleContainer);
        }
        $suiteManager->loadTests($test);
        $tests = $suiteManager->getSuite()->tests();

        $this->dispatch($dispatcher, Events::SUITE_INIT, new SuiteEvent($suiteManager->getSuite(), null, $settings));
        $this->dispatch($dispatcher, Events::SUITE_BEFORE, new SuiteEvent($suiteManager->getSuite(), null, $settings));
        foreach ($tests as $test) {
            if ($test instanceof Test and $test instanceof ScenarioDriven) {
                $this->dryRunTest($output, $dispatcher, $test);
            }
        }
        $this->dispatch($dispatcher, Events::SUITE_AFTER, new SuiteEvent($suiteManager->getSuite()));
        return 0;
    }


    protected function matchTestFromFilename($filename, $tests_path)
    {
        $filename = str_replace(['//', '\/', '\\'], '/', $filename);
        $res = preg_match("~^$tests_path/(.*?)/(.*)$~", $filename, $matches);
        if (!$res) {
            throw new \InvalidArgumentException("Test file can't be matched");
        }

        return $matches;
    }

    /**
     * @param OutputInterface $output
     * @param $dispatcher
     * @param $test
     */
    protected function dryRunTest(OutputInterface $output, EventDispatcher $dispatcher, Test $test)
    {
        $this->dispatch($dispatcher, Events::TEST_START, new TestEvent($test));
        $this->dispatch($dispatcher, Events::TEST_BEFORE, new TestEvent($test));
        try {
            $test->test();
        } catch (\Exception $e) {
        }
        $this->dispatch($dispatcher, Events::TEST_AFTER, new TestEvent($test));
        $this->dispatch($dispatcher, Events::TEST_END, new TestEvent($test));
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

    /**
     * @return Module&MockObject
     */
    private function mockModule($moduleName, ModuleContainer $moduleContainer)
    {
        $module = $moduleContainer->getModule($moduleName);
        $class = new \ReflectionClass($module);
        $methodResults = [];
        foreach ($class->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            if ($method->isConstructor()) {
                continue;
            }
            $methodResults[$method->getName()] = $this->getDefaultResultForMethod($class, $method);
        }

        $moduleContainer->mock($moduleName, Stub::makeEmpty($module, $methodResults));
    }

    private function getDefaultResultForMethod(\ReflectionClass $class, ReflectionMethod $method)
    {
        if (PHP_VERSION_ID < 70000) {
            return new Maybe();
        }

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

        if (PHP_VERSION_ID >= 70100 && $returnType->isBuiltin()) {
            return $this->getDefaultValueForBuiltinType($returnType);
        }

        $typeName = Actions::stringifyNamedType($returnType, $class);
        return Stub::makeEmpty($typeName);
    }



    private function getDefaultValueForBuiltinType(ReflectionType $returnType)
    {
        switch ($returnType->getName()) {
            case 'mixed':
            case 'void':
                return null;
            case 'string':
                return '';
            case 'int':
                return 0;
            case 'float':
                return 0.0;
            case 'bool':
                return false;
            case 'array':
                return [];
            case 'resource':
                return fopen('data://text/plain;base64,', 'r');
            default:
                throw new \Exception('Unsupported return type ' . $returnType->getName());
        }
    }

    private function getDefaultValueOfUnionType($returnType)
    {
        $unionTypes = $returnType->getTypes();
        foreach ($unionTypes as $type) {
            if ($type->isBuiltin()) {
                return $this->getDefaultValueForBuiltinType($type);
            }
        }

        return Stub::makeEmpty($unionTypes[0]);
    }

    private function returnDefaultValueForIntersectionType(ReflectionIntersectionType $returnType)
    {
        $extends    = null;
        $implements = [];
        foreach ($returnType->getTypes() as $type) {
            if (class_exists($type)) {
                $extends = $type;
            } else {
                $implements [] = $type;
            }
        }
        $className = uniqid('anonymous_class_');
        $code      = "abstract class $className";
        if ($extends !== null) {
            $code .= " extends \\$extends";
        }
        if (count($implements) > 0) {
            $code .= ' implements ' . implode(', ', $implements);
        }
        $code .= ' {}';
        eval($code);

        return Stub::makeEmpty($className);
    }
}
