<?php

namespace spkm\isams;

use Illuminate\Support\ServiceProvider;

class IsamsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/Config/config.php' => config_path('isams.php'),
        ], 'config');
    }

    public function register()
    {
        $this->app->bind('isams', function ($app) {
            return new Facade();
        });
    }
}
