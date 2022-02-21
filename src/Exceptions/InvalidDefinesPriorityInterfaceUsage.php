<?php

namespace Kitsune\Core\Exceptions;

use Exception;
use Kitsune\Core\Contracts\DefinesClassPriority;
use Kitsune\Core\Contracts\DefinesEnumPriority;
use Kitsune\Core\Contracts\DefinesPriority;
use Throwable;

class InvalidDefinesPriorityInterfaceUsage extends Exception
{
    public function __construct($class, $code = 0, Throwable $previous = null)
    {
        parent::__construct(
            sprintf(
                'Class "%s" uses "%s" as interface which is only used for unionized type hinting but should implement "%s" or "%s" instead.',
                $class,
                DefinesPriority::class,
                DefinesClassPriority::class,
                DefinesEnumPriority::class
            ),
            $code,
            $previous
        );
    }
}
