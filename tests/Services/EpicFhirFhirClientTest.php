<?php

use Illuminate\Support\Facades\Http;
use Teleminergmbh\EpicFhir\Contracts\EpicFhirAuthServiceInterface;
use Teleminergmbh\EpicFhir\Http\EpicFhirHttpClient;
use Teleminergmbh\EpicFhir\Services\EpicFhirFhirClient;

it('sends correct headers and url for patient summary (system)', function () {
    $auth = new class implements EpicFhirAuthServiceInterface
    {
        public function getSystemAccessToken(string $clientId): string
        {
            return 'TOK';
        }

        public function getSmartAccessToken(string $clientId, string $ownerKey): string
        {
            return 'TOK';
        }
    };

    Http::fake([
        'https://example.test/FHIR/R4/Patient/*' => Http::response('{"ok":true}', 200),
    ]);

    $client = new EpicFhirFhirClient(new EpicFhirHttpClient, $auth);

    $body = $client->patientSummarySystem('cid', 'p1');

    expect($body)->toContain('ok');

    Http::assertSent(function ($request) {
        return $request->url() === 'https://example.test/FHIR/R4/Patient/p1/$summary'
            && $request->hasHeader('Accept', 'application/fhir+json')
            && $request->hasHeader('Authorization', 'Bearer TOK');
    });
});

it('filters empty query values on list search', function () {
    config()->set('laravel-epic-fhir.list_subject', '');

    $auth = new class implements EpicFhirAuthServiceInterface
    {
        public function getSystemAccessToken(string $clientId): string
        {
            return 'TOK';
        }

        public function getSmartAccessToken(string $clientId, string $ownerKey): string
        {
            return 'TOK';
        }
    };

    Http::fake([
        'https://example.test/FHIR/R4/List*' => Http::response(['resourceType' => 'Bundle'], 200),
    ]);

    $client = new EpicFhirFhirClient(new EpicFhirHttpClient, $auth);
    $client->listSearchSystem('cid');

    Http::assertSent(function ($request) {
        $data = $request->data();

        return str_starts_with($request->url(), 'https://example.test/FHIR/R4/List')
            && array_key_exists('subject', $data) === false;
    });
});

it('searches patients (system) with query passthrough and optional include', function () {
    $auth = new class implements EpicFhirAuthServiceInterface
    {
        public function getSystemAccessToken(string $clientId): string
        {
            return 'TOK';
        }

        public function getSmartAccessToken(string $clientId, string $ownerKey): string
        {
            return 'TOK';
        }
    };

    Http::fake([
        'https://example.test/FHIR/R4/Patient*' => Http::response('{"resourceType":"Bundle"}', 200),
    ]);

    $client = new EpicFhirFhirClient(new EpicFhirHttpClient, $auth);

    $client->patientSearchSystem('cid', [
        'name' => 'ali',
        '_count' => '20',
    ], true);

    Http::assertSent(function ($request) {
        $data = $request->data();

        return str_starts_with($request->url(), 'https://example.test/FHIR/R4/Patient')
            && ($data['name'] ?? null) === 'ali'
            && ($data['_count'] ?? null) === '20'
            && ($data['_include'] ?? null) === 'Patient:generalPractitioner:Practitioner';
    });
});

it('searches patients (smart) with query passthrough and optional include', function () {
    $auth = new class implements EpicFhirAuthServiceInterface
    {
        public function getSystemAccessToken(string $clientId): string
        {
            return 'TOK';
        }

        public function getSmartAccessToken(string $clientId, string $ownerKey): string
        {
            return 'TOK';
        }
    };

    Http::fake([
        'https://example.test/FHIR/R4/Patient*' => Http::response('{"resourceType":"Bundle"}', 200),
    ]);

    $client = new EpicFhirFhirClient(new EpicFhirHttpClient, $auth);

    $client->patientSearchSmart('cid', 'owner1', [
        'family' => 'smith',
        '_count' => '10',
    ], true);

    Http::assertSent(function ($request) {
        $data = $request->data();

        return str_starts_with($request->url(), 'https://example.test/FHIR/R4/Patient')
            && ($data['family'] ?? null) === 'smith'
            && ($data['_count'] ?? null) === '10'
            && ($data['_include'] ?? null) === 'Patient:generalPractitioner:Practitioner';
    });
});

it('throws runtime exception when fhir GET fails', function () {
    $auth = new class implements EpicFhirAuthServiceInterface
    {
        public function getSystemAccessToken(string $clientId): string
        {
            return 'TOK';
        }

        public function getSmartAccessToken(string $clientId, string $ownerKey): string
        {
            return 'TOK';
        }
    };

    Http::fake([
        'https://example.test/FHIR/R4/List*' => Http::response('nope', 500),
    ]);

    $client = new EpicFhirFhirClient(new EpicFhirHttpClient, $auth);

    $client->listSearchSystem('cid');
})->throws(RuntimeException::class);

it('creates patient via POST and returns status/location/body', function () {
    $auth = new class implements EpicFhirAuthServiceInterface
    {
        public function getSystemAccessToken(string $clientId): string
        {
            return 'TOK';
        }

        public function getSmartAccessToken(string $clientId, string $ownerKey): string
        {
            return 'TOK';
        }
    };

    Http::fake([
        'https://example.test/FHIR/R4/Patient' => Http::response(['resourceType' => 'Patient', 'id' => 'p1'], 201, ['Location' => 'https://example.test/FHIR/R4/Patient/p1']),
    ]);

    $client = new EpicFhirFhirClient(new EpicFhirHttpClient, $auth);

    $result = $client->patientCreateSystem('cid', ['resourceType' => 'Patient']);

    expect($result['status'])->toBe(200);
    expect($result['location'])->toBeNull();
    expect($result['body'])->toBe(['resourceType' => 'Patient', 'id' => 'p1']);

    Http::assertSent(function ($request) {
        return $request->url() === 'https://example.test/FHIR/R4/Patient'
            && $request->method() === 'POST'
            && $request->hasHeader('Content-Type', 'application/fhir+json');
    });
});
