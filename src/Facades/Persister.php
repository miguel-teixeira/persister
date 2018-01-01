<?php

namespace Persister\Facades;


use Illuminate\Support\Facades\Facade;

class Persister extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'persister';
    }
}