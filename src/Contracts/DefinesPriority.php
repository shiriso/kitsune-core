<?php

namespace Shiriso\Kitsune\Core\Contracts;

interface DefinesPriority
{
    /**
     * Get the numeric representation of the priority.
     *
     * @return int
     */
    public function getValue(): int;
}
