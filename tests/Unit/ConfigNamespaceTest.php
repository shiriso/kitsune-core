<?php

namespace Kitsune\Core\Tests\Unit;

use Illuminate\Support\Facades\Config;
use Kitsune\Core\Concerns\UtilisesKitsune;
use Kitsune\Core\Contracts\IsSourceNamespace;
use Kitsune\Core\Tests\AbstractNamespaceTestCase;

class ConfigNamespaceTest extends AbstractNamespaceTestCase
{
    use UtilisesKitsune;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('kitsune.view.namespaces', [
            'config-default',
        ]);

        $this->getKitsuneManager()->initializeNamespaces();
    }

    /**
     * @test
     * @return IsSourceNamespace
     */
    public function namespaceCreatedFromConfig(): IsSourceNamespace
    {
        $this->assertTrue($this->getKitsuneManager()->hasNamespace('config-default'));

        return $this->getKitsuneManager()->getNamespace('config-default');
    }

    /**
     * @test
     * @depends namespaceCreatedFromConfig
     * @param  IsSourceNamespace  $namespace
     * @return IsSourceNamespace
     */
    public function isAutomaticallyCreatedByManager(IsSourceNamespace $namespace): IsSourceNamespace
    {
        return $this->validatesNamespaceConfiguration($namespace);
    }

    /**
     * @test
     * @depends isAutomaticallyCreatedByManager
     * @param  IsSourceNamespace  $namespace
     * @return IsSourceNamespace
     */
    public function hasDefaultSourceRepositories(IsSourceNamespace $namespace): IsSourceNamespace
    {
        $this->assertContains('vendor', $namespace->getRegisteredSources());
        $this->assertTrue($namespace->hasSource('vendor'));
        $this->assertInstanceOf(config('kitsune.core.service.source'), $namespace->getSource('vendor'));

        $this->assertContains('published', $namespace->getRegisteredSources());
        $this->assertTrue($namespace->hasSource('published'));
        $this->assertInstanceOf(config('kitsune.core.service.source'), $namespace->getSource('published'));

        $this->assertFalse($namespace->hasSource('does-not-exist'));

        $this->expectErrorMessage('Undefined array key "does-not-exist"');
        $namespace->getSource('does-not-exist');

        return $namespace;
    }
}
