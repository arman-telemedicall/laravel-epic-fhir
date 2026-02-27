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
     * Merge package defaults with Laravel config + optional runtime overrides.
     *
     * @param  array  $overrides
     * @return array
     */
    public function initializeEpicConfig(array $overrides = []): array
    {
        // Start with Laravel config (published config/epic-fhir.php)
        $config = config('EpicFhir', []);

        // Merge defaults → published config → runtime overrides
        $merged = array_replace_recursive(
            $config,
            $overrides
        );

        // Ensure key paths are resolved (prevent null or empty breaking openssl)
        $merged['private_key_path'] = $this->resolveKeyPath($merged['private_key_path']);

        $merged['public_key_path'] = $this->resolveKeyPath($merged['public_key_path']);

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