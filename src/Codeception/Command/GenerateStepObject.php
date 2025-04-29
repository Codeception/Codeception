<?php

declare(strict_types=1);

namespace Codeception\Command;

use Codeception\Configuration;
use Codeception\Lib\Generator\StepObject as StepObjectGenerator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

use function ucfirst;

/**
 * Generates StepObject class. You will be asked for steps you want to implement.
 *
 * * `codecept g:stepobject acceptance AdminSteps`
 * * `codecept g:stepobject acceptance UserSteps --silent` - skip action questions
 *
 */
#[AsCommand(
    name: 'generate:stepobject',
    description: 'Generates empty StepObject class'
)]
class GenerateStepObject extends Command
{
    use Shared\FileSystemTrait;
    use Shared\ConfigTrait;

    protected function configure(): void
    {
        $this
            ->addArgument('suite', InputArgument::REQUIRED, 'Suite for StepObject')
            ->addArgument('step', InputArgument::REQUIRED, 'StepObject name')
            ->addOption('silent', '', InputOption::VALUE_NONE, 'Skip verification question');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $suite = (string)$input->getArgument('suite');
        $step = $input->getArgument('step');
        $config = $this->getSuiteConfig($suite);
        $class = $this->getShortClassName($step);
        $path = $this->createDirectoryFor(Configuration::supportDir() . 'Step' . DIRECTORY_SEPARATOR . ucfirst($suite), $step);

        /** @var QuestionHelper $dialog */
        $dialog = $this->getHelper('question');
        $filename = $path . $class . '.php';
        $stepObject = new StepObjectGenerator($config, ucfirst($suite) . '\\' . $step);

        if (!$input->getOption('silent')) {
            do {
                $question = new Question('Add action to StepObject class (ENTER to exit): ', null);
                $action = $dialog->ask($input, $output, $question);
                if ($action) {
                    $stepObject->createAction($action);
                }
            } while ($action);
        }

        $res = $this->createFile($filename, $stepObject->produce());

        if (!$res) {
            $output->writeln("<error>StepObject {$filename} already exists</error>");
            return Command::FAILURE;
        }
        $output->writeln("<info>StepObject was created in {$filename}</info>");
        return Command::SUCCESS;
    }
}
