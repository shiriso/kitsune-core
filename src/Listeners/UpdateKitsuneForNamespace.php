<?php

namespace Shiriso\Kitsune\Core\Listeners;

use Shiriso\Kitsune\Core\Contracts\IsKitsuneCore;
use Shiriso\Kitsune\Core\Contracts\IsKitsuneManager;
use Shiriso\Kitsune\Core\Contracts\IsSourceNamespace;
use Shiriso\Kitsune\Core\Events\KitsuneNamespaceUpdated;

class UpdateKitsuneForNamespace
{
    protected IsKitsuneManager $manager;
    protected IsKitsuneCore $kitsune;
    protected ?IsSourceNamespace $namespace = null;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        $this->kitsune = app('kitsune');
        $this->manager = app('kitsune.manager');
    }

    /**
     * Handle the event.
     *
     * @param  KitsuneNamespaceUpdated  $event
     * @return void
     */
    public function __invoke(KitsuneNamespaceUpdated $event)
    {
        if (!$this->manager->shouldAutoRefresh()) {
            return;
        }

        //dd($this->namespace, $this->namespace);
    }
}