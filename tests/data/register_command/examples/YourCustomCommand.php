<?php
/**
 * An example for a custom command to add to the framework.
 *
 * @author    Tobias Matthaiou <tm@solutionDrive.de>
 * @date      27.01.16
 */
namespace Project\Command;

use \Symfony\Component\Console\Command\Command;
use \Codeception\CustomCommandInterface;
use \Symfony\Component\Console\Input\InputOption;
use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Output\OutputInterface;

class YourCustomCommand extends Command implements CustomCommandInterface
{

    use \Codeception\Command\Shared\FileSystem;
    use \Codeception\Command\Shared\Config;

    /**
     * returns the name of the command
     *
     * @return string
     */
    public static function getCommandName()
    {
        return "myProject:yourCommand";
    }

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setDefinition(array(
            new InputOption('something', 's', InputOption::VALUE_NONE, 'The Message will show you something more'),
        ));

        parent::configure();
    }

    /**
     * Returns the description for the command.
     *
     * @return string The description for the command
     */
    public function getDescription()
    {
        return "This is your command make something";
    }

    /**
     * Executes the current command.
     *
     * This method is not abstract because you can use this class
     * as a concrete class. In this case, instead of defining the
     * execute() method, you set the code to execute by passing
     * a Closure to the setCode() method.
     *
     * @param \Symfony\Component\Console\Input\InputInterface   $input  An InputInterface instance
     * @param \Symfony\Component\Console\Output\OutputInterface $output An OutputInterface instance
     *
     * @return null|int null or 0 if everything went fine, or an error code
     *
     * @throws LogicException When this abstract method is not implemented
     *
     * @see setCode()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $messageEnd = "!" . PHP_EOL;

        if ($input->getOption('something')) {
            $messageEnd = "," . PHP_EOL;
            $messageEnd .= "push the Button!" . PHP_EOL;
        }

        echo "Hello Rabbit";
        echo $messageEnd . PHP_EOL;
    }
}
