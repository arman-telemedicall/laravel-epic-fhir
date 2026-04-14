<?php

use Teleminergmbh\EpicFhir\Contracts\EpicFhirAuthServiceInterface;
use Teleminergmbh\EpicFhir\Contracts\EpicFhirFhirClientInterface;
use Teleminergmbh\EpicFhir\EpicFhir;

it('EpicFhir delegates token methods to auth service and exposes fhir client', function () {
    $auth = new class implements EpicFhirAuthServiceInterface
    {
        public int $systemCalls = 0;

        public int $smartCalls = 0;

        public function getSystemAccessToken(string $clientId): string
        {
            $this->systemCalls++;

            return 'SYS';
        }

        public function getSmartAccessToken(string $clientId, string $ownerKey): string
        {
            $this->smartCalls++;

            return 'SM';
        }
    };

    $fhir = new class implements EpicFhirFhirClientInterface
    {
        public function listSearchSystem(string $clientId): string
        {
            return '';
        }

        public function myListSearchSystem(string $clientId): string
        {
            return '';
        }

        public function listReadSystem(string $clientId, string $listId): string
        {
            return '';
        }

        public function patientSearchSystem(string $clientId, array $query = [], bool $includeGeneralPractitioner = false): string
        {
            return '';
        }

        public function patientSummarySystem(string $clientId, string $patientId): string
        {
            return '';
        }

        public function patientCreateSystem(string $clientId, array $patientData): array
        {
            return [];
        }

        public function listSearchSmart(string $clientId, string $ownerKey): string
        {
            return '';
        }

        public function myListSearchSmart(string $clientId, string $ownerKey): string
        {
            return '';
        }

        public function listReadSmart(string $clientId, string $ownerKey, string $listId): string
        {
            return '';
        }

        public function patientSearchSmart(string $clientId, string $ownerKey, array $query = [], bool $includeGeneralPractitioner = false): string
        {
            return '';
        }

        public function patientSummarySmart(string $clientId, string $ownerKey, string $patientId): string
        {
            return '';
        }

        public function patientCreateSmart(string $clientId, string $ownerKey, array $patientData): array
        {
            return [];
        }
    };

    app()->instance(EpicFhirAuthServiceInterface::class, $auth);
    app()->instance(EpicFhirFhirClientInterface::class, $fhir);

    $svc = app(EpicFhir::class);

    expect($svc->systemAccessToken('cid'))->toBe('SYS');
    expect($svc->smartAccessToken('cid', 'owner1'))->toBe('SM');
    expect($svc->fhir())->toBe($fhir);

    expect($auth->systemCalls)->toBe(1);
    expect($auth->smartCalls)->toBe(1);
});
