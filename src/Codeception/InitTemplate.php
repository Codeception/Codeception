<?php

declare(strict_types=1);

namespace Codeception;

use Codeception\Command\Shared\FileSystemTrait;
use Codeception\Command\Shared\StyleTrait;
use Codeception\Lib\Generator\Actor;
use Codeception\Lib\Generator\Actions;
use Codeception\Lib\Generator\Helper;
use Codeception\Lib\ModuleContainer;
use Exception;
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
    use FileSystemTrait;
    use StyleTrait;

    /**
     * @var string
     */
    public const GIT_IGNORE = '.gitignore';

    protected string $namespace        = 'Tests';
    protected string $actorSuffix      = 'Tester';
    protected string $supportNamespace = 'Support';
    protected string $workDir          = '.';

    protected OutputInterface $output;

    public function __construct(protected InputInterface $input, OutputInterface $output)
    {
        $this->addStyles($output);
        $this->output = $output;
    }

    /**
     * Change the directory where Codeception should be installed.
     */
    public function initDir(string $workDir): void
    {
        $this->checkInstalled($workDir);
        $this->sayInfo("Initializing Codeception in {$workDir}");
        $this->createDirectoryFor($workDir);
        chdir($workDir);
        $this->workDir = $workDir;
    }

    /**
     * Override this class to create customized setup.
     *
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
     * @return mixed|string
     */
    protected function ask(string $question, string|bool|array|null $answer = null): mixed
    {
        $question = '? ' . $question;
        $dialog   = new QuestionHelper();

        if (is_array($answer)) {
            $question .= ' <info>(' . $answer[0] . ')</info> ';
            return $dialog->ask($this->input, $this->output, new ChoiceQuestion($question, $answer, 0));
        }

        if (is_bool($answer)) {
            $question .= ' (y/n) ';
            return $dialog->ask($this->input, $this->output, new ConfirmationQuestion($question, $answer));
        }

        if (is_string($answer)) {
            $question .= " <info>({$answer})</info>";
        }

        return $dialog->ask($this->input, $this->output, new Question("{$question} ", $answer));
    }

    /**
     * Print a message to console.
     *
     * ```php
     * <?php
     * $this->say('Welcome to Setup');
     * ```
     */
    protected function say(string $message = ''): void
    {
        $this->output->writeln($message);
    }

    /**
     * Print a successful message
     */
    protected function saySuccess(string $message): void
    {
        $this->say("<notice> {$message} </notice>");
    }

    /**
     * Print error message
     */
    protected function sayError(string $message): void
    {
        $this->say("<error> {$message} </error>");
    }

    /**
     * Print warning message
     */
    protected function sayWarning(string $message): void
    {
        $this->say("<warning> {$message} </warning>");
    }

    /**
     * Print info message
     */
    protected function sayInfo(string $message): void
    {
        $this->say("<debug> {$message}</debug>");
    }

    /**
     * Create a helper class inside a directory
     */
    protected function createHelper(string $name, string $directory, array $settings = []): void
    {
        $dir  = $directory . DIRECTORY_SEPARATOR . 'Helper';
        $file = $this->createDirectoryFor($dir, "{$name}.php") . "{$name}.php";

        $gen = new Helper($settings, $name);
        $this->createFile($file, $gen->produce());
        require_once $file;
        $this->sayInfo("{$name} helper has been created in {$dir}");
    }

    /**
     * Create an empty directory and add a placeholder file into it
     */
    protected function createEmptyDirectory(string $dir): void
    {
        $this->createDirectoryFor($dir);
        $this->createFile($dir . DIRECTORY_SEPARATOR . '.gitkeep', '');
    }

    protected function gitIgnore(string $path): void
    {
        file_put_contents(
            $path . DIRECTORY_SEPARATOR . self::GIT_IGNORE,
            "*\n!" . self::GIT_IGNORE . "\n"
        );
    }

    protected function checkInstalled(string $dir = '.'): void
    {
        if (file_exists("{$dir}/codeception.yml") || file_exists("{$dir}/codeception.dist.yml")) {
            throw new Exception('Codeception is already installed in this directory');
        }
    }

    /**
     * Create an Actor class and generate actions for it.
     * Requires a suite config as array in 3rd parameter.
     * @param array<string,mixed> $suiteConfig
     */
    protected function createActor(string $name, string $directory, array $suiteConfig): void
    {
        $file  = $this->createDirectoryFor($directory, $name) . $this->getShortClassName($name) . '.php';
        $suiteConfig['namespace'] = $this->namespace;

        $config     = Configuration::mergeConfigs(Configuration::$defaultSuiteSettings, $suiteConfig);
        $actorGen   = new Actor($config);
        $this->createFile($file, $actorGen->produce());
        $this->sayInfo("{$name} actor has been created in {$directory}");

        $actionsGen = new Actions($config);
        $generated  = $directory . DIRECTORY_SEPARATOR . '_generated';
        $this->createDirectoryFor($generated, 'Actions.php');
        $this->createFile(
            $generated . DIRECTORY_SEPARATOR . $actorGen->getActorName() . 'Actions.php',
            $actionsGen->produce()
        );
        $this->sayInfo('Actions have been loaded');
    }

    protected function addModulesToComposer(array $modules): ?int
    {
        $packages = ModuleContainer::$packages;

        if (!file_exists('composer.json')) {
            $this->say('');
            $this->sayWarning('Can\'t locate composer.json, please add following packages into "require-dev" section of composer.json:');
            $this->say('');
            foreach (array_unique($modules) as $module) {
                if (!isset($packages[$module])) {
                    continue;
                }
                $package = $packages[$module];
                $this->say(sprintf('"%s": "%s"', $package, '*'));
            }
            $this->say('');
            return null;
        }

        $composer = json_decode(
            file_get_contents('composer.json'),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        if (!is_array($composer)) {
            throw new Exception("Invalid composer.json file. JSON can't be decoded");
        }

        $section = null;
        if (!empty($composer['require']['codeception/codeception'])) {
            $section = 'require';
        }
        if (!empty($composer['require-dev']['codeception/codeception'])) {
            $section = 'require-dev';
        }
        if ($section === null) {
            $section = 'require';
        }

        $added = 0;
        foreach (array_unique($modules) as $module) {
            if (!isset($packages[$module])) {
                continue;
            }
            $pkg = $packages[$module];
            if (isset($composer[$section][$pkg])) {
                continue;
            }
            $this->sayInfo("Adding {$pkg} for {$module} to composer.json");
            $composer[$section][$pkg] = '*';
            ++$added;
        }

        file_put_contents(
            'composer.json',
            json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );

        if ($added !== 0) {
            $this->say("{$added} new packages added to {$section}");
            if ($this->ask('composer.json updated. Do you want to run "composer update"?', true)) {
                $this->sayInfo('Running composer update');
                exec('composer update', $out, $status);
                if ($status !== 0) {
                    $this->sayInfo('Composer installation failed. Please check composer.json and try to run "composer update" manually');
                    return null;
                }
                $vendor = $composer['config']['vendor_dir'] ?? 'vendor';
                $this->updateComposerClassMap($vendor);
            }
        }

        return $added;
    }

    private function updateComposerClassMap(string $vendorDir = 'vendor'): void
    {
        $loader = require $vendorDir . '/autoload.php';
        $loader->addClassMap(require $vendorDir . '/composer/autoload_classmap.php');

        $map = require $vendorDir . '/composer/autoload_psr4.php';
        foreach ($map as $namespace => $paths) {
            $loader->setPsr4($namespace, $paths);
        }
    }
}
