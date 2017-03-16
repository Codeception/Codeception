<?php
namespace Codeception\Command;

use Codeception\Configuration;
use Codeception\Lib\Generator\StepObject as StepObjectGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

/**
 * Generates StepObject class. You will be asked for steps you want to implement.
 *
 * * `codecept g:step acceptance AdminSteps`
 * * `codecept g:step acceptance UserSteps --silent` - skip action questions
 *
 */
class GenerateStepObject extends Command
{
    use Shared\FileSystem;
    use Shared\Config;

    protected function configure()
    {
        $this->setDefinition([
            new InputArgument('suite', InputArgument::REQUIRED, 'Suite for StepObject'),
            new InputArgument('step', InputArgument::REQUIRED, 'StepObject name'),
            new InputOption('silent', '', InputOption::VALUE_NONE, 'skip verification question'),
        ]);
    }

    public function getDescription()
    {
        return 'Generates empty StepObject class';
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $suite = $input->getArgument('suite');
        $step = $input->getArgument('step');
        $config = $this->getSuiteConfig($suite);

        $class = $this->getClassName($step);

        $path = $this->buildPath(Configuration::supportDir() . 'Step' . DIRECTORY_SEPARATOR . ucfirst($suite), $step);

        $dialog = $this->getHelperSet()->get('question');
        $filename = $path . $class . '.php';

        $helper = $this->getHelper('question');
        $question = new Question("Add action to StepObject class (ENTER to exit): ");

        $gen = new StepObjectGenerator($config, ucfirst($suite) . '\\' . $step);

        if (!$input->getOption('silent')) {
            do {
                $question = new Question('Add action to StepObject class (ENTER to exit): ', null);
                $action = $dialog->ask($input, $output, $question);
                if ($action) {
                    $gen->createAction($action);
                }
            } while ($action);
        }

        $res = $this->save($filename, $gen->produce());

        if (!$res) {
            $output->writeln("<error>StepObject $filename already exists</error>");
            exit;
        }
        $output->writeln("<info>StepObject was created in $filename</info>");
    }
}
