<?php


namespace Codeception\Lib\Connector\Yii2;

use yii\base\Event;
use yii\db\Connection;
use yii\db\Transaction;

/**
 * Class TransactionForcer
 * This class adds support for forcing transactions as well as reusing PDO objects.
 * @package Codeception\Lib\Connector\Yii2
 */
class TransactionForcer extends ConnectionWatcher
{
    private $ignoreCollidingDSN;

    private $pdoCache = [];

    private $dsnCache;

    private $transactions = [];

    public function __construct(
        $ignoreCollidingDSN
    ) {
        parent::__construct();
        $this->ignoreCollidingDSN = $ignoreCollidingDSN;
    }


    protected function connectionOpened(Connection $connection)
    {
        parent::connectionOpened($connection);
        /**
         * We should check if the known PDO objects are the same, in which case we should reuse the PDO
         * object so only 1 transaction is started and multiple connections to the same database see the
         * same data (due to writes inside a transaction not being visible from the outside).
         *
         */
        $key = md5(json_encode([
            'dsn' => $connection->dsn,
            'user' => $connection->username,
            'pass' => $connection->password,
            'attributes' => $connection->attributes,
            'emulatePrepare' => $connection->emulatePrepare,
            'charset' => $connection->charset
        ]));

        /*
         * If keys match we assume connections are "similar enough".
         */
        if (isset($this->pdoCache[$key])) {
            $connection->pdo = $this->pdoCache[$key];
        } else {
            $this->pdoCache[$key] = $connection->pdo;
        }

        if (isset($this->dsnCache[$connection->dsn])
            && $this->dsnCache[$connection->dsn] !== $key
            && !$this->ignoreCollidingDSN
        ) {
            $this->debug(<<<TEXT
You use multiple connections to the same DSN ({$connection->dsn}) with different configuration.
These connections will not see the same database state since we cannot share a transaction between different PDO
instances.
You can remove this message by adding 'ignoreCollidingDSN = true' in the module configuration.
TEXT
            );
            Debug::pause();
        }

        if (isset($this->transactions[$key])) {
            $this->debug('Reusing PDO, so no need for a new transaction');
            return;
        }

        $this->debug('Transaction started for: ' . $connection->dsn);
        $this->transactions[$key] = $connection->beginTransaction();
    }

    public function rollbackAll()
    {
        /** @var Transaction $transaction */
        foreach ($this->transactions as $transaction) {
            if ($transaction->db->isActive) {
                $transaction->rollBack();
                $this->debug('Transaction cancelled; all changes reverted.');
            }
        }

        $this->transactions = [];
        $this->pdoCache = [];
        $this->dsnCache = [];
    }
}
