<?php

namespace Shiriso\Kitsune\Core\Exceptions;

use Exception;
use Shiriso\Kitsune\Core\Contracts\IsSourceRepository;
use Throwable;

class InvalidSourceRepositoryException extends Exception
{
    public function __construct($repositoryClass, $code = 0, Throwable $previous = null)
    {
        parent::__construct(
            sprintf('Configured class "%s" does not implement %s.', $repositoryClass, IsSourceRepository::class),
            $code,
            $previous
        );
    }
}
