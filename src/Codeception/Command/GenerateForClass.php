<?php
namespace Codeception\Command;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class GenerateForClass extends Base
{
    protected $template  = "<?php\n\$I = new %s(\$scenario);\n\$I->testMethod('%s');\n";

    protected function configure()
    {
        $this->setDefinition(array(

            new \Symfony\Component\Console\Input\InputArgument('suite', InputArgument::REQUIRED, 'suite where tests will be put'),
            new \Symfony\Component\Console\Input\InputArgument('class', InputArgument::REQUIRED, 'class to be tested'),
        ));
        parent::configure();
    }

    public function getDescription() {
        return 'Generates empty test file in suite';
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->initCodeception();
        $suite = $input->getArgument('suite');
        $class = $input->getArgument('class');

        if (!isset($this->suites[$suite])) throw new \Exception("Suite $suite not declared");

        $guy = $this->suites[$suite]['class_name'];

        if (isset($this->suites[$suite]['bootstrap'])) {
            if (file_exists($this->suites[$suite]['bootstrap']))
                require_once $this->suites[$suite]['bootstrap'];
        }

        if (!class_exists($class, true)) {
            throw new \Exception("Class $class is not loaded. Please, add autoloader to suite bootstrap");
        }

        $namespaces = explode('\\', $class);
        $path = $this->config['paths']['tests'].DIRECTORY_SEPARATOR.$suite;
        foreach ($namespaces as $namespace) {
            $path .= DIRECTORY_SEPARATOR.$namespace;
            @mkdir($path);
        }

        $reflected = new \ReflectionClass($class);
        $methods = $reflected->getMethods(\ReflectionMethod::IS_PUBLIC);
        foreach ($methods as $method) {
            if ($method->getDeclaringClass()->name != $class) continue;
            if ($method->isConstructor() or $method->isDestructor()) continue;

                $filename = $path.DIRECTORY_SEPARATOR.$method->name.'Cept.php';
                if (file_exists($filename)) {
                    $output->writeln("<comment>Test $filename already exists</comment>");
                    continue;
                }

                if (!$method->isStatic()) {
                    $file = sprintf($this->template, $guy, $class.'.'.$method->name);
                    file_put_contents($filename, $file);
                } else {
                    $file = sprintf($this->template, $guy, $class.'::'.$method->name);
                    file_put_contents($filename, $file);
                }
                $output->writeln("<info>Test was generated in $filename</info>");
        }

    }

}