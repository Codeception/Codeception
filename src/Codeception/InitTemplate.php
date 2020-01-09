<?php

namespace Codeception;

use Codeception\Command\Shared\FileSystem;
use Codeception\Command\Shared\Style;
use Codeception\Lib\ModuleContainer;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

/**
 * Codeception templates allow creating a customized setup and configuration for your project.
 * An abstract class for installation template. Each init template should extend it and implement a `setup` method.
 * Use it to build a custom setup class which can be started with `codecept init` command.
 *
 *
 * ```php
 * <?php
 * namespace Codeception\Template; // it is important to use this namespace so codecept init could locate this template
 * class CustomInstall extends \Codeception\InitTemplate
 * {
 *      public function setup()
 *      {
 *         // implement this
 *      }
 * }
 * ```
 * This class provides various helper methods for building customized setup
 */
abstract class InitTemplate
{
    use FileSystem;
    use Style;

    const GIT_IGNORE = '.gitignore';

    /**
     * @var string
     */
    protected $namespace = '';

    /**
     * @var string
     */
    protected $actorSuffix = 'Tester';

    /**
     * @var string
     */
    protected $workDir = '.';

    /**
     * @var InputInterface
     */
    protected $input;

    /**
     * @var OutputInterface
     */
    protected $output;

    public function __construct(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->addStyles($output);
        $this->output = $output;
    }

    /**
     * Change the directory where Codeception should be installed.
     */
    public function initDir($workDir)
    {
        $this->checkInstalled($workDir);
        $this->sayInfo("Initializing Codeception in $workDir");
        $this->createDirectoryFor($workDir);
        chdir($workDir);
        $this->workDir = $workDir;
    }

    /**
     * Override this class to create customized setup.
     * @return mixed
     */
    abstract public function setup();

    /**
     * ```php
     * <?php
     * // propose firefox as default browser
     * $this->ask('select the browser of your choice', 'firefox');
     *
     * // propose firefox or chrome possible options
     * $this->ask('select the browser of your choice', ['firefox', 'chrome']);
     *
     * // ask true/false question
     * $this->ask('do you want to proceed (y/n)', true);
     * ```
     *
     * @param string $question
     * @param mixed $answer
     * @return mixed|string
     */
    protected function ask($question, $answer = null)
    {
        $question = "? $question";
        $dialog = new QuestionHelper();
        if (is_array($answer)) {
            $question .= " <info>(" . $answer[0] . ")</info> ";
            return $dialog->ask($this->input, $this->output, new ChoiceQuestion($question, $answer, 0));
        }
        if (is_bool($answer)) {
            $question .= " (y/n) ";
            return $dialog->ask($this->input, $this->output, new ConfirmationQuestion($question, $answer));
        }
        if ($answer) {
            $question .= " <info>($answer)</info>";
        }
        return $dialog->ask($this->input, $this->output, new Question("$question ", $answer));
    }

    /**
     * Print a message to console.
     *
     * ```php
     * <?php
     * $this->say('Welcome to Setup');
     * ```
     *
     *
     * @param string $message
     */
    protected function say($message = '')
    {
        $this->output->writeln($message);
    }

    /**
     * Print a successful message
     * @param string $message
     */
    protected function saySuccess($message)
    {
        $this->say("<notice> $message </notice>");
    }

    /**
     * Print error message
     * @param string $message
     */
    protected function sayError($message)
    {
        $this->say("<error> $message </error>");
    }

    /**
     * Print warning message
     * @param $message
     */
    protected function sayWarning($message)
    {
        $this->say("<warning> $message </warning>");
    }

    /**
     * Print info message
     * @param string $message
     */
    protected function sayInfo($message)
    {
        $this->say("<debug> $message</debug>");
    }

    /**
     * Create a helper class inside a directory
     *
     * @param $name
     * @param $directory
     */
    protected function createHelper($name, $directory)
    {
        $file = $this->createDirectoryFor(
            $dir = $directory . DIRECTORY_SEPARATOR . "Helper",
            "$name.php"
        ) . "$name.php";

        $gen = new Lib\Generator\Helper($name, $this->namespace);
        // generate helper
        $this->createFile(
            $file,
            $gen->produce()
        );
        require_once $file;
        $this->sayInfo("$name helper has been created in $dir");
    }

