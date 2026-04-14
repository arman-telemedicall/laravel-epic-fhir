<?php

namespace Teleminergmbh\EpicFhir\Contracts;

interface EpicFhirAuthServiceInterface
{
    public function getSystemAccessToken(string $clientId): string;

    public function getSmartAccessToken(string $clientId, string $ownerKey): string;
}
