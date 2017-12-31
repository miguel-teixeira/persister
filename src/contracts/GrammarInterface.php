<?php

namespace Persister\contracts;

interface GrammarInterface
{
    public function compileSelectTableRows($tableName, $keyColumns, $keys);

    public function compileUpdate(Record $record);

    public function compileInsertRows(array $records, array $recordsColumns);
}