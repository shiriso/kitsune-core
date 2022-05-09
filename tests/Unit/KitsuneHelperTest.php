<?php

namespace Kitsune\Core\Tests\Unit;

use Closure;
use Illuminate\Support\Facades\Config;
use Kitsune\Core\Concerns\HasPriority;
use Kitsune\Core\Concerns\UtilisesKitsune;
use Kitsune\Core\Contracts\DefinesPriority;
use Kitsune\Core\Contracts\ImplementsPriority;
use Kitsune\Core\Exceptions\InvalidKitsuneCoreException;
use Kitsune\Core\Exceptions\InvalidKitsuneManagerException;
use Kitsune\Core\Exceptions\InvalidPriorityDefinitionException;
use Kitsune\Core\Exceptions\InvalidSourceNamespaceException;
use Kitsune\Core\Exceptions\InvalidSourceRepositoryException;
use Kitsune\Core\Kitsune;
use Kitsune\Core\KitsuneEnumPriority;
use Kitsune\Core\KitsuneManager;
use Kitsune\Core\KitsunePriority;
use Kitsune\Core\SourceNamespace;
use Kitsune\Core\SourceRepository;
use Kitsune\Core\Tests\AbstractTestCase;

class KitsuneHelperTest extends AbstractTestCase
{
    use UtilisesKitsune;

    /**
     * @test
     * @dataProvider serviceDataProvider
     * @param  array  $service
     * @return void
     */
    public function usesServiceFromConfig(array $service): void
    {
        $this->assertEquals(config(sprintf('kitsune.core.service.%s', $service['config'])), $service['resolver']());
    }

    /**
     * @test
     * @dataProvider serviceDataProvider
     * @param  array  $service
     * @return void
     */
    public function fallsBackToDefaultService(array $service): void
    {
        $serviceConfig = config('kitsune.core.service');
        unset($serviceConfig[$service['config']]);
        Config::set('kitsune.core.service', $serviceConfig);

        $this->assertEquals($service['default'], $service['resolver']());
    }

    /**
     * @test
     * @dataProvider serviceDataProvider
     * @param  array  $service
     * @return void
     */
    public function exceptionIfServiceInterfaceIsMissing(array $service): void
    {
        Config::set(sprintf('kitsune.core.service.%s', $service['config']), null);

        $this->expectException($service['exception']);
        $service['resolver']();
    }

    /**
     * @test
     * @dataProvider toCamelCaseDataProvider
     * @param  array  $toTransform
     * @param  array  $expectation
     * @return void
     */
    public function convertsArrayKeysToCamelCaseKeys(array $toTransform, array $expectation): void
    {
        $this->assertEquals($expectation, $this->getKitsuneHelper()->toCamelKeys($toTransform));
    }

    /**
     * @test
     * @dataProvider toCamelCaseDataProvider
     * @param  array  $toTransform
     * @param  array  $expectation
     * @return void
     */
    public function convertsCollectionKeysToCamelCaseKeys(array $toTransform, array $expectation): void
    {
        $this->assertEquals(collect($expectation), $this->getKitsuneHelper()->toCamelKeys(collect($toTransform)));
    }

    /**
     * @test
     * @dataProvider toCamelCaseDataProvider
     * @param  array  $toTransform
     * @param  array  $expectation
     * @return void
     */
    public function convertsCollectionKeysToCamelCaseArrayKeys(array $toTransform, array $expectation): void
    {
        $this->assertEquals($expectation, $this->getKitsuneHelper()->toCamelKeys(collect($toTransform), true));
    }

    /**
     * @test
     * @return void
     */
    public function usesPriorityFromConfig(): void
    {
        $this->assertEquals(
            config('kitsune.core.priority.definition'),
            $this->getKitsuneHelper()->getPriorityDefinition()
        );
    }

