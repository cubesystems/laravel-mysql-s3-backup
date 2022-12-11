<?php

use Illuminate\Support\Str;

return [
    /*
     * Configure with your Amazon S3 credentials
     * You should use an IAM user who only has PutObject access
     * to a specified bucket
     */
    's3' => [
        'key'    => env('MYSQL_S3_BACKUP_AWS_API_KEY'),
        'secret' => env('MYSQL_S3_BACKUP_AWS_API_SECRET'),
        'bucket' => env('MYSQL_S3_BACKUP_AWS_S3_BUCKET'),
        'region' => env('MYSQL_S3_BACKUP_AWS_S3_REGION'),
        'endpoint' => env('MYSQL_S3_BACKUP_AWS_ENDPOINT'),
        'folder' => env('MYSQL_S3_BACKUP_FOLDER'),
        'use_path_style_endpoint' => env('MYSQL_S3_BACKUP_USE_PATH_STYLE_ENDPOINT', false),
    ],

    /*
     * Want to add some custom mysqldump args?
     */
    'custom_mysqldump_args' => null,

    /*
     * Time allowed to run backup
     */
    'sql_timeout' => 7200, // 2 hours

    /*
     * Backup filename
     */
    'filename' => Str::slug(env('APP_NAME')) . '-' . env('APP_ENV') . '-' . env('DB_DATABASE') . '-backup-%s.sql',

    /*
     * Where to store the backup file locally
     */
    'backup_dir' => env('MYSQL_S3_BACKUP_LOCAL_DIR', '/tmp'),

    /*
     * Configure whether scheduler is enabled
     */
    'scheduler_enabled' => env('MYSQL_S3_BACKUP_SCHEDULER_ENABLED', false),

    /*
     * Scheduler backup job cron time
     */
    'scheduler_cron' => env('MYSQL_S3_BACKUP_SCHEDULER_CRON', '10 0 * * *'),
];
