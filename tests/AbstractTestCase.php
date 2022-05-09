<?php

namespace Kitsune\Core\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Kitsune\Core\KitsuneCoreServiceProvider;

abstract class AbstractTestCase extends Orchestra
{
    /**
     * Get base path.
     *
     * @return string
     */
    protected function getBasePath()
    {
        return __DIR__.'/scaffolds/includes-kitsune';
    }

    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     *
     * @return array
     */
    protected function getPackageProviders($app): array
    {
        return [
            KitsuneCoreServiceProvider::class
        ];
    }
}
