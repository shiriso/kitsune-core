<?php

namespace Kitsune\Core\Tests\Unit;

use Kitsune\Core\Contracts\IsSourceNamespace;
use Kitsune\Core\Tests\AbstractNamespaceTestCase;

class DefaultNamespaceConfigurationTest extends AbstractNamespaceTestCase
{
    /**
     * @test
     * @return IsSourceNamespace
     */
    public function canBeCreatedUsingDefaults(): IsSourceNamespace
    {
        return $this->createNamespace('default');
    }

    /**
     * @test
     * @depends canBeCreatedUsingDefaults
     * @param  IsSourceNamespace  $namespace
     * @return IsSourceNamespace
     */
    public function hasValidDefaultConfiguration(IsSourceNamespace $namespace): IsSourceNamespace
    {
        return $this->validatesNamespaceConfiguration($namespace);
    }

}
