<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Default source repository type
    |--------------------------------------------------------------------------
    |
    | The default source repository type you want to pull your updates from.
    |
    */

    'default' => env('SELF_UPDATER_SOURCE', 'github'),

    /*
    |--------------------------------------------------------------------------
    | Version installed
    |--------------------------------------------------------------------------
    |
    | This is the version your application is currently running
    |
    */

    'version_installed' => env('SELF_UPDATER_VERSION_INSTALLED', '1.0.0'),

    /*
    |--------------------------------------------------------------------------
    | Repository types
    |--------------------------------------------------------------------------
    |
    | Here you can define all the available repository types.
    |
    */

    'repository_types' => [
        'github' => [
            'type'                 => 'github',
            'repository_vendor'    => env('SELF_UPDATER_REPO_VENDOR', 'Spade-kun'),
            'repository_name'      => env('SELF_UPDATER_REPO_NAME', 'Pageant_Multi_Tenant'),
            'repository_url'       => '',
            'download_path'        => env('SELF_UPDATER_DOWNLOAD_PATH', '/tmp'),
            'private_access_token' => env('SELF_UPDATER_GITHUB_PRIVATE_ACCESS_TOKEN', ''),
            'use_branch'           => false,
            'package_file_name'    => env('SELF_UPDATER_PACKAGE_FILE_NAME'),
        ],
        'gitlab' => [
            'base_url'             => '',
            'type'                 => 'gitlab',
            'repository_id'        => env('SELF_UPDATER_REPO_URL', ''),
            'download_path'        => env('SELF_UPDATER_DOWNLOAD_PATH', '/tmp'),
            'private_access_token' => env('SELF_UPDATER_GITLAB_PRIVATE_ACCESS_TOKEN', ''),
        ],
        'http' => [
            'type'                 => 'http',
            'repository_url'       => env('SELF_UPDATER_REPO_URL', ''),
            'pkg_filename_format'  => env('SELF_UPDATER_PKG_FILENAME_FORMAT', 'v_VERSION_'),
            'download_path'        => env('SELF_UPDATER_DOWNLOAD_PATH', '/tmp'),
            'private_access_token' => env('SELF_UPDATER_HTTP_PRIVATE_ACCESS_TOKEN', ''),
        ],
        'gitea' => [
            'type'                 => 'gitea',
            'repository_vendor'    => env('SELF_UPDATER_REPO_VENDOR', ''),
            'gitea_url'            => env('SELF_UPDATER_GITEA_URL', ''),
            'repository_name'      => env('SELF_UPDATER_REPO_NAME', ''),
            'download_path'        => env('SELF_UPDATER_DOWNLOAD_PATH', '/tmp'),
            'private_access_token' => env('SELF_UPDATER_GITEA_PRIVATE_ACCESS_TOKEN', ''),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Exclude folders from update
    |--------------------------------------------------------------------------
    |
    | Specific folders which should not be updated and will be skipped during the
    | update process.
    |
    */

    'exclude_folders' => [
        'node_modules',
        'bootstrap/cache',
        'bower',
        'storage/app',
        'storage/framework',
        'storage/logs',
        'storage/self-update',
        'vendor',
    ],

    /*
    |--------------------------------------------------------------------------
    | Download Timeout
    |--------------------------------------------------------------------------
    |
    | Specifies the duration (in seconds) for how long downloads can take
    | until they timeout.
    |
    */

    'download_timeout' => env('SELF_UPDATER_DOWNLOAD_TIMEOUT', 400),

    /*
    |--------------------------------------------------------------------------
    | Event Logging
    |--------------------------------------------------------------------------
    |
    | Configure if fired events should be logged
    |
    */

    'log_events' => env('SELF_UPDATER_LOG_EVENTS', true),

    /*
    |--------------------------------------------------------------------------
    | Mail To Settings
    |--------------------------------------------------------------------------
    |
    | Configure if fired events should be logged
    |
    */

    'mail_to' => [
        'address' => env('SELF_UPDATER_MAILTO_ADDRESS', ''),
        'name' => env('SELF_UPDATER_MAILTO_NAME', ''),
        'subject_update_available' => env('SELF_UPDATER_MAILTO_SUBJECT_UPDATE_AVAILABLE', 'Update available'),
        'subject_update_succeeded' => env('SELF_UPDATER_MAILTO_SUBJECT_UPDATE_SUCCEEDED', 'Update succeeded'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    |
    | Specify for which events you want to get notifications.
    |
    */

    'notifications' => [
        'notifications' => [
            \Codedge\Updater\Notifications\Notifications\UpdateSucceeded::class => ['mail'],
            \Codedge\Updater\Notifications\Notifications\UpdateFailed::class => ['mail'],
            \Codedge\Updater\Notifications\Notifications\UpdateAvailable::class => ['mail'],
        ],

        /*
         * Here you can specify the notifiable to which the notifications should be sent.
         */
        'notifiable' => \Codedge\Updater\Notifications\Notifiable::class,

        'mail' => [
            'to' => [
                'address' => env('SELF_UPDATER_MAILTO_ADDRESS', ''),
                'name' => env('SELF_UPDATER_MAILTO_NAME', '')
            ]
        ]
    ],

    /*
    |---------------------------------------------------------------------------
    | Register custom artisan commands
    |---------------------------------------------------------------------------
    */

    'artisan_commands' => [
        'pre_update' => [
            //'command:signature' => [
            //    'class' => Command class
            //    'params' => []
            //]
        ],
        'post_update' => [

        ],
    ],

];
