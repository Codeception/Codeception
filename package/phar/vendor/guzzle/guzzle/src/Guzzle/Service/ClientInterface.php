<?php

namespace Guzzle\Service;

use Guzzle\Common\FromConfigInterface;
use Guzzle\Common\Exception\InvalidArgumentException;
use Guzzle\Http\ClientInterface as HttpClientInterface;
use Guzzle\Service\Command\CommandInterface;
use Guzzle\Service\Description\ServiceDescription;
use Guzzle\Service\Command\Factory\FactoryInterface as CommandFactoryInterface;
use Guzzle\Service\Resource\ResourceIteratorFactoryInterface;

/**
 * Client interface for executing commands on a web service.
 */
interface ClientInterface extends HttpClientInterface, FromConfigInterface
{
    const MAGIC_CALL_DISABLED = 0;
    const MAGIC_CALL_RETURN = 1;
    const MAGIC_CALL_EXECUTE = 2;

    /**
     * Get a command by name.  First, the client will see if it has a service
     * description and if the service description defines a command by the
     * supplied name.  If no dynamic command is found, the client will look for
     * a concrete command class exists matching the name supplied.  If neither
     * are found, an InvalidArgumentException is thrown.
     *
     * @param string $name Name of the command to retrieve
     * @param array  $args Arguments to pass to the command
     *
     * @return CommandInterface
     * @throws InvalidArgumentException if no command can be found by name
     */
    function getCommand($name, array $args = array());

    /**
     * Execute one or more commands
     *
     * @param CommandInterface|array $command Command or array of commands to execute
     *
     * @return mixed Returns the result of the executed command or an array of
     *               commands if an array of commands was passed.
     * @throws InvalidArgumentException if an invalid command is passed
     */
    function execute($command);

    /**
     * Set the service description of the client
     *
     * @param ServiceDescription $service Service description
     * @param bool $updateFactory Set to FALSE to not update the service description based
     *                            command factory if it is not already on the client.
     *
     * @return ClientInterface
     */
    function setDescription(ServiceDescription $service, $updateFactory = true);

    /**
     * Get the service description of the client
     *
     * @return ServiceDescription|null
     */
    function getDescription();

    /**
     * Set the command factory used to create commands by name
     *
     * @param CommandFactoryInterface $factory Command factory
     *
     * @return ClientInterface
     */
    function setCommandFactory(CommandFactoryInterface $factory);

    /**
     * Get a resource iterator from the client.
     *
     * @param string|CommandInterface $command         Command class or command name.
     * @param array                   $commandOptions  Command options used when creating commands.
     * @param array                   $iteratorOptions Iterator options passed to the iterator when it is instantiated.
     *
     * @return ResourceIteratorInterface
     */
    function getIterator($command, array $commandOptions = null, array $iteratorOptions = array());

    /**
     * Set the resource iterator factory associated with the client
     *
     * @param ResourceIteratorFactoryInterface $factory Resource iterator factory
     *
     * @return ClientInterface
     */
    function setResourceIteratorFactory(ResourceIteratorFactoryInterface $factory);
}
