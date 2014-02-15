<?php
namespace Codeception\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Codeception\Lib\Generator\Group as GroupGenerator;


class GenerateGroup extends Base
{
    protected function configure()
    {
        $this->setDefinition(array(
            new InputArgument('group', InputArgument::REQUIRED, 'Group class name'),
            new InputOption('config', 'c', InputOption::VALUE_OPTIONAL, 'Use custom path for config'),
        ));
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $config = $this->getGlobalConfig($input->getOption('config'));
        $group = $input->getArgument('group');

        $class = ucfirst($group);
        $path = $this->buildPath($config['paths']['tests'].'/_groups/', $class);
        $filename = $this->completeSuffix($class, 'Group');
        $filename = $path.$filename;

        $this->introduceAutoloader($config['paths']['tests'].DIRECTORY_SEPARATOR.$config['settings']['bootstrap'],'Group','_groups');

        $gen = new GroupGenerator($config, $group);
        $res = $this->save($filename, $gen->produce());

        if (!$res) {
            $output->writeln("<error>Group $filename already exists</error>");
            return;
        }
        
        $output->writeln("<info>Group extension was created in $filename</info>");
        $output->writeln('To use this group extension, include it to "extensions" option of global Codeception config.');
    }

}