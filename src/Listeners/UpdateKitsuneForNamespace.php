<?php

namespace Shiriso\Kitsune\Core\Listeners;

use Shiriso\Kitsune\Core\Concerns\UtilisesKitsune;
use Shiriso\Kitsune\Core\Events\KitsuneNamespaceUpdated;

class UpdateKitsuneForNamespace
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
     * @param  KitsuneNamespaceUpdated  $event
     * @return void
     */
    public function __invoke(KitsuneNamespaceUpdated $event)
    {
        if ($this->getKitsuneCore()->shouldAutoRefresh()) {
            $this->getKitsuneCore()->refreshNamespacePaths($event->namespace);
        }
    }
}