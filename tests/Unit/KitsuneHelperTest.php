<?php

namespace Kitsune\Core\Tests\Unit;

use Illuminate\Support\Facades\Config;
use Kitsune\Core\Concerns\UtilisesKitsune;
use Kitsune\Core\Exceptions\InvalidKitsuneCoreException;
use Kitsune\Core\Exceptions\InvalidKitsuneManagerException;
use Kitsune\Core\Exceptions\InvalidSourceNamespaceException;
use Kitsune\Core\Exceptions\InvalidSourceRepositoryException;
use Kitsune\Core\Kitsune;
use Kitsune\Core\KitsuneManager;
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
}
