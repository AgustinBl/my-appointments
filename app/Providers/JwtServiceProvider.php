<?php

namespace App\Providers;

use App\Auth\JwtCodec;
use App\Auth\JwtGuard;
use App\Auth\JwtManager;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class JwtServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function register()
    {
        $this->app->singleton(JwtCodec::class, function ($app) {
            return new JwtCodec((string) $app['config']->get('jwt.key'));
        });

        $this->app->singleton(JwtManager::class, function ($app) {
            return new JwtManager(
                $app->make(JwtCodec::class),
                $app['cache']->store(),
                (array) $app['config']->get('jwt')
            );
        });
    }

    /**
     * Registra el driver "jwt" que usa el guard "api" de config/auth.php.
     *
     * @return void
     */
    public function boot()
    {
        Auth::extend('jwt', function ($app, $name, array $config) {
            $guard = new JwtGuard(
                $app->make(JwtManager::class),
                Auth::createUserProvider($config['provider']),
                $app['request']
            );

            // El guard se resuelve una sola vez, pero el Request puede
            // cambiar despues (tests, requests internos). refresh() le
            // reinyecta el nuevo via setRequest().
            $app->refresh('request', $guard, 'setRequest');

            return $guard;
        });
    }
}
