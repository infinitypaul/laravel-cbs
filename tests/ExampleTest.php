<?php

namespace Infinitypaul\LaravelCbs\Tests;

use Orchestra\Testbench\TestCase;
use Infinitypaul\LaravelCbs\LaravelCbsServiceProvider;

class ExampleTest extends TestCase
{

    protected function getPackageProviders($app)
    {
        return [LaravelCbsServiceProvider::class];
    }
    
    /** @test */
    public function true_is_true()
    {
        $this->assertTrue(true);
    }
}
