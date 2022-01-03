<?php

namespace Kitsune\Core\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Kitsune\Core\Contracts\IsKitsuneManager;

class KitsuneManagerInitialized
{
    use Dispatchable;

    /**
     * Create a new event instance.
     *
     * @param  IsKitsuneManager  $manager
     */
    public function __construct(public IsKitsuneManager $manager)
    {
    }
}
