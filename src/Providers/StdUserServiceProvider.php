<?php

declare(strict_types=1);

namespace Webkernel\Component\User\Providers;

use Illuminate\Support\ServiceProvider;
use Webkernel\Component\User\Contracts\RegistersAuthProvider;
use Webkernel\Component\User\Providers\AuthConfigurator;

class StdUserServiceProvider extends ServiceProvider
{
    #[\Override]
    public function register(): void
    {
        $this->app->singleton(AuthConfigurator::class, fn($app): AuthConfigurator => new AuthConfigurator($app));
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../../migrations');

        /** @var AuthConfigurator $configurator */
        $configurator = $this->app->make(AuthConfigurator::class);

        // Point the primary users provider at App\Models\User.
        // App\Models\User extends Webkernel\Models\User extends Webkernel\Component\User\Models\User.
        // The host application owns this model and may override $table and $connection freely.
        $configurator->applyDefaults(\App\Models\User::class);

        // Modules dispatch this event from their own service providers
        // when they need to register an additional auth provider or guard.
        $this->app['events']->listen(
            'webkernel.auth.register_provider',
            function (RegistersAuthProvider $registration) use ($configurator): void {
                $configurator->registerProvider($registration);
            }
        );
    }
}
