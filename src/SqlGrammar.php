<?php

namespace Persister;


use PDO;
use Persister\contracts\GrammarInterface;
use Persister\contracts\Record;

class SqlGrammar implements GrammarInterface
{
    protected $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function compileSelectRows($tableName, $keyColumns, $keys)
    {
        $keys = array_map(function($key) {
            return $this->pdo->quote($key);
        }, $keys);

        return 'SELECT *' .
            ' FROM ' . $tableName .
            ' WHERE ' . $keyColumns .
                ' IN (' . implode(',', $keys) . ')';
    }

    public function compileUpdate(Record $record)
    {
        $recordData = $record->getDataWithUpdatedAt();

        $columns = array_map(function ($column) {
            return $this->wrapInBackticks($column);
        }, array_keys($recordData));

        $values = array_values($recordData);

        $fields = array_map(function($column, $value) {
            return $column . ' = ' . $this->pdo->quote($value);
        }, $columns, $values);

        return 'UPDATE ' . $record->getTable() .
            ' SET ' . implode(', ', $fields) .
            ' WHERE ' . $record->getKeyColumn() .
            ' = ' . $this->pdo->quote($record->getKey());
    }

    public function compileInsertRows(array $records, array $recordsColumns)
    {
        $columns = array_map(function ($column) {
            return $this->wrapInBackticks($column);
        },  $recordsColumns);

        $rows = [];

        foreach ($records as $record) {
            $values = [];

            $data = $record->getDataWithTimestamps();

            foreach ($recordsColumns as $recordsColumn) {
                $values[] = array_has($data, $recordsColumn)
                    ? $this->pdo->quote($data[$recordsColumn])
                    : 'NULL';
            }

            $rows[] = $values;
        }

        $rows = array_map(function($row) {
            return '(' . implode(', ', $row) . ')';
        }, $rows);

        return 'INSERT INTO ' . $record->getTable() .
            ' (' . implode(', ', $columns) . ')' .
            ' VALUES ' . implode(', ', $rows);
    }

    public function compileInsertOrUpdateRows(array $records)
    {
        $columns = $this->getRecordsColumns($records);

        $rows = [];

        foreach ($records as $record) {
            $values = [];

            $data = $record->getDataWithTimestamps();

            foreach ($columns as $column) {
                $values[] = array_has($data, $column)
                    ? $this->pdo->quote($data[$column])
                    : 'NULL';
            }

            $rows[] = $values;
        }

        $rows = array_map(function($row) {
            return '(' . implode(', ', $row) . ')';
        }, $rows);

        $wrappedColumns = array_map(function ($column) {
            return $this->wrapInBackticks($column);
        },  $columns);

        $updates = implode(',',
            array_map(function ($column) {
                return $column . '= VALUES(' . $column . ')';
            }, $wrappedColumns)
        );

        return 'INSERT INTO ' . $record->getTable() .
            ' (' . implode(', ', $wrappedColumns) . ')' .
            ' VALUES ' . implode(', ', $rows) .
            ' ON DUPLICATE KEY UPDATE ' . $updates;
    }

    protected function getRecordsColumns(array $records)
    {
        $columns = [];

        foreach ($records as $record) {
            $columns = array_unique(array_merge($columns, array_keys($record->getDataWithTimestamps())));
        }

        return $columns;
    }

    protected function wrapInBackticks($column) {
        return '`' . implode('`.`', explode('.', $column)) . '`';
    }
}