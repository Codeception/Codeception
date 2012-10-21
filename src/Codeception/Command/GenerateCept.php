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
        return 'Generates empty Cept file in suite';
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $suite = $input->getArgument('suite');
        $filename = $input->getArgument('test');

        $config = \Codeception\Configuration::config();
        $suiteconf = \Codeception\Configuration::suiteSettings($suite, $config);

        $guy = $suiteconf['class_name'];

        $file = sprintf($this->template, $guy);

        if (file_exists($suiteconf['path'].DIRECTORY_SEPARATOR.$filename)) {
            $output->writeln("<comment>Test $filename already exists</comment>");
            return;
        }

        if (strpos(strrev($filename), strrev('Cept')) === 0) $filename .= '.php';
        if (strpos(strrev($filename), strrev('Cept.php')) !== 0) $filename .= 'Cept.php';
        if (strpos(strrev($filename), strrev('.php')) !== 0) $filename .= '.php';

        $filename = str_replace('\\','/', $filename);
        $dirs = explode('/', $filename);
        array_pop($dirs);
        $path = $suiteconf['path'].DIRECTORY_SEPARATOR;
        foreach ($dirs as $dir) {
            $path .= $dir.DIRECTORY_SEPARATOR;
            @mkdir($path);
        }

        file_put_contents($suiteconf['path'].DIRECTORY_SEPARATOR.$filename, $file);
        $output->writeln("<info>Test was generated in $filename</info>");
    }


}
