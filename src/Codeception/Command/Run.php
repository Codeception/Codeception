<?php
namespace Codeception\Command;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class Run extends Base
{

    protected function configure()
    {
        $this->setDefinition(array(

            new \Symfony\Component\Console\Input\InputArgument('suite', InputArgument::OPTIONAL, 'suite to be tested'),
            new \Symfony\Component\Console\Input\InputArgument('test', InputArgument::OPTIONAL, 'test to be run'),

            new \Symfony\Component\Console\Input\InputOption('config', 'c', InputOption::VALUE_REQUIRED, 'Use specified config instead of default'),
            new \Symfony\Component\Console\Input\InputOption('report', '', InputOption::VALUE_NONE, 'Show output in compact style'),
            new \Symfony\Component\Console\Input\InputOption('html', '', InputOption::VALUE_NONE, 'Generate html with results'),
            new \Symfony\Component\Console\Input\InputOption('xml', '', InputOption::VALUE_NONE, 'Generate JUnit XML Log'),
            new \Symfony\Component\Console\Input\InputOption('tap', '', InputOption::VALUE_NONE, 'Generate Tap Log'),
            new \Symfony\Component\Console\Input\InputOption('json', '', InputOption::VALUE_NONE, 'Generate Json Log'),
            new \Symfony\Component\Console\Input\InputOption('colors', '', InputOption::VALUE_NONE, 'Use colors in output'),
            new \Symfony\Component\Console\Input\InputOption('silent', '', InputOption::VALUE_NONE, 'Use colors in output'),
            new \Symfony\Component\Console\Input\InputOption('steps', '', InputOption::VALUE_NONE, 'Show steps in output'),
            new \Symfony\Component\Console\Input\InputOption('debug', '', InputOption::VALUE_NONE, 'Show debug and scenario output'),
            new \Symfony\Component\Console\Input\InputOption('no-exit', '', InputOption::VALUE_NONE, 'Dont\'t finish with exit code')
        ));
        parent::configure();
    }

    public function getDescription()
    {
        return 'Runs the test suites';
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $options = $input->getOptions();
        if ($input->getArgument('test')) $options['steps'] = true;

        $suite = $input->getArgument('suite');
        $test = $input->getArgument('test');

        $codecept = new \Codeception\Codecept((array) $options);

        $suites = $suite ? array($suite) : \Codeception\Configuration::suites();

        $output->writeln(\Codeception\Codecept::versionString() . "\nPowered by " . \PHPUnit_Runner_Version::getVersionString());

        if ($suite and $test) {
            $codecept->runSuite($suite, $test);
        }

        if (!$test) {
            foreach ($suites as $suite) {
                $codecept->runSuite($suite);
            }
        }

        $codecept->printResult();

        if (!$input->getOption('no-exit')) {
            if ($codecept->getResult()->failureCount() or $codecept->getResult()->errorCount()) exit(1);
        }
    }
}
