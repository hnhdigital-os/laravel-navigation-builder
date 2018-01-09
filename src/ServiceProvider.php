<?php

/*
 * This file is part of Laravel Navigation Builder.
 *
 * (c) Rocco Howard <rocco@hnh.digital>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HnhDigital\NavigationBuilder;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;

/**
 * This is the service provider class.
 *
 * @author Rocco Howard <rocco@hnh.digital>
 */
class ServiceProvider extends BaseServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('Nav', function () {
            return new Navigation();
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['Nav'];
    }
}
