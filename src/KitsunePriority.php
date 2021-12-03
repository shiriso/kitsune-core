<?php

namespace Shiriso\Kitsune\Core;

use Shiriso\Kitsune\Core\Contracts\DefinesPriority;
use Shiriso\Kitsune\Core\Exceptions\InvalidPriorityException;

class KitsunePriority implements DefinesPriority
{
    protected int $layoutIncrement = 5;
    protected array $priorities = [
        'least' => 10,
        'low' => 20,
        'medium' => 30,
        'high' => 40,
        'important' => 50,
    ];
    protected string $priority;

    /**
     * @throws InvalidPriorityException
     */
    public function __construct(string $priority = 'medium')
    {
        if (!array_key_exists($priority, $this->priorities)) {
            throw new InvalidPriorityException($priority);
        }

        $this->priority = $priority;
    }

    public function getPriorityValue(): int
    {
        return $this->priorities[$this->priority];
    }

    public function getIncreasedPriorityValue(): int
    {
        return $this->getPriorityValue() + $this->layoutIncrement;
    }
}