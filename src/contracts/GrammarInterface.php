<?php

namespace Persister\contracts;

interface GrammarInterface
{
    public function compileSelectRows($tableName, $keyColumns, $keys);

    public function compileUpdate(Record $record);

    public function compileInsertRows(array $records, array $recordsColumns);

    public function compileInsertOrUpdateRows(array $records);
}