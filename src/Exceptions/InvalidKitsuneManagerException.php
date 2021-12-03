<?php

namespace Shiriso\Kitsune\Core\Exceptions;

use Exception;
use Shiriso\Kitsune\Core\Contracts\ProvidesKitsuneManager;
use Throwable;

class InvalidKitsuneManagerException extends Exception
{
    public function __construct($managerClass, $code = 0, Throwable $previous = null)
    {
        parent::__construct(
            sprintf('Configured class "%s" does not implement %s.', $managerClass, ProvidesKitsuneManager::class),
            $code,
            $previous
        );
    }
}