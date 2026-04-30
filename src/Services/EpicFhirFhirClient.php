<?php

namespace Teleminergmbh\EpicFhir\Services;

use RuntimeException;
use Teleminergmbh\EpicFhir\Contracts\EpicFhirAuthServiceInterface;
use Teleminergmbh\EpicFhir\Contracts\EpicFhirFhirClientInterface;
use Teleminergmbh\EpicFhir\Contracts\EpicFhirHttpClientInterface;
use Teleminergmbh\EpicFhir\Database\Traits\EpicConfigTrait;

class EpicFhirFhirClient implements EpicFhirFhirClientInterface
{
    use EpicConfigTrait;

    public function __construct(
        protected EpicFhirHttpClientInterface $http,
        protected EpicFhirAuthServiceInterface $auth,
        array $overrides = [],
    ) {
        $this->initializeEpicConfig($overrides);
    }

    public function listSearchSystem(string $clientId): string
    {
        $token = $this->auth->getSystemAccessToken($clientId);

        return $this->getFhir($clientId, '/List', $token, [
            'code' => $this->epicConfig('list_code'),
            'identifier' => $this->epicConfig('system_lists_identifier'),
            'subject' => $this->epicConfig('list_subject'),
            'status' => $this->epicConfig('list_status'),
        ]);
    }

    public function myListSearchSystem(string $clientId): string
    {
        $token = $this->auth->getSystemAccessToken($clientId);

        return $this->getFhir($clientId, '/List', $token, [
            'code' => $this->epicConfig('list_code'),
            'identifier' => $this->epicConfig('user_lists_identifier'),
            'status' => $this->epicConfig('list_status'),
        ]);
    }

    public function listReadSystem(string $clientId, string $listId): string
    {
        $token = $this->auth->getSystemAccessToken($clientId);

        return $this->getFhir($clientId, "/List/{$listId}", $token);
    }

    /** @param array<string, string|null> $query */
    public function patientSearchSystem(string $clientId, array $query = [], bool $includeGeneralPractitioner = false): string
    {
        $token = $this->auth->getSystemAccessToken($clientId);

        if ($includeGeneralPractitioner) {
            $query['_include'] = 'Patient:generalPractitioner:Practitioner';
        }

        return $this->getFhir($clientId, '/Patient', $token, $query);
    }

    public function patientSummarySystem(string $clientId, string $patientId): string
    {
        $token = $this->auth->getSystemAccessToken($clientId);

        return $this->getFhir($clientId, "/Patient/{$patientId}/\$summary", $token);
    }

	public function patientSystem(string $clientId, string $patientId): string
    {
        $token = $this->auth->getSystemAccessToken($clientId);

        return $this->getFhir($clientId, "/Patient/{$patientId}", $token);
    }

    public function patientCreateSystem(string $clientId, array $patientData): array
    {
        $token = $this->auth->getSystemAccessToken($clientId);

        return $this->postFhir($clientId, '/Patient', $token, $patientData);
    }

    public function listSearchSmart(string $clientId, string $ownerKey): string
    {
        $token = $this->auth->getSmartAccessToken($clientId, $ownerKey);

        return $this->getFhir($clientId, '/List', $token, [
            'code' => $this->epicConfig('list_code'),
            'identifier' => $this->epicConfig('system_lists_identifier'),
            'subject' => $this->epicConfig('list_subject'),
            'status' => $this->epicConfig('list_status'),
        ]);
    }

    public function myListSearchSmart(string $clientId, string $ownerKey): string
    {
        $token = $this->auth->getSmartAccessToken($clientId, $ownerKey);

        return $this->getFhir($clientId, '/List', $token, [
            'code' => $this->epicConfig('list_code'),
            'identifier' => $this->epicConfig('user_lists_identifier'),
            'status' => $this->epicConfig('list_status'),
        ]);
    }

    public function listReadSmart(string $clientId, string $ownerKey, string $listId): string
    {
        $token = $this->auth->getSmartAccessToken($clientId, $ownerKey);

        return $this->getFhir($clientId, "/List/{$listId}", $token);
    }

    /** @param array<string, string|null> $query */
    public function patientSearchSmart(string $clientId, string $ownerKey, array $query = [], bool $includeGeneralPractitioner = false): string
    {
        $token = $this->auth->getSmartAccessToken($clientId, $ownerKey);

        if ($includeGeneralPractitioner) {
            $query['_include'] = 'Patient:generalPractitioner:Practitioner';
        }

        return $this->getFhir($clientId, '/Patient', $token, $query);
    }

    public function patientSummarySmart(string $clientId, string $ownerKey, string $patientId): string
    {
        $token = $this->auth->getSmartAccessToken($clientId, $ownerKey);

        return $this->getFhir($clientId, "/Patient/{$patientId}/\$summary", $token);
    }

    public function patientCreateSmart(string $clientId, string $ownerKey, array $patientData): array
    {
        $token = $this->auth->getSmartAccessToken($clientId, $ownerKey);

        return $this->postFhir($clientId, '/Patient', $token, $patientData);
    }

    /** @param array<string, string|null> $query */
    protected function getFhir(string $clientId, string $path, string $accessToken, array $query = []): string
    {
        $url = rtrim((string) $this->epicConfig('fhir_base'), '/').$path;

        try {
            return $this->http->getRaw($url, array_filter($query, fn ($v) => $v !== null && $v !== ''), [
                'Authorization' => "Bearer {$accessToken}",
                'Accept' => 'application/fhir+json',
                'Epic-Client-ID' => $clientId,
            ]);
        } catch (\Throwable $e) {
            throw new RuntimeException($e->getMessage(), 0, $e);
        }
    }

    /** @param array<string, mixed> $payload */
    protected function postFhir(string $clientId, string $path, string $accessToken, array $payload): array
    {
        $url = rtrim((string) $this->epicConfig('fhir_base'), '/').$path;

        try {
            $body = $this->http->postHeader($url, $payload, [
                'Authorization' => "Bearer {$accessToken}",
                'Accept' => 'application/fhir+json',
                'Content-Type' => 'application/fhir+json',
                'Epic-Client-ID' => $clientId,
            ]);
        } catch (\Throwable $e) {
            throw new RuntimeException($e->getMessage(), 0, $e);
        }

        return [
            'status' => 200,
            'location' => null,
            'body' => $body,
        ];
    }
}
