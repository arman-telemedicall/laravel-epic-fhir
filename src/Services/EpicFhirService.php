<?php

namespace Telemedicall\EpicFhir\Services;

use Telemedicall\EpicFhir\Controllers\UserController;

class EpicFhirService
{
    protected array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function userController(): UserController
    {
        return new UserController($this->config);
    }

    // Convenience methods
    public function ListSearch(string $userId, string $clientId)
    {
        return $this->userController()->ListSearch($userId, $clientId);
    }

    public function jwks(string $clientId)
    {
        return $this->userController()->jwks($clientId);
    }

    // ... add more proxy methods
}