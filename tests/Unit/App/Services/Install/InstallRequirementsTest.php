<?php

declare(strict_types=1);

namespace Tests\Unit\App\Services\Install;

use App\Services\InstallRequirements;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings("PHPMD.StaticAccess")
 */
class InstallRequirementsTest extends TestCase
{
    public function testRequiredPhpExtensionsMatchTheInstallerContract(): void
    {
        $this->assertSame(
            [
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
            ],
            InstallRequirements::REQUIRED_PHP_EXTENSIONS,
        );
    }

    public function testItRecognizesSupportedPhpVersions(): void
    {
        $this->assertTrue(InstallRequirements::isSupportedPhpVersion('8.2.0'));
        $this->assertTrue(InstallRequirements::isSupportedPhpVersion('8.4.1'));
        $this->assertFalse(InstallRequirements::isSupportedPhpVersion('8.1.99'));
    }

    public function testItExtractsDatabaseVersionsFromServerStrings(): void
    {
        $this->assertSame('8.0.42', InstallRequirements::extractDatabaseVersion('8.0.42'));
        $this->assertSame('10.11.8', InstallRequirements::extractDatabaseVersion('10.11.8-MariaDB-ubu2204'));
        $this->assertNull(InstallRequirements::extractDatabaseVersion('invalid'));
    }

    public function testItRecognizesSupportedDatabaseVersions(): void
    {
        $this->assertTrue(InstallRequirements::isSupportedDatabaseVersion('5.7.44'));
        $this->assertTrue(InstallRequirements::isSupportedDatabaseVersion('10.11.8-MariaDB-ubu2204'));
        $this->assertFalse(InstallRequirements::isSupportedDatabaseVersion('5.6.51'));
        $this->assertFalse(InstallRequirements::isSupportedDatabaseVersion('invalid'));
    }

    public function testMissingPhpExtensionsReflectsTheConfiguredList(): void
    {
        $expected = array_values(array_filter(
            InstallRequirements::REQUIRED_PHP_EXTENSIONS,
            static fn (string $extension): bool => !extension_loaded($extension),
        ));

        $this->assertSame($expected, InstallRequirements::missingPhpExtensions());
    }
}
