<?php

namespace Teleminergmbh\EpicFhir\Tests\Support;

use Teleminergmbh\EpicFhir\Contracts\EpicFhirTokenStoreInterface;

class FakeTokenStore implements EpicFhirTokenStoreInterface
{
    /** @var array<string, array<string, mixed>> */
    private array $store = [];

    /** @var array<int, array{clientId:string,flow:string,ownerKey:string,data:array,ttl:?int}> */
    public array $puts = [];

    public function seed(string $clientId, string $flow, string $ownerKey, array $data): void
    {
        $this->store[$this->key($clientId, $flow, $ownerKey)] = $data;
    }

    public function get(string $clientId, string $flow, string $ownerKey): array
    {
        return $this->store[$this->key($clientId, $flow, $ownerKey)] ?? [];
    }

    public function put(string $clientId, string $flow, string $ownerKey, array $data, ?int $ttlSeconds = null): void
    {
        $this->store[$this->key($clientId, $flow, $ownerKey)] = $data;
        $this->puts[] = [
            'clientId' => $clientId,
            'flow' => $flow,
            'ownerKey' => $ownerKey,
            'data' => $data,
            'ttl' => $ttlSeconds,
        ];
    }

    public function forget(string $clientId, string $flow, string $ownerKey): void
    {
        unset($this->store[$this->key($clientId, $flow, $ownerKey)]);
    }

    private function key(string $clientId, string $flow, string $ownerKey): string
    {
        return $clientId.':'.$flow.':'.$ownerKey;
    }
}
