<?php

namespace spkm\isams;

use Illuminate\Support\ServiceProvider;

class IsamsServerProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/Config/config.php' => config_path('isams.php'),
        ], 'config');
    }
}
