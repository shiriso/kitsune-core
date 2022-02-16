<?php

namespace Kitsune\Core\Tests\Unit;

use Kitsune\Core\Facades\KitsuneCoreFacade;
use Kitsune\Core\Facades\KitsuneHelperFacade;
use Kitsune\Core\Facades\KitsuneManagerFacade;
use Kitsune\Core\Tests\AbstractTestCase;

class FacadeTest extends AbstractTestCase
{
    /**
     * @test
     * @return void
     */
    public function facadeDoesReturnCore()
    {
        $this->assertSame(app('kitsune'), KitsuneCoreFacade::getFacadeRoot());
    }

    /**
     * @test
     * @return void
     */
    public function facadeDoesReturnHelper()
    {
        $this->assertSame(app('kitsune.helper'), KitsuneHelperFacade::getFacadeRoot());
    }

    /**
     * @test
     * @return void
     */
    public function facadeDoesReturnManager()
    {
        $this->assertSame(app('kitsune.manager'), KitsuneManagerFacade::getFacadeRoot());
    }
}
