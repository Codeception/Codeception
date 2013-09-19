<?php
namespace Codeception\Command;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;


class GenerateCept extends Base
{
    protected $template  = "<?php%s\n\$I = new %s(\$scenario);\n\$I->wantTo('perform actions and see result');\n";

    protected function configure()
    {
        $this->setDefinition(array(
            new InputArgument('suite', InputArgument::REQUIRED, 'suite to be tested'),
            new InputArgument('test', InputArgument::REQUIRED, 'test to be run'),
            new InputOption('config', 'c', InputOption::VALUE_OPTIONAL, 'Use custom path for config'),
        ));
        parent::configure();
    }

    public function getDescription() {
        return 'Generates empty Cept file in suite';
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $suite = $input->getArgument('suite');
        $filename = $input->getArgument('test');

        $suiteconf = $this->getSuiteConfig($suite, $input->getOption('config'));

        $guy = $suiteconf['class_name'];
        $use = $suiteconf['namespace'] ? " use ".$suiteconf['namespace'].'\\'.$guy.";\n" : '';

        $file = sprintf($this->template, $use, $guy);

        $this->buildPath($suiteconf['path'], $filename);

        $filename = $this->completeSuffix($filename, 'Cept');

        $res = $this->save($suiteconf['path'].DIRECTORY_SEPARATOR . $filename, $file);
        if (!$res) {
            $output->writeln("<error>Test $filename already exists</error>");
            return;
        }
        $output->writeln("<info>Test was created in $filename</info>");
    }


}
