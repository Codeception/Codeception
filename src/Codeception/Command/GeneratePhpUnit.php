<?php

namespace Codeception\Command;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class GeneratePhpUnit extends Base {

    protected $template  = <<<EOF
<?php
%s
%s %sTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
    }

    protected function tearDown()
    {
    }

    // tests
    public function testMe()
    {
    }

}
EOF;

    protected function configure()
    {
        $this->setDefinition(array(

            new InputArgument('suite', InputArgument::REQUIRED, 'suite where tests will be put'),
            new InputArgument('class', InputArgument::REQUIRED, 'class name'),
            new InputOption('config', 'c', InputOption::VALUE_OPTIONAL, 'Use custom path for config'),
        ));
        parent::configure();
    }

    public function getDescription() {
        return 'Generates empty PHPUnit test without Codeception additions';
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $suite = $input->getArgument('suite');
        $class = $input->getArgument('class');

        $suiteconf = $this->getSuiteConfig($suite, $input->getOption('config'));

        $classname = $this->getClassName($class);
        $path = $this->buildPath($suiteconf['path'], $class);
        $ns = $this->getNamespaceString($class);

        $filename = $this->completeSuffix($classname, 'Test');
        $filename = $path.DIRECTORY_SEPARATOR.$filename;

        $classname = $this->removeSuffix($classname, 'Test');

        $res = $this->save($filename, sprintf($this->template, $ns, 'class', $classname));
        if (!$res) {
            $output->writeln("<error>Test $filename already exists</error>");
            exit;
        }

        $output->writeln("<info>Test was created in $filename</info>");
    }

}

