<?php

namespace Infinitypaul\Cbs\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Infinitypaul\LaravelCbs\Skeleton\SkeletonClass
 */
class Cbs extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'laravel-cbs';
    }
}
