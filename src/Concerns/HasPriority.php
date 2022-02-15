<?php

namespace Kitsune\Core\Concerns;

use Kitsune\Core\Contracts\DefinesPriority;

trait HasPriority
{
    use UtilisesKitsune;

    /**
     * Set a new priority.
     *
     * @param  string|DefinesPriority|null  $priority
     * @return bool
     */
    public function setPriority(string|DefinesPriority|null $priority): bool
    {
        if (!is_a($priority, DefinesPriority::class)) {
            $priority = $this->getDefaultPriority($priority);
        }

        if (!is_a($this->priority, DefinesPriority::class)) {
            $this->priority = $this->getDefaultPriority($this->priority);
        }

        if ($this->priority->getValue() !== $priority->getValue()) {
            $this->priority = $priority;

            if (method_exists($this, 'dispatchUpdatedEvent')) {
                $this->dispatchUpdatedEvent();
            }

            return true;
        }

        return false;
    }

    /**
     * Get the current priority.
     *
     * @return DefinesPriority
     */
    public function getPriority(): DefinesPriority
    {
        return $this->priority;
    }

    /**
     * Get the default priority for the source based on a given priority or the global default.
     *
     * @param  string|null  $priority
     * @return DefinesPriority
     */
    protected function getDefaultPriority(?string $priority = null): DefinesPriority
    {
        return $this->getKitsuneHelper()->getPriorityDefault($priority ?? $this);
    }
}
