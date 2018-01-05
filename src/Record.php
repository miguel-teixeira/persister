<?php

namespace Persister;


use Persister\Contracts\Record as RecordInterface;


class Record implements RecordInterface
{
    protected $table;

    protected $keyColumn;

    protected $key;

    protected $data = [];

    protected $originalData;

    protected $changedData;

    protected $hasChangedData = false;

    protected $usesUpdatedAt = true;

    protected $usesCreatedAt = true;

    protected $hasOriginalData = false;

    protected $operation;

    protected $casts = [];

    static $inserted = 0;

    static $changed = 0;

    static $notChanged = 0;

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

    public function getDataWithTimestamps()
    {
        return array_merge($this->data,
            ($this->usesUpdatedAt() ? ['updated_at' => date("Y-m-d H:i:s")] : []),
            ($this->usesCreatedAt() ? ['created_at' => date("Y-m-d H:i:s")] : []));
    }

    public function getDataWithUpdatedAt()
    {
        return array_merge($this->data,
            ($this->usesUpdatedAt() ? ['updated_at' => date("Y-m-d H:i:s")] : []));
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

    public function getOriginalData()
    {
        return $this->originalData;
    }

    public function setOriginalData($originalData)
    {
        $this->originalData = $this->applyCasts($originalData);

        $this->changedData = array_diff_assoc($this->data, $this->originalData);

        $this->hasChangedData = count($this->changedData) > 0;

        $this->hasOriginalData = !empty($this->originalData);
    }

    public function hasOriginalData()
    {
        return $this->hasOriginalData;
    }

    public function getChangedData()
    {
        return $this->changedData;
    }

    public function hasChangedData()
    {
        return $this->hasChangedData;
    }

    public function setCasts(array $casts)
    {
        $this->casts = $casts;
    }

    public function mergeData(array $data)
    {
        $this->data = array_merge($this->data, $data);
    }

    protected function applyCasts($data)
    {
        foreach ($this->casts as $key => $type) {
            if (array_key_exists($key, $data)) {
                settype($data[$key], $type);
            }
        }

        return $data;
    }
}