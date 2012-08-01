<?php

namespace Guzzle\Service\Command;

use Guzzle\Common\Batch\BatchTransferInterface;
use Guzzle\Common\Batch\BatchDivisorInterface;
use Guzzle\Common\Exception\InvalidArgumentException;
use Guzzle\Service\Exception\InconsistentClientTransferException;

/**
 * Efficiently transfers multiple commands in parallel per client
 * This class is to be used with {@see Guzzle\Common\Batch\BatchInterface}
 */
class BatchCommandTransfer implements BatchTransferInterface, BatchDivisorInterface
{
    /**
     * @var int Size of each command batch
     */
    protected $batchSize;

    /**
     * Constructor used to specify how large each batch should be
     *
     * @param int $batchSize Size of each batch
     */
    public function __construct($batchSize)
    {
        $this->batchSize = $batchSize;
    }

    /**
     * Creates batches by grouping commands by their associated client
     *
     * {@inheritdoc}
     */
    public function createBatches(\SplQueue $queue)
    {
        $groups = new \SplObjectStorage();
        foreach ($queue as $item) {
            if (!$item instanceof CommandInterface) {
                throw new InvalidArgumentException('All items must implement Guzzle\Service\Command\CommandInterface');
            }
            $client = $item->getClient();
            if (!$groups->contains($client)) {
                $groups->attach($client, new \ArrayObject(array($item)));
            } else {
                $groups[$client]->append($item);
            }
        }

        $batches = array();
        foreach ($groups as $batch) {
            $batches = array_merge($batches, array_chunk($groups[$batch]->getArrayCopy(), $this->batchSize));
        }

        return $batches;
    }

    /**
     * {@inheritdoc}
     */
    public function transfer(array $batch)
    {
        if (empty($batch)) {
            return;
        }

        // Get the client of the first found command
        $client = reset($batch)->getClient();

        // Keep a list of all commands with invalid clients
        $invalid = array_filter($batch, function ($command) use ($client) {
            return $command->getClient() !== $client;
        });

        if (!empty($invalid)) {
            throw new InconsistentClientTransferException($invalid);
        }

        $client->execute($batch);
    }
}
