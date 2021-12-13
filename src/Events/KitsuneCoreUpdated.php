<?php

namespace Shiriso\Kitsune\Core\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Shiriso\Kitsune\Core\Contracts\IsKitsuneCore;

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