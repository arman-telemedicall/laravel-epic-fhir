<?php

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Teleminergmbh\EpicFhir\Contracts\EpicFhirHttpClientInterface;
use Teleminergmbh\EpicFhir\Contracts\EpicFhirTokenStoreInterface;
use Teleminergmbh\EpicFhir\Services\EpicFhirAuthService;
use Teleminergmbh\EpicFhir\Tests\Support\FakeTokenStore;

it('returns cached system token when valid', function () {
    /** @var FakeTokenStore $store */
    $store = app(EpicFhirTokenStoreInterface::class);
    $http = app(EpicFhirHttpClientInterface::class);
    $store->seed('cid', 'system', 'default', [
        'access_token' => 'tok',
        'expires_at' => time() + 3600,
    ]);

    Http::fake();

    $svc = new EpicFhirAuthService($http, $store);

    expect($svc->getSystemAccessToken('cid'))->toBe('tok');
    Http::assertNothingSent();
});

it('requests client_credentials token when system token missing', function () {
    /** @var FakeTokenStore $store */
    $store = app(EpicFhirTokenStoreInterface::class);
    $http = app(EpicFhirHttpClientInterface::class);

    Http::fake([
        'https://example.test/oauth/token' => Http::response([
            'access_token' => 'newtok',
            'expires_in' => 120,
            'token_type' => 'Bearer',
            'scope' => 'x',
        ], 200),
    ]);

    $svc = new EpicFhirAuthService($http, $store);

    $token = $svc->getSystemAccessToken('cid');

    expect($token)->toBe('newtok');
    expect($store->puts)->toHaveCount(1);
    expect($store->puts[0]['flow'])->toBe('system');
    expect($store->puts[0]['ownerKey'])->toBe('default');
    expect($store->puts[0]['data'])->toHaveKey('expires_at');

    Http::assertSent(function ($request) {
        if ($request->url() !== 'https://example.test/oauth/token') {
            return false;
        }

        $data = $request->data();

        return ($data['grant_type'] ?? null) === 'client_credentials'
            && ($data['scope'] ?? null) !== null
            && is_string($data['client_assertion'] ?? null)
            && ($data['client_assertion_type'] ?? null) === 'urn:ietf:params:oauth:client-assertion-type:jwt-bearer';
    });
});

it('stores system token under connection_key ownerKey when override is provided', function () {
    /** @var FakeTokenStore $store */
    $store = app(EpicFhirTokenStoreInterface::class);
    $http = app(EpicFhirHttpClientInterface::class);

    Http::fake([
        'https://example.test/oauth/token' => Http::response([
            'access_token' => 'newtok',
            'expires_in' => 120,
            'token_type' => 'Bearer',
            'scope' => 'x',
        ], 200),
    ]);

    $svc = new EpicFhirAuthService($http, $store, [
        'connection_key' => 'prov-123',
    ]);

    $token = $svc->getSystemAccessToken('cid');

    expect($token)->toBe('newtok');
    expect($store->puts)->toHaveCount(1);
    expect($store->puts[0]['flow'])->toBe('system');
    expect($store->puts[0]['ownerKey'])->toBe('prov-123');
});

it('throws when system token request fails', function () {
    /** @var FakeTokenStore $store */
    $store = app(EpicFhirTokenStoreInterface::class);
    $http = app(EpicFhirHttpClientInterface::class);

    Http::fake([
        'https://example.test/oauth/token' => Http::response('bad', 400),
    ]);

    $svc = new EpicFhirAuthService($http, $store);

    $svc->getSystemAccessToken('cid');
})->throws(RuntimeException::class);

it('returns cached smart token when valid', function () {
    /** @var FakeTokenStore $store */
    $store = app(EpicFhirTokenStoreInterface::class);
    $http = app(EpicFhirHttpClientInterface::class);
    $store->seed('cid', 'smart', 'owner1', [
        'access_token' => 'stok',
        'expires_at' => time() + 3600,
    ]);

    Http::fake();

    $svc = new EpicFhirAuthService($http, $store);

    expect($svc->getSmartAccessToken('cid', 'owner1'))->toBe('stok');
    Http::assertNothingSent();
});