    /**
     * Create an empty directory and add a placeholder file into it
     * @param $dir
     */
    protected function createEmptyDirectory($dir)
    {
        $this->createDirectoryFor($dir);
        $this->createFile($dir . DIRECTORY_SEPARATOR . '.gitkeep', '');
    }

    protected function gitIgnore($path)
    {
        if (file_exists(self::GIT_IGNORE)) {
            file_put_contents($path . DIRECTORY_SEPARATOR . self::GIT_IGNORE, "*\n!" . self::GIT_IGNORE);
        }
    }

    protected function checkInstalled($dir = '.')
    {
        if (file_exists($dir . DIRECTORY_SEPARATOR . 'codeception.yml') || file_exists($dir . DIRECTORY_SEPARATOR . 'codeception.dist.yml')) {
            throw new \Exception("Codeception is already installed in this directory");
        }
    }

    /**
     * Create an Actor class and generate actions for it.
     * Requires a suite config as array in 3rd parameter.
     *
     * @param $name
     * @param $directory
     * @param $suiteConfig
     */
    protected function createActor($name, $directory, $suiteConfig)
    {
        $file = $this->createDirectoryFor(
            $directory,
            $name
        ) . $this->getShortClassName($name);
        $file .= '.php';

        $suiteConfig['namespace'] = $this->namespace;
        $config = Configuration::mergeConfigs(Configuration::$defaultSuiteSettings, $suiteConfig);

        $actorGenerator = new Lib\Generator\Actor($config);

        $content = $actorGenerator->produce();

        $this->createFile($file, $content);
        $this->sayInfo("$name actor has been created in $directory");

        $actionsGenerator = new Lib\Generator\Actions($config);
        $content = $actionsGenerator->produce();

        $generatedDir = $directory . DIRECTORY_SEPARATOR . '_generated';
        $this->createDirectoryFor($generatedDir, 'Actions.php');
        $this->createFile($generatedDir . DIRECTORY_SEPARATOR . $actorGenerator->getActorName() . 'Actions.php', $content);
        $this->sayInfo("Actions have been loaded");
    }

    protected function addModulesToComposer($modules)
    {
        $packages = ModuleContainer::$packages;
        $section = null;
        if (!file_exists('composer.json')) {
            $this->say('');
            $this->sayWarning('Can\'t locate composer.json, please add following packages into "require-dev" section of composer.json:');
            $this->say('');

            foreach (array_unique($modules) as $module) {
                if (!isset($packages[$module])) {
                    continue;
                }
                $package = $packages[$module];
                $this->say(sprintf('"%s": "%s"', $package, "^1.0.0"));
                $composer[$section][$package] = "^1.0.0";
            }
            $this->say('');
            return;
        }
        $composer = json_decode(file_get_contents('composer.json'), true);
        if ($composer === null) {
            throw new \Exception("Invalid composer.json file. JSON can't be decoded");
        }
        $section = null;
        if (isset($composer['require'])) {
            if (isset($composer['require']['codeception/codeception'])) {
                $section = 'require';
            }
        }
        if (isset($composer['require-dev'])) {
            if (isset($composer['require-dev']['codeception/codeception'])) {
                $section = 'require-dev';
            }
        }
        if (!$section) {
            $section = 'require';
        }

        $packageCounter = 0;
        foreach (array_unique($modules) as $module) {
            if (!isset($packages[$module])) {
                continue;
            }
            $package = $packages[$module];
            if (isset($composer[$section][$package])) {
                continue;
            }
            $this->sayInfo("Adding $package for $module to composer.json");
            $composer[$section][$package] = "^1.0.0";
            $packageCounter++;
        }

        file_put_contents('composer.json', json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        if ($packageCounter) {
            $this->say("$packageCounter new packages added to $section");
        }

        if ($packageCounter && $this->ask('composer.json updated. Do you want to run "composer update"?', true)) {
            $this->sayInfo('Running composer update');
            exec('composer update', $output, $status);
            if ($status !== 0) {
                $this->sayInfo('Composer installation failed. Please check composer.json and try to run "composer update" manually');
                return;
            }
            $this->updateComposerClassMap();
        }

        return $packageCounter;
    }

    private function updateComposerClassMap()
    {
        $loader = require 'vendor/autoload.php';
        $classMap = require 'vendor/composer/autoload_classmap.php';
        $loader->addClassMap($classMap);
        $map = require 'vendor/composer/autoload_psr4.php';
        foreach ($map as $namespace => $path) {
            $loader->setPsr4($namespace, $path);
        }
    }
}
