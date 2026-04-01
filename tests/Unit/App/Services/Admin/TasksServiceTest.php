<?php

declare(strict_types=1);

namespace Tests\Unit\App\Services\Admin;

use App\Data\Admin\TaskData;
use App\Enums\Admin\AdminTask;
use App\Services\Admin\TasksService;
use App\Services\SettingsService;
use App\Services\TimingService;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

/**
 * @SuppressWarnings("PHPMD.StaticAccess")
 */
class TasksServiceTest extends TestCase
{
    private TasksService $service;
    private SettingsService & MockObject $settings;
    private TimingService & MockObject $timingService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->settings = $this->createMock(SettingsService::class);
        $this->timingService = $this->createMock(TimingService::class);
        $this->timingService->method('formatExtendedDate')->willReturn('2025-01-01 00:00:00');
        $this->app->instance(SettingsService::class, $this->settings);
        $this->service = new TasksService($this->settings, $this->timingService);
    }

    public function testGetTasksReturnsOneEntryPerAdminTaskCase(): void
    {
        $this->settings->method('getBool')->willReturn(false);
        $this->settings->method('getString')->willReturn('0');

        $this->assertCount(count(AdminTask::cases()), $this->service->getTasks());
    }

    public function testGetTasksReturnsTaskDataInstances(): void
    {
        $this->settings->method('getBool')->willReturn(false);
        $this->settings->method('getString')->willReturn('0');

        foreach ($this->service->getTasks() as $task) {
            $this->assertInstanceOf(TaskData::class, $task);
        }
    }

    public function testLastBackupShowsDashesWhenAutoBackupDisabled(): void
    {
        $this->settings->method('getBool')
            ->with('auto_backup')
            ->willReturn(false);

        $this->settings->method('getString')->willReturn('0');

        $lastBackup = $this->service->getTasks()->first(
            fn (TaskData $t) => $t->name === AdminTask::LastBackup->label()
        );

        $this->assertInstanceOf(TaskData::class, $lastBackup);
        $this->assertSame('-', $lastBackup->nextRun);
        $this->assertSame('-', $lastBackup->lastRun);
    }

    public function testLastBackupHasTimestampWhenAutoBackupEnabled(): void
    {
        $this->settings->method('getBool')
            ->with('auto_backup')
            ->willReturn(true);

        $this->settings->method('getString')->willReturn((string) time());

        $lastBackup = $this->service->getTasks()->first(
            fn (TaskData $t) => $t->name === AdminTask::LastBackup->label()
        );

        $this->assertInstanceOf(TaskData::class, $lastBackup);
        $this->assertNotSame('-', $lastBackup->nextRun);
        $this->assertNotSame('-', $lastBackup->lastRun);
    }

    public function testStatLastUpdateIsAlwaysScheduled(): void
    {
        $this->settings->method('getBool')->willReturn(false);
        $this->settings->method('getString')->willReturn((string) time());

        $task = $this->service->getTasks()->first(
            fn (TaskData $t) => $t->name === AdminTask::StatLastUpdate->label()
        );

        $this->assertInstanceOf(TaskData::class, $task);
        $this->assertNotSame('-', $task->nextRun);
    }
}
