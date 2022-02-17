<?php

namespace Kitsune\Core\Concerns;

use Kitsune\Core\Contracts\DefinesPriority;
use Kitsune\Core\Exceptions\MissingPriorityPropertyException;

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
        $this->validateHasPriorityIntegration();

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
        $this->validateHasPriorityIntegration();

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

    /**
     * Validates that all necessary properties exist in the class.
     *
     * @return void
     * @throws MissingPriorityPropertyException
     */
    protected function validateHasPriorityIntegration(): void
    {
        if (!property_exists($this, 'priority')) {
            throw new MissingPriorityPropertyException(static::class);
        }
    }
}
