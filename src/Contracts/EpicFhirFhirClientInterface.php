<?php

namespace Teleminergmbh\EpicFhir\Contracts;

interface EpicFhirFhirClientInterface
{
    public function listSearchSystem(string $clientId): string;

    public function myListSearchSystem(string $clientId): string;

    public function listReadSystem(string $clientId, string $listId): string;

    /** @param array<string, string|null> $query */
    public function patientSearchSystem(string $clientId, array $query = [], bool $includeGeneralPractitioner = false): string;

    public function patientSummarySystem(string $clientId, string $patientId): string;

	public function patientSystem(string $clientId, string $patientId): string;

    /** @param array<string, mixed> $patientData */
    public function patientCreateSystem(string $clientId, array $patientData): array;

    public function listSearchSmart(string $clientId, string $ownerKey): string;

    public function myListSearchSmart(string $clientId, string $ownerKey): string;

    public function listReadSmart(string $clientId, string $ownerKey, string $listId): string;

    /** @param array<string, string|null> $query */
    public function patientSearchSmart(string $clientId, string $ownerKey, array $query = [], bool $includeGeneralPractitioner = false): string;

    public function patientSummarySmart(string $clientId, string $ownerKey, string $patientId): string;

    /** @param array<string, mixed> $patientData */
    public function patientCreateSmart(string $clientId, string $ownerKey, array $patientData): array;
}
