<?php

namespace Kitsune\Core\Listeners;

use Kitsune\Core\Concerns\UtilisesKitsune;
use Kitsune\Core\Events\KitsuneSourceNamespaceUpdated;

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
     * @param  KitsuneSourceNamespaceUpdated  $event
     * @return void
     */
    public function __invoke(KitsuneSourceNamespaceUpdated $event)
    {
        if ($this->getKitsuneCore()->shouldAutoRefresh()) {
            $this->getKitsuneCore()->refreshNamespacePaths($event->namespace);
        }
    }
}
