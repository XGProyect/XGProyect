<?php

declare(strict_types=1);

namespace App\Services\Admin;

use Illuminate\Contracts\Console\Kernel as ArtisanKernel;
use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;
use Illuminate\Support\Collection;
use Spatie\Backup\BackupDestination\Backup;
use Spatie\Backup\BackupDestination\BackupDestination;
use Spatie\Backup\Tasks\Backup\BackupJob;
use Xgp\App\Core\Options;

class BackupService
{
    private const DISK = 'backups';

    /** Spatie zip filename: 2026-02-28-12-00-00.zip */
    private const FILE_PATTERN = '/^\d{4}-\d{2}-\d{2}-\d{2}-\d{2}-\d{2}\.zip$/';

    public function __construct(
        private readonly ArtisanKernel $artisan,
        private readonly FilesystemFactory $storage,
        private readonly string $backupName = '',
        private readonly string $dateFormat = '',
    ) {
    }

    public function isAutoBackupEnabled(): bool
    {
        return Options::getInstance()->get('auto_backup') == 1;
    }

    public function saveSettings(bool $autoBackup): void
    {
        Options::getInstance()->write('auto_backup', $autoBackup ? 1 : 0);
    }

    /**
     * @return Collection<int, array{file_name: string, file_size: string, full_file_name: string}>
     */
    public function getBackupList(): Collection
    {
        return collect($this->destination()->backups())
            ->sortByDesc(fn (Backup $backup) => $backup->date()->timestamp)
            ->values()
            ->map(fn (Backup $backup) => [
                'file_name' => $backup->date()->format($this->dateFormat()),
                'file_size' => $this->prettyBytes($backup->sizeInBytes()),
                'full_file_name' => basename($backup->path()),
            ]);
    }

    public function createBackup(): void
    {
        $this->artisan->call('backup:run', ['--only-db' => true]);
    }

    public function deleteBackup(string $fileName): void
    {
        $path = $this->backupSubdir() . '/' . $fileName;

        $this->storage->disk(self::DISK)->delete($path);
    }

    public function isValidFileName(string $fileName): bool
    {
        return (bool) preg_match(self::FILE_PATTERN, $fileName);
    }

    public function filePath(string $fileName): string
    {
        return $this->backupSubdir() . '/' . $fileName;
    }

    public function diskName(): string
    {
        return self::DISK;
    }

    private function destination(): BackupDestination
    {
        $disk = $this->storage->disk(self::DISK);

        return new BackupDestination($disk, $this->resolvedBackupName(), self::DISK);
    }

    private function backupSubdir(): string
    {
        return $this->resolvedBackupName();
    }

    private function resolvedBackupName(): string
    {
        if ($this->backupName !== '') {
            return $this->backupName;
        }

        $name = config('backup.backup.name');

        return is_string($name) ? $name : '';
    }

    private function dateFormat(): string
    {
        if ($this->dateFormat !== '') {
            return $this->dateFormat;
        }

        $format = Options::getInstance()->get('date_format_extended');

        return is_string($format) ? $format : BackupJob::FILENAME_FORMAT;
    }

    private function prettyBytes(int | float $bytes, int $precision = 2): string
    {
        $units = ['Bytes', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = (int) floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
