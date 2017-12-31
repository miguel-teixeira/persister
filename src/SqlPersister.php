<?php

namespace Persister;


use PDO;
use Persister\contracts\GrammarInterface;
use Persister\events\RecordInserted;
use Persister\events\RecordUpdated;

class SqlPersister extends Persister
{
    protected $pdo;

    protected $grammar;

    public function __construct(PDO $pdo, GrammarInterface $grammar)
    {
        $this->pdo = $pdo;

        $this->grammar = $grammar;
    }

    protected function discoverExistingRecords()
    {
        $tables = $this->flattenTablesWithKeys();

        foreach ($tables as $tableName => $table) {
            $existingRecords = $this->fetchTableExistingRecords($tableName, $table);

            $this->setTableRecordsOriginalData($tableName, $existingRecords);
        }
    }

    protected function flattenTablesWithKeys()
    {
        $tables = [];

        foreach ($this->records as $record) {
            $tableName = strtolower($record->getTable());

            if (!array_key_exists($tableName, $tables)) {
                $tables[$tableName] = [
                    'keyColumn' => $record->getKeyColumn(),
                    'keys' => []
                ];
            }

            $tables[$tableName]['keys'][] = $record->getKey();
        }

        return $tables;
    }

    protected function fetchTableExistingRecords($tableName, array $table)
    {
        $sql = $this->grammar->compileSelectTableRows(
            $tableName,
            $table['keyColumn'],
            $table['keys']
        );

        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }


    protected function setTableRecordsOriginalData($tableName, array $existingRecords)
    {
        foreach ($this->records as $record) {
            foreach ($existingRecords as $existingRecord) {
                if ($record->getTable() === $tableName
                    && $record->getKey() === $existingRecord[$record->getKeyColumn()]
                ) {
                    $record->setOriginalData($existingRecord);
                }
            }
        }
    }

    protected function persistRecords()
    {
        $statements = array_merge($this->buildInsertStatements(), $this->buildUpdateStatements());

        try {
            $this->beginTransaction();

            foreach ($statements as $statement) {
                $this->pdo->exec($statement);
            }

            $this->commitTransaction();
        } catch (\Exception $e) {
            $this->rollbackTransaction();

            throw $e;
        }
    }

    protected function getInsertRecords()
    {
        $inserts = [];

        foreach ($this->records as $record) {
            $tableName = strtolower($record->getTable());

            if (!$record->hasOriginalData()) {
                if (!array_key_exists($tableName, $inserts)) {
                    $inserts[$tableName] = [];
                }

                $inserts[$tableName][] = $record;
            }
        }

        return $inserts;
    }

    protected function buildInsertStatements()
    {
        $inserts = $this->getInsertRecords();

        $statements = [];

        foreach ($inserts as $tableName => $records) {
            $columns = [];

            foreach ($records as $record) {
                $columns = array_unique(array_merge($columns, array_keys($record->getDataWithTimestamps())));
            }

            $statements[] = $this->grammar->compileInsertRows($records, $columns);
        }

        return $statements;
    }

    protected function buildUpdateStatements() {
        $statements = [];

        foreach ($this->records as $record) {
            if ($record->hasOriginalData()
                && $record->hasChangedData()) {

                $statements[] = $this->grammar->compileUpdate($record);
            }
        }

        return $statements;
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
        if (is_null($this->eventDispatcher)) {
            return;
        }

        foreach ($this->records as $record) {
            if ($record->getOperation() === 'update') {
                $this->eventDispatcher->dispatch(new RecordUpdated($record));
            } else {
                $this->eventDispatcher->dispatch(new RecordInserted($record));
            }
        }
    }

    protected function getDistinctTables()
    {
        $tables = [];

        foreach ($this->records as $record) {
            if (array_has($tables, strtolower($record->getTable()))) {
                $tables[] = $record->getTable();
            }
        }

        return $tables;
    }

    protected function getRecordKeys($table)
    {
        $keys = [];

        foreach ($this->records as $record) {
            $keys[] = $record->getKey();
        }
    }
}