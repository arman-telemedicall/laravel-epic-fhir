<?php

namespace Teleminergmbh\EpicFhir\Tests;

use Illuminate\Support\Facades\Facade;
use Orchestra\Testbench\TestCase as Orchestra;
use Teleminergmbh\EpicFhir\Contracts\EpicFhirTokenStoreInterface;
use Teleminergmbh\EpicFhir\EpicFhirServiceProvider;
use Teleminergmbh\EpicFhir\Tests\Support\FakeTokenStore;

/**
 * @method mixed get(string $uri, array $headers = [])
 * @method $this withSession(array $data)
 */
class TestCase extends Orchestra
{
    protected static ?string $privateKeyPath = null;

    protected static ?string $publicKeyPath = null;

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('app.key', 'base64:'.base64_encode(random_bytes(32)));
        $app['config']->set('app.cipher', 'AES-256-CBC');
    }

    protected function setUp(): void
    {
        parent::setUp();

        Facade::setFacadeApplication($this->app);

        $this->ensureTestKeys();

        config()->set('laravel-epic-fhir.private_key_path', self::$privateKeyPath);
        config()->set('laravel-epic-fhir.public_key_path', self::$publicKeyPath);

        config()->set('laravel-epic-fhir.token_url', 'https://example.test/oauth/token');
        config()->set('laravel-epic-fhir.auth_url', 'https://example.test/oauth/authorize');
        config()->set('laravel-epic-fhir.fhir_base', 'https://example.test/FHIR/R4');
        config()->set('laravel-epic-fhir.allowed_root', 'example.test');
        config()->set('laravel-epic-fhir.routes.prefix', 'fhir/R4');

        $this->app->singleton(EpicFhirTokenStoreInterface::class, fn () => new FakeTokenStore);
    }

    protected function ensureTestKeys(): void
    {
        if (
            self::$privateKeyPath &&
            self::$publicKeyPath &&
            file_exists(self::$privateKeyPath) &&
            file_exists(self::$publicKeyPath)
        ) {
            return;
        }

        $res = openssl_pkey_new([
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]);

        if ($res === false) {
            throw new \RuntimeException('Failed to generate RSA keypair for tests');
        }

        $privateKey = '';
        if (! openssl_pkey_export($res, $privateKey)) {
            throw new \RuntimeException('Failed to export private key for tests');
        }

        $details = openssl_pkey_get_details($res);
        if (! is_array($details) || ! isset($details['key'])) {
            throw new \RuntimeException('Failed to extract public key for tests');
        }

        $tmpDir = rtrim(sys_get_temp_dir(), '/');
        $suffix = 'epic-fhir-'.bin2hex(random_bytes(8));

        self::$privateKeyPath = $tmpDir.'/'.$suffix.'.private.pem';
        self::$publicKeyPath = $tmpDir.'/'.$suffix.'.public.pem';

        file_put_contents(self::$privateKeyPath, $privateKey);
        file_put_contents(self::$publicKeyPath, $details['key']);
    }

    protected function getPackageProviders($app)
    {
        return [EpicFhirServiceProvider::class];
    }
}
