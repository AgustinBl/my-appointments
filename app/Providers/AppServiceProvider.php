<?php

namespace App\Providers;

use App\Services\Fcm;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Singleton para leer el JSON de credenciales una sola vez por request.
        $this->app->singleton(Fcm::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
