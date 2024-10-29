<?php

namespace Chupacabramiamor\Lead9Connect;

use Illuminate\Support\ServiceProvider;

class Lead9ConnectServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(
            Manager::class,
            fn () => new Manager(config('lead9connect.endpoint'))
        );
    }

    /**
     * Bootstrap any package services.
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/lead9connect.php' => config_path('lead9connect.php'),
        ], 'config');
    }
}
