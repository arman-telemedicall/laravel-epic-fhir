<?php

namespace Teleminergmbh\EpicFhir\Database\Traits;

use Illuminate\Support\Arr;

trait EpicConfigTrait
{
    /**
     * The merged configuration array.
     *
     * @var array<string, mixed>
     */
    protected array $epicConfig = [];

    /**
     * Merge package defaults with Laravel config + optional runtime overrides.
     */
    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    public function initializeEpicConfig(array $overrides = []): array
    {
        // Start with Laravel config (published config/epic-fhir.php)
        $config = config('laravel-epic-fhir', []);

        // Merge defaults → published config → runtime overrides
        $merged = array_replace_recursive(
            $config,
            $overrides
        );

        $this->epicConfig = $merged;

        return $this->epicConfig;
    }

    /**
     * Get a config value using dot notation.
     *
     * @param  mixed  $default
     * @return mixed
     */
    public function epicConfig(string $key, $default = null)
    {
        return Arr::get($this->epicConfig, $key, $default);
    }

    /**
     * Set a config value at runtime (useful for testing or dynamic changes).
     *
     * @param  mixed  $value
     */
    public function setEpicConfig(string $key, $value): void
    {
        Arr::set($this->epicConfig, $key, $value);
    }

    /**
     * Get the full config array.
     *
     * @return array<string, mixed>
     */
    public function allEpicConfig(): array
    {
        return $this->epicConfig;
    }

    public function requirePrivateKeyPath(): string
    {
        return $this->resolveKeyPath($this->epicConfig('private_key_path'));
    }

    public function requirePublicKeyPath(): string
    {
        return $this->resolveKeyPath($this->epicConfig('public_key_path'));
    }

    /**
     * Resolve and validate key file path.
     *
     * @throws \RuntimeException
     */
    protected function resolveKeyPath(?string $path): string
    {
        if (empty($path) || ! file_exists($path) || ! is_readable($path)) {
            throw new \RuntimeException(
                'Epic FHIR key file not found or not readable: '.($path ?? 'null')
            );
        }

        return $path;
    }
}
