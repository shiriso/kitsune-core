<?php

namespace Shiriso\Kitsune\Core\Exceptions;

use Exception;
use Shiriso\Kitsune\Core\Contracts\ProvidesKitsuneCore;
use Throwable;

class InvalidKitsuneHelperException extends Exception
{
    public function __construct($helperClass, $code = 0, Throwable $previous = null)
    {
        parent::__construct(
            sprintf('Configured class "%s" does not implement %s.', $helperClass, ProvidesKitsuneCore::class),
            $code,
            $previous
        );
    }
}