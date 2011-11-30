<?php
namespace Codeception\Command;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class GenerateCept extends Base
{
    protected $template  = "<?php\n\$I = new %s(\$scenario);\n\$I->wantTo('perform actions and see result');\n";

    protected function configure()
    {
        $this->setDefinition(array(

            new \Symfony\Component\Console\Input\InputArgument('suite', InputArgument::REQUIRED, 'suite to be tested'),
            new \Symfony\Component\Console\Input\InputArgument('test', InputArgument::REQUIRED, 'test to be run'),
        ));
        parent::configure();
    }

    public function getDescription() {
        return 'Generates empty test file in suite';
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->initCodeception();
        $suite = $input->getArgument('suite');
        if (!isset($this->suites[$suite])) throw new \Exception("Suite $suite not declared");

        $guy = $this->suites[$suite]['class_name'];

        $file = sprintf($this->template, $guy);
        $filename = $input->getArgument('test');

        $filename = $this->config['paths']['tests'].DIRECTORY_SEPARATOR.$suite.DIRECTORY_SEPARATOR.$filename;
        if (file_exists($filename)) {
            $output->writeln("<comment>Test $filename already exists</comment>");
            return;
        }

        if (strpos(strrev($filename), strrev('Cept.php')) !== 0) $filename .= 'Cept.php';
        if (strpos(strrev($filename), strrev('.php')) !== 0) $filename .= '.php';

        $dirs = explode(DIRECTORY_SEPARATOR, dirname($filename));
        $path = '';
        foreach ($dirs as $dir) {
            $path .= DIRECTORY_SEPARATOR.$dir;
            @mkdir($path);
        }

        file_put_contents($filename, $file);
        $output->writeln("<info>Test was generated in $filename</info>");
    }


}
