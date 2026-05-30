<?php

declare(strict_types=1);

namespace App\Services;

final class InstallRequirements
{
    public const MINIMUM_DATABASE_VERSION = '5.7';
    public const MINIMUM_PHP_VERSION = '8.2';

    /** @var list<non-empty-string> */
    public const REQUIRED_PHP_EXTENSIONS = [
        'dom',
        'fileinfo',
        'filter',
        'hash',
        'iconv',
        'json',
        'libxml',
        'openssl',
        'pcre',
        'pdo_mysql',
        'session',
        'tokenizer',
        'zip',
    ];

    private function __construct()
    {
    }

    public static function isSupportedPhpVersion(string $version): bool
    {
        return version_compare($version, self::MINIMUM_PHP_VERSION, '>=');
    }

    /** @return list<non-empty-string> */
    public static function missingPhpExtensions(): array
    {
        return array_values(array_filter(
            self::REQUIRED_PHP_EXTENSIONS,
            static fn (string $extension): bool => !extension_loaded($extension),
        ));
    }

    public static function extractDatabaseVersion(string $serverVersion): ?string
    {
        if (!preg_match('/\d+(?:\.\d+){1,2}/', $serverVersion, $matches)) {
            return null;
        }

        return $matches[0];
    }

    public static function isSupportedDatabaseVersion(string $serverVersion): bool
    {
        $version = self::extractDatabaseVersion($serverVersion);

        return $version !== null && version_compare($version, self::MINIMUM_DATABASE_VERSION, '>=');
    }
}
