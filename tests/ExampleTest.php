<?php

namespace Infinitypaul\LaravelCbs\Tests;

use Infinitypaul\LaravelCbs\LaravelCbsServiceProvider;
use Orchestra\Testbench\TestCase;

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
