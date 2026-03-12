<?php

declare(strict_types=1);

namespace Tests\Unit\App\Services\Admin;

use App\Services\Admin\PermissionsService;
use App\Services\SettingsService;
use RuntimeException;
use Tests\TestCase;
use Xgp\App\Core\Enumerators\UserRanksEnumerator as UserRanks;

/**
 * @SuppressWarnings("PHPMD.UnusedFormalParameter")
 */
class PermissionsServiceTest extends TestCase
{
    public function testConstructorSucceedsWithEmptyPermissionsJson(): void
    {
        $service = $this->buildService('{}');

        // No exception thrown — construction succeeded
        $this->assertInstanceOf(PermissionsService::class, $service);
    }

    public function testConstructorThrowsRuntimeExceptionForInvalidJson(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Failed to load admin permissions');

        $this->buildService('{invalid json}');
    }

    public function testUpdatePermissionsGrantsAccessForCheckedModule(): void
    {
        $settings = $this->createMock(SettingsService::class);
        $settings->method('getString')->willReturn('{}');

        // Capture what gets written back
        $written = null;
        $settings->method('write')->willReturnCallback(function (string $_key, mixed $value) use (&$written) {
            $written = $value;
            return true;
        });

        $service = new PermissionsService($settings);

        // Simulate form input: 'backup' module, GO role checked
        $service->updatePermissions(['backup' => [UserRanks::GO => 'on']]);

        $this->assertNotNull($written);
        $this->assertIsString($written);

        /** @var array<string, array<int, int>> $decoded */
        $decoded = json_decode($written, true);
        $this->assertSame(1, $decoded['backup'][UserRanks::GO]);
    }

    public function testUpdatePermissionsRemovesAccessForUncheckedModule(): void
    {
        $initialPermissions = json_encode(['backup' => [UserRanks::GO => 1]]);

        $settings = $this->createMock(SettingsService::class);
        $settings->method('getString')->willReturn($initialPermissions);

        $written = null;
        $settings->method('write')->willReturnCallback(function (string $_key, mixed $value) use (&$written) {
            $written = $value;
            return true;
        });

        $service = new PermissionsService($settings);

        // Simulate form input: 'backup' module, GO role NOT checked (absent from input)
        $service->updatePermissions(['backup' => []]);

        $this->assertNotNull($written);
        $this->assertIsString($written);

        /** @var array<string, array<int, int>> $decoded */
        $decoded = json_decode($written, true);
        $this->assertSame(0, $decoded['backup'][UserRanks::GO]);
    }

    public function testUpdatePermissionsCannotModifyAdminRole(): void
    {
        $settings = $this->createMock(SettingsService::class);
        $settings->method('getString')->willReturn('{}');

        $written = null;
        $settings->method('write')->willReturnCallback(function (string $_key, mixed $value) use (&$written) {
            $written = $value;
            return true;
        });

        $service = new PermissionsService($settings);

        // Attempt to explicitly grant ADMIN role via form — should be silently ignored
        $service->updatePermissions(['backup' => [UserRanks::ADMIN => 'on']]);

        $this->assertNotNull($written);
        $this->assertIsString($written);

        /** @var array<string, array<int, int>> $decoded */
        $decoded = json_decode($written, true);

        // ADMIN key should not be set — the role is not editable
        $this->assertArrayNotHasKey(UserRanks::ADMIN, $decoded['backup'] ?? []);
    }

    private function buildService(string $permissionsJson): PermissionsService
    {
        $settings = $this->createMock(SettingsService::class);
        $settings->method('getString')->willReturn($permissionsJson);

        return new PermissionsService($settings);
    }
}
