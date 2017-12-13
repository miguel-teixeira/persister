<?php
namespace Persister\Contracts;

interface Persister
{
    public function insert(Record $record);

    public function insertOrUpdate(Record $record);

    public function persist();

    public function usesTransaction($boolean);

    public function setMaxStatementsLimit($maxStatementsLimit);
}