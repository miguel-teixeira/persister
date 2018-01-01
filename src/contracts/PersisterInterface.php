<?php

namespace Persister\Contracts;


interface PersisterInterface
{
    public function insertOrUpdate(Record $record);

    public function persist();

    public function usesTransaction($boolean);
}