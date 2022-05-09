<?php

namespace Kitsune\Core\Exceptions;

use Exception;
use Kitsune\Core\Concerns\ManagesPaths;
use Throwable;

class MissingPathsPropertyException extends Exception
{
    public function __construct($class, $code = 0, Throwable $previous = null)
    {
        parent::__construct(
            sprintf('Class "%s" uses "%s" but does not implement a paths property.', $class, ManagesPaths::class),
            $code,
            $previous
        );
    }
}
