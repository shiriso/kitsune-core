<?php

namespace Kitsune\Core\Tests\Unit;

use Kitsune\Core\Contracts\IsSourceNamespace;
use Kitsune\Core\Contracts\IsSourceRepository;
use Kitsune\Core\Tests\AbstractNamespaceTestCase;

class PackageNamespaceTest extends AbstractNamespaceTestCase
{
    protected int $expectedSourceCreatedEvents = 3;
    protected ?string $expectedLayout = 'testing';
    protected bool $expectedIncludeDefaults = true;
    protected string $expectedPriority = 'low';

    /**
     * @test
     * @return IsSourceNamespace
     */
    public function isAutomaticallyCreatedByManager(): IsSourceNamespace
    {
        return $this->validatesNamespaceConfiguration(app('kitsune.manager')->getNamespace('package'));
    }

    /**
     * @test
     * @return IsSourceNamespace
     */
    public function canBeCreatedUsingPackageDeclaration(): IsSourceNamespace
    {
        return $this->createNamespace(
            'package',
            app('kitsune.helper')->toCamelKeys(config('kitsune.packages.package.namespace'))
        );
    }

    /**
     * @test
     * @depends canBeCreatedUsingPackageDeclaration
     * @param  IsSourceNamespace  $namespace
     * @return IsSourceNamespace
     */
    public function hasValidConfigurationFromPackage(IsSourceNamespace $namespace): IsSourceNamespace
    {
        return $this->validatesNamespaceConfiguration($namespace);
    }

    /**
     * @test
     * @depends hasValidConfigurationFromPackage
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
     * @test
     * @depends hasValidConfigurationFromPackage
     * @param  IsSourceNamespace  $namespace
     * @return IsSourceNamespace
     */
    public function publishedSourceIsCustomisedFromPackage(IsSourceNamespace $namespace): IsSourceNamespace
    {
        $source = $namespace->getSource('published');

        $this->assertEquals(resource_path('views/package/source/published/'), $source->getBasePath());
        $this->assertEquals([$this->sourceDefaultPath()], $source->getRegisteredPaths());
        $this->hasValidPriority('high', $source->getPriority());

        return $namespace;
    }

    /**
     * @test
     * @depends hasValidConfigurationFromPackage
     * @param  IsSourceNamespace  $namespace
     * @return IsSourceNamespace
     */
    public function vendorSourceHasDefaultConfiguration(IsSourceNamespace $namespace): IsSourceNamespace
    {
        $source = $namespace->getSource('vendor');

        $this->assertEquals(base_path('vendor/'), $source->getBasePath());
        $this->assertEquals([], $source->getRegisteredPaths());
        $this->hasValidPriority('vendor', $source->getPriority());

        return $namespace;
    }

    /**
     * @test
     * @depends hasValidConfigurationFromPackage
     * @param  IsSourceNamespace  $namespace
     * @return IsSourceNamespace
     */
    public function hasTestingSourceFromPackage(IsSourceNamespace $namespace): IsSourceNamespace
    {
        $source = $namespace->getSource('testing');

        $this->assertEquals(resource_path('views/package/source/testing/'), $source->getBasePath());
        $this->assertEquals([$this->sourceDefaultPath()], $source->getRegisteredPaths());
        $this->hasValidPriority('high', $source->getPriority());

        return $namespace;
    }

    /**
     * @test
     * @depends hasValidConfigurationFromPackage
     * @param  IsSourceNamespace  $namespace
     * @return IsSourceNamespace
     */
    public function generatesGroupedPathsByPriority(IsSourceNamespace $namespace): IsSourceNamespace
    {
        $this->assertEqualsCanonicalizing([
            app('kitsune.helper')->getPriorityDefault('low')->getValue() => [
                $this->namespacePath(),
            ],
            app('kitsune.helper')->getPriorityDefault('high')->getValue() => [
                $this->sourcePath($namespace->getSource('published')),
                $this->sourcePath($namespace->getSource('testing')),
            ],
        ], $namespace->getPaths());

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
        return resource_path('views/package/namespace');
    }

    /**
     * Get the default relative path used for sources.
     *
     * @return string
     */
    protected function sourceDefaultPath(): string
    {
        return 'path';
    }

    /**
     * Get the compiled path for a given source based on the source's basePath and given path.
     *
     * @param  IsSourceRepository  $source
     * @param  string|null  $path
     * @return string
     */
    protected function sourcePath(IsSourceRepository $source, string $path = null): string
    {
        return $source->getBasePath().($path ?? $this->sourceDefaultPath());
    }
}
