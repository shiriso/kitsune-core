<?php

namespace Shiriso\Kitsune\Core\Facades;

use Illuminate\Support\Facades\Facade;

class KitsuneHelperFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'kitsune.helper';
    }
}