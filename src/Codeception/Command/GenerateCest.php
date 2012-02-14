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
    protected $template  = "<?php\n%s\n%s %sCest\n{\n    public %s = '%s';\n\n%s\n}\n";
    protected $methodTemplate = "    // Test for %s.%s\n    public function %s(\\%s %s) {\n    \n    }";

    protected function configure()
    {
        $this->setDefinition(array(

            new \Symfony\Component\Console\Input\InputArgument('suite', InputArgument::REQUIRED, 'suite where tests will be put'),
            new \Symfony\Component\Console\Input\InputArgument('class', InputArgument::REQUIRED, 'class to be tested'),
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

        $config = \Codeception\Configuration::config();
        $suiteconf = \Codeception\Configuration::suiteSettings($suite, $config);

        $guy = $suiteconf['class_name'];

        if (isset($suiteconf['bootstrap'])) {
            if (file_exists($suiteconf['path'].$suiteconf['bootstrap'])) {
                require_once $suiteconf['path'].$suiteconf['bootstrap'];
            } else {
                throw new \RuntimeException($suiteconf['path'].$suiteconf['bootstrap']." couldn't be loaded");
            }
        }

        if (!class_exists($class, true)) {
            throw new \Exception("Class $class is not loaded. Please, add autoloader to suite bootstrap");
        }

        $namespaces = explode('\\', $class);
        $classname = array_pop($namespaces);

        $use = '';

        $path = $suiteconf['path'];
        foreach ($namespaces as $namespace) {
            $path .= DIRECTORY_SEPARATOR.$namespace;
            @mkdir($path);
        }

        if (!empty($namespaces)) $use = 'namespace '.implode('\\', $namespaces).";\n";

        $reflected = new \ReflectionClass($class);

        $filename = $path.DIRECTORY_SEPARATOR.$classname.'Cest.php';

        if (file_exists($filename)) {
            $output->writeln("<error>Test $filename already exists</error>");
            exit;
        }

        $tests = array();

        $methods = $reflected->getMethods(\ReflectionMethod::IS_PUBLIC);
        foreach ($methods as $method) {
            if ($method->getDeclaringClass()->name != $class) continue;
            if ($method->isConstructor() or $method->isDestructor()) continue;

            $tests[] = sprintf($this->methodTemplate, $classname, $method->name,$method->name, $guy, '$I');
        }

        $tests = implode("\n\n", $tests);

        file_put_contents($filename, sprintf($this->template, $use, 'class', $classname,'$class', $class, $tests));

        $output->writeln("<info>Cest for $class was created in $filename</info>");

    }
}