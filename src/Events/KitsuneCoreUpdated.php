<?php

namespace Kitsune\Core\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Kitsune\Core\Contracts\IsKitsuneCore;

class KitsuneCoreUpdated
{
    use Dispatchable;

    /**
     * Create a new event instance.
     */
    public function __construct(public IsKitsuneCore $kitsune)
    {
    }
}
