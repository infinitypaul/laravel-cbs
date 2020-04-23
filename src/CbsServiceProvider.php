<?php

/*
 * This file is part of the Laravel Cbs package.
 *
 * (c) Edward Paul <infinitypaul@live.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Infinitypaul\Cbs;

use Illuminate\Support\ServiceProvider;

class CbsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/cbs.php' => config_path('cbs.php'),
            ], 'config');
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__.'/../config/cbs.php', 'cbs');

        // Register the main class to use with the facade
        $this->app->singleton('laravel-cbs', function () {
            return new Cbs;
        });
    }
}
