<?php

return [
    'token_url' => env('EPIC_FHIR_TOKEN_URL', 'https://fhir.epic.com/interconnect-fhir-oauth/oauth2/token'),
    'auth_url' => env('EPIC_FHIR_AUTH_URL', 'https://fhir.epic.com/interconnect-fhir-oauth/oauth2/authorize'),
    'fhir_base' => env('EPIC_FHIR_FHIR_BASE', 'https://fhir.epic.com/interconnect-fhir-oauth/api/FHIR/R4'),

    'jwt_alg' => env('EPIC_FHIR_JWT_ALG', 'RS256'),
    'jwt_kid' => env('EPIC_FHIR_JWT_KID', 'Epic-key'),
    'jwt_exp_seconds' => (int) env('EPIC_FHIR_JWT_EXP_SECONDS', 300),

    'private_key_path' => env('EPIC_FHIR_PRIVATE_KEY_PATH'),
    'public_key_path' => env('EPIC_FHIR_PUBLIC_KEY_PATH'),

    'session_cookie_lifetime' => (int) env('EPIC_FHIR_SESSION_COOKIE_LIFETIME', 3600),
    'cookie_domain' => env('EPIC_FHIR_COOKIE_DOMAIN'),

    'oauth_scope' => env('EPIC_FHIR_OAUTH_SCOPE', 'system/Patient.read system/Patient.search system/Patient.write'),
    'smart_scope' => env('EPIC_FHIR_SMART_SCOPE', 'openid fhirUser patient.read patient.search launch launch/patient'),
    'code_challenge_method' => env('EPIC_FHIR_CODE_CHALLENGE_METHOD', 'S256'),

    'smart_flow_ttl_seconds' => (int) env('EPIC_FHIR_SMART_FLOW_TTL_SECONDS', 600),

    'routes' => [
        'enabled' => (bool) env('EPIC_FHIR_ROUTES_ENABLED', true),
        'prefix' => env('EPIC_FHIR_ROUTES_PREFIX', 'epic/fhir/R4'),
        'middleware' => env('EPIC_FHIR_ROUTES_MIDDLEWARE', 'web'),
    ],

    'migrations' => [
        'enabled' => (bool) env('EPIC_FHIR_MIGRATIONS_ENABLED', true),
    ],

    'token_store' => [
        'driver' => env('EPIC_FHIR_TOKEN_STORE_DRIVER', 'cache'),

        'cache' => [
            'store' => env('EPIC_FHIR_CACHE_STORE', null),
            'prefix' => env('EPIC_FHIR_CACHE_PREFIX', 'epic_fhir'),
            'lock_seconds' => (int) env('EPIC_FHIR_LOCK_SECONDS', 10),
            'expires_buffer_seconds' => (int) env('EPIC_FHIR_EXPIRES_BUFFER_SECONDS', 60),
        ],

        'database' => [
            'connection' => env('EPIC_FHIR_DB_CONNECTION', env('DB_CONNECTION', 'mysql')),
            'table' => env('EPIC_FHIR_DB_TABLE', 'epic_fhir_tokens'),
        ],
    ],

    'list_code' => env('EPIC_FHIR_LIST_CODE', 'patients'),
    'list_subject' => env('EPIC_FHIR_LIST_SUBJECT', ''),
    'list_status' => env('EPIC_FHIR_LIST_STATUS', 'current'),

    'system_lists_identifier' => env('EPIC_FHIR_SYSTEM_LISTS_IDENTIFIER', 'urn:oid:1.2.840.114350.1.13.0.1.7.2.806567|5332'),
    'user_lists_identifier' => env('EPIC_FHIR_USER_LISTS_IDENTIFIER', 'urn:oid:1.2.840.114350.1.13.0.1.7.2.698283|9192'),

    'allowed_root' => env('EPIC_FHIR_ALLOWED_ROOT'),

    'base_url' => env('EPIC_FHIR_BASE_URL', null),
    'api_key' => env('EPIC_FHIR_API_KEY', null),
    'timeout' => (int) env('EPIC_FHIR_TIMEOUT', 30),

    'cache' => [
        'enabled' => (bool) env('EPIC_FHIR_CACHE_ENABLED', false),
        'ttl' => (int) env('EPIC_FHIR_CACHE_TTL', 300),
    ],
];
