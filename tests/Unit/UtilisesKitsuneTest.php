<?php

namespace Kitsune\Core\Tests\Unit;

use Kitsune\Core\Concerns\UtilisesKitsune;
use Kitsune\Core\Tests\AbstractTestCase;

class UtilisesKitsuneTest extends AbstractTestCase
{
    use UtilisesKitsune;

    /**
     * @test
     * @return void
     */
    public function doesReturnCore()
    {
        $this->assertSame(app('kitsune'), $this->getKitsuneCore());
    }

    /**
     * @test
     * @return void
     */
    public function doesReturnHelper()
    {
        $this->assertSame(app('kitsune.helper'), $this->getKitsuneHelper());
    }

    /**
     * @test
     * @return void
     */
    public function doesReturnManager()
    {
        $this->assertSame(app('kitsune.manager'), $this->getKitsuneManager());
    }
}