    /**
     * @test
     * @return void
     */
    public function fallsBackToDefaultPriority(): void
    {
        $priorityConfig = config('kitsune.core.priority');
        unset($priorityConfig['definition']);
        Config::set('kitsune.core.priority', $priorityConfig);

        $this->assertEquals(KitsunePriority::class, $this->getKitsuneHelper()->getPriorityDefinition());
    }

    /**
     * @test
     * @return void
     */
    public function exceptionIfDefinesPriorityInterfaceIsMissing(): void
    {
        Config::set('kitsune.core.priority.definition', null);

        $this->expectException(InvalidPriorityDefinitionException::class);
        $this->getKitsuneHelper()->getPriorityDefinition();
    }

    /**
     * @test
     * @return void
     */
    public function justReturnsPriorityIfPriorityObjectIsGiven(): void
    {
        $priority = $this->getKitsuneHelper()->getPriorityDefault('medium');

        $this->assertSame($priority, $this->getKitsuneHelper()->getPriorityDefault($priority));
    }

    /**
     * @test
     * @dataProvider priorityValueMappingDataProvider
     * @param  string  $priority
     * @param  int  $expectation
     * @return void
     */
    public function fallsBackToProgrammedPriorityDefaults(string $priority, int $expectation): void
    {
        Config::set('kitsune.core.priority.defaults', []);

        $this->assertEquals($expectation, $this->getKitsuneHelper()->getPriorityDefault($priority)->getValue());
    }

    /**
     * @test
     * @return void
     */
    public function retrievesPriorityBasedOnImplementsPriority(): void
    {
        foreach ($this->implementsPriorityMapping() as [$implementsPriority, $expectation]) {
            $this->assertEquals($expectation, $this->getKitsuneHelper()->getDefaultIdentifier($implementsPriority));
        }
    }

    /**
     * @test
     * @dataProvider availableDefaultSourcesDataProvider
     * @param  array  $expectation
     * @param  Closure|null  $closure
     * @return void
     */
    public function availableDefaultSources(array $expectation, Closure $closure = null): void
    {
        $closure && $closure();

        $this->assertEqualsCanonicalizing(
            $expectation,
            $this->getKitsuneHelper()->getAvailableDefaultSourceConfigurations()
        );
    }

    /**
     * @test
     * @requires PHP >= 8.1.0
     * @return void
     */
    public function canIdentifyPriorityType(): void
    {
        $this->assertFalse($this->getKitsuneHelper()->priorityDefinitionIsEnum());

        Config::set('kitsune.core.priority.definition', KitsuneEnumPriority::class);

        $this->assertTrue($this->getKitsuneHelper()->priorityDefinitionIsEnum());
    }

    /**
     * @test
     * @return void
     */
    public function filtersNonExistentPaths(): void
    {
        $this->assertEqualsCanonicalizing(
            [
                resource_path('views'),
            ],
            $this->getKitsuneHelper()->filterPaths([
                resource_path('views'),
                resource_path('non-existent')
            ])
        );
    }

    /**
     * @test
     * @dataProvider pathComparisonDataProvider
     * @param  array  $newPaths
     * @param  array|null  $oldPaths
     * @param  bool  $expectation
     * @return void
     */
    public function canIdentifyPathUpdates(array $newPaths, ?array $oldPaths, bool $expectation): void
    {
        $this->assertEquals($expectation, $this->getKitsuneHelper()->pathsHaveUpdates($newPaths, $oldPaths));
    }

    /**
     * @test
     * @dataProvider viewConfigOverrideDataProvider
     * @param  Closure  $customisations
     * @param  Closure  $expectations
     * @return void
     */
    public function viewConfigCanOverrideDefaultSources(Closure $customisations, Closure $expectations): void
    {
        Config::set('kitsune.view.sources', $customisations());

        $this->assertEquals($expectations(), $this->getKitsuneHelper()->getDefaultSourceConfigurations());
    }

