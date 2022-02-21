<?php

namespace Kitsune\Core\Contracts;

use Kitsune\Core\Exceptions\InvalidPriorityException;

interface DefinesClassPriority extends DefinesPriority
{
    /**
     * Creates a new instance of a Priority.
     *
     * @throws InvalidPriorityException
     */
    public function __construct(string $priority);

    /**
     * Get the numeric representation of the priority.
     *
     * @return int
     */
    public function getValue(): int;
}
