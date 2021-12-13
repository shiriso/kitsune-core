<?php

namespace Shiriso\Kitsune\Core\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Shiriso\Kitsune\Core\Contracts\IsSourceNamespace;
use Shiriso\Kitsune\Core\Contracts\IsSourceRepository;

class KitsuneSourceRepositoryUpdated
{
    use Dispatchable;

    public IsSourceNamespace $namespace;

    /**
     * Create a new event instance.
     *
     * @param  IsSourceRepository  $repository
     */
    public function __construct(public IsSourceRepository $repository)
    {
        $this->namespace = $this->repository->getNamespace();
    }
}
