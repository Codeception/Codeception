<?php
namespace Codeception\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;


class GenerateGroup extends Base
{
    protected $template = <<<EOF
<?php
%s

/**
* Group class is Codeception Extension which is allowed to handle to all internal events.
* This class itself can be used to listen events for test execution of one particular group.
* It may be especially useful to create fixtures data, prepare server, etc.
*
* INSTALLATION:
*
* To use this group extension, include it to "extensions" option of global Codeception config.
*/

%s %sGroup extends \Codeception\Platform\Group
{
    static \$group = '%s';

    public function _before(\Codeception\Event\Test \$e)
    {
    }

    public function _after(\Codeception\Event\Test \$e)
    {
    }
}
EOF;



    protected function configure()
    {
        $this->setDefinition(array(
            new InputArgument('group', InputArgument::REQUIRED, 'Group class name'),
            new InputOption('config', 'c', InputOption::VALUE_OPTIONAL, 'Use custom path for config'),
        ));
        parent::configure();
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $config = $this->getGlobalConfig($input->getOption('config'));
        $group = $input->getArgument('group');

        $class = ucfirst($group);
        $ns = $this->getNamespaceString($config['namespace'].'\\'.$class);

        $path = $this->buildPath($config['paths']['tests'].'/_groups/', $class);
        $filename = $this->completeSuffix($class, 'Group');
        $filename = $path.$filename;

        $this->introduceAutoloader($config['paths']['tests'].DIRECTORY_SEPARATOR.$config['settings']['bootstrap'],'Group','_groups');
        $res = $this->save($filename, sprintf($this->template, $ns, 'class', $class, $group));

        if (!$res) {
            $output->writeln("<error>Group $filename already exists</error>");
            exit;
        }
        
        $output->writeln("<info>Group extension was created in $filename</info>");
        $output->writeln('To use this group extension, include it to "extensions" option of global Codeception config.');
    }

}