<?php

namespace Jalno\AAA;

use dnj\AAA\Contracts\ITypeManager;
use dnj\AAA\Contracts\IUserManager;
use Illuminate\Routing\Router;
use Jalno\UserLogger\Contracts\ILogger;
use Jalno\AAA\Http\Middleware\AuthenticateSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider as SupportServiceProvider;
use Illuminate\Session\FileSessionHandler;
use Jalno\AAA\Session\JalnoDatabaseSessionHandler;
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
            $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
            $this->publishes([
                __DIR__ . '/../config/jalno-aaa.php' => config_path('jalno-aaa.php'),
            ], 'config');
        }
    }

    protected function registerConfiguration(): void
    {
        if (!app()->configurationIsCached()) {
            $this->mergeConfigFrom(__DIR__ . '/../config/jalno-aaa.php', 'jalno-aaa');

            // In our Eloquent models, we use 'jalno' connection to be able to set diffrent database configuration.
            // We configure the 'jalno' connection with default connection if the end-user doesn't do that.
            if (null === config('database.connections.jalno')) {
                config(['database.connections.jalno' => config('database.connections.' . config('database.default'))]);
            }

            // The Jalno-AAA and Laravel-AAA should not use as same time.
            // So if Jalno's routing is enabled, we should disable Laravel-AAA routing disable.
            if (config('jalno-aaa.routes.enable')) {
                config(['aaa.routes' => array_merge(config('aaa.routes', []), ['enable' => false])]);
            }
        }
    }

    protected function registerManagers(): void
    {
        $this->app->extend(
            ITypeManager::class,
            fn ($parent, $app) => new TypeManager($app->make(ILogger::class))
        );
        $this->app->extend(
            IUserManager::class,
            fn ($parent, $app) => new UserManager($app->make(ILogger::class))
        );
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
            $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
        });
    }

    /**
     * Register the Jalno's session driver instance.
     *
     * @return void
     */
    protected function registerSessionDriver()
    {
        if (!config('jalno-aaa.jalno-session.enable')) {
            return;
        }

        $driver = strtolower(config('jalno-aaa.jalno-session.driver'));

        $handler = match ($driver) {
            'php' => new FileSessionHandler(
                app(\Illuminate\Filesystem\Filesystem::class),
                config('jalno-aaa.jalno-session.options.php.save_path'),
                config('jalno-aaa.jalno-session.lifetime'),
            ),
            'db' => new JalnoDatabaseSessionHandler(
                app(\Illuminate\Database\ConnectionResolverInterface::class)->connection(
                    config('jalno-aaa.jalno-session.options.db.connection', 'jalno')
                ),
                config('jalno-aaa.jalno-session.options.db.table', 'base_sessions'),
                config('jalno-aaa.jalno-session.lifetime')
            ),
        };
        $serialization = match ($driver) {
            'php' => 'php',
            'db' => 'json',
        };

        $this->app->singleton('session.jalno-store', function ($app) use ($handler, $serialization) {
            return new JalnoStore(
                config('jalno-aaa.jalno-session.cookie.name', 'PHPSESSID'),
                $handler,
                null,
                $serialization
            );
        });
    }
}
