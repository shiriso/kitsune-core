<?php

namespace Kitsune\Core\Exceptions;

use Exception;
use Throwable;

class MissingBasePathException extends Exception
{
    public function __construct($extraSourceAlias, $code = 0, Throwable $previous = null)
    {
        parent::__construct(
            sprintf('There was no valid base path configured for "%s".', $extraSourceAlias),
            $code,
            $previous
        );
    }
}
