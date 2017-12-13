<?php

namespace Persister\events;


use Persister\Contracts\Record;

class RecordInserted
{
    public $record;

    public function __construct(Record $record)
    {
        $this->record = $record;
    }
}