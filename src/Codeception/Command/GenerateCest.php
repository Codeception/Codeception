<?php
namespace Codeception\Command;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class GenerateCest extends Base
{
    protected $template  = <<<EOF
<?php
%suse Codeception\Util\Stub;

%s %sCest
{
    protected $%s = '%s';

    public function _before()
    {
    }

    public function _after()
    {
    }

    // tests
    %s

}
EOF;

    protected $methodTemplate = "public function %s(\\%s %s) {\n    \n    }";

    protected function configure()
    {
        $this->setDefinition(array(

            new \Symfony\Component\Console\Input\InputArgument('suite', InputArgument::REQUIRED, 'suite where tests will be put'),
            new \Symfony\Component\Console\Input\InputArgument('class', InputArgument::REQUIRED, 'test name'),
        ));
        parent::configure();
    }

    public function getDescription() {
        return 'Generates empty Cest file in suite';
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $suite = $input->getArgument('suite');
        $class = $input->getArgument('class');

        $config = \Codeception\Configuration::config($input->getOption('config'));
        $suiteconf = \Codeception\Configuration::suiteSettings($suite, $config);

        $guy = $suiteconf['class_name'];

        $classname = $this->getClassName($class);
        $path = $this->buildPath($suiteconf['path'], $class);
        $ns = $this->getNamespaceString($class);

        $filename = $this->completeSuffix($classname, 'Cest');
        $filename = $path.DIRECTORY_SEPARATOR.$filename;

        if (file_exists($filename)) {
            $output->writeln("<error>Test $filename already exists</error>");
            exit;
        }

        $tests = sprintf($this->methodTemplate, "shouldBe", $guy, '$I');

        file_put_contents($filename, sprintf($this->template, $ns, 'class', $classname, 'class', $class, $tests));

        $output->writeln("<info>Cest was created in $filename</info>");

    }
}
