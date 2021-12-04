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

    /**
     * Get the numeric representation of the priority for cases it should be higher than the default.
     * In Kitsune's context this is the case for paths which have been modified with the layout.
     *
     * @return int
     */
    public function getIncreasedValue(): int;
}