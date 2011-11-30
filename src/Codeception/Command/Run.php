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

            new \Symfony\Component\Console\Input\InputOption('report', '', InputOption::VALUE_NONE, 'Show output in compact style'),
            new \Symfony\Component\Console\Input\InputOption('html', '', InputOption::VALUE_NONE, 'Generate html with results'),
            new \Symfony\Component\Console\Input\InputOption('colors', '', InputOption::VALUE_NONE, 'Use colors in output'),
            new \Symfony\Component\Console\Input\InputOption('silent', '', InputOption::VALUE_NONE, 'Use colors in output'),
            new \Symfony\Component\Console\Input\InputOption('debug', '', InputOption::VALUE_NONE, 'Show debug and scenario output')
        ));
        parent::configure();
    }

    public function getDescription()
    {
        return 'Runs the test suites';
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->initCodeception();

        ini_set('memory_limit', isset($this->config['settings']['memory_limit']) ? $this->config['settings']['memory_limit'] : '1024M');

        $options = $input->getOptions();
        if ($input->getArgument('test')) $options['debug'] = true;

        $suite = $input->getArgument('suite');
        $test = $input->getArgument('test');

        if ($suite) $this->suites = array($suite => $this->suites[$suite]);

        $codecept = new \Codeception\Codecept($this->config, $options);
        $options = $codecept->getOptions();

        $output->writeln(\Codeception\Codecept::versionString() . "\nPowered by " . \PHPUnit_Runner_Version::getVersionString());



        if ($suite and $test) {
            $output->writeln("Running <comment>{$test}Spec.php</comment>:");
            $codecept->runSuite($suite, $this->suites[$suite], $test);
        }

        if (!$test) {
            foreach ($this->suites as $suite => $settings) {
                if (!$options['silent']) {
                    $guy = isset($settings['class_name']) ? $settings['class_name'] : 'NullGuy';
                    $output->write("\n\n# <comment>$guy's $suite tests</comment> started");
                }
                $codecept->runSuite($suite, $settings, $test);
            }
        }

        $codecept->getRunner()->getPrinter()->printResult($codecept->getResult());


        if ($codecept->getResult()->failures() > 0) exit(1);

    }
}
