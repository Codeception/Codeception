<?php

declare(strict_types=1);

namespace Codeception\Command;

use Codeception\Configuration;
use Codeception\Lib\Generator\Actions as ActionsGenerator;
use Codeception\Lib\Generator\Actor as ActorGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface as SymfonyOutputInterface;

use function implode;

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
    use Shared\ConfigTrait;
    use Shared\FileSystemTrait;

    protected string $inheritedMethodTemplate = ' * @method void %s(%s)';

    protected ?SymfonyOutputInterface $output = null;

    public function getDescription(): string
    {
        return 'Generates base classes for all suites';
    }

    protected function execute(InputInterface $input, SymfonyOutputInterface $output): int
    {
        $this->output = $output;
        $this->buildActorsForConfig();
        return Command::SUCCESS;
    }

    private function buildActor(array $settings): bool
    {
        $actorGenerator = new ActorGenerator($settings);
        $this->output->writeln(
            '<info>' . Configuration::config()['namespace'] . '\\' . $actorGenerator->getActorName()
            . "</info> includes modules: " . implode(', ', $actorGenerator->getModules())
        );

        $content = $actorGenerator->produce();

        $file = $this->createDirectoryFor(
            Configuration::supportDir(),
            $settings['actor']
        ) . $this->getShortClassName($settings['actor']);
        $file .=  '.php';
        return $this->createFile($file, $content);
    }

    private function buildActions(array $settings): bool
    {
        $actionsGenerator = new ActionsGenerator($settings);
        $content = $actionsGenerator->produce();
        $this->output->writeln(
            sprintf(' -> %sActions.php generated successfully. ', $settings['actor'])
            . $actionsGenerator->getNumMethods() . " methods added"
        );

        $file = $this->createDirectoryFor(Configuration::supportDir() . '_generated', $settings['actor']);
        $file .= $this->getShortClassName($settings['actor']) . 'Actions.php';
        return $this->createFile($file, $content, true);
    }

    private function buildSuiteActors(): void
    {
        $suites = $this->getSuites();
        if ($suites !== []) {
            $this->output->writeln("<info>Building Actor classes for suites: " . implode(', ', $suites) . '</info>');
        }
        foreach ($suites as $suite) {
            $settings = $this->getSuiteConfig($suite);
            if (!$settings['actor']) {
                continue; // no actor
            }
            $this->buildActions($settings);
            $actorBuilt = $this->buildActor($settings);

            if ($actorBuilt) {
                $this->output->writeln($settings['actor'] . '.php created.');
            }
        }
    }

    protected function buildActorsForConfig(?string $configFile = null): void
    {
        $config = $this->getGlobalConfig($configFile);

        $dir = Configuration::projectDir();
        $this->buildSuiteActors();

        foreach ($config['include'] as $subConfig) {
            $this->output->writeln("\n<comment>Included Configuration: {$subConfig}</comment>");
            $this->buildActorsForConfig($dir . DIRECTORY_SEPARATOR . $subConfig);
        }
    }
}
