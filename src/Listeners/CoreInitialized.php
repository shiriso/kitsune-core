<?php

namespace Kitsune\Core\Listeners;

use Kitsune\Core\Events\KitsuneCoreInitialized;
use Kitsune\Core\Events\KitsuneCoreUpdated;

class CoreInitialized
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Handle the event.
     *
     * @param  KitsuneCoreInitialized  $event
     * @return void
     */
    public function __invoke(KitsuneCoreInitialized $event)
    {
        KitsuneCoreUpdated::dispatch($event->kitsune);
    }
}
