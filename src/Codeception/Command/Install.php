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

	public function execute(InputInterface $input, OutputInterface $output) {

        $dialog = new DialogHelper();
        $confirmed = $dialog->askConfirmation($output,
            "This will install all TestGuy dependencies through PEAR installer.\n"
            . "PHPUnit, Symfony Components, and Mink will be installed.\n"
            . "Do you want to proceed? (Y/n)");
        if (!$confirmed) return;

        $output->writeln('Intalling PHPUnit...');
		$output->write(shell_exec('pear config-set auto_discover 1'));
		$output->write(shell_exec('pear install pear.phpunit.de/PHPUnit'));

        $output->writeln("Installing Symfony Components...");
        $output->write(shell_exec("pear channel-discover pear.symfony.com"));
        $output->write(shell_exec('pear install symfony2/Finder'));

        $output->writeln("Installing Mink...");
        $output->write(shell_exec("pear channel-discover pear.behat.org"));
        $output->write(shell_exec("pear install behat/mink"));

        $output->writeln("Installaction complete. Init your new TestGuy suite calling the 'init' command");
    }


}
