<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'google_gen_ai' => [
        'api_key' => env('GOOGLE_GEN_AI_KEY', 'replace-me'),
        'base_url' => env('GOOGLE_GEN_AI_BASE_URL', 'https://generativelanguage.googleapis.com/v1beta'),
        'model' => env('GOOGLE_GEN_AI_MODEL', 'models/gemini-2.0-flash'),
    ],

    'firebase' => [
        'credentials' => env('FIREBASE_CREDENTIALS', 'storage/app/corralx-777-aipp-firebase-adminsdk-fbsvc-7d6a9eda94.json'),
        'database_url' => env('FIREBASE_DATABASE_URL', ''),
        'storage_bucket' => env('FIREBASE_STORAGE_BUCKET', ''),
    ],

    'recaptcha' => [
        'key' => env('RECAPTCHA_SITE_KEY'),
        'secret' => env('RECAPTCHA_SECRET_KEY'),
    ],

];
