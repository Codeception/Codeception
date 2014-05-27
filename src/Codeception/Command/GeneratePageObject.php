<?php
namespace Codeception\Command;

use Codeception\Configuration;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Codeception\Lib\Generator\PageObject as PageObjectGenerator;

/**
 * Generates PageObject. Can be generated either globally, or just for one suite.
 * If PageObject is generated globally it will act as UIMap, without any logic in it.
 *
 * * `codecept g:page Login`
 * * `codecept g:page Registration`
 * * `codecept g:page acceptance Login`
 */
class GeneratePageObject extends Command
{
    use Shared\FileSystem;
    use Shared\Config;

    protected function configure()
    {
        $this->setDefinition(array(

            new InputArgument('suite', InputArgument::REQUIRED, 'Either suite name or page object name)'),
            new InputArgument('page', InputArgument::OPTIONAL, 'Page name of pageobject to represent'),
            new InputOption('config', 'c', InputOption::VALUE_OPTIONAL, 'Use custom path for config'),
        ));
        parent::configure();
    }

    public function getDescription() {
        return 'Generates empty PageObject class';
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $suite = $input->getArgument('suite');
        $class = $input->getArgument('page');

        if (!$class) {
            $class = $suite;
            $suite = null;
        }

        $conf = $suite
            ? $this->getSuiteConfig($suite, $input->getOption('config'))
            : $this->getGlobalConfig($input->getOption('config'));

        $className = $this->getClassName($class);

        $filename = $suite
            ? $this->pathToSuitePageObject($conf, $className)
            : $this->pathToGlobalPageObject($conf, $className);

        $gen = new PageObjectGenerator($conf, $class);
        $res = $this->save($filename, $gen->produce());

        if (!$res) {
            $output->writeln("<error>PageObject $filename already exists</error>");
            exit;
        }
        $output->writeln("<info>PageObject was created in $filename</info>");
    }

    protected function pathToGlobalPageObject($config, $class)
    {
        $path = $this->buildPath(Configuration::projectDir().$config['paths']['tests'].'/_pages/', $class);
        $filename = $this->completeSuffix($class, 'Page');
        $this->introduceAutoloader(Configuration::projectDir().$config['paths']['tests'].DIRECTORY_SEPARATOR.$config['settings']['bootstrap'],'Page','_pages');
        return  $path.$filename;
    }

    protected function pathToSuitePageObject($config, $class)
    {
        $path = $this->buildPath($config['path'].'/_pages/', $class);
        $filename = $this->completeSuffix($class, 'Page');
        $this->introduceAutoloader($config['path'].DIRECTORY_SEPARATOR.$config['bootstrap'],'Page','_pages');
        return  $path.$filename;
    }

}
