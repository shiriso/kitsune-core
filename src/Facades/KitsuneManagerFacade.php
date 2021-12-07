<?php

namespace Shiriso\Kitsune\Core\Facades;

use Illuminate\Support\Facades\Facade;

class KitsuneManagerFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'kitsune.manager';
    }
}