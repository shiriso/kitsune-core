<?php

namespace Kitsune\Core\Contracts;

use Kitsune\Core\Exceptions\InvalidPriorityException;

interface DefinesPriority
{
    /**
     * Get the numeric representation of the priority.
     *
     * @return int
     */
    public function getValue(): int;

    /**
     * Get the priority based on the name.
     *
     * @param  string  $name
     * @return DefinesPriority
     * @throws InvalidPriorityException
     */
    public static function fromName(string $name = 'medium'): DefinesPriority;
}
