<?php

namespace Kitsune\Core\Exceptions;

use Exception;
use Kitsune\Core\Contracts\IsKitsuneManager;
use Throwable;

class InvalidKitsuneManagerException extends Exception
{
    public function __construct($managerClass, $code = 0, Throwable $previous = null)
    {
        parent::__construct(
            sprintf('Configured class "%s" does not implement %s.', $managerClass, IsKitsuneManager::class),
            $code,
            $previous
        );
    }
}
