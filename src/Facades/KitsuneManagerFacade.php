<?php

namespace Shiriso\Kitsune\Core\Facades;

use Illuminate\Support\Facades\Facade;

class KitsuneManagerFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'kitsune.manager';
    }
}
