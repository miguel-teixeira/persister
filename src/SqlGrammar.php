<?php

namespace Persister;


use PDO;
use Persister\contracts\Grammar;
use Persister\contracts\Record;

class SqlGrammar implements Grammar
{
    protected $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function compileExists(Record $record, $uid)
    {
        return 'SELECT ' . $this->pdo->quote($uid) .
            ' FROM ' . $record->getTable() .
            ' WHERE ' . $record->getKeyColumn() .
               ' = ' . $this->pdo->quote($record->getKey());
    }

    public function compileUpdate(Record $record)
    {
        $recordData = $record->getData();

        unset($recordData['updated_at']);

        $columns = array_map(function ($column) {
            return $this->wrapInBackticks($column);
        }, array_keys($recordData));

        $values = array_values($recordData);

        if ($record->usesUpdatedAt()) {
            $columns[] = 'updated_at';
            $values[] = date("Y-m-d H:i:s");
        }

        $fields = array_map(function($column, $value) {
            return $column . ' = ' . $this->pdo->quote($value);
        }, $columns, $values);

        return 'UPDATE ' . $record->getTable() .
            ' SET ' . implode(', ', $fields) .
            ' WHERE ' . $record->getKeyColumn() .
            ' = ' . $this->pdo->quote($record->getKey());
    }

    public function compileInsert(Record $record)
    {
        $columns = array_map(function ($column) {
            return $this->wrapInBackticks($column);
        },  array_keys($record->getData()));

        $values = array_map(function ($value) {
            return $this->pdo->quote($value);
        }, array_values($record->getData()));

        if ($record->usesUpdatedAt()) {
            $columns[] = 'updated_at';
            $values[] = $this->pdo->quote(date("Y-m-d H:i:s"));
        }

        if ($record->usesCreatedAt()) {
            $columns[] = 'created_at';
            $values[] = $this->pdo->quote(date("Y-m-d H:i:s"));
        }

        return 'INSERT INTO ' . $record->getTable() .
            ' (' . implode(', ', $columns) . ')' .
            ' VALUES (' . implode(', ', $values) . ')';
    }

    protected function wrapInBackticks($column) {
        return '`' . implode('`.`', explode('.', $column)) . '`';
    }
}