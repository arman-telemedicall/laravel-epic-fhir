<?php

namespace Teleminergmbh\EpicFhir;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Teleminergmbh\EpicFhir\Contracts\EpicFhirAuthServiceInterface;
use Teleminergmbh\EpicFhir\Contracts\EpicFhirFhirClientInterface;
use Teleminergmbh\EpicFhir\Contracts\EpicFhirHttpClientInterface;
use Teleminergmbh\EpicFhir\Contracts\EpicFhirRequestConfigResolverInterface;
use Teleminergmbh\EpicFhir\Contracts\EpicFhirTokenStoreInterface;
use Teleminergmbh\EpicFhir\Controllers\UserController;
use Teleminergmbh\EpicFhir\Http\EpicFhirHttpClient;
use Teleminergmbh\EpicFhir\Resolvers\NullEpicFhirRequestConfigResolver;
use Teleminergmbh\EpicFhir\Services\EpicFhirAuthService;
use Teleminergmbh\EpicFhir\Services\EpicFhirFhirClient;
use Teleminergmbh\EpicFhir\TokenStores\CacheTokenStore;
use Teleminergmbh\EpicFhir\TokenStores\DatabaseTokenStore;

class EpicFhirServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/laravel-epic-fhir.php' => config_path('laravel-epic-fhir.php'),
        ], 'laravel-epic-fhir-config');

        if (config('laravel-epic-fhir.migrations.enabled', false)) {
            $this->publishes([
                __DIR__.'/Database/Migrations/2026_03_17_000000_create_epic_fhir_tokens_table.php' => database_path('migrations/2026_03_17_000000_create_epic_fhir_tokens_table.php'),
            ], 'epic-fhir-migrations');
        }

        if (config('laravel-epic-fhir.routes.enabled', true)) {
            $prefix = config('laravel-epic-fhir.routes.prefix', 'fhir/R4');
            $middleware = config('laravel-epic-fhir.routes.middleware', 'web');

            Route::prefix($prefix)
                ->middleware($middleware)
                ->group(function () {
                    Route::get('/jwks/{clientId?}', [UserController::class, 'jwks'])->name('EpicFhir.jwks');
                    Route::get('/smart/launch/{clientId}', [UserController::class, 'smartLaunch'])->name('EpicFhir.smart.launch');
                    Route::get('/smart/callback', [UserController::class, 'smartCallback'])->name('EpicFhir.smart.callback');
                });
        }
    }

    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/laravel-epic-fhir.php',
            'laravel-epic-fhir'
        );

        $this->app->bind(EpicFhirHttpClientInterface::class, EpicFhirHttpClient::class);

        $this->app->bind(EpicFhirRequestConfigResolverInterface::class, NullEpicFhirRequestConfigResolver::class);

        $this->app->singleton(EpicFhirTokenStoreInterface::class, function () {
            $driver = (string) config('laravel-epic-fhir.token_store.driver', 'cache');

            if ($driver === 'database') {
                $connection = (string) config('laravel-epic-fhir.token_store.database.connection', 'mysql');
                $table = (string) config('laravel-epic-fhir.token_store.database.table', 'epic_fhir_tokens');

                return new DatabaseTokenStore($connection, $table);
            }

            $prefix = (string) config('laravel-epic-fhir.token_store.cache.prefix', 'epic_fhir');
            $store = config('laravel-epic-fhir.token_store.cache.store');

            return new CacheTokenStore($prefix, is_string($store) && $store !== '' ? $store : null);
        });

        $this->app->singleton(EpicFhirAuthServiceInterface::class, function ($app) {
            return new EpicFhirAuthService(
                $app->make(EpicFhirHttpClientInterface::class),
                $app->make(EpicFhirTokenStoreInterface::class)
            );
        });

        $this->app->singleton(EpicFhirFhirClientInterface::class, function ($app) {
            return new EpicFhirFhirClient(
                $app->make(EpicFhirHttpClientInterface::class),
                $app->make(EpicFhirAuthServiceInterface::class)
            );
        });

        $this->app->singleton(EpicFhirManager::class, function ($app) {
            return new EpicFhirManager(
                $app->make(EpicFhirHttpClientInterface::class),
                $app->make(EpicFhirTokenStoreInterface::class),
            );
        });

        $this->app->singleton(EpicFhir::class);
        $this->app->alias(EpicFhir::class, 'EpicFhir');
    }
}
