<?php

namespace Kitsune\Core\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Kitsune\Core\Contracts\IsSourceRepository;

class KitsuneSourceRepositoryCreated
{
    use Dispatchable;

    /**
     * Create a new event instance.
     *
     * @param  IsSourceRepository  $sourceRepository
     */
    public function __construct(public IsSourceRepository $sourceRepository)
    {
    }
}
