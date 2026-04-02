<?php

declare(strict_types=1);

namespace Tests\Unit\App\Services\Admin;

use App\Services\Admin\BackupService;
use App\Services\SettingsService;
use Illuminate\Contracts\Console\Kernel as ArtisanKernel;
use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings("PHPMD.TooManyPublicMethods")
 */
#[CoversClass(BackupService::class)]
class BackupServiceTest extends TestCase
{
    private BackupService $service;
    private ArtisanKernel & MockObject $artisan;
    private FilesystemFactory & MockObject $storageFactory;
    private SettingsService & MockObject $settings;
    private Filesystem & MockObject $disk;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan = $this->createMock(ArtisanKernel::class);
        $this->disk = $this->createMock(Filesystem::class);
        $this->storageFactory = $this->createMock(FilesystemFactory::class);
        $this->settings = $this->createMock(SettingsService::class);

        $this->storageFactory->method('disk')->willReturn($this->disk);

        $this->service = new BackupService($this->artisan, $this->storageFactory, $this->settings, 'test-app', 'Y-m-d H:i:s');
    }

    // -------------------------------------------------------------------------
    // isValidFileName
    // -------------------------------------------------------------------------

    public function testIsValidFileNameAcceptsWellFormedName(): void
    {
        $this->assertTrue(
            $this->service->isValidFileName('2026-02-28-12-00-00.zip')
        );
    }

    public function testIsValidFileNameRejectsPathTraversal(): void
    {
        $this->assertFalse($this->service->isValidFileName('../etc/passwd'));
    }

    public function testIsValidFileNameRejectsArbitraryFilename(): void
    {
        $this->assertFalse($this->service->isValidFileName('malicious.php'));
    }

    public function testIsValidFileNameRejectsEmptyString(): void
    {
        $this->assertFalse($this->service->isValidFileName(''));
    }

    public function testIsValidFileNameRejectsMissingTimePart(): void
    {
        $this->assertFalse($this->service->isValidFileName('2026-02-28.zip'));
    }

    public function testIsValidFileNameRejectsWrongExtension(): void
    {
        $this->assertFalse($this->service->isValidFileName('2026-02-28-12-00-00.sql'));
    }

    // -------------------------------------------------------------------------
    // diskName / filePath
    // -------------------------------------------------------------------------

    public function testDiskNameReturnsBackups(): void
    {
        $this->assertSame('backups', $this->service->diskName());
    }

    public function testFilePathPrefixesWithAppName(): void
    {
        $path = $this->service->filePath('2026-02-28-12-00-00.zip');
        $this->assertSame('test-app/2026-02-28-12-00-00.zip', $path);
    }

    // -------------------------------------------------------------------------
    // createBackup
    // -------------------------------------------------------------------------

    public function testCreateBackupCallsArtisan(): void
    {
        $this->artisan
            ->expects($this->once())
            ->method('call')
            ->with('backup:run', ['--only-db' => true]);

        $this->service->createBackup();
    }

    // -------------------------------------------------------------------------
    // deleteBackup
    // -------------------------------------------------------------------------

    public function testDeleteBackupDeletesViaTheDisk(): void
    {
        $this->disk
            ->expects($this->once())
            ->method('delete');

        $this->service->deleteBackup('2026-02-28-12-00-00.zip');
    }

    // -------------------------------------------------------------------------
    // getBackupList
    // -------------------------------------------------------------------------

    public function testGetBackupListReturnsMostRecentFirst(): void
    {
        $this->disk->method('allFiles')->willReturn([
            'test-app/2026-01-01-00-00-00.zip',
            'test-app/2026-02-28-12-00-00.zip',
        ]);
        $this->disk->method('exists')->willReturn(true);
        $this->disk->method('size')->willReturn(1024);

        $list = $this->service->getBackupList();
        $first = $list->get(0);
        $second = $list->get(1);

        $this->assertInstanceOf(Collection::class, $list);
        $this->assertCount(2, $list);
        $this->assertNotNull($first);
        $this->assertNotNull($second);
        $this->assertStringContainsString('2026-02-28', $first['full_file_name']);
        $this->assertStringContainsString('2026-01-01', $second['full_file_name']);
    }

    public function testGetBackupListReturnsEmptyWhenNoneExist(): void
    {
        $this->disk->method('allFiles')->willReturn([]);

        $list = $this->service->getBackupList();

        $this->assertInstanceOf(Collection::class, $list);
        $this->assertEmpty($list);
    }

    /**
     * @return array<string, array{int, string}>
     */
    public static function prettyBytesProvider(): array
    {
        return [
            'bytes' => [500,     '500 Bytes'],
            'kilobytes' => [1024,    '1 KB'],
            'megabytes' => [1048576, '1 MB'],
        ];
    }

    #[DataProvider('prettyBytesProvider')]
    public function testGetBackupListFileSizeFormatting(int $bytes, string $expected): void
    {
        $this->disk->method('allFiles')->willReturn(['test-app/2026-02-28-12-00-00.zip']);
        $this->disk->method('exists')->willReturn(true);
        $this->disk->method('size')->willReturn($bytes);

        $list = $this->service->getBackupList();
        $first = $list->get(0);
        $this->assertNotNull($first);

        $this->assertSame($expected, $first['file_size']);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------
}
