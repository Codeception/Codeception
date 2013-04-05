<?php
namespace Codeception\Command;

use Codeception\AbstractGuy;
use Codeception\Event\Suite;
use Codeception\Scenario;
use Codeception\SuiteManager;
use Codeception\TestCase\Cept;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Console extends Base {

    protected $test;
    protected $codecept;
    protected $suite;


    protected function configure()
    {
        $this->setDefinition(array(
            new InputArgument('suite', InputArgument::REQUIRED, 'suite to be executed'),
            new InputOption('config', 'c', InputOption::VALUE_OPTIONAL, 'Use custom path for config'),
            new InputOption('colors', '', InputOption::VALUE_NONE, 'Use colors in output'),
        ));
        parent::configure();
    }

    public function getDescription()
    {
        return 'Launches interactive test console';
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $suiteName = $input->getArgument('suite');
        $this->output = $output;

        $config = \Codeception\Configuration::config($input->getOption('config'));
        $settings = \Codeception\Configuration::suiteSettings($suiteName, $config);

        $options = $input->getOptions();
        $options['debug'] = true;
        $options['steps'] = true;

        $this->codecept = new \Codeception\Codecept($options);
        $dispatcher = $this->codecept->getDispatcher();
        $suiteManager = new SuiteManager($dispatcher, $suiteName, $settings);
        $this->suite = $suiteManager->getSuite();
        $this->test = new Cept($dispatcher, array('name' => 'interactive', 'file' => 'interactive'));

        $guy = $settings['class_name'];
        $scenario = new Scenario($this->test);
        $I = new $guy($scenario);
        
        $this->listenToSignals();

        $output->writeln("<info>Interactive console started for suite $suiteName</info>");
        $output->writeln("<info>Try Codeception commands without writing a test</info>");
        $output->writeln("<info>type 'exit' to leave console</info>");

        $dispatcher->dispatch('suite.before', new Suite($this->suite, $this->codecept->getResult(), $settings));
        $dispatcher->dispatch('test.parsed', new \Codeception\Event\Test($this->test));
        $dispatcher->dispatch('test.before', new \Codeception\Event\Test($this->test));

        $output->writeln("\n\n\$I = new {$settings['class_name']}(\$scenario);");
        $scenario->run();
        
        $this->executeCommands($output, $I, $settings['bootstrap']);
        $dispatcher->dispatch('test.after', new \Codeception\Event\Test($this->test));
        $dispatcher->dispatch('suite.after', new Suite($this->suite));

        $output->writeln("<info>Bye-bye!</info>");
    }


    protected function executeCommands(OutputInterface $output, AbstractGuy $I, $bootstrap)
    {
        $dialog = $this->getHelperSet()->get('dialog');

        if (file_exists($bootstrap)) require $bootstrap;

        do {
            $command = $dialog->ask($output, '$I->');
            if ($command == 'exit') return;
            if ($command == '') continue;
            try {
                eval("\$I->$command;");
            } catch (\PHPUnit_Framework_AssertionFailedError $fail) {
                $output->writeln("<error>fail</error> ".$fail->getMessage());
            } catch (\Exception $e) {
                $output->writeln("<error>error</error> ".$e->getMessage());
            }

        } while (true);
    }

    protected function listenToSignals()
    {
        if (function_exists('pcntl_signal')) {
            declare(ticks = 1);
            pcntl_signal(SIGINT,  function () {});
            pcntl_signal(SIGTERM, function () {});
        }
    }

}