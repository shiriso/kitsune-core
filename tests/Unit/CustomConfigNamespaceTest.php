<?php

namespace Kitsune\Core\Tests\Unit;

use Illuminate\Support\Facades\Config;
use Kitsune\Core\Concerns\UtilisesKitsune;
use Kitsune\Core\Contracts\IsSourceNamespace;
use Kitsune\Core\Tests\AbstractNamespaceTestCase;

class CustomConfigNamespaceTest extends AbstractNamespaceTestCase
{
    use UtilisesKitsune;

    protected int $expectedSourceCreatedEvents = 3;
    protected ?string $expectedLayout = 'testing';
    protected bool $expectedIncludeDefaults = true;
    protected string $expectedPriority = 'low';

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('kitsune.view.namespaces', [
            'config-custom' => [
                'layout' => 'testing',
                'include_defaults' => true,
                'paths' => $this->getExpectedPaths(),
                'priority' => 'low',
            ],
        ]);

        app('kitsune.manager')->initializeNamespaces();
    }

    /**
     * @test
     * @return IsSourceNamespace
     */
    public function namespaceCreatedFromConfig(): IsSourceNamespace
    {
        $this->assertTrue($this->getKitsuneManager()->hasNamespace('config-custom'));

        return $this->getKitsuneManager()->getNamespace('config-custom');
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

    /**
     * Override the getter for the property, as calls like resource_path are not allowed in declarations.
     *
     * @return array|null
     */
    protected function getExpectedPaths(): ?array
    {
        return [$this->namespacePath()];
    }

    /**
     * Get the test package's namespace path.
     *
     * @return string
     */
    protected function namespacePath(): string
    {
        return resource_path('views/config/namespace');
    }

}
