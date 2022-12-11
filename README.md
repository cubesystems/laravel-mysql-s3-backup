# Laravel MySQL to S3 Backup

This is a very simple database backup script for Laravel. It takes a `mysqldump` and saves it to [Amazon S3](http://aws.amazon.com/s3/) or compatible object storage.

This package is very opinionated. Other backup scripts can support other database types or other places besides S3 to store your backup. This does not.

## Installation

1. Install package

    ```
    composer require cubesystems/laravel-mysql-s3-backup
    ```

2. Publish and edit the config

    ```bash
    php artisan vendor:publish --provider="LaravelMysqlS3Backup\ServiceProvider"
    ```

    Edit `config/mysql-s3-backup.php`

## Usage

```bash
$ php artisan db:backup
```

That's it. No arguments or optional parameters.

### Credit

This package was originally forked from [fitztrev](https://github.com/fitztrev/laravel-mysql-s3-backup) before a complete rewrite.

## License

Laravel MySQL to S3 Backup is open-sourced software licensed under the [MIT license](https://github.com/ayles-software/laravel-mysql-s3-backup/blob/master/LICENSE.md).
