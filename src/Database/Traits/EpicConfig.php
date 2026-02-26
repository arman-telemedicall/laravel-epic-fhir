<?php

namespace Telemedicall\EpicFhir\Database\Traits;

use Illuminate\Support\Arr;

trait EpicConfigTrait
{
    /**
     * The merged configuration array.
     *
     * @var array
     */
    protected array $epicConfig = [];

    /**
     * Default configuration values.
     *
     * @var array
     */
    protected array $epicDefaults = [
        'token_url'               => 'https://fhir.epic.com/interconnect-fhir-oauth/oauth2/token',
        'auth_url'                => 'https://fhir.epic.com/interconnect-fhir-oauth/oauth2/authorize',
        'fhir_base'               => 'https://fhir.epic.com/interconnect-fhir-oauth/api/FHIR/R4',

        'jwt_alg'                 => 'RS256',
        'jwt_kid'                 => 'Epic-key',
        'jwt_exp_seconds'         => 300,

        'private_key_path'        => null,  // should come from env / config
        'public_key_path'         => null,

        'session_cookie_lifetime' => 3600,
        'cookie_domain'           => '.telemedicall.com',

        'oauth_scope'             => 'system/Patient.read system/Patient.search system/Patient.write',
        'smart_scope'             => 'openid fhirUser patient.read patient.search launch launch/patient',
        'code_challenge_method'   => 'S256',

        'list_code'               => 'patients',
        'list_subject'            => '',
        'list_status'             => 'current',

        'system_lists_identifier' => 'urn:oid:1.2.840.114350.1.13.0.1.7.2.806567|5332',
        'user_lists_identifier'   => 'urn:oid:1.2.840.114350.1.13.0.1.7.2.698283|9192',

        'db' => [
            'connection' => 'mysql',
            // If you prefer inline credentials (not recommended):
            // 'host'     => 'localhost',
            // 'database' => 'epic',
            // 'username' => '',
            // 'password' => '',
        ],
    ];

    /**
     * Merge package defaults with Laravel config + optional runtime overrides.
     *
     * @param  array  $overrides
     * @return array
     */
    protected function initializeEpicConfig(array $overrides = []): array
    {
        // Start with Laravel config (published config/epic-fhir.php)
        $config = config('epic-fhir', []);

        // Merge defaults → published config → runtime overrides
        $merged = array_replace_recursive(
            $this->epicDefaults,
            $config,
            $overrides
        );

        // Ensure key paths are resolved (prevent null or empty breaking openssl)
        $merged['private_key_path'] = $this->resolveKeyPath(
            $merged['private_key_path'] ?? storage_path('epic/private.key')
        );

        $merged['public_key_path'] = $this->resolveKeyPath(
            $merged['public_key_path'] ?? storage_path('epic/public.key')
        );

        $this->epicConfig = $merged;

        return $this->epicConfig;
    }

    /**
     * Get a config value using dot notation.
     *
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    public function epicConfig(string $key, $default = null)
    {
        return Arr::get($this->epicConfig, $key, $default);
    }

    /**
     * Set a config value at runtime (useful for testing or dynamic changes).
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return void
     */
    public function setEpicConfig(string $key, $value): void
    {
        Arr::set($this->epicConfig, $key, $value);
    }

    /**
     * Get the full config array.
     *
     * @return array
     */
    public function allEpicConfig(): array
    {
        return $this->epicConfig;
    }

    /**
     * Resolve and validate key file path.
     *
     * @param  string|null  $path
     * @return string
     * @throws \RuntimeException
     */
    protected function resolveKeyPath(?string $path): string
    {
        if (empty($path) || !file_exists($path) || !is_readable($path)) {
            throw new \RuntimeException(
                "Epic FHIR key file not found or not readable: " . ($path ?? 'null')
            );
        }

        return $path;
    }
}
