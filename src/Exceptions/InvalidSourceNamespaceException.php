<?php

namespace Kitsune\Core\Exceptions;

use Exception;
use Kitsune\Core\Contracts\IsSourceNamespace;
use Throwable;

class InvalidSourceNamespaceException extends Exception
{
    public function __construct($namespaceClass, $code = 0, Throwable $previous = null)
    {
        parent::__construct(
            sprintf('Configured class "%s" does not implement %s.', $namespaceClass, IsSourceNamespace::class),
            $code,
            $previous
        );
    }
}
