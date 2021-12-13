<?php

namespace Shiriso\Kitsune\Core\Listeners;

use Shiriso\Kitsune\Core\Events\KitsuneCoreUpdated;

class HandleCoreUpdate
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
     * @param  KitsuneCoreUpdated  $event
     * @return void
     */
    public function __invoke(KitsuneCoreUpdated $event)
    {
        $event->kitsune->refreshViewSources();
    }
}
