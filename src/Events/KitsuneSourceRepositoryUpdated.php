<?php

namespace Kitsune\Core\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Kitsune\Core\Contracts\IsSourceNamespace;
use Kitsune\Core\Contracts\IsSourceRepository;

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
