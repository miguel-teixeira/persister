<?php

namespace Persister;


use Persister\Contracts\Record;
use Ramsey\Uuid\Uuid;

class RecordDetails
{
    protected $record;

    protected $operation;

    protected $exists = false;

    protected $uid;

    public function __construct(Record $record, $operation)
    {
        $this->record = $record;

        $this->operation = $operation;

        $this->uid = Uuid::uuid4()->toString();
    }

    public function getRecord() {
        return $this->record;
    }

    public function getOperation()
    {
        return $this->operation;
    }

    public function getUid()
    {
        return $this->uid;
    }

    public function setOperation($operation)
    {
        $this->operation = $operation;
    }
}