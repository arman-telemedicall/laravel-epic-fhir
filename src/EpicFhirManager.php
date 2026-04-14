<?php

namespace Teleminergmbh\EpicFhir;

use Teleminergmbh\EpicFhir\Contracts\EpicFhirHttpClientInterface;
use Teleminergmbh\EpicFhir\Contracts\EpicFhirTokenStoreInterface;
use Teleminergmbh\EpicFhir\Services\EpicFhirAuthService;
use Teleminergmbh\EpicFhir\Services\EpicFhirFhirClient;

class EpicFhirManager
{
    public function __construct(
        protected EpicFhirHttpClientInterface $http,
        protected EpicFhirTokenStoreInterface $tokens,
    ) {}

    /** @param array<string, mixed> $overrides */
    public function makeAuth(array $overrides = []): EpicFhirAuthService
    {
        return new EpicFhirAuthService($this->http, $this->tokens, $overrides);
    }

    /** @param array<string, mixed> $overrides */
    public function makeFhir(array $overrides = []): EpicFhirFhirClient
    {
        return new EpicFhirFhirClient($this->http, $this->makeAuth($overrides), $overrides);
    }

    /** @param array<string, mixed> $overrides */
    public function makeEpic(array $overrides = []): EpicFhir
    {
        $auth = $this->makeAuth($overrides);
        $fhir = new EpicFhirFhirClient($this->http, $auth, $overrides);

        return new EpicFhir($this->http, $auth, $fhir);
    }
}
