<?php

namespace Kitsune\Core\Tests\Unit;

use Kitsune\Core\Concerns\ManagesPaths;
use Kitsune\Core\Contracts\CanManagePaths;
use Kitsune\Core\Exceptions\MissingPathsPropertyException;
use Kitsune\Core\Tests\AbstractTestCase;

class ManagesPathsTest extends AbstractTestCase
{
    protected CanManagePaths $canManagePaths;
    protected CanManagePaths $canNotManagePaths;
    protected array $referencePaths = ['reference'];

    protected function setUp(): void
    {
        parent::setUp();

        $this->canManagePaths = new class ($this->referencePaths) implements CanManagePaths {
            use ManagesPaths;

            public function __construct(protected array $paths)
            {
            }
        };

        $this->canNotManagePaths = new class () implements CanManagePaths {
            use ManagesPaths;
        };
    }

    /**
     * @test
     * @return void
     */
    public function throwsMissingPathPropertyExceptionWhenModifying()
    {
        $this->expectException(MissingPathsPropertyException::class);

        $this->canNotManagePaths->addPath('test');
    }

    /**
     * @test
     * @return void
     */
    public function throwsMissingPathPropertyExceptionWhenRequesting()
    {
        $this->expectException(MissingPathsPropertyException::class);

        $this->canNotManagePaths->getRegisteredPaths();
    }

    /**
     * @test
     * @return CanManagePaths
     */
    public function canAppendPath(): CanManagePaths
    {
        $this->assertTrue($this->canManagePaths->addPath($this->appendPath()));
        $this->assertEquals(
            [...$this->referencePaths, $this->appendPath()],
            $this->canManagePaths->getRegisteredPaths()
        );

        return $this->canManagePaths;
    }

    /**
     * @test
     * @depends canAppendPath
     * @param  CanManagePaths  $canManagePaths
     * @return CanManagePaths
     */
    public function canPrependPath(CanManagePaths $canManagePaths): CanManagePaths
    {
        $this->assertTrue($canManagePaths->prependPath($this->prependPath()));
        $this->assertEquals(
            [$this->prependPath(), ...$this->referencePaths, $this->appendPath()],
            $canManagePaths->getRegisteredPaths()
        );

        return $canManagePaths;
    }

    /**
     * @test
     * @depends canPrependPath
     * @param  CanManagePaths  $canManagePaths
     * @return CanManagePaths
     */
    public function canAppendPathArray(CanManagePaths $canManagePaths): CanManagePaths
    {
        $this->assertTrue($canManagePaths->addPath($this->appendPathArray()));

        $expectedPaths = [
            $this->prependPath(),
            ...$this->referencePaths,
            $this->appendPath(),
            ...$this->appendPathArray()
        ];

        $this->assertEquals($expectedPaths, $canManagePaths->getRegisteredPaths());

        return $canManagePaths;
    }

    /**
     * @test
     * @depends canAppendPathArray
     * @param  CanManagePaths  $canManagePaths
     * @return CanManagePaths
     */
    public function canPrependPathArray(CanManagePaths $canManagePaths): CanManagePaths
    {
        $this->assertTrue($canManagePaths->prependPath($this->prependPathArray()));

        $expectedPaths = [
            ...$this->prependPathArray(),
            $this->prependPath(),
            ...$this->referencePaths,
            $this->appendPath(),
            ...$this->appendPathArray()
        ];

        $this->assertEquals($expectedPaths, $canManagePaths->getRegisteredPaths());

        return $canManagePaths;
    }

    /**
     * @test
     * @depends canPrependPathArray
     * @param  CanManagePaths  $canManagePaths
     * @return CanManagePaths
     */
    public function cantAddDuplicatePaths(CanManagePaths $canManagePaths): CanManagePaths
    {
        $this->assertFalse($canManagePaths->addPath($this->appendPath()));
        $this->assertFalse($canManagePaths->addPath($this->appendPathArray()));
        $this->assertFalse($canManagePaths->addPath($this->prependPath()));
        $this->assertFalse($canManagePaths->addPath($this->prependPathArray()));

        return $canManagePaths;
    }


    /**
     * Generates the resource path to test appends.
     *
     * @return string
     */
    protected function appendPath(): string
    {
        return 'append';
    }

    /**
     * Generates the resource path to test prepends.
     *
     * @return string
     */
    protected function prependPath(): string
    {
        return 'prepend';
    }

    /**
     * Generates a list of resource paths to test appending.
     *
     * @return array
     */
    protected function appendPathArray(): array
    {
        return [
            'append-array-1',
            'append-array-2',
        ];
    }

    /**
     * Generates a list of resource paths to test prepending.
     *
     * @return array
     */
    protected function prependPathArray(): array
    {
        return [
            'prepend-array-1',
            'prepend-array-2',
        ];
    }
}
