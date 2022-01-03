<?php

namespace Kitsune\Core\Exceptions;

use Exception;
use Throwable;

class InvalidPriorityException extends Exception
{
    public function __construct($priority, $code = 0, Throwable $previous = null)
    {
        parent::__construct(sprintf('Given priority "%s" is not defined.', $priority), $code, $previous);
    }
}
