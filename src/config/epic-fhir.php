<?php

return [
    'token_url'      => env('EPIC_TOKEN_URL', 'https://fhir.epic.com/interconnect-fhir-oauth/oauth2/token'),
    'auth_url'       => env('EPIC_AUTH_URL', 'https://fhir.epic.com/interconnect-fhir-oauth/oauth2/authorize'),
    'fhir_base'      => env('EPIC_FHIR_BASE', 'https://fhir.epic.com/interconnect-fhir-oauth/api/FHIR/R4'),

    'jwt_alg'                 => 'RS256',
    'jwt_kid'                 => 'Epic-key',
    'jwt_exp_seconds'         => 300,

    // Very important → should be OUTSIDE version control!
    'private_key_path'        => env('EPIC_PRIVATE_KEY_PATH', '/home/admin/domains/gblvck.com/etc/private.key'),
    'public_key_path'         => env('EPIC_PUBLIC_KEY_PATH', '/home/admin/domains/gblvck.com/etc/public.key'),

    'session_cookie_lifetime' => 3600,
    'cookie_domain'           => env('EPIC_COOKIE_DOMAIN', '.telemedicall.com'),

    'oauth_scope'             => 'system/Patient.read system/Patient.search system/Patient.write',
    'smart_scope'             => 'openid fhirUser patient.read patient.search launch launch/patient',
    'code_challenge_method'   => 'S256',

    'list_code'               => 'patients',
    'list_subject'            => env('EPIC_LIST_SUBJECT', ''), // e.g. Practitioner/123
    'list_status'             => env('EPIC_LIST_STATUS', 'current'),

    'system_lists_identifier' => 'urn:oid:1.2.840.114350.1.13.0.1.7.2.806567|5332',
    'user_lists_identifier'   => 'urn:oid:1.2.840.114350.1.13.0.1.7.2.698283|9192',

    'db' => [
        'connection' => env('EPIC_DB_CONNECTION', 'mysql'),
        //dedicated connection:
        'host'     => env('EPIC_DB_HOST', 'localhost'),
        'port'     => env('EPIC_DB_PORT', '3306'),
        'database' => env('EPIC_DB_DATABASE', 'admin_epic'),
        'username' => env('EPIC_DB_USERNAME', 'admin_epic'),
        'password' => env('EPIC_DB_PASSWORD', 'K3xuPkXWhwR3zp3GtKag'),
    ],
    'allowed_root' => env('EPIC_ALLOWED_ROOT', 'gblvck.com'),
];