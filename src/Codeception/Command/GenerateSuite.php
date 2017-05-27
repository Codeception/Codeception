<?php
namespace Codeception\Command;

use Codeception\Configuration;
use Codeception\Lib\Generator\Helper;
use Codeception\Util\Template;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Codeception\Lib\Generator\Actor as ActorGenerator;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

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
    use Shared\FileSystem;
    use Shared\Config;
    use Shared\Style;

    protected function configure()
    {
        $this->setDefinition([
            new InputArgument('suite', InputArgument::REQUIRED, 'suite to be generated'),
            new InputArgument('actor', InputArgument::OPTIONAL, 'name of new actor class'),
        ]);
    }

    public function getDescription()
    {
        return 'Generates new test suite';
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->addStyles($output);
        $suite = $input->getArgument('suite');
        $actor = $input->getArgument('actor');

        if ($this->containsInvalidCharacters($suite)) {
            $output->writeln("<error>Suite name '$suite' contains invalid characters. ([A-Za-z0-9_]).</error>");
            return;
        }

        $config = $this->getGlobalConfig();
        if (!$actor) {
            $actor = ucfirst($suite) . $config['actor_suffix'];
        }

        $dir = Configuration::testsDir();
        if (file_exists($dir . $suite . '.suite.yml')) {
            throw new \Exception("Suite configuration file '$suite.suite.yml' already exists.");
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

        $helperName = ucfirst($suite);

        $file = $this->createDirectoryFor(
            Configuration::supportDir() . "Helper",
            "$helperName.php"
        ) . "$helperName.php";

        $gen = new Helper($helperName, $config['namespace']);
        // generate helper
        $this->createFile(
            $file,
            $gen->produce()
        );

        $output->writeln("Helper <info>" . $gen->getHelperName() . "</info> was created in $file");

        $yamlSuiteConfigTemplate = <<<EOF
actor: {{actor}}
modules:
    enabled:
        - {{helper}}
EOF;

        $this->createFile(
            $dir . $suite . '.suite.yml',
            $yamlSuiteConfig = (new Template($yamlSuiteConfigTemplate))
                ->place('actor', $actor)
                ->place('helper', $gen->getHelperName())
                ->produce()
        );

        Configuration::append(Yaml::parse($yamlSuiteConfig));
        $actorGenerator = new ActorGenerator(Configuration::config());

        $content = $actorGenerator->produce();

        $file = $this->createDirectoryFor(
            Configuration::supportDir(),
            $actor
        ) . $this->getShortClassName($actor);
        $file .=  '.php';

        $this->createFile($file, $content);

        $output->writeln("Actor <info>" . $actor . "</info> was created in $file");

        $output->writeln("Suite config <info>$suite.suite.yml</info> was created.");
        $output->writeln(' ');
        $output->writeln("Next steps:");
        $output->writeln("1. Edit <bold>$suite.suite.yml</bold> to enable modules for this suite");
        $output->writeln("2. Create first test with <bold>generate:cest testName</bold> ( or test|cept) command");
        $output->writeln("3. Run tests of this suite with <bold>codecept run $suite</bold> command");

        $output->writeln("<info>Suite $suite generated</info>");
    }

    private function containsInvalidCharacters($suite)
    {
        return preg_match('#[^A-Za-z0-9_]#', $suite) ? true : false;
    }
}
