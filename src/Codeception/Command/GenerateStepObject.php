<?php
namespace Codeception\Command;

use Codeception\Lib\Generator\StepObject as StepObjectGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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
         $this->setDefinition(array(
             new InputArgument('suite', InputArgument::REQUIRED, 'Suite for StepObject'),
             new InputArgument('step', InputArgument::REQUIRED, 'StepObject name'),
             new InputOption('config', 'c', InputOption::VALUE_OPTIONAL, 'Use custom path for config'),
             new InputOption('silent', '',InputOption::VALUE_NONE, 'skip verification question'),
         ));
     }

     public function getDescription() {
         return 'Generates empty StepObject class';
     }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $suite = $input->getArgument('suite');
        $step = $input->getArgument('step');
        $conf = $this->getSuiteConfig($suite, $input->getOption('config'));

        $class = $this->getClassName($step);
        $class = $this->removeSuffix($class, 'Steps');

        $path = $this->buildPath($conf['path'].'/_steps/', $class);
        $filename = $this->completeSuffix($class, 'Steps');
        $filename = $path.$filename;

        $dialog = $this->getHelperSet()->get('dialog');

        $gen = new StepObjectGenerator($conf, $class);

        if (!$input->getOption('silent')) {
            do {
                $action = $dialog->ask($output, "Add action to StepObject class (ENTER to exit): ", null);
                if ($action) {
                    $gen->createAction($action);
                }
            } while ($action);
        }

        $res = $this->save($filename, $gen->produce());
        
        $this->introduceAutoloader($conf['path'].'/'.$conf['bootstrap'], 'Steps', '_steps');

        if (!$res) {
            $output->writeln("<error>StepObject $filename already exists</error>");
            exit;
        }
        $output->writeln("<info>StepObject was created in $filename</info>");
    }

}
