<?php

namespace Shiriso\Kitsune\Core\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Shiriso\Kitsune\Core\Contracts\IsKitsuneCore;

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
