<?php

namespace LaravelMysqlS3Backup;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config/config.php' => config_path('laravel-mysql-s3-backup.php'),
        ], 'config');

        if (config('mysql-s3-backup.scheduler_enabled')) {
            $this->callAfterResolving(Schedule::class, function (Schedule $schedule) {
                $schedule->call(fn() => Artisan::call('db:backup'))->cron(config('mysql-s3-backup.scheduler_cron'));
            });
        }
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->commands([
            MysqlS3Backup::class,
        ]);
    }
}
