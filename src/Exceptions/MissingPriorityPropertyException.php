<?php

namespace Kitsune\Core\Exceptions;

use Exception;
use Kitsune\Core\Concerns\HasPriority;
use Throwable;

class MissingPriorityPropertyException extends Exception
{
    public function __construct($class, $code = 0, Throwable $previous = null)
    {
        parent::__construct(
            sprintf('Class "%s" uses "%s" but does not implement a priority property.', $class, HasPriority::class),
            $code,
            $previous
        );
    }
}
