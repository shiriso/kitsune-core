<?php

namespace Kitsune\Core\Contracts;

interface ImplementsPriority
{
    /**
     * Get the current priority.
     *
     * @return DefinesPriority
     */
    public function getPriority(): DefinesPriority;

    /**
     * Set a new priority.
     *
     * @param  string|DefinesPriority|null  $priority
     * @return bool
     */
    public function setPriority(string|DefinesPriority|null $priority): bool;
}
