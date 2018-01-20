<?php

namespace Persister;


use PDO;
use Persister\contracts\GrammarInterface;
use Persister\Contracts\PersisterInterface;
use Persister\Contracts\Record;

abstract class Persister implements PersisterInterface
{
    protected $records = [];

    protected $usesTransaction = true;

    protected $pdo;

    protected $grammar;

    public function __construct(PDO $pdo, GrammarInterface $grammar)
    {
        $this->pdo = $pdo;

        $this->grammar = $grammar;
    }

    public function insertOrUpdate(Record $record)
    {
        $oldRecord = $this->findRecord($record->getTable(), $record->getKeyColumn(), $record->getKey());

        if (is_null($oldRecord)) {
            $this->records[] = $record;
        } else {
            $oldRecord->mergeData($record->getData());
        }
    }

    public function persist()
    {
        $this->persistRecords();

        $this->clearRecords();
    }

    protected function persistRecords() {
        $statements = $this->buildInsertOrUpdatedStatements();

        try {
            $this->beginTransaction();

            foreach ($statements as $statement) {
//                dump($statement);
                $this->pdo->exec($statement);
            }

            $this->commitTransaction();
        } catch (\Exception $e) {
            $this->rollbackTransaction();

            throw $e;
        }
    }

    public function usesTransaction($boolean)
    {
        $this->usesTransaction = $boolean;
    }

    abstract protected function buildInsertOrUpdatedStatements();

    protected function clearRecords()
    {
        $this->records = [];
    }

    protected function beginTransaction()
    {
        if ($this->usesTransaction) {
            $this->pdo->beginTransaction();
        }
    }

    protected function commitTransaction()
    {
        if ($this->usesTransaction) {
            $this->pdo->commit();
        }
    }

    protected function rollbackTransaction()
    {
        if ($this->usesTransaction) {
            $this->pdo->rollBack();
        }
    }

    protected function findRecord($tableName, $keyColumn, $keyValue)
    {
        foreach ($this->records as $record) {
            if ($record->getTable() === $tableName
                && $record->getKeyColumn() === $keyColumn
                && $record->getKey() === $keyValue
            ) {
                return $record;
            }
        }

        return null;
    }
}