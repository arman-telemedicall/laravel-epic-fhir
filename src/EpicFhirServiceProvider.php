<?php

namespace Telemedicall\EpicFhir;

use Illuminate\Support\ServiceProvider;

class EpicFhirServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Publish config
        $this->publishes([
            __DIR__.'/../config/epic-fhir.php' => config_path('epic-fhir.php'),
        ], 'epic-fhir-config');

        // Publish migration (with automatic timestamp prefix)
        if (method_exists($this, 'publishesMigrations')) {
            // Laravel 11+
            $this->publishesMigrations(__DIR__.'/Database/Migrations');
        } else {
            // Laravel 10 & below fallback
            $this->publishes([
                __DIR__.'/Database/Migrations/' => database_path('migrations'),
            ], 'epic-fhir-migrations');
        }
        // ────────────────────────────────────────────────
        // Automatically register your Epic routes
        // ────────────────────────────────────────────────
        Route::prefix('epic')
            ->middleware('web')           // applies session, CSRF, etc.
            ->group(function () {
                Route::get('/jwks', [UserController::class, 'jwks'])
                    ->name('epic.jwks');

                Route::get('/launch', [UserController::class, 'smartOnFhir'])
                    ->name('epic.launch');

                Route::get('/callback', [UserController::class, 'callback'])
                    ->name('epic.callback');
            });

        // You can also publish keys folder if needed (careful with secrets!)
        // $this->publishes([...], 'epic-fhir-keys');
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/epic-fhir.php', 'epic-fhir'
        );

        // Bind the main service class
        $this->app->singleton(EpicFhirService::class, function ($app) {
            return new EpicFhirService(config('epic-fhir'));
        });

        // Optional: alias / facade
        $this->app->alias(EpicFhirService::class, 'epic-fhir');
    }
}
