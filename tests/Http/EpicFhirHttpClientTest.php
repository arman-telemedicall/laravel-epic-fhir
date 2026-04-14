<?php

use Illuminate\Support\Facades\Http;
use Teleminergmbh\EpicFhir\Http\EpicFhirHttpClient;

it('can perform GET requests via Laravel Http and return body', function () {
    Http::fake([
        'https://example.test/*' => Http::response(['ok' => true], 200),
    ]);

    $client = new EpicFhirHttpClient;

    $body = $client->getJson('https://example.test/ping');

    expect($body)->toBe(['ok' => true]);
});
