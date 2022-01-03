<?php

namespace Kitsune\Core\Exceptions;

use Exception;
use Throwable;

class PriorityDefinitionNotEnumException extends Exception
{
    public function __construct($priorityDefinition, $code = 0, Throwable $previous = null)
    {
        parent::__construct(
            sprintf('Given priority definition "%s" is not a enum and can not be used like one.', $priorityDefinition),
            $code,
            $previous
        );
    }
}
