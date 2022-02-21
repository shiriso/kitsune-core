<?php

namespace Kitsune\Core;

use Kitsune\Core\Contracts\DefinesClassPriority;
use Kitsune\Core\Exceptions\InvalidPriorityException;

class KitsunePriority implements DefinesClassPriority
{
    protected array $priorities = [
        'least' => 10,
        'low' => 20,
        'medium' => 30,
        'high' => 40,
        'important' => 50,
    ];

    /**
     * Creates a new instance of a Priority.
     *
     * @throws InvalidPriorityException
     */
    public function __construct(protected string $priority = 'medium')
    {
        if (!array_key_exists($priority, $this->priorities)) {
            throw new InvalidPriorityException($priority);
        }
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
