<?php

namespace Persister;


use PDO;

class SqlPersister extends Persister
{
    protected function buildInsertOrUpdatedStatements()
    {
        $this->discoverExistingRecords();

        return array_merge($this->buildInsertStatements(), $this->buildUpdateStatements());
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
            $tableName = $record->getTable();

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
        $sql = $this->grammar->compileSelectRows(
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

    protected function getInsertRecords()
    {
        $inserts = [];

        foreach ($this->records as $record) {
            $tableName = strtolower($record->getTable());

            if (!array_key_exists($tableName, $inserts)) {
                $inserts[$tableName] = [];
            }

            if (!$record->hasOriginalData()) {

                $inserts[$tableName][] = $record;
            }
        }

        return array_filter($inserts);
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

    protected function buildUpdateStatements()
    {
        $statements = [];

        foreach ($this->records as $record) {
            if ($record->hasOriginalData()
                && $record->hasChangedData()) {

                $statements[] = $this->grammar->compileUpdate($record);
            }
        }

        return $statements;
    }
}