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

    /*
     * basic cest class template
     * @var string 
     */
    protected $template  =
'<?php

/**
 * {cestName}Cest class file.
 *
 * @author codeception
 * @version $Id$
 */
{namespace}

use Codeception\Util\Stub;

class {cestName}Cest
{

    /**
     * @param \Codeception\Event\Test $event
     */
    public function _before($event)
    {
	// called before each public method of this class
    }

    /**
     * @param \Codeception\Event\Test $event
     */
    public function _after($event)
    {
	//called after each public method of this class, even if test failed
    }

    /**
     * @param \Codeception\Event\Fail $event
     */
    public function _failed($event)
    {
	//called when test failed
    }

    // tests
    {testMethod}

}
';

    /*
     * basic cest class method template
     * @var string 
     */
    protected $methodTemplate = '
    /**
     *
     * @param \{guyClass} $I
     * @param \Codeception\Scenario $scenario
     */
    public function tryToTest(\{guyClass} $I, $scenario)
    {
        //$scenario->incomplete(\'not implemented yet\'); use this to mark scenario as incomplete
        //$scenario->skip(\'some important reason\');     use this to skip scenario

        $I->wantTo(\'Test index page of my application\');                      //describe your feature
        $I->am(\'system root user\');                                           //describe who you are
        $I->amGoingTo(\'check that all required elements presented on page\');  //describe what you want to do
    }';

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

        $classname = preg_replace("~Cest$~",'',$classname);

        $tests = str_replace('{guyClass}',$guy,$this->methodTemplate);

        $cestFileContent = str_replace(
		array('{namespace}', '{cestName}', '{testMethod}'),
		array($ns, $classname, $tests),
		$this->template
	);

        file_put_contents($filename, $cestFileContent);

        $output->writeln("<info>Cest was created in $filename</info>");
    }

}
