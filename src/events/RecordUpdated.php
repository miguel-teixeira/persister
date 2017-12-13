<?php

namespace Persister\events;


use Persister\Contracts\Record;

class RecordUpdated
{
    public $record;

    public function __construct(Record $record)
    {
        $this->record = $record;
        dump('record update was dispatched');
    }
}