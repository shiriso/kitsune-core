<?php

namespace Kitsune\Core\Contracts;

use Kitsune\Core\Exceptions\InvalidPriorityException;

interface DefinesEnumPriority extends DefinesPriority
{
    /**
     * Get the priority based on the name.
     *
     * @param  string  $name
     * @return DefinesEnumPriority
     * @throws InvalidPriorityException
     */
    public static function fromName(string $name = 'medium'): DefinesEnumPriority;

    /**
     * Get the numeric representation of the priority.
     *
     * @return int
     */
    public function getValue(): int;
}
