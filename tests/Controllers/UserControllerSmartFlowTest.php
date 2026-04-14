<?php

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Teleminergmbh\EpicFhir\Contracts\EpicFhirTokenStoreInterface;
use Teleminergmbh\EpicFhir\Tests\Support\FakeTokenStore;
use Teleminergmbh\EpicFhir\Tests\TestCase;

it('SmartOnFhir redirects to auth url and seeds cache state/pkce/ownerKey', function () {
    /** @var TestCase $this */
    $resp = $this->get('http://example.test/fhir/R4/smart/launch/cid?ownerKey=owner1');

    $resp->assertRedirect();
    expect($resp->headers->get('Location'))->toContain('https://example.test/oauth/authorize');

    $state = request()->session()->get('oauth2_state');
    expect($state)->toBeNull();

    $query = [];
    parse_str(parse_url((string) $resp->headers->get('Location'), PHP_URL_QUERY) ?: '', $query);
    expect($query['state'] ?? null)->not->toBeNull();

    $flow = Cache::get('epic_fhir:smart_flow:'.$query['state']);
    expect($flow)->toBeArray();
    expect($flow['client_id'])->toBe('cid');
    expect($flow['owner_key'])->toBe('owner1');
    expect($flow['code_verifier'])->not->toBeNull();
});

it('SmartOnFhir rejects invalid host', function () {
    /** @var TestCase $this */
    $resp = $this->get('http://bad.test/fhir/R4/smart/launch/cid?ownerKey=owner1');

    $resp->assertStatus(400);
    $resp->assertJson(['error' => 'Invalid host for redirect URI']);
});

it('Callback exchanges code and stores smart token then fetches patient summary', function () {
    /** @var TestCase $this */
    /** @var FakeTokenStore $store */
    $store = app(EpicFhirTokenStoreInterface::class);

    Http::fake([
        'https://example.test/oauth/token' => Http::response([
            'access_token' => 'AT',
            'refresh_token' => 'RT',
            'expires_in' => 120,
            'patient' => 'p1',
            'token_type' => 'Bearer',
            'scope' => 's',
        ], 200),
        'https://example.test/FHIR/R4/Patient/p1/$summary' => Http::response(['ok' => true], 200),
    ]);

    $state = 'state123';

    Cache::put('epic_fhir:smart_flow:'.$state, [
        'client_id' => 'cid',
        'owner_key' => 'owner1',
        'code_verifier' => 'verifier',
    ], now()->addMinutes(10));

    $resp = $this
        ->get('http://example.test/fhir/R4/smart/callback?code=abc&state='.$state);

    $resp->assertOk();
    expect($resp->getContent())->toContain('ok');

    expect($store->puts)->toHaveCount(1);
    expect($store->puts[0]['flow'])->toBe('smart');
    expect($store->puts[0]['ownerKey'])->toBe('owner1');
    expect($store->puts[0]['data']['access_token'])->toBe('AT');
    expect($store->puts[0]['data']['refresh_token'])->toBe('RT');
    expect($store->puts[0]['data']['patient_id'])->toBe('p1');

    Http::assertSent(function ($request) {
        return $request->url() === 'https://example.test/oauth/token'
            && ($request->data()['grant_type'] ?? null) === 'authorization_code';
    });
});

it('Callback validation fails when state is invalid', function () {
    /** @var TestCase $this */
    Cache::put('epic_fhir:smart_flow:expected', [
        'client_id' => 'cid',
        'owner_key' => 'owner1',
        'code_verifier' => 'verifier',
    ], now()->addMinutes(10));

    $resp = $this
        ->get('http://example.test/fhir/R4/smart/callback?code=abc&state=wrong');

    $resp->assertStatus(400);
    $resp->assertJson(['error' => 'Invalid or expired state']);
});

it('Callback validation fails when ownerKey missing from session', function () {
    /** @var TestCase $this */
    Cache::put('epic_fhir:smart_flow:s', [
        'client_id' => 'cid',
        'code_verifier' => 'verifier',
    ], now()->addMinutes(10));

    $resp = $this
        ->get('http://example.test/fhir/R4/smart/callback?code=abc&state=s');

    $resp->assertStatus(400);
});
