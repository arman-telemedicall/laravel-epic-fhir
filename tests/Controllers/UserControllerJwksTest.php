<?php

use Illuminate\Support\Facades\Route;
use Teleminergmbh\EpicFhir\Tests\TestCase;

it('registers package routes by default', function () {
    expect(Route::has('EpicFhir.jwks'))->toBeTrue();
    expect(Route::has('EpicFhir.smart.launch'))->toBeTrue();
    expect(Route::has('EpicFhir.smart.callback'))->toBeTrue();
});

it('jwks returns keys array', function () {
    /** @var TestCase $this */
    $resp = $this->get('http://example.test/fhir/R4/jwks/cid');

    $resp->assertOk();
    $resp->assertJsonStructure([
        'keys' => [
            ['kty', 'use', 'alg', 'kid', 'n', 'e'],
        ],
    ]);
});
