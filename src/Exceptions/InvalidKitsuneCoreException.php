<?php

namespace Kitsune\Core\Exceptions;

use Exception;
use Kitsune\Core\Contracts\IsKitsuneCore;
use Throwable;

class InvalidKitsuneCoreException extends Exception
{
    public function __construct($coreClass, $code = 0, Throwable $previous = null)
    {
        parent::__construct(
            sprintf('Configured class "%s" does not implement %s.', $coreClass, IsKitsuneCore::class),
            $code,
            $previous
        );
    }
}
