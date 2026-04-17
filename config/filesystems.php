<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application for file storage.
    |
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Below you may configure as many filesystem disks as necessary, and you
    | may even configure multiple disks for the same driver. Examples for
    | most supported storage drivers are configured here for reference.
    |
    | Supported drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
            'serve' => true,
            'throw' => false,
            'report' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],

        /*
         | ══ AWS S3 / Cloudflare R2 / Wasabi (pour 1M+ images) ══
         | Passer à ce driver pour un stockage illimité et une distribution
         | mondiale via CDN. Compatible S3 : AWS, Cloudflare R2 (moins cher),
         | Wasabi (pas de frais de sortie), Backblaze B2.
         |
         | Pour activer : mettre FILESYSTEM_DISK=s3 dans .env
         |                et renseigner les variables ci-dessous.
         */
        's3' => [
            'driver'                  => 's3',
            'key'                     => env('AWS_ACCESS_KEY_ID'),
            'secret'                  => env('AWS_SECRET_ACCESS_KEY'),
            'region'                  => env('AWS_DEFAULT_REGION', 'eu-west-3'),
            'bucket'                  => env('AWS_BUCKET'),
            // CDN URL (Cloudflare, CloudFront, etc.) — si défini, toutes les URLs
            // passeront par le CDN au lieu de S3 directement.
            'url'                     => env('AWS_URL'),         // ex: https://cdn.monsite.com
            'endpoint'                => env('AWS_ENDPOINT'),    // pour Cloudflare R2 / Wasabi
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'visibility'              => 'public',
            'throw'                   => false,
            'report'                  => false,
        ],

        /*
         | ══ Cloudflare R2 (alternative S3 sans frais de sortie) ══
         | R2 = stockage S3-compatible + bande passante GRATUITE.
         | Idéal pour des millions d'images servies via Cloudflare CDN.
         | endpoint = https://<ACCOUNT_ID>.r2.cloudflarestorage.com
         */
        'r2' => [
            'driver'                  => 's3',
            'key'                     => env('CLOUDFLARE_R2_ACCESS_KEY'),
            'secret'                  => env('CLOUDFLARE_R2_SECRET_KEY'),
            'region'                  => 'auto',
            'bucket'                  => env('CLOUDFLARE_R2_BUCKET'),
            'endpoint'                => env('CLOUDFLARE_R2_ENDPOINT'),
            'url'                     => env('CLOUDFLARE_R2_PUBLIC_URL'), // ton domaine CDN custom
            'use_path_style_endpoint' => true,
            'visibility'              => 'public',
            'throw'                   => false,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