    /**
     * @test
     * @dataProvider packageConfigOverrideDataProvider
     * @param  Closure  $viewCustomisations
     * @param  Closure  $packageCustomisations
     * @param  Closure  $expectations
     * @return void
     */
    public function packageConfigCanOverrideDefaultSources(
        Closure $viewCustomisations,
        Closure $packageCustomisations,
        Closure $expectations
    ): void {
        Config::set('kitsune.view.sources', $viewCustomisations());
        Config::set('kitsune.packages.kitsune.sources', $packageCustomisations());

        $this->assertEquals($expectations(), $this->getKitsuneHelper()->getPackageSourceConfigurations('kitsune'));
    }

    /**
     * @return array
     */
    public function availableDefaultSourcesDataProvider(): array
    {
        return [
            'default' => [
                ['published', 'vendor'],
            ],
            'with-custom' => [
                ['custom', 'published', 'vendor'],
                function () {
                    Config::set('kitsune.view.sources.custom', ['base_path' => resource_path('custom')]);
                },
            ],
            'with-multiple' => [
                ['custom', 'multiple', 'published', 'vendor'],
                function () {
                    Config::set('kitsune.view.sources.custom', ['base_path' => resource_path('custom')]);
                    Config::set('kitsune.view.sources.multiple', ['base_path' => resource_path('multiple')]);
                },
            ],
        ];
    }

    /**
     * Data provider for all kinds of services which classes can be exchanged by configuration and are retrieved by the Helper.
     *
     * @return array
     */
    public function serviceDataProvider(): array
    {
        return [
            'KitsuneCore' => [
                [
                    'config' => 'class',
                    'default' => Kitsune::class,
                    'resolver' => fn() => $this->getKitsuneHelper()->getCoreClass(),
                    'exception' => InvalidKitsuneCoreException::class
                ]
            ],
            'KitsuneManager' => [
                [
                    'config' => 'manager',
                    'default' => KitsuneManager::class,
                    'resolver' => fn() => $this->getKitsuneHelper()->getManagerClass(),
                    'exception' => InvalidKitsuneManagerException::class
                ]
            ],
            'SourceNamespace' => [
                [
                    'config' => 'namespace',
                    'default' => SourceNamespace::class,
                    'resolver' => fn() => $this->getKitsuneHelper()->getSourceNamespaceClass(),
                    'exception' => InvalidSourceNamespaceException::class
                ]
            ],
            'SourceRepository' => [
                [
                    'config' => 'source',
                    'default' => SourceRepository::class,
                    'resolver' => fn() => $this->getKitsuneHelper()->getSourceRepositoryClass(),
                    'exception' => InvalidSourceRepositoryException::class
                ]
            ],
        ];
    }

    /**
     * Data Provider for different kinds of possible inputs to test the to camel keys functionality.
     *
     * @return array
     */
    public function toCamelCaseDataProvider(): array
    {
        return [
            'underscores' => [
                ['using_under_score' => 'test'],
                ['usingUnderScore' => 'test'],
            ],
            'spaces' => [
                ['using spaces' => 'test'],
                ['usingSpaces' => 'test'],
            ],
            'dashes' => [
                ['using-dashes' => 'test'],
                ['usingDashes' => 'test'],
            ],
            'camel case' => [
                ['usingCamelCase' => 'test'],
                ['usingCamelCase' => 'test'],
            ],
        ];
    }

    /**
     * Data Provider for priorities to default values.
     *
     * @return array
     */
    public function priorityValueMappingDataProvider(): array
    {
        return [
            'namespace' => ['namespace', 30],
            'source' => ['source', 30],
            'laravel' => ['laravel', 50],
            'published' => ['published', 40],
            'vendor' => ['vendor', 20],
            'least' => ['least', 10],
            'low' => ['low', 20],
            'medium' => ['medium', 30],
            'high' => ['high', 40],
            'important' => ['important', 50],
        ];
    }

