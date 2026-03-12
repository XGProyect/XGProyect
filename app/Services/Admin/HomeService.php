<?php

declare(strict_types=1);

namespace App\Services\Admin;

use App\Services\SettingsService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Throwable;
use Xgp\App\Libraries\FormatLib as Format;

class HomeService
{
    public function __construct(private readonly SettingsService $settings)
    {
    }

    public function hasServerErrors(): bool
    {
        return file_exists(storage_path('logs/xgproyect.log'));
    }

    public function isInstallFilePresent(): bool
    {
        return file_exists(public_path('install.php'));
    }

    public function isConfigFileWorldWritable(): bool
    {
        $file = config_path('xgp-db-config.php');

        return file_exists($file) && (fileperms($file) & 0x0002) !== 0;
    }

    public function isVersionOutdated(): bool
    {
        try {
            $response = Http::timeout(1)->get('https://updates.xgproyect.org/latest.json');

            if ($response->successful()) {
                $latestVersion = $response->json('version');

                return is_string($latestVersion) &&
                    version_compare($this->settings->getString('version'), $latestVersion, '<');
            }

            return false;
        } catch (Throwable) {
            return false;
        }
    }

    public function hasPendingUpdate(): bool
    {
        return $this->settings->getString('version') !== config('version.files');
    }

    public function getDbVersion(): string
    {
        return (string) DB::scalar('SELECT @@version'); // @phpstan-ignore cast.string
    }

    public function getDbSize(): string
    {
        return Format::prettyBytes(
            (int) DB::scalar( // @phpstan-ignore cast.int
                'SELECT SUM(data_length + index_length) FROM information_schema.TABLES WHERE table_schema = ?',
                [DB::getDatabaseName()]
            )
        );
    }
}
