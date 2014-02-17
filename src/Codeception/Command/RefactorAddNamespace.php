<?php
namespace Codeception\Command;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

/**
 * If you want to implement namespace to your test config (for multi-app), this command will scan your test classes and add the namespace.
 * Be careful running this.
 *
 * `codecept r:add-namespace Frontend` - applies Frontend namespace
 * `codecept r:add-namespace Frontend --silent`
 *
 *
 */
class RefactorAddNamespace extends Base {

    protected $namespace;

    public function getDescription() {
        return 'Introduces namespace into the current configuration';
    }

    protected function configure()
    {
        $this->setDefinition(array(
            new InputArgument('namespace', InputArgument::REQUIRED, 'namespace to add for guy classes and helpers'),
            new InputOption('silent', '',InputOption::VALUE_NONE, 'skip verification question'),
            new InputOption('config', 'c', InputOption::VALUE_OPTIONAL, 'Use custom path for config'),
        ));
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('This command adds namespaces to your Helper and Guy classes and Cepts');
        $output->writeln('It will files of your tests. Use with care.');
        $output->writeln('Please do not execute this command twice on a project.');

        $config = \Codeception\Configuration::config($input->getOption('config'));
        if ($config['namespace']) {
            $output->writeln("<info>Config already contains namespace, exiting....</info>");
            return;
        }

        if (!$input->getOption('silent')) {
            $dialog = $this->getHelperSet()->get('dialog');
            if (!$dialog->askConfirmation($output, '<question>Do you want to proceed?</question>', false)) {
                return;
            }
        }

        $this->namespace = $input->getArgument('namespace');

        $this->updateConfigs();
        $output->writeln("+ Config file updated with namespace {$this->namespace}");

        $this->getApplication()->find('build')->run(new ArrayInput(array('command' => 'build', '-c' => $input->getOption('config'))), $output);
        $output->writeln('+ Actor classes rebuilt');

        $this->updateHelpers();
        $output->writeln("+ Helpers updated with namespace {$this->namespace}\\Codeception\\Module)");

        $counter = $this->updateCepts();
        $output->writeln("+ Cept tests updated (total $counter files changed)");
        $output->writeln("\nNamespace {$this->namespace} injected to current tests");
    }

    protected function updateConfigs()
    {
        $config_file = \Codeception\Configuration::projectDir().'codeception.dist.yml';
        if (!file_exists($config_file)) {
            $config_file = \Codeception\Configuration::projectDir().'codeception.yml';
            if (!file_exists($config_file)) {
                throw new \RuntimeException("Config file $config_file does not exists");
            }
        }
        $config_body = file_get_contents($config_file);
        $prepend_line = "namespace: {$this->namespace}\n";
        $config_body = $prepend_line . $config_body;
        $this->save($config_file, $config_body, true);
    }

    protected function updateHelpers()
    {
        $helpers = Finder::create()->files()->name('*Helper.php')->in(\Codeception\Configuration::helpersDir());
        foreach ($helpers as $helper_file) {
            $helper_body = file_get_contents($helper_file);
            $helper_body = preg_replace('~namespace Codeception\\\\Module;~',"namespace {$this->namespace}\\Codeception\\Module;", $helper_body);
            $this->save($helper_file, $helper_body, true);
        }
    }

    protected function updateCepts()
    {
        $counter = 0;
        $suites = \Codeception\Configuration::suites();
        $config = \Codeception\Configuration::config();
        foreach ($suites as $suite) {
            $settings = \Codeception\Configuration::suiteSettings($suite, $config);
            $cepts = Finder::create()->files()->name('*Cept.php')->in($settings['path']);
            $prepend_line = "use {$this->namespace}\\{$settings['class_name']};\n\n";
            foreach ($cepts as $cept) {
                $cept_body = file_get_contents($cept);
                $cept_body = str_replace('<?php', '<?php '.$prepend_line,$cept_body);
                $this->save($cept, $cept_body, true);
                $counter++;
            }
        }
        return $counter;
    }
}
