<?php

namespace LaravelMysqlS3Backup;

use Aws\S3\MultipartUploader;
use Aws\S3\S3Client;
use Illuminate\Console\Command;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class MysqlS3Backup extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'db:backup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a sqldump of your MySQL database and upload it to Amazon S3';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $cmd = sprintf('mysqldump --host=%s --port=%s --user=%s --password=%s --single-transaction --routines --triggers',
            escapeshellarg(config('database.connections.mysql.host')),
            escapeshellarg(config('database.connections.mysql.port')),
            escapeshellarg(config('database.connections.mysql.username')),
            escapeshellarg(config('database.connections.mysql.password'))
        );

        if (config('laravel-mysql-s3-backup.custom_mysqldump_args')) {
            $cmd .= ' ' . config('laravel-mysql-s3-backup.custom_mysqldump_args');
        }

        $cmd .= ' ' . escapeshellarg(config('database.connections.mysql.database'));

        $fileName = config('laravel-mysql-s3-backup.backup_dir') . '/' . sprintf(config('laravel-mysql-s3-backup.filename'), date('Ymd-His'));

        // Handle gzip
        if (config('laravel-mysql-s3-backup.gzip')) {
            $fileName .= '.gz';
            $cmd .= sprintf(' | gzip > %s', escapeshellarg($fileName));
        } else {
            $cmd .= sprintf(' > %s', escapeshellarg($fileName));
        }

        if ($this->output->isVerbose()) {
            $this->output->writeln('Running backup for database `' . config('database.connections.mysql.database') . '`');
            $this->output->writeln('Saving to ' . $fileName);
        }

        if ($this->output->isDebug()) {
            $this->output->writeln("Running command: {$cmd}");
        }

        $process = Process::fromShellCommandline('bash -o pipefail -c "' . $cmd . '"');
        $process->setTimeout(config('laravel-mysql-s3-backup.sql_timeout'));
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        if ($this->output->isVerbose()) {
            $this->output->writeln("Backup saved to {$fileName}");
        }

        // Upload to S3
        $s3 = new S3Client([
            'credentials' => [
                'key' => config('laravel-mysql-s3-backup.s3.key'),
                'secret' => config('laravel-mysql-s3-backup.s3.secret'),
            ],
            'endpoint' => config('laravel-mysql-s3-backup.s3.endpoint'),
            'region' => config('laravel-mysql-s3-backup.s3.region'),
            'version' => 'latest',
            'use_path_style_endpoint' => config('laravel-mysql-s3-backup.s3.use_path_style_endpoint'),
        ]);

        $bucket = config('laravel-mysql-s3-backup.s3.bucket');
        $key = basename($fileName);

        if ($folder = config('laravel-mysql-s3-backup.s3.folder')) {
            $key = $folder . '/' . $key;
        }

        if ($this->output->isVerbose()) {
            $this->output->writeln(sprintf('Uploading %s to S3/%s', $key, $bucket));
        }

        $uploader = new MultipartUploader($s3, $fileName, [
            'bucket' => $bucket,
            'key' => $key,
        ]);

        try {
            $uploader->upload();
        } finally {
            unlink($fileName);
        }

        if ($this->output->isVerbose()) {
            $this->output->writeln("Backup {$fileName} successfully uploaded to s3");
        }
    }
}
