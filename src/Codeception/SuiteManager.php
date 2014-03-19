<?php

namespace Codeception;

use Codeception\Event\Suite;
use Codeception\Event\SuiteEvent;
use Codeception\Lib\GroupManager;
use Codeception\Lib\Parser;
use Codeception\Util\Annotation;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Finder\Finder;

class SuiteManager
{

    protected static $formats = array('Cest', 'Cept', 'Test');

    public static $modules = array();
    public static $actions = array();
    public static $environment;
    public static $name;

    /**
     * @var \PHPUnit_Framework_TestSuite
     */
    protected $suite = null;

    /**
     * @var null|\Symfony\Component\EventDispatcher\EventDispatcher
     */
    protected $dispatcher = null;

    /**
     * @var GroupManager
     */
    protected $groupManager;

    protected $tests = array();
    protected $debug = false;
    protected $path = '';
    protected $printer = null;
    protected $env = null;


    protected $settings = array();

    public function __construct(EventDispatcher $dispatcher, $name, $settings)
    {
        $this->settings = $settings;
        $this->dispatcher = $dispatcher;
        $this->suite = $this->createSuite($name);
        $this->path = $settings['path'];
        $this->groupManager = new GroupManager($settings['groups']);

        if (isset($settings['current_environment'])) {
            $this->env = $settings['current_environment'];
        }
        $this->suite = $this->createSuite($name);

        if (!file_exists($settings['path'] . $settings['class_name'] . '.php')) {
            throw new Exception\Configuration($settings['class_name'] . " class doesn't exists in suite folder.\nRun the 'build' command to generate it");
        }
        $this->initializeModules($settings);
        $this->dispatcher->dispatch(Events::SUITE_INIT, new SuiteEvent($this->suite, null, $this->settings));
        require_once $this->settings['path'] . DIRECTORY_SEPARATOR . $this->settings['class_name'] . '.php';
    }

    public static function hasModule($moduleName)
    {
        return isset(self::$modules[$moduleName]);
    }

    protected function initializeModules($settings)
    {
        self::$modules = Configuration::modules($settings);
        self::$actions = Configuration::actions(self::$modules);
        
        foreach (self::$modules as $module) {
            $module->_initialize();
        }             
    }
    
    protected function createSuite($name)
    {
        $suite = new \PHPUnit_Framework_TestSuite();
        $suite->baseName = $this->env ? substr($name, 0, strpos($name, '-' . $this->env)) : $name;
        if ($this->settings['namespace']) {
            $name = $this->settings['namespace'] . ".$name";
        }
        $suite->setName($name);
        if (!($suite instanceof \PHPUnit_Framework_TestSuite)) {
            throw new Exception\Configuration("Suite class is not inherited from PHPUnit_Framework_TestSuite");
        }
        return $suite;
    }

    public function addTest($path)
    {
        $testClasses = Parser::getClassesFromFile($path);

        foreach ($testClasses as $testClass) {
            $reflected = new \ReflectionClass($testClass);
            if ($reflected->isAbstract()) {
                continue;
            }

            foreach ($reflected->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
                $test = $this->createTestFromPhpUnitMethod($reflected, $method);
                if (!$test) {
                    continue;
                }
                if (!$this->isCurrentEnvironment(Annotation::forMethod($testClass, $method->name)->fetchAll('env'))) {
                    continue;
                }
                $this->addToSuite($test);
            }
        }
    }

    public function addCept($file)
    {
        $name = $this->relativeName($file);
        $this->tests[$name] = $file;

        $cept = new TestCase\Cept();
        $cept->configDispatcher($this->dispatcher)
            ->configName($name)
            ->configFile($file)
            ->configEnv($this->env)
            ->initConfig();

        $cept->preload();

        if (!$this->isCurrentEnvironment($cept->getScenario()->getEnv())) {
            return;
        }
        $this->addToSuite($cept);
    }

    public function addCest($file)
    {
        $name = $this->relativeName($file);
        $this->tests[$name] = $file;
        $testClasses = Parser::getClassesFromFile($file);

        foreach ($testClasses as $testClass) {
            $reflected = new \ReflectionClass($testClass);
            if ($reflected->isAbstract()) {
                continue;
            }

            $unit = new $testClass;
            $methods = get_class_methods($testClass);
            foreach ($methods as $method) {
                $test = $this->createTestFromCestMethod($unit, $method, $file);
                if (!$test) {
                    continue;
                }
                if (!$this->isCurrentEnvironment($test->getScenario()->getEnv())) {
                    continue;
                }
                $this->addToSuite($test);
            }
        }
    }

