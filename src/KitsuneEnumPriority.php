<?php

namespace Kitsune\Core;

use Kitsune\Core\Contracts\DefinesPriority;

enum KitsuneEnumPriority: int implements DefinesPriority
{
    case LEAST = 10;
    case LOW = 20;
    case MEDIUM = 30;
    case HIGH = 40;
    case IMPORTANT = 50;

    /**
     * Get the numeric representation of the priority.
     *
     * @return int
     */
    public function getValue(): int
    {
        return $this->value;
    }
}
