<?php

namespace Kitsune\Core\Exceptions;

use Exception;
use Kitsune\Core\Contracts\DefinesPriority;
use Throwable;

class InvalidPriorityDefinitionException extends Exception
{
    public function __construct($priorityDefinition, $code = 0, Throwable $previous = null)
    {
        parent::__construct(
            sprintf(
                'Configured definition class "%s" does not implement %s.',
                $priorityDefinition,
                DefinesPriority::class
            ),
            $code,
            $previous
        );
    }
}
