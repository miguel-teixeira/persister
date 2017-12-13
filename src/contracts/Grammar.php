<?php

namespace Persister\contracts;

interface Grammar
{
    public function compileExists(Record $record, $uid);

    public function compileUpdate(Record $record);

    public function compileInsert(Record $record);
}