    /**
     * "Data Provider" for default priorities for objects using ImplementsPriority.
     *
     * @return array
     */
    protected function implementsPriorityMapping(): array
    {
        $namespace = $this->getKitsuneManager()->getNamespace('kitsune');

        !$namespace->hasSource('testing') && $namespace->addSource('testing', basePath: resource_path('testing'),);

        $customObject = new class (null) implements ImplementsPriority {
            use HasPriority;

            public function __construct(protected string|DefinesPriority|null $priority)
            {
                $this->setPriority($this->priority);
            }
        };

        return [
            'namespace' => [$namespace, 'namespace'],
            'published' => [$namespace->getSource('published'), 'published'],
            'vendor' => [$namespace->getSource('vendor'), 'vendor'],
            'custom source' => [$namespace->getSource('testing'), 'source'],
            'custom class' => [$customObject, 'medium'],
        ];
    }

    /**
     * Data Provider for path comparisons if there have been updates or not.
     *
     * @return array
     */
    public function pathComparisonDataProvider(): array
    {
        return [
            'old path null' => [
                ['test'],
                null,
                true,
            ],
            'matching single entry paths' => [
                ['test'],
                ['test'],
                false,
            ],
            'new paths with increased amount' => [
                ['test', 'extra'],
                ['test'],
                true,
            ],
            'new paths with decreased amount' => [
                ['test'],
                ['test', 'extra'],
                true,
            ],
            'matching multi entry paths' => [
                ['test', 'extra'],
                ['test', 'extra'],
                false,
            ],
            'order gets normalized' => [
                ['test', 'extra'],
                ['extra', 'test'],
                false,
            ],
        ];
    }

