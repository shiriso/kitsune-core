<?php

namespace Shiriso\Kitsune\Core\Exceptions;

use Exception;
use Throwable;

class InvalidDefaultSourceConfiguration extends Exception
{
    public function __construct($source, $code = 0, Throwable $previous = null)
    {
        parent::__construct(sprintf('Given default source "%s" is not defined.', $source), $code, $previous);
    }
}
