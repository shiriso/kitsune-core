<?php

namespace Shiriso\Kitsune\Core\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Shiriso\Kitsune\Core\Contracts\IsSourceNamespace;

class KitsuneSourceNamespaceUpdated
{
    use Dispatchable;

    /**
     * Create a new event instance.
     *
     * @param  IsSourceNamespace  $namespace
     */
    public function __construct(public IsSourceNamespace $namespace)
    {
    }
}
