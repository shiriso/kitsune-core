<?php

namespace Shiriso\Kitsune\Core\Exceptions;

use Exception;
use Shiriso\Kitsune\Core\Contracts\ProvidesKitsuneCore;
use Throwable;

class InvalidKitsuneCoreException extends Exception
{
    public function __construct($coreClass, $code = 0, Throwable $previous = null)
    {
        parent::__construct(
            sprintf('Configured class "%s" does not implement %s.', $coreClass, ProvidesKitsuneCore::class),
            $code,
            $previous
        );
    }
}