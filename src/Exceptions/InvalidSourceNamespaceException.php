<?php

namespace Shiriso\Kitsune\Core\Exceptions;

use Exception;
use Shiriso\Kitsune\Core\Contracts\IsSourceNamespace;
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