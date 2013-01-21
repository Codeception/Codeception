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
    protected $template  = "<?php\n%s %sCest\n{\n    // the class name you want to test\n    public %s = '';\n\n%s\n}\n";
    protected $methodTemplate = "    // sample test\n    public function %s(\\%s %s) {\n    \n    }";

    protected function configure()
    {
        $this->setDefinition(array(

            new \Symfony\Component\Console\Input\InputArgument('suite', InputArgument::REQUIRED, 'suite where tests will be put'),
            new \Symfony\Component\Console\Input\InputArgument('name', InputArgument::REQUIRED, 'test name'),
        ));
        parent::configure();
    }

    public function getDescription() {
        return 'Generates empty Cest file in suite';
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $suite = $input->getArgument('suite');
        $testName = $input->getArgument('name');

        $config = \Codeception\Configuration::config();
        $suiteconf = \Codeception\Configuration::suiteSettings($suite, $config);

        $guy = $suiteconf['class_name'];

        $classname = $this->getClassName($testName);

        $path = $this->buildPath($suiteconf['path'], $testName);

        $filename = $this->completeSuffix($testName, 'Cest');

        $filename = $path.DIRECTORY_SEPARATOR . $filename;

        if (file_exists($filename)) {
            $output->writeln("<error>Test $filename already exists</error>");
            exit;
        }

//        $methods = $reflected->getMethods(\ReflectionMethod::IS_PUBLIC);
//        foreach ($methods as $method) {
//            if ($method->getDeclaringClass()->name != $class) continue;
//            if ($method->isConstructor() or $method->isDestructor()) continue;
//
//            $tests[] = sprintf($this->methodTemplate, $classname, $method->name,$method->name, $guy, '$I');
//        }

        $tests = sprintf($this->methodTemplate, "shouldBe", $guy, '$I');

        file_put_contents($filename, sprintf($this->template, 'class', $classname,'$class', $tests));

        $output->writeln("<info>Cest was created in $filename</info>");

    }
}