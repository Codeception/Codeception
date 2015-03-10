<?php
namespace Codeception\Command;

use Codeception\Lib\Generator\Helper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
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

    protected function configure()
    {
        $this->setDefinition(
            [
                new InputArgument('suite', InputArgument::REQUIRED, 'suite to be generated'),
                new InputArgument('actor', InputArgument::OPTIONAL, 'name of new actor class'),
                new InputOption('config', 'c', InputOption::VALUE_OPTIONAL, 'Use custom path for config'),
            ]
        );
    }

    public function getDescription()
    {
        return 'Generates new test suite';
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $suite = lcfirst($input->getArgument('suite'));
        $actor = $input->getArgument('actor');

        if ($this->containsInvalidCharacters($suite)) {
            $output->writeln("<error>Suite name '$suite' contains invalid characters. ([A-Za-z0-9_]).</error>");
            return;
        }

        $config = \Codeception\Configuration::config($input->getOption('config'));
        if (!$actor) {
            $actor = ucfirst($suite) . $config['actor'];
        }
        $config['class_name'] = $actor;

        $dir = \Codeception\Configuration::testsDir();
        if (file_exists($dir . $suite . '.suite.yml')) {
            throw new \Exception("Suite configuration file '$suite.suite.yml' already exists.");
        }

        $this->buildPath($dir . $suite . DIRECTORY_SEPARATOR, '_bootstrap.php');

        // generate bootstrap
        $this->save(
            $dir . $suite . DIRECTORY_SEPARATOR . '_bootstrap.php',
            "<?php\n// Here you can initialize variables that will be available to your tests\n",
            true
        );
        $actorName = $this->removeSuffix($actor, $config['actor']);

        $file = $this->buildPath(\Codeception\Configuration::supportDir() . "Helper", "$actorName.php") . "$actorName.php";

        $gen = new Helper($actorName, $config['namespace']);
        // generate helper
        $this->save(
            $file,
            $gen->produce()
        );

        $conf = [
            'class_name' => $actorName . $config['actor'],
            'modules'    => [
                'enabled' => [$gen->getHelperName()]
            ],
        ];

        $this->save($dir . $suite . '.suite.yml', Yaml::dump($conf, 2));

        $output->writeln("<info>Suite $suite generated</info>");
    }

    private function containsInvalidCharacters($suite)
    {
        return preg_match('#[^A-Za-z0-9_]#', $suite) ? true : false;
    }

}