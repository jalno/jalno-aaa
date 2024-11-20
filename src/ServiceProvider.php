<?php

namespace Jalno\AAA;

use dnj\AAA\Contracts\ITypeManager;
use dnj\AAA\Contracts\IUserManager;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider as SupportServiceProvider;
use Jalno\AAA\Http\Middleware\AuthenticateSession;
use Jalno\AAA\Session\JalnoDatabaseSessionHandler;
use Jalno\AAA\Session\JalnoFileSessionHandler;
use Jalno\AAA\Session\JalnoStore;

class ServiceProvider extends SupportServiceProvider
{
    public function register()
    {
        $this->registerConfiguration();
        $this->registerManagers();
        $this->registerUserProvider();
        $this->registerSessionDriver();
    }

    public function boot()
    {
        $this->registerRoutes();

        /** @var Router $router */
        $router = $this->app['router'];
        $router->aliasMiddleware('auth.jalno', AuthenticateSession::class);
        $router->pushMiddlewareToGroup('api', 'auth.jalno');

        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
            $this->publishes([
                __DIR__.'/../config/jalno-aaa.php' => config_path('jalno-aaa.php'),
            ], 'config');
        }
    }

    protected function registerConfiguration(): void
    {
        if (!app()->configurationIsCached()) {
            $this->mergeConfigFrom(__DIR__.'/../config/jalno-aaa.php', 'jalno-aaa');

            // The Jalno-AAA and Laravel-AAA should not use as same time.
            // So if Jalno's routing is enabled, we should disable Laravel-AAA routing disable.
            if (config('jalno-aaa.routes.enable')) {
                config(['aaa.routes' => array_merge(config('aaa.routes', []), ['enable' => false])]);
            }
        }
    }

    protected function registerManagers(): void
    {
        $this->app->singleton(ITypeManager::class, TypeManager::class);
        $this->app->singleton(IUserManager::class, UserManager::class);
    }

    protected function registerUserProvider(): void
    {
        /*
         * @param \Illuminate\Auth\AuthManager $auth
         */
        Auth::resolved(function ($auth) {
            $auth->provider('jalno-aaa', function ($app) {
                return $app->make(UserProvider::class);
            });
        });
    }

    protected function registerRoutes(): void
    {
        if (app()->routesAreCached() or !config('jalno-aaa.routes.enable')) {
            return;
        }
        $prefix = config('jalno-aaa.routes.prefix', 'api/users');
        Route::prefix($prefix)->group(function () {
            $this->loadRoutesFrom(__DIR__.'/../routes/api.php');
        });
    }

    /**
     * Register the Jalno's session driver instance.
     */
    protected function registerSessionDriver(): void
    {
        if (!config('jalno-aaa.session.enable')) {
            return;
        }

        $this->app->singleton('session.jalno-store', function ($app) {
            $driver = strtolower(config('jalno-aaa.session.driver'));

            $handler = match ($driver) {
                'php' => JalnoFileSessionHandler::create(),
                'db' => JalnoDatabaseSessionHandler::create(),
            };
            $serialization = match ($driver) {
                'php' => 'php',
                'db' => 'json',
            };

            return new JalnoStore(
                config('jalno-aaa.session.cookie.name', 'PHPSESSID'),
                $handler,
                null,
                $serialization
            );
        });
    }
}
