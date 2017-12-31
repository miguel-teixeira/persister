<?php

namespace Persister;


use Persister\Contracts\PersisterInterface;
use Illuminate\Contracts\Events\Dispatcher;
use Persister\Contracts\Record;

abstract class Persister implements PersisterInterface
{
    protected $records = [];

    protected $usesTransaction = false;

    protected $maxStatementsLimit = 500;

    protected $eventDispatcher;

    public function insertOrUpdate(Record $record)
    {
        $this->records[] = $record;
    }

    public function persist()
    {
        $this->discoverExistingRecords();

        $this->persistRecords();

//        $this->dispatchEvents();

        $this->clearRecords();
    }

    public function usesTransaction($boolean)
    {
        $this->usesTransaction = $boolean;
    }

    public function setMaxStatementsLimit($maxStatementsLimit)
    {
        $this->maxStatementsLimit = $maxStatementsLimit;
    }

    public function setEventDispatcher(Dispatcher $dispatcher)
    {
        $this->eventDispatcher = $dispatcher;
    }

    abstract protected function discoverExistingRecords();

    abstract protected function persistRecords();

    abstract protected function dispatchEvents();

    protected function clearRecords()
    {
        $this->records = [];
    }
}