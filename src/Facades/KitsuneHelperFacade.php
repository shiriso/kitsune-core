<?php

namespace Shiriso\Kitsune\Core\Facades;

use Illuminate\Support\Facades\Facade;

class KitsuneHelperFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'kitsune.helper';
    }
}