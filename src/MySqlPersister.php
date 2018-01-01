<?php

namespace Persister;


class MySqlPersister extends Persister
{
    protected function buildInsertOrUpdatedStatements()
    {
        $statements = [];

        $tableRecords = $this->groupRecordsByTable();

        foreach ($tableRecords as $records) {
            $statements[] = $this->grammar->compileInsertOrUpdateRows($records);
        }

        return $statements;
    }

    protected function groupRecordsByTable()
    {
        $records = [];

        foreach ($this->records as $record) {
            $tableName = $record->getTable();

            if (!array_key_exists($tableName, $records)) {
                $records[$tableName] = [];
            }

            $records[$tableName][] = $record;
        }

        return $records;
    }

    protected function wrapInBackticks($column) {
        return '`' . implode('`.`', explode('.', $column)) . '`';
    }
}