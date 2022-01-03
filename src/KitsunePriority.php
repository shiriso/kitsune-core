<?php

namespace Kitsune\Core;

use Kitsune\Core\Contracts\DefinesPriority;
use Kitsune\Core\Exceptions\InvalidPriorityException;

class KitsunePriority implements DefinesPriority
{
    protected array $priorities = [
        'least' => 10,
        'low' => 20,
        'medium' => 30,
        'high' => 40,
        'important' => 50,
    ];
    protected string $priority;

    /**
     * Creates a new instance of a Priority.
     *
     * @throws InvalidPriorityException
     */
    public function __construct(string $priority = 'medium')
    {
        if (!array_key_exists($priority, $this->priorities)) {
            throw new InvalidPriorityException($priority);
        }

        $this->priority = $priority;
    }

    /**
     * Get the numeric representation of the priority.
     *
     * @return int
     */
    public function getValue(): int
    {
        return $this->priorities[$this->priority];
    }
}
