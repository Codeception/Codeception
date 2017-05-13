<?php

namespace Codeception;

use Codeception\Command\Shared\FileSystem;
use Codeception\Command\Shared\Style;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

abstract class InitTemplate
{
    use FileSystem;
    use Style;

    const GIT_IGNORE = '.gitignore';

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

    abstract public function setup();

    /**
     * ```php
     * <?php
     * // propose firefox as default browser
     * $this->ask('select the browser of your choice', 'firefox');
     *
     * // propose firefox or chrome possible options
     * $this->ask('select the browser of your choice', ['firefox', 'chrome']);
     * ```
     *
     * @param $question
     * @param null $answer
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
        if ($answer) {
            $question .= " <info>($answer)</info> ";
        }
        return $dialog->ask($this->input, $this->output, new Question($question, $answer));
    }

    protected function say($message = '')
    {
        $this->output->writeln($message);
    }

    protected function saySuccess($message)
    {
        $this->say("<notice> $message </notice>");
    }

    protected function sayWarning($message)
    {
        $this->say("<warning> $message </warning>");
    }

    protected function sayInfo($message)
    {
        $this->say("<debug>> $message</debug>");
    }

    protected function createHelper($name, $directory, $namespace = null)
    {
        $file = $this->createDirectoryFor(
                $dir = $directory . DIRECTORY_SEPARATOR . "Helper",
                "$name.php"
            ) . "$name.php";

        $gen = new Lib\Generator\Helper($name, $namespace);
        // generate helper
        $this->createFile(
            $file,
            $gen->produce()
        );
        require_once $file;
        $this->sayInfo("$name helper has been created in $dir");
    }

    protected function createEmptyDirectory($dir)
    {
        $this->createDirectoryFor($dir);
        $this->createFile($dir . DIRECTORY_SEPARATOR . '.gitkeep', '');
    }

    protected function gitIgnore($path)
    {
        if (!file_exists(self::GIT_IGNORE)) {
            return;
        }
        file_put_contents(self::GIT_IGNORE, $path . "\r\n", FILE_APPEND);
        $this->sayInfo("Added $path to " . self::GIT_IGNORE);
    }

    protected function createActor($name, $directory, $suiteConfig)
    {
        $file = $this->createDirectoryFor(
                $directory,
                $name
            ) . $this->getShortClassName($name);
        $file .= '.php';

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
}
