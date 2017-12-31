<?php

namespace Persister\Contracts;

interface Record
{
    public function getTable();

    public function getKeyColumn();

    public function getKey();

    public function getData();

    public function usesUpdatedAt($boolean = null);

    public function usesCreatedAt($boolean = null);
}