<?php
namespace Codeception\Command;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use \Symfony\Component\Console\Helper\DialogHelper;

class Install extends \Symfony\Component\Console\Command\Command {

    public function getDescription() {
        return 'Installs all required components: PHPUnit, Mink, Symfony Components';
    }

    protected function configure()
    {
        $this->setDefinition(array(
            new \Symfony\Component\Console\Input\InputOption('silent', '', InputOption::VALUE_NONE, 'Don\'t ask for permissions')
        ));
        parent::configure();
    }

	public function execute(InputInterface $input, OutputInterface $output) {

        $dialog = new DialogHelper();
        if (!$input->getOption('silent')) {
            $confirmed = $dialog->askConfirmation($output,
                "This will install all TestGuy dependencies through PEAR installer.\n"
                    . "PHPUnit, Symfony Components, and Mink will be installed.\n"
                    . "Make sure this script has permission to install PEAR packages.\n"
                    . "Do you want to proceed? (Y/n)");
            if (!$confirmed) return;
        }



        $output->writeln('Intalling PHPUnit...');
		$output->write(shell_exec('pear config-set auto_discover 1'));
		$output->write(shell_exec('pear install --alldeps pear.phpunit.de/PHPUnit'));

        $output->writeln("Installing Symfony Components...");
        $output->write(shell_exec("pear channel-discover pear.symfony.com"));
        $output->write(shell_exec('pear install symfony2/Finder'));
        $output->write(shell_exec('pear install symfony2/Process'));
        $output->write(shell_exec('pear install symfony2/CssSelector'));
        $output->write(shell_exec('pear install symfony2/DomCrawler'));
        $output->write(shell_exec('pear install symfony2/BrowserKit'));

        $output->writeln("Installing Mink...");
        $output->write(shell_exec("pear channel-discover pear.behat.org"));
        $output->write(shell_exec("pear install behat/mink"));

        $output->writeln('Please check PHPUnit was installed sucessfully. Run the "phpunit" command. If it is not avaible try installing PHPUnit manually');
        $output->writeln("Installaction complete. Init your new TestGuy suite calling the 'init' command");
    }


}
