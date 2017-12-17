<?php

namespace Persister;


use Illuminate\Contracts\Events\Dispatcher;
use Persister\Contracts\Record;
use Persister\Contracts\Persister as PersisterInterface;

abstract class Persister implements PersisterInterface
{
    protected $records = [];

    protected $usesTransaction = false;

    protected $maxStatementsLimit = 500;

    protected $eventDispatcher;

    public function insert(Record $record)
    {
        $this->records[] = new RecordDetails($record, 'insert');
    }

    public function insertOrUpdate(Record $record)
    {
        $this->records[] = new RecordDetails($record, 'insertOrUpdate');
    }

    public function persist()
    {
        $uids = $this->discoverExistingRecords();

        $this->resolveInsertOrUpdateOperations($uids);

        $this->persistRecords();

        $this->dispatchEvents();

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

    abstract protected function resolveInsertOrUpdateOperations(array $uids);

    abstract protected function persistRecords();

    abstract protected function dispatchEvents();

    protected function clearRecords()
    {
        $this->records = [];
    }
}