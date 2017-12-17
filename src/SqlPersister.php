<?php

namespace Persister;


use Exception;
use PDO;
use Persister\contracts\Grammar;
use Persister\events\RecordInserted;
use Persister\events\RecordUpdated;

class SqlPersister extends Persister
{
    protected $pdo;

    protected $grammar;

    public function __construct(PDO $pdo, Grammar $grammar)
    {
        $this->pdo = $pdo;

        $this->grammar = $grammar;
    }

    protected function discoverExistingRecords()
    {
        $statements = [];

        foreach ($this->records as $record) {
            if ($record->getOperation() === 'insertOrUpdate') {
                $statements[] = $this->grammar->compileExists($record->getRecord(), $record->getUid());
            }
        }

        $statementsChunks = array_chunk($statements, $this->maxStatementsLimit);

        $results = [];

        foreach ($statementsChunks as $statementsChunk) {
            $sql = implode(' UNION ', $statementsChunk);

            $results = array_merge(
                $this->pdo->query($sql)->fetchAll(PDO::FETCH_NUM),
                $results
            );
        }

        return array_map(
            function($row) {
                return $row[0];
            },
            $results
        );
    }

    protected function resolveInsertOrUpdateOperations(array $uids)
    {
        foreach ($this->records as $record) {
            foreach ($uids as $uid) {
                if ($record->getUid() === $uid) {
                    $record->setOperation('update');

                    break;
                }
            }
            if ($record->getOperation() === 'insertOrUpdate') {
                $record->setOperation('insert');
            }
        }
    }

    protected function persistRecords()
    {
        $statements = [];

        foreach ($this->records as $record) {
            $statements[] = $record->getOperation() === 'update'
                ? $this->grammar->compileUpdate($record->getRecord())
                : $this->grammar->compileInsert($record->getRecord());
        }

        try {
            $this->beginTransaction();

            foreach ($statements as $statement) {
                $this->pdo->exec($statement);
            }

            $this->commitTransaction();
        } catch (Exception $e) {
            $this->rollbackTransaction();

            throw ($e);
        }
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

    protected function dispatchEvents()
    {
        foreach ($this->records as $record) {
            if ($record->getOperation() === 'update') {
                $this->eventDispatcher->dispatch(new RecordUpdated($record->getRecord()));
            } else {
                $this->eventDispatcher->dispatch(new RecordInserted($record->getRecord()));
            }
        }
    }
}