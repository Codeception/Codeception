<?php
namespace Codeception\Command;

use Codeception\Lib\Generator\Actor as GuyGenerator;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;
use \Symfony\Component\Console\Helper\DialogHelper;

/**
 * Generates Actor classes (initially Guy classes) from suite configs.
 * Starting from Codeception 2.0 actor classes are auto-generated. Use this command to generate them manually.
 *
 * `codecept build`
 * `codecept build path/to/project`
 *
 */
class Build extends Base
{

    protected $inheritedMethodTemplate = ' * @method void %s(%s)';

    public function getDescription() {
        return 'Generates base classes for all suites';
    }

    protected function configure()
    {
        $this->setDefinition(array(
            new InputOption('config', 'c', InputOption::VALUE_OPTIONAL, 'Use custom path for config'),
        ));
    }

	protected function execute(InputInterface $input, OutputInterface $output)
	{
        $suites = $this->getSuites($input->getOption('config'));

        $output->writeln("<info>Building Guy classes for suites: ".implode(', ', $suites).'</info>');

        foreach ($suites as $suite) {
            $settings = $this->getSuiteConfig($suite, $input->getOption('config'));
            $gen = new GuyGenerator($settings);
            $output->writeln('<info>'.$gen->getGuy() . "</info> includes modules: ".implode(', ',$gen->getModules()));
            $contents = $gen->produce();

            $file = $settings['path'].$this->getClassName($settings['class_name']).'.php';
            $this->save($file, $contents, true);
            $output->writeln("{$settings['class_name']}.php generated successfully. ".$gen->getNumMethods()." methods added");
        }
    }

}
