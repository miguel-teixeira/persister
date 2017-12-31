<?php

namespace Persister\contracts;

interface GrammarInterface
{
    public function compileExists(Record $record, $uid);

    public function compileSelectTableRows($tableName, $keyColumns, $keys);

    public function compileUpdate(Record $record);

    public function compileInsert(Record $record);
}