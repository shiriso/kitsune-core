<?php

namespace Kitsune\Core;

use Kitsune\Core\Contracts\DefinesPriority;
use Kitsune\Core\Exceptions\InvalidPriorityException;

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

    /**
     * Get the priority based on the name.
     *
     * @param  string  $name
     * @return DefinesPriority
     * @throws InvalidPriorityException
     */
    public static function fromName(string $name = 'medium'): DefinesPriority
    {
        $enumPriorityCase = strtoupper($name);

        foreach(self::cases() as $priorityDefinition) {
            if($priorityDefinition->name === $enumPriorityCase) {
                return $priorityDefinition;
            }
        }

        throw new InvalidPriorityException($name);
    }
}