    protected function addToSuite($test)
    {
        $groups = $this->groupManager->groupsForTest($test);
        $this->suite->addTest($test, $groups);
    }

    protected function relativeName($file)
    {
        return $name = str_replace($this->path, '', $file);
    }

    public function run(PHPUnit\Runner $runner, \PHPUnit_Framework_TestResult $result, $options)
    {
        $this->dispatcher->dispatch(Events::SUITE_BEFORE, new Event\SuiteEvent($this->suite, $result, $this->settings));
        $runner->doEnhancedRun($this->suite, $result, $options);
        $this->dispatcher->dispatch(Events::SUITE_AFTER, new Event\SuiteEvent($this->suite, $result, $this->settings));
    }

    public function loadTest($path)
    {
        if (!file_exists($path)) {
            throw new \Exception("File $path not found");
        }

        foreach (self::$formats as $format) {
            if (preg_match("~$format.php$~", $path)) {
                call_user_func(array($this, "add$format"), $path);
                return;
            }
        }

        if (is_dir($path)) {
            $this->path = $path;
            $this->loadTests();
            return;
        }
        throw new \Exception('Test format not supported. Please, check you use the right suffix. Available filetypes: Cept, Cest, Test');
    }

    public function loadTests()
    {
        $finder = Finder::create()->files()->sortByName()->in($this->path);

        foreach (self::$formats as $format) {
            $formatFinder = clone($finder);
            $testFiles = $formatFinder->name("*$format.php");
            foreach ($testFiles as $test) {
                call_user_func(array($this, "add$format"), $test->getPathname());
            }
        }
    }

    protected function createTestFromPhpUnitMethod(\ReflectionClass $class, \ReflectionMethod $method)
    {
        if (!\PHPUnit_Framework_TestSuite::isTestMethod($method)) {
            return;
        }
        $test = \PHPUnit_Framework_TestSuite::createTest($class, $method->name);

        if ($test instanceof \PHPUnit_Framework_TestSuite_DataProvider) {
            foreach ($test->tests() as $t) {
                $this->enhancePhpunitTest($t);
            }
            return $test;
        }

        $this->enhancePhpunitTest($test);
        return $test;
    }

    protected function enhancePhpunitTest(\PHPUnit_Framework_TestCase $test)
    {
        $className = get_class($test);
        $methodName = $test->getName(false);
        $test->setDependencies(\PHPUnit_Util_Test::getDependencies($className, $methodName));

        if (!$test instanceof TestCase\Test) {
            return;
        }

        $test->configDispatcher($this->dispatcher)
            ->configActor($this->getActor())
            ->initConfig();

        $test->getScenario()->env(Annotation::forMethod($className, $methodName)->fetchAll('env'));
    }

    protected function createTestFromCestMethod($cestInstance, $methodName, $file)
    {
        if ((strpos($methodName, '_') === 0) or ($methodName == '__construct')) {
            return null;
        }
        $testClass = get_class($cestInstance);

        $cest = new TestCase\Cest();
        $cest->configDispatcher($this->dispatcher)
            ->configName($methodName)
            ->configFile($file)
            ->config('testClassInstance', $cestInstance)
            ->config('testMethod', $methodName)
            ->configActor($this->getActor())
            ->initConfig();

        $cest->getScenario()->env(Annotation::forMethod($testClass, $methodName)->fetchAll('env'));
        $cest->setDependencies(\PHPUnit_Util_Test::getDependencies($testClass, $methodName));
        $cest->preload();
        return $cest;
    }

    /**
     * @return null|\PHPUnit_Framework_TestSuite
     */
    public function getSuite()
    {
        return $this->suite;
    }

    protected function isCurrentEnvironment($envs)
    {
        if (empty($envs)) {
            return true;
        }
        return $this->env and in_array($this->env, $envs);
    }

    protected function getActor()
    {
        return $this->settings['namespace']
            ? $this->settings['namespace'] . '\\' . $this->settings['class_name']
            : $this->settings['class_name'];
    }
}
