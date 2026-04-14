<?php

use Illuminate\Support\Facades\Route;
use Teleminergmbh\EpicFhir\Contracts\EpicFhirAuthServiceInterface;
use Teleminergmbh\EpicFhir\Contracts\EpicFhirFhirClientInterface;
use Teleminergmbh\EpicFhir\Contracts\EpicFhirHttpClientInterface;
use Teleminergmbh\EpicFhir\Contracts\EpicFhirTokenStoreInterface;
use Teleminergmbh\EpicFhir\EpicFhir;
use Teleminergmbh\EpicFhir\EpicFhirManager;
use Teleminergmbh\EpicFhir\Http\EpicFhirHttpClient;
use Teleminergmbh\EpicFhir\Services\EpicFhirAuthService;
use Teleminergmbh\EpicFhir\Services\EpicFhirFhirClient;
use Teleminergmbh\EpicFhir\Tests\Support\FakeTokenStore;

it('binds core interfaces to expected implementations', function () {
    expect(app(EpicFhirHttpClientInterface::class))->toBeInstanceOf(EpicFhirHttpClient::class);
    expect(app(EpicFhirTokenStoreInterface::class))->toBeInstanceOf(FakeTokenStore::class);
    expect(app(EpicFhirAuthServiceInterface::class))->toBeInstanceOf(EpicFhirAuthService::class);
    expect(app(EpicFhirFhirClientInterface::class))->toBeInstanceOf(EpicFhirFhirClient::class);
    expect(app(EpicFhirManager::class))->toBeInstanceOf(EpicFhirManager::class);
    expect(app(EpicFhir::class))->toBeInstanceOf(EpicFhir::class);
});

it('routes are registered under configured prefix', function () {
    expect(Route::has('EpicFhir.jwks'))->toBeTrue();
    expect(Route::has('EpicFhir.smart.launch'))->toBeTrue();
    expect(Route::has('EpicFhir.smart.callback'))->toBeTrue();

    $route = Route::getRoutes()->getByName('EpicFhir.jwks');
    expect($route)->not->toBeNull();
    expect($route->uri())->toBe('fhir/R4/jwks/{clientId?}');
});
