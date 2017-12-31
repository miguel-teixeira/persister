<?php
namespace Persister\Contracts;

use Illuminate\Contracts\Events\Dispatcher;

interface PersisterInterface
{
    public function insert(Record $record);

    public function insertOrUpdate(Record $record);

    public function persist();

    public function usesTransaction($boolean);

    public function setMaxStatementsLimit($maxStatementsLimit);

    public function setEventDispatcher(Dispatcher $dispatcher);
}