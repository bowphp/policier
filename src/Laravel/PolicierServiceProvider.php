<?php

namespace Policier\Laravel;

use Illuminate\Support\ServiceProvider;
use Policier\Policier;

class PolicierServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../../config/policier.php',
            'policier'
        );

        $this->app->singleton(Policier::class, function ($app) {
            return Policier::configure($app['config']['policier']);
        });
    }
    
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../../config/policier.php' => config_path('policier.php'),
        ]);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [Policier::class, 'policier'];
    }
}