it('throws when smart token expired and no refresh_token exists', function () {
    /** @var FakeTokenStore $store */
    $store = app(EpicFhirTokenStoreInterface::class);
    $http = app(EpicFhirHttpClientInterface::class);
    $store->seed('cid', 'smart', 'owner1', [
        'access_token' => 'old',
        'expires_at' => time() - 10,
    ]);

    $svc = new EpicFhirAuthService($http, $store);

    $svc->getSmartAccessToken('cid', 'owner1');
})->throws(RuntimeException::class);

it('refreshes smart token when expired and refresh_token exists (lock path)', function () {
    /** @var FakeTokenStore $store */
    $store = app(EpicFhirTokenStoreInterface::class);
    $http = app(EpicFhirHttpClientInterface::class);
    $store->seed('cid', 'smart', 'owner1', [
        'access_token' => 'old',
        'refresh_token' => 'r1',
        'expires_at' => time() - 10,
        'scope' => 's',
        'token_type' => 'Bearer',
    ]);

    $fakeLock = new class
    {
        public function block(int $seconds, callable $callback): mixed
        {
            return $callback();
        }
    };

    Cache::shouldReceive('lock')->once()->andReturn($fakeLock);

    Http::fake([
        'https://example.test/oauth/token' => Http::response([
            'access_token' => 'new',
            'expires_in' => 120,
            'refresh_token' => 'r2',
            'scope' => 's2',
            'token_type' => 'Bearer',
        ], 200),
    ]);

    $svc = new EpicFhirAuthService($http, $store);

    expect($svc->getSmartAccessToken('cid', 'owner1'))->toBe('new');
    expect($store->puts)->toHaveCount(1);
    expect($store->puts[0]['flow'])->toBe('smart');
    expect($store->puts[0]['ownerKey'])->toBe('owner1');
    expect($store->puts[0]['data']['refresh_token'])->toBe('r2');

    Http::assertSent(function ($request) {
        if ($request->url() !== 'https://example.test/oauth/token') {
            return false;
        }

        $data = $request->data();

        return ($data['grant_type'] ?? null) === 'refresh_token'
            && ($data['refresh_token'] ?? null) === 'r1'
            && ($data['client_id'] ?? null) === 'cid';
    });
});

it('does not refresh smart token if it became valid inside the lock', function () {
    /** @var FakeTokenStore $store */
    $store = app(EpicFhirTokenStoreInterface::class);
    $http = app(EpicFhirHttpClientInterface::class);

    $store->seed('cid', 'smart', 'owner1', [
        'access_token' => 'old',
        'refresh_token' => 'r1',
        'expires_at' => time() - 10,
    ]);

    $decorated = new class($store) implements EpicFhirTokenStoreInterface
    {
        public int $getCalls = 0;

        public function __construct(private FakeTokenStore $inner) {}

        public function get(string $clientId, string $flow, string $ownerKey): array
        {
            $this->getCalls++;

            if ($this->getCalls === 2) {
                return [
                    'access_token' => 'fresh',
                    'expires_at' => time() + 3600,
                    'refresh_token' => 'r1',
                ];
            }

            return $this->inner->get($clientId, $flow, $ownerKey);
        }

        public function put(string $clientId, string $flow, string $ownerKey, array $data, ?int $ttlSeconds = null): void
        {
            $this->inner->put($clientId, $flow, $ownerKey, $data, $ttlSeconds);
        }

        public function forget(string $clientId, string $flow, string $ownerKey): void
        {
            $this->inner->forget($clientId, $flow, $ownerKey);
        }
    };

    $fakeLock = new class
    {
        public function block(int $seconds, callable $callback): mixed
        {
            return $callback();
        }
    };

    Cache::shouldReceive('lock')->once()->andReturn($fakeLock);

    Http::fake();

    $svc = new EpicFhirAuthService($http, $decorated);

    expect($svc->getSmartAccessToken('cid', 'owner1'))->toBe('fresh');
    Http::assertNothingSent();
});
