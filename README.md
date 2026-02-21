# Laravel Epic FHIR Client

A Laravel package for integrating with Epic's FHIR API, supporting both **system-level OAuth client credentials flow** (JWT assertion) and **SMART on FHIR patient launch** (authorization code + PKCE).

Features:
- Client credentials token management with JWT assertion
- JWKS endpoint for public key distribution
- FHIR List searching (system & user lists)
- Patient $summary endpoint
- Basic Patient resource creation
- SMART on FHIR authorization code flow with PKCE
- Session & token persistence in database

## Requirements

- PHP ≥ 8.1
- Laravel ≥ 10.0 (tested up to Laravel 12)
- OpenSSL extension (for JWT signing)
- Valid Epic FHIR application credentials & RSA key pair

## Installation

```bash
composer require telemedicall/laravel-epic-fhir
```

## Publish assets
Publish the configuration file and migration:

```bash
# Publish config
php artisan vendor:publish --tag=epic-fhir-config

# Publish migration (epic_users table)
php artisan vendor:publish --tag=epic-fhir-migrations
```

#Run the migration:
```bash
php artisan migrate
```
This creates the epic_users table used for token & session persistence.

#Basic Usage
Via Service Class / Facade

```bash
use Telemedicall\EpicFhir\Facades\EpicFhir;

// System-level list search
$result = EpicFhir::listSearch('USER123', 'your-client-id-here');

// JWKS endpoint (returns JSON)
$jwks = EpicFhir::jwks('your-client-id-here');
```

#Direct Controller Usage
```bash
use Telemedicall\EpicFhir\Controllers\UserController;

$controller = new UserController();
echo $controller->listSearch('USER123', 'your-client-id-here');
```

#SMART on FHIR Launch (Patient-authorized flow)
Define routes:
```bash
// routes/web.php
Route::get('/epic/jwks', [UserController::class, 'jwks']);
Route::get('/epic/launch', [UserController::class, 'smartOnFhir']);
Route::get('/epic/callback', [UserController::class, 'callback']);
```

Then link to /epic/launch?client_id=your-client-id.

#Important Notes

Tokens are stored in epic_users table and associated with a SessionHash cookie.
Session expiration is set to 1 hour by default (configurable via jwt_exp_seconds + buffer).
For production, always use HTTPS (secure cookie flag is enabled).
Epic sandbox: https://fhir.epic.com/interconnect-fhir-oauth
Full Epic FHIR documentation: https://open.epic.com/Interface/FHIR
