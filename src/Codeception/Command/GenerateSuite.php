<?php

declare(strict_types=1);

namespace Codeception\Command;

use Codeception\Configuration;
use Codeception\Lib\Generator\Actor as ActorGenerator;
use Codeception\Util\Template;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

use function file_exists;
use function preg_match;
use function ucfirst;

/**
 * Create new test suite. Requires suite name and actor name
 *
 * * ``
 * * `codecept g:suite api` -> api + ApiTester
 * * `codecept g:suite integration Code` -> integration + CodeTester
 * * `codecept g:suite frontend Front` -> frontend + FrontTester
 *
 */
class GenerateSuite extends Command
{
    use Shared\FileSystemTrait;
    use Shared\ConfigTrait;
    use Shared\StyleTrait;

    protected function configure(): void
    {
        $this->setDescription('Generates new test suite')
            ->addArgument('suite', InputArgument::REQUIRED, 'suite to be generated')
            ->addArgument('actor', InputArgument::OPTIONAL, 'name of new actor class');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->addStyles($output);
        $suite = ucfirst((string)$input->getArgument('suite'));
        $config = $this->getGlobalConfig();
        $actor = $input->getArgument('actor') ?: $suite . $config['actor_suffix'];

        if ($this->containsInvalidCharacters($suite)) {
            $output->writeln("<error>Suite name '{$suite}' contains invalid characters. ([A-Za-z0-9_]).</error>");
            return Command::FAILURE;
        }

        $dir = Configuration::testsDir();
        if (file_exists($dir . $suite . '.suite.yml')) {
            throw new Exception("Suite configuration file '{$suite}.suite.yml' already exists.");
        }

        $this->createDirectoryFor($dir . $suite);

        if ($config['settings']['bootstrap']) {
            // generate bootstrap file
            $this->createFile(
                $dir . $suite . DIRECTORY_SEPARATOR . $config['settings']['bootstrap'],
                "<?php\n",
                true
            );
        }

        $yamlSuiteConfigTemplate = <<<EOF
actor: {{actor}}
suite_namespace: {{suite_namespace}}
modules:
    # enable helpers as array
    enabled: []
EOF;
        $yamlSuiteConfig = (new Template($yamlSuiteConfigTemplate))
            ->place('actor', $actor)
            ->place('suite_namespace', $config['namespace'] . '\\' . $suite)
            ->produce();
        $this->createFile($dir . $suite . '.suite.yml', $yamlSuiteConfig);
        Configuration::append(Yaml::parse($yamlSuiteConfig));
        $actorGenerator = new ActorGenerator(Configuration::config());

        $content = $actorGenerator->produce();
        $file = $this->createDirectoryFor(Configuration::supportDir(), $actor) . $this->getShortClassName($actor) . '.php';
        $this->createFile($file, $content);

        $output->writeln("Actor <info>" . $actor . "</info> was created in {$file}");

        $output->writeln("Suite config <info>{$suite}.suite.yml</info> was created.");
        $output->writeln(' ');
        $output->writeln("Next steps:");
        $output->writeln("1. Edit <bold>{$suite}.suite.yml</bold> to enable modules for this suite");
        $output->writeln("2. Create first test with <bold>generate:cest testName</bold> ( or test|cept) command");
        $output->writeln("3. Run tests of this suite with <bold>codecept run {$suite}</bold> command");

        $output->writeln("<info>Suite {$suite} generated</info>");
        return Command::SUCCESS;
    }

    private function containsInvalidCharacters(string $suite): bool
    {
        return (bool)preg_match('#[^A-Za-z0-9_]#', $suite);
    }
}
