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
%s

%s %sCest
{

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

    protected $methodTemplate = "public function %s(%s %s) {\n    \n    }";

    protected function configure()
    {
        $this->setDefinition(array(

            new InputArgument('suite', InputArgument::REQUIRED, 'suite where tests will be put'),
            new InputArgument('class', InputArgument::REQUIRED, 'test name'),
            new InputOption('config', 'c', InputOption::VALUE_OPTIONAL, 'Use custom path for config'),
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

        $suiteconf = $this->getSuiteConfig($suite, $input->getOption('config'));

        $guy = $suiteconf['class_name'];

        $classname = $this->getClassName($class);
        $path = $this->buildPath($suiteconf['path'], $class);

        $ns = $this->getNamespaceString($suiteconf['namespace'].'\\'.$class);
        $ns .= "use ".$suiteconf['namespace'].'\\'.$guy.";";

        $filename = $this->completeSuffix($classname, 'Cest');
        $filename = $path.$filename;

        if (file_exists($filename)) {
            $output->writeln("<error>Test $filename already exists</error>");
            exit;
        }

        $classname = $this->removeSuffix($classname, 'Cest');

        $tests = sprintf($this->methodTemplate, "tryToTest", $guy, '$I');

        $res = $this->save($filename, sprintf($this->template, $ns, 'class', $classname, $tests));
        if (!$res) {
            $output->writeln("<error>Test $filename already exists</error>");
            return;
        }

        $output->writeln("<info>Test was created in $filename</info>");

    }
}