    /**
     * Data Provider to validate view config overrides for default sources.
     *
     * @return array
     */
    public function viewConfigOverrideDataProvider(): array
    {
        return [
            'without overrides' => [
                fn() => [],
                fn() => [
                    'published' => [
                        'basePath' => resource_path('views/vendor'),
                        'priority' => 'published',
                        'paths' => [],
                    ],
                    'vendor' => [
                        'basePath' => base_path('vendor'),
                        'priority' => 'vendor',
                        'paths' => [],
                    ],
                ],
            ],
            'overrides base path' => [
                fn() => [
                    'published' => [
                        'base_path' => resource_path('views/vendor/override'),
                    ],
                    'vendor' => [
                        'base_path' => base_path('vendor/override'),
                    ],
                ],
                fn() => [
                    'published' => [
                        'basePath' => resource_path('views/vendor/override'),
                        'priority' => 'published',
                        'paths' => [],
                    ],
                    'vendor' => [
                        'basePath' => base_path('vendor/override'),
                        'priority' => 'vendor',
                        'paths' => [],
                    ],
                ],
            ],
            'overrides paths' => [
                fn() => [
                    'published' => [
                        'paths' => ['paths'],
                    ],
                    'vendor' => [
                        'paths' => ['paths'],
                    ],
                ],
                fn() => [
                    'published' => [
                        'basePath' => resource_path('views/vendor'),
                        'priority' => 'published',
                        'paths' => ['paths'],
                    ],
                    'vendor' => [
                        'basePath' => base_path('vendor'),
                        'priority' => 'vendor',
                        'paths' => ['paths'],
                    ],
                ],
            ],
            'overrides priority' => [
                fn() => [
                    'published' => [
                        'priority' => 'source',
                    ],
                    'vendor' => [
                        'priority' => 'source',
                    ],
                ],
                fn() => [
                    'published' => [
                        'basePath' => resource_path('views/vendor'),
                        'priority' => 'source',
                        'paths' => [],
                    ],
                    'vendor' => [
                        'basePath' => base_path('vendor'),
                        'priority' => 'source',
                        'paths' => [],
                    ],
                ],
            ],
            'override various' => [
                fn() => [
                    'published' => [
                        'base_path' => resource_path('views/vendor/override'),
                        'priority' => 'source',
                        'paths' => ['paths'],
                    ],
                    'vendor' => [
                        'base_path' => base_path('vendor/override'),
                        'priority' => 'source',
                        'paths' => ['paths'],
                    ],
                ],
                fn() => [
                    'published' => [
                        'basePath' => resource_path('views/vendor/override'),
                        'priority' => 'source',
                        'paths' => ['paths'],
                    ],
                    'vendor' => [
                        'basePath' => base_path('vendor/override'),
                        'priority' => 'source',
                        'paths' => ['paths'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Data Provider to validate view config overrides for default sources.
     *
     * @return array
     */
    public function packageConfigOverrideDataProvider(): array
    {
        return [
            'without overrides' => [
                fn() => [],
                fn() => [],
                fn() => [
                    'published' => [
                        'basePath' => resource_path('views/vendor'),
                        'priority' => 'published',
                        'paths' => [],
                    ],
                    'vendor' => [
                        'basePath' => base_path('vendor'),
                        'priority' => 'vendor',
                        'paths' => [],
                    ],
                ],
            ],
            'package overrides base path' => [
                fn() => [],
                fn() => [
                    'published' => [
                        'base_path' => resource_path('views/vendor/override'),
                    ],
                    'vendor' => [
                        'base_path' => base_path('vendor/override'),
                    ],
                ],
                fn() => [
                    'published' => [
                        'basePath' => resource_path('views/vendor/override'),
                        'priority' => 'published',
                        'paths' => [],
                    ],
                    'vendor' => [
                        'basePath' => base_path('vendor/override'),
                        'priority' => 'vendor',
                        'paths' => [],
                    ],
                ],
            ],
            'package overrides paths' => [
                fn() => [],
                fn() => [
                    'published' => [
                        'paths' => ['paths'],
                    ],
                    'vendor' => [
                        'paths' => ['paths'],
                    ],
                ],
                fn() => [
                    'published' => [
                        'basePath' => resource_path('views/vendor'),
                        'priority' => 'published',
                        'paths' => ['paths'],
                    ],
                    'vendor' => [
                        'basePath' => base_path('vendor'),
                        'priority' => 'vendor',
                        'paths' => ['paths'],
                    ],
                ],
            ],
            'package overrides priority' => [
                fn() => [],
                fn() => [
                    'published' => [
                        'priority' => 'source',
                    ],
                    'vendor' => [
                        'priority' => 'source',
                    ],
                ],
                fn() => [
                    'published' => [
                        'basePath' => resource_path('views/vendor'),
                        'priority' => 'source',
                        'paths' => [],
                    ],
                    'vendor' => [
                        'basePath' => base_path('vendor'),
                        'priority' => 'source',
                        'paths' => [],
                    ],
                ],
            ],
            'package override various' => [
                fn() => [],
                fn() => [
                    'published' => [
                        'base_path' => resource_path('views/vendor/override'),
                        'priority' => 'source',
                        'paths' => ['paths'],
                    ],
                    'vendor' => [
                        'base_path' => base_path('vendor/override'),
                        'priority' => 'source',
                        'paths' => ['paths'],
                    ],
                ],
                fn() => [
                    'published' => [
                        'basePath' => resource_path('views/vendor/override'),
                        'priority' => 'source',
                        'paths' => ['paths'],
                    ],
                    'vendor' => [
                        'basePath' => base_path('vendor/override'),
                        'priority' => 'source',
                        'paths' => ['paths'],
                    ],
                ],
            ],
            'package without but view with overrides' => [
                fn() => [
                    'published' => [
                        'base_path' => resource_path('views/vendor/override'),
                    ],
                    'vendor' => [
                        'base_path' => base_path('vendor/override'),
                    ],
                ],
                fn() => [],
                fn() => [
                    'published' => [
                        'basePath' => resource_path('views/vendor/override'),
                        'priority' => 'published',
                        'paths' => [],
                    ],
                    'vendor' => [
                        'basePath' => base_path('vendor/override'),
                        'priority' => 'vendor',
                        'paths' => [],
                    ],
                ],
            ],
            'package overrides base path from view override' => [
                fn() => [
                    'published' => [
                        'base_path' => resource_path('views/vendor/override'),
                    ],
                    'vendor' => [
                        'base_path' => base_path('vendor/override'),
                    ],
                ],
                fn() => [
                    'published' => [
                        'base_path' => resource_path('views/vendor/package'),
                    ],
                    'vendor' => [
                        'base_path' => base_path('vendor/package'),
                    ],
                ],
                fn() => [
                    'published' => [
                        'basePath' => resource_path('views/vendor/package'),
                        'priority' => 'published',
                        'paths' => [],
                    ],
                    'vendor' => [
                        'basePath' => base_path('vendor/package'),
                        'priority' => 'vendor',
                        'paths' => [],
                    ],
                ],
            ],
            'package overrides paths from view override' => [
                fn() => [
                    'published' => [
                        'paths' => ['paths'],
                    ],
                    'vendor' => [
                        'paths' => ['paths'],
                    ],
                ],
                fn() => [
                    'published' => [
                        'paths' => ['package'],
                    ],
                    'vendor' => [
                        'paths' => ['package'],
                    ],
                ],
                fn() => [
                    'published' => [
                        'basePath' => resource_path('views/vendor'),
                        'priority' => 'published',
                        'paths' => ['package'],
                    ],
                    'vendor' => [
                        'basePath' => base_path('vendor'),
                        'priority' => 'vendor',
                        'paths' => ['package'],
                    ],
                ],
            ],
            'package overrides priority from view override' => [
                fn() => [
                    'published' => [
                        'priority' => 'source',
                    ],
                    'vendor' => [
                        'priority' => 'source',
                    ],
                ],
                fn() => [
                    'published' => [
                        'priority' => 'least',
                    ],
                    'vendor' => [
                        'priority' => 'least',
                    ],
                ],
                fn() => [
                    'published' => [
                        'basePath' => resource_path('views/vendor'),
                        'priority' => 'least',
                        'paths' => [],
                    ],
                    'vendor' => [
                        'basePath' => base_path('vendor'),
                        'priority' => 'least',
                        'paths' => [],
                    ],
                ],
            ],
            'package partially overrides various' => [
                fn() => [],
                fn() => [
                    'published' => [
                        'base_path' => resource_path('views/vendor/package'),
                        'priority' => 'least',
                        'paths' => ['package'],
                    ],
                    'vendor' => [
                        'base_path' => base_path('vendor/package'),
                        'priority' => 'least',
                        'paths' => ['package'],
                    ],
                ],
                fn() => [
                    'published' => [
                        'basePath' => resource_path('views/vendor/package'),
                        'priority' => 'least',
                        'paths' => ['package'],
                    ],
                    'vendor' => [
                        'basePath' => base_path('vendor/package'),
                        'priority' => 'least',
                        'paths' => ['package'],
                    ],
                ],
            ],
            'package partially overrides various mixed with view' => [
                fn() => [
                    'published' => [
                        'paths' => ['paths'],
                    ],
                    'vendor' => [
                        'base_path' => base_path('vendor/override'),
                        'paths' => ['paths'],
                    ],
                ],
                fn() => [
                    'published' => [
                        'base_path' => resource_path('views/vendor/package'),
                        'priority' => 'least',
                    ],
                    'vendor' => [
                        'paths' => ['package'],
                    ],
                ],
                fn() => [
                    'published' => [
                        'basePath' => resource_path('views/vendor/package'),
                        'priority' => 'least',
                        'paths' => ['paths'],
                    ],
                    'vendor' => [
                        'basePath' => base_path('vendor/override'),
                        'priority' => 'vendor',
                        'paths' => ['package'],
                    ],
                ],
            ],
        ];
    }
}
