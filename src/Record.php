<?php

namespace Persister;


use Persister\Contracts\Record as RecordInterface;

class Record implements RecordInterface
{
    protected $table;

    protected $keyColumn;

    protected $key;

    protected $data;

    protected $usesUpdatedAt = true;

    protected $usesCreatedAt = true;

    public function __construct($table, $keyColumn, $key, array $data)
    {
        $this->table = $table;

        $this->keyColumn = $keyColumn;

        $this->key = $key;

        $this->data = $data;
    }

    public function getTable()
    {
        return $this->table;
    }

    public function getKeyColumn()
    {
        return $this->keyColumn;
    }

    public function getKey()
    {
        return $this->key;
    }

    public function getData()
    {
        return $this->data;
    }

    public function usesUpdatedAt($boolean = null)
    {
        if ($boolean !== null) {
            $this->usesUpdatedAt = $boolean;
        }

        return $this->usesUpdatedAt;
    }

    public function usesCreatedAt($boolean = null)
    {
        if ($boolean !== null) {
            $this->usesCreatedAt = $boolean;
        }

        return $this->usesCreatedAt;
    }
}