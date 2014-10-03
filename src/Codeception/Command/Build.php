<?php
namespace Codeception\Command;

use Codeception\Configuration;
use Codeception\Lib\Generator\Actor as ActorGenerator;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;

/**
 * Generates Actor classes (initially Guy classes) from suite configs.
 * Starting from Codeception 2.0 actor classes are auto-generated. Use this command to generate them manually.
 *
 * * `codecept build`
 * * `codecept build path/to/project`
 *
 */
class Build extends Command
{
    use Shared\Config;
    use Shared\FileSystem;

    protected $inheritedMethodTemplate = ' * @method void %s(%s)';

    /**
     * @var OutputInterface
     */
    protected $output;

    public function getDescription()
    {
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
        $this->output = $output;
        $this->buildActorsForConfig($input->getOption('config'));
    }

    protected function buildActorsForConfig($configFile)
    {
        $config = $this->getGlobalConfig($configFile);
        $suites = $this->getSuites($configFile);

        $path = pathinfo($configFile);
        $dir = isset($path['dirname']) ? $path['dirname'] : getcwd();

        foreach ($config['include'] as $subConfig) {
            $this->output->writeln("<comment>Included Configuration: $subConfig</comment>");
            $this->buildActorsForConfig($dir . DIRECTORY_SEPARATOR . $subConfig);
        }

        if (!empty($suites)) {
            $this->output->writeln("<info>Building Actor classes for suites: ".implode(', ', $suites).'</info>');
        }
        foreach ($suites as $suite) {
            $settings = $this->getSuiteConfig($suite, $configFile);
            $gen = new ActorGenerator($settings);
            $this->output->writeln('<info>'.Configuration::config()['namespace'].'\\'.$gen->getActorName() . "</info> includes modules: ".implode(', ',$gen->getModules()));
            $contents = $gen->produce();

            @mkdir($settings['path'],0755, true);
            $file = $settings['path'].$this->getClassName($settings['class_name']).'.php';
            $this->save($file, $contents, true);
            $this->output->writeln("{$settings['class_name']}.php generated successfully. ".$gen->getNumMethods()." methods added");
        }
    }

}
