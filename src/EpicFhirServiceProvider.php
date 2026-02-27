<?php

namespace Telemedicall\EpicFhir;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Telemedicall\EpicFhir\Controllers\UserController;

class EpicFhirServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__.'/config/epic-fhir.php' => config_path('epic-fhir.php'),
        ], 'epic-fhir-config');

        $this->publishes([
            __DIR__.'/Database/Migrations' => database_path('migrations'),
        ], 'epic-fhir-migrations');

		Route::get('/jwks/{clientId}', [UserController::class, 'jwks']);

        Route::prefix('fhir/R4')
            ->middleware('web')           // applies session, CSRF, etc.
            ->group(function () {
                Route::get('/jwks/{clientId}', [UserController::class, 'jwks'])->name('EpicFhir.jwks');

                Route::get('/Callback', [UserController::class, 'Callback'])->name('EpicFhir.Callback');
            });
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/config/epic-fhir.php',
            'EpicFhir'
        );

        $this->app->singleton(UserController::class, function ($app) {
            return new UserController(config('EpicFhir'));
        });

        $this->app->alias(UserController::class, 'EpicFhir');
    }
}
