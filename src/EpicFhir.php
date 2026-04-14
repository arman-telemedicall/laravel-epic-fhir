<?php

namespace Teleminergmbh\EpicFhir;

use Teleminergmbh\EpicFhir\Contracts\EpicFhirAuthServiceInterface;
use Teleminergmbh\EpicFhir\Contracts\EpicFhirFhirClientInterface;
use Teleminergmbh\EpicFhir\Contracts\EpicFhirHttpClientInterface;

class EpicFhir
{
    public function __construct(
        protected EpicFhirHttpClientInterface $http,
        protected EpicFhirAuthServiceInterface $auth,
        protected EpicFhirFhirClientInterface $fhir,
    ) {}

    /** @param array<string, mixed> $overrides */
    public function connection(array $overrides): self
    {
        return app(EpicFhirManager::class)->makeEpic($overrides);
    }

    public function http(): EpicFhirHttpClientInterface
    {
        return $this->http;
    }

    public function systemAccessToken(string $clientId): string
    {
        return $this->auth->getSystemAccessToken($clientId);
    }

    public function smartAccessToken(string $clientId, string $ownerKey): string
    {
        return $this->auth->getSmartAccessToken($clientId, $ownerKey);
    }

    public function fhir(): EpicFhirFhirClientInterface
    {
        return $this->fhir;
    }
}
