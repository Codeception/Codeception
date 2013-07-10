<?php
namespace Codeception\Command;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class GeneratePageObject extends Base
{


    protected $template  = <<<EOF
<?php
%s
%s %sPage
{
    // include url of current page
    const URL = '';

    /**
     * Declare UI map for this page here. CSS or XPath allowed.
     * public static \$usernameField = '#username';
     * public static \$formSubmitButton = "#mainForm input[type=submit]";
     */

    /**
     * Basic route example for your current URL
     * You can append any additional parameter to URL
     * and use it in tests like: EditPage::route('/123-post');
     */
     public static function route(\$param)
     {
        return static::URL.\$param;
     }

%s
}
EOF;

    protected $actionsTemplate  = <<<EOF
    /**
     * @var %s;
     */
    protected \$%s;

    public function __construct(%s \$I)
    {
        \$this->%s = \$I;
    }

    public static function of(%s \$I)
    {
        return new static(\$I);
    }
EOF;

    protected $actions = '';


    protected function configure()
    {
        $this->setDefinition(array(

            new InputArgument('suite', InputArgument::REQUIRED, 'Either suite name or page object name)'),
            new InputArgument('page', InputArgument::OPTIONAL, 'Page name of pageobject to represent'),
            new InputOption('config', 'c', InputOption::VALUE_OPTIONAL, 'Use custom path for config'),
        ));
        parent::configure();
    }

    public function getDescription() {
        return 'Generates empty PageObject class';
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $suite = $input->getArgument('suite');
        $class = $input->getArgument('page');

        if (!$class) {
            $class = $suite;
            $suite = null;
        }

        $conf = $suite
            ? $this->getSuiteConfig($suite, $input->getOption('config'))
            : $this->getGlobalConfig($input->getOption('config'));

        $classname = $this->getClassName($class);
        $classname = $this->removeSuffix($classname, 'Page');
        $ns = $this->getNamespaceString($conf['namespace'].'\\'.$class);

        $filename = $suite
            ? $this->pathToSuitePageObject($conf, $class)
            : $this->pathToGlobalPageObject($conf, $class);

        if ($suite) $this->createActions($conf);

        $res = $this->save($filename, sprintf($this->template, $ns, 'class', $classname, $this->actions));

        if (!$res) {
            $output->writeln("<error>PageObject $filename already exists</error>");
            exit;
        }
        $output->writeln("<info>PageObject was created in $filename</info>");
    }

    protected function pathToGlobalPageObject($config, $class)
    {
        $path = $this->buildPath($config['paths']['tests'].'/_pages/', $class);
        $filename = $this->completeSuffix($class, 'Page');
        return  $path.DIRECTORY_SEPARATOR.$filename;
    }

    protected function pathToSuitePageObject($config, $class)
    {
        $path = $this->buildPath($config['path'].'_pages/', $class);
        $filename = $this->completeSuffix($class, 'Page');
        return  $path.DIRECTORY_SEPARATOR.$filename;
    }

    protected function createActions($conf)
    {
        $class = $conf['namespace'].'\\'.$conf['class_name'];
        $guy = lcfirst($conf['class_name']);
        $this->actions = sprintf($this->actionsTemplate, $class, $guy, $class, $guy, $class);
    }

}
