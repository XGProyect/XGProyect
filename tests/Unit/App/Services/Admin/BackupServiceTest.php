<?php

declare(strict_types=1);

namespace Tests\Unit\App\Services\Admin;

use App\Services\Admin\BackupService;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings("PHPMD.TooManyPublicMethods")
 */
#[CoversClass(BackupService::class)]
class BackupServiceTest extends TestCase
{
    private BackupService $service;
    private string $tempDir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tempDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'xgp_backup_test_' . uniqid();
        mkdir($this->tempDir, 0755, true);

        // Inject the temp dir so storage_path() is never called
        $this->service = new BackupService($this->tempDir);
    }

    protected function tearDown(): void
    {
        foreach (glob($this->tempDir . DIRECTORY_SEPARATOR . '*') ?: [] as $file) {
            unlink($file);
        }
        rmdir($this->tempDir);

        parent::tearDown();
    }

    public function testIsValidFileNameAcceptsWellFormedName(): void
    {
        $this->assertTrue(
            $this->service->isValidFileName('db-backup-20260228-1740700800-abc123def456.sql')
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

    public function testIsValidFileNameRejectsMissingDatePart(): void
    {
        $this->assertFalse($this->service->isValidFileName('db-backup-.sql'));
    }

    public function testIsValidFileNameRejectsWrongExtension(): void
    {
        $this->assertFalse(
            $this->service->isValidFileName('db-backup-20260228-1740700800-abc123.txt')
        );
    }

    public function testBackupPathWithNoArgumentReturnsBaseDir(): void
    {
        $this->assertSame($this->tempDir, $this->service->backupPath());
    }

    public function testBackupPathWithFileAppendsSeparatorAndName(): void
    {
        $this->assertSame(
            $this->tempDir . DIRECTORY_SEPARATOR . 'test.sql',
            $this->service->backupPath('test.sql')
        );
    }

    public function testBackupPathWithEmptyStringMatchesNoArgument(): void
    {
        $this->assertSame(
            $this->service->backupPath(),
            $this->service->backupPath('')
        );
    }

    public function testDeleteBackupRemovesExistingFile(): void
    {
        $fileName = 'db-backup-20260228-1740700800-abc123def456.sql';
        $filePath = $this->tempDir . DIRECTORY_SEPARATOR . $fileName;
        file_put_contents($filePath, 'dummy');

        $this->assertFileExists($filePath);

        $this->service->deleteBackup($fileName);

        $this->assertFileDoesNotExist($filePath);
    }

    public function testDeleteBackupDoesNotThrowWhenFileIsMissing(): void
    {
        $this->service->deleteBackup('nonexistent.sql');
        $this->assertTrue(true);
    }

    public function testGetBackupListReturnsMostRecentFirst(): void
    {
        $names = [
            'db-backup-20260101-1000000000-aaa000000001.sql',
            'db-backup-20260201-1100000000-bbb000000002.sql',
            'db-backup-20260228-1200000000-ccc000000003.sql',
        ];

        foreach ($names as $name) {
            file_put_contents($this->tempDir . DIRECTORY_SEPARATOR . $name, 'SELECT 1;');
        }

        // Stub only the protected formatFileName so Options::getInstance() is never called
        $service = $this->getMockBuilder(BackupService::class)
            ->setConstructorArgs([$this->tempDir])
            ->onlyMethods(['formatFileName'])
            ->getMock();

        $service->method('formatFileName')->willReturnArgument(0);

        $list = $service->getBackupList();

        $this->assertCount(3, $list);
        $this->assertStringContainsString('1200000000', $list[0]['full_file_name']);
        $this->assertStringContainsString('1100000000', $list[1]['full_file_name']);
        $this->assertStringContainsString('1000000000', $list[2]['full_file_name']);
    }

    public function testGetBackupListReturnsEmptyArrayWhenNoneExist(): void
    {
        $list = $this->service->getBackupList();

        $this->assertInstanceOf(Collection::class, $list);
        $this->assertEmpty($list);
    }

    /**
     * @dataProvider prettyBytesProvider
     */
    #[DataProvider('prettyBytesProvider')]
    public function testGetBackupListFileSizeFormatting(int $bytes, string $expected): void
    {
        $fileName = 'db-backup-20260228-1740700800-abc123def456.sql';
        file_put_contents($this->tempDir . DIRECTORY_SEPARATOR . $fileName, str_repeat('x', $bytes));

        $service = $this->getMockBuilder(BackupService::class)
            ->setConstructorArgs([$this->tempDir])
            ->onlyMethods(['formatFileName'])
            ->getMock();

        $service->method('formatFileName')->willReturnArgument(0);

        $list = $service->getBackupList();

        $this->assertSame($expected, $list[0]['file_size']);
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
}
