<?php

namespace Shiriso\Kitsune\Core\Listeners;

use Shiriso\Kitsune\Core\Events\KitsuneCoreInitialized;
use Shiriso\Kitsune\Core\Events\KitsuneCoreUpdated;

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
