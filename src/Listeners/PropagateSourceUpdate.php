<?php

namespace Shiriso\Kitsune\Core\Listeners;

use Shiriso\Kitsune\Core\Concerns\UtilisesKitsune;
use Shiriso\Kitsune\Core\Events\KitsuneSourceRepositoryUpdated;

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
