<?php
namespace Codeception\Command;

use Codeception\Configuration;
use Codeception\Lib\Generator\Actions as ActionsGenerator;
use Codeception\Lib\Generator\Actor as ActorGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $this->buildActorsForConfig();
    }
    
    private function buildActor(array $settings)
    {
        $actorGenerator = new ActorGenerator($settings);
        $this->output->writeln(
            '<info>' . Configuration::config()['namespace'] . '\\' . $actorGenerator->getActorName()
            . "</info> includes modules: " . implode(', ', $actorGenerator->getModules())
        );
        
        $content = $actorGenerator->produce();

        $file = $this->buildPath(
            Configuration::supportDir(),
            $settings['class_name']
        ) . $this->getClassName($settings['class_name']);
        $file .=  '.php';
        return $this->save($file, $content);
    }
    
    private function buildActions(array $settings)
    {
        $actionsGenerator = new ActionsGenerator($settings);
        $this->output->writeln(
            " -> {$settings['class_name']}Actions.php generated successfully. "
            . $actionsGenerator->getNumMethods() . " methods added"
        );
        
        $content = $actionsGenerator->produce();
        
        $file = $this->buildPath(Configuration::supportDir() . '_generated', $settings['class_name']);
        $file .= $this->getClassName($settings['class_name']) . 'Actions.php';
        return $this->save($file, $content, true);
    }

    private function buildSuiteActors()
    {
        $suites = $this->getSuites();
        if (!empty($suites)) {
            $this->output->writeln("<info>Building Actor classes for suites: " . implode(', ', $suites) . '</info>');
        }
        foreach ($suites as $suite) {
            $settings = $this->getSuiteConfig($suite);
            $this->buildActions($settings);
            $actorBuilt = $this->buildActor($settings);
            
            if ($actorBuilt) {
                $this->output->writeln("{$settings['class_name']}.php created.");
            }
        }
    }
    
    protected function buildActorsForConfig($configFile = null)
    {
        $config = $this->getGlobalConfig($configFile);
        
        $dir = Configuration::projectDir();
        $this->buildSuiteActors();

        foreach ($config['include'] as $subConfig) {
            $this->output->writeln("\n<comment>Included Configuration: $subConfig</comment>");
            $this->buildActorsForConfig($dir . DIRECTORY_SEPARATOR . $subConfig);
        }
    }
}
