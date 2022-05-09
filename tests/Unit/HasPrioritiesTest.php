<?php

namespace Kitsune\Core\Tests\Unit;

use Kitsune\Core\Concerns\HasPriority;
use Kitsune\Core\Concerns\UtilisesKitsune;
use Kitsune\Core\Contracts\DefinesPriority;
use Kitsune\Core\Contracts\ImplementsPriority;
use Kitsune\Core\Exceptions\InvalidPriorityException;
use Kitsune\Core\Exceptions\MissingPriorityPropertyException;
use Kitsune\Core\Tests\AbstractTestCase;

class HasPrioritiesTest extends AbstractTestCase
{
    use UtilisesKitsune;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @test
     * @return void
     */
    public function throwsMissingPriorityPropertyExceptionWhenModifying()
    {
        $invalidPriorityObject = new class () implements ImplementsPriority {
            use HasPriority;
        };

        $this->expectException(MissingPriorityPropertyException::class);
        $invalidPriorityObject->setPriority(null);
    }

    /**
     * @test
     * @return void
     */
    public function cantBeCreatedUsingInvalidPriority(): void
    {
        $this->expectException(InvalidPriorityException::class);
        $this->getPriorityImplementingObject('invalid');
    }

    /**
     * @test
     * @return ImplementsPriority
     */
    public function canBeCreatedUsingNullDefault(): ImplementsPriority
    {
        $implementsPriority = $this->getPriorityImplementingObject(null);

        $this->assertInstanceOf(ImplementsPriority::class, $implementsPriority);

        return $implementsPriority;
    }

    /**
     * @test
     * @depends canBeCreatedUsingNullDefault
     * @param  ImplementsPriority  $implementsPriority
     * @return DefinesPriority
     */
    public function canRetrievePriority(ImplementsPriority $implementsPriority): DefinesPriority
    {
        $definesPriority = $implementsPriority->getPriority();

        $this->assertInstanceOf(DefinesPriority::class, $definesPriority);

        return $definesPriority;
    }

    /**
     * @test
     * @depends canRetrievePriority
     * @param  DefinesPriority  $definesPriority
     * @return void
     */
    public function priorityUsesClassBasedDefinition(DefinesPriority $definesPriority): void
    {
        $this->assertInstanceOf(config('kitsune.core.priority.definition'), $definesPriority);
        $this->assertFalse($this->getKitsuneHelper()->priorityDefinitionIsEnum());
    }

    /**
     * @test
     * @depends canRetrievePriority
     * @param  DefinesPriority  $definesPriority
     * @return void
     */
    public function canRetrievePriorityValue(DefinesPriority $definesPriority): void
    {
        $this->assertEquals(30, $definesPriority->getValue());
    }

    /**
     * @test
     * @depends canBeCreatedUsingNullDefault
     * @param  ImplementsPriority  $implementsPriority
     * @return ImplementsPriority
     */
    public function doesNotChangeOnSamePriority(ImplementsPriority $implementsPriority): ImplementsPriority
    {
        $this->assertFalse($implementsPriority->setPriority('medium'));
        $this->assertFalse($implementsPriority->isUpdated());

        return $implementsPriority;
    }

    /**
     * @test
     * @depends canBeCreatedUsingNullDefault
     * @param  ImplementsPriority  $implementsPriority
     * @return ImplementsPriority
     */
    public function canSetNewPriority(ImplementsPriority $implementsPriority): ImplementsPriority
    {
        $this->assertTrue($implementsPriority->setPriority('important'));
        $this->assertEquals(50, $implementsPriority->getPriority()->getValue());

        return $implementsPriority;
    }

    /**
     * @test
     * @depends canSetNewPriority
     * @param  ImplementsPriority  $implementsPriority
     * @return ImplementsPriority
     */
    public function hasExecutedDispatchUpdatedEvent(ImplementsPriority $implementsPriority): ImplementsPriority
    {
        $this->assertTrue($implementsPriority->isUpdated());

        return $implementsPriority;
    }

    /**
     * @test
     * @dataProvider availablePriorityRatings
     * @param  string  $rating
     * @param  int  $expectedValue
     * @return void
     */
    public function priorityValueMatchesWithRating(string $rating, int $expectedValue): void
    {
        $implementsPriority = $this->getPriorityImplementingObject($rating);
        $this->assertIsObject($implementsPriority);
        $this->assertEquals($expectedValue, $implementsPriority->getPriority()->getValue());
    }

    /**
     * @test
     * @dataProvider availableMappedPriorityRatings
     * @param  string  $rating
     * @param  int  $expectedValue
     * @return void
     */
    public function mappedPriorityValueMatchesWithRating(string $rating, int $expectedValue): void
    {
        $implementsPriority = $this->getPriorityImplementingObject($rating);
        $this->assertIsObject($implementsPriority);
        $this->assertEquals($expectedValue, $implementsPriority->getPriority()->getValue());
    }

    /**
     * @return array
     */
    public function availablePriorityRatings(): array
    {
        return [
            'least' => ['least', 10],
            'low' => ['low', 20],
            'medium' => ['medium', 30],
            'high' => ['high', 40],
            'important' => ['important', 50],
        ];
    }

    /**
     * @return array
     */
    public function availableMappedPriorityRatings(): array
    {
        return [
            'namespace' => ['namespace', 30],
            'source' => ['source', 20],
            'vendor' => ['vendor', 20],
            'published' => ['published', 30],
        ];
    }

    /**
     * @param  string|DefinesPriority|null  $priority
     * @return ImplementsPriority
     */
    protected function getPriorityImplementingObject(string|DefinesPriority|null $priority): ImplementsPriority
    {
        return new class ($priority) implements ImplementsPriority {
            use HasPriority;

            protected bool $isUpdated = false;

            public function __construct(protected string|DefinesPriority|null $priority)
            {
                $this->setPriority($this->priority);
            }

            public function isUpdated(): bool
            {
                return $this->isUpdated;
            }

            protected function dispatchUpdatedEvent(): void
            {
                $this->isUpdated = true;
            }
        };
    }
}
