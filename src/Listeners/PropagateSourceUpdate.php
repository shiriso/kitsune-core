<?php

namespace Kitsune\Core\Listeners;

use Kitsune\Core\Concerns\UtilisesKitsune;
use Kitsune\Core\Events\KitsuneSourceRepositoryUpdated;

class PropagateSourceUpdate
{
    use UtilisesKitsune;

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
     * @param  KitsuneSourceRepositoryUpdated  $event
     * @return void
     */
    public function __invoke(KitsuneSourceRepositoryUpdated $event)
    {
        $event->namespace->setUpdateState();
    }
}
