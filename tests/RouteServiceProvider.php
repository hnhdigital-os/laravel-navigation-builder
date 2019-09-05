<?php

namespace HnhDigital\NavigationBuilder\Tests;

use Illuminate\Support\ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__.'/routes/test.php');
    }
}