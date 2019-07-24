<?php

declare(strict_types=1);

namespace Framework\Utilities\DatabaseManager;

/**
 * This class provides functions for committing and rolling back transactions
 *
 * @category   UtilityClass
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General public License, version 2
 */
class DbTransactionManager
{
    /** @var object The \PDO class object */
    private $pdo;
    
    /**
     * Class constructor
     * Sets the initializer object
     * 
     * @param array $parameters the constructor parameters
     *    pdo => \PDO the \PDO class object
     */
    function __construct(array $parameters) 
    {
        /** The \PDO object is set */
        $this->pdo             = $parameters['pdo'];
    }
    
    /**
     * Starts the transaction
     *
     * It starts a transaction
     * Auto commit is disabled, so the data is not added untill the commit functions is called    
     */
    public function BeginTransaction() : void
    {
        /** The transaction is started */
        $this->pdo->beginTransaction();
    }
    /**
     * Commits the current transaction
     *
     * SQL commit only works with transactional table types like innodb
     * It does not support MyISAM table type
     * Once the transaction is commited the changes are written to database
     */
    public function Commit() : void
    {
        /** The transaction is commited */
        $this->pdo->commit();
    }
    /**
     * Rolls back the current transaction
     *
     * SQL rollback only works with transactional table types like innodb
     * It does not support MyISAM table type
     * Once the transaction is rolledback it cannot be saved to database
     */
    public function Rollback() : void
    {
        /** The transaction is rolled back */
        $this->pdo->rollback();
    }
}

