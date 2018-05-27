<?php

namespace Persister\Contracts;

interface Record
{
    public function getTable();

    public function getKeyColumn();

    public function getKey(): string;

    public function getData();

    public function getDataWithTimestamps();

    public function getDataWithUpdatedAt();

    public function getOriginalData();

    public function setOriginalData($originalData);

    public function hasChangedData();

    public function setCasts(array $casts);

    public function mergeData(array $data);

    public function usesUpdatedAt($boolean = null);

    public function usesCreatedAt($boolean = null);
}