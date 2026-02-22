<?php

namespace Telemedicall\EpicFhir;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Telemedicall\EpicFhir\Controllers\UserController;

class EpicFhirServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Publish config
        $this->publishes([
            __DIR__.'/config/epic-fhir.php' => config_path('epic-fhir.php'),
        ], 'epic-fhir-config');

        // Publish migration
        $this->publishes([
            __DIR__.'/Database/Migrations' => database_path('migrations'),
        ], 'epic-fhir-migrations');

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
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/config/epic-fhir.php',
            'epic-fhir'
        );

        // Bind the service class
        $this->app->singleton(EpicFhirService::class, function ($app) {
            return new EpicFhirService(config('epic-fhir'));
        });

        // Facade alias (if you want to use EpicFhir::...)
        $this->app->alias(EpicFhirService::class, 'epic-fhir');
    }
}
