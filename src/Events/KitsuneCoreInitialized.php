<?php

namespace Kitsune\Core\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Kitsune\Core\Contracts\IsKitsuneCore;

class KitsuneCoreInitialized
{
    use Dispatchable;

    /**
     * Create a new event instance.
     *
     * @param  IsKitsuneCore  $kitsune
     */
    public function __construct(public IsKitsuneCore $kitsune)
    {
    }
}
