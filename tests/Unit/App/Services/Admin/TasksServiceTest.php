<?php

declare(strict_types=1);

namespace Tests\Unit\App\Services\Admin;

use App\Data\Admin\TaskData;
use App\Enums\Admin\AdminTask;
use App\Services\Admin\TasksService;
use App\Services\SettingsService;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionClass;
use Tests\TestCase;
use Xgp\App\Core\Options;

class TasksServiceTest extends TestCase
{
    private TasksService $service;
    private SettingsService & MockObject $settings;

    protected function setUp(): void
    {
        parent::setUp();

        // Pre-load the legacy Options singleton so TimingLibrary doesn't hit the DB
        $this->bootOptionsWithoutDb(['date_format_extended' => 'Y-m-d H:i:s']);

        $this->settings = $this->createMock(SettingsService::class);
        $this->service = new TasksService($this->settings);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // Reset the Options singleton so other tests are not affected
        $ref = new ReflectionClass(Options::class);
        $prop = $ref->getProperty('instance');
        $prop->setValue(null, null);
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

        /** @var TaskData $lastBackup */
        $lastBackup = $this->service->getTasks()->first(
            fn (TaskData $t) => $t->name === AdminTask::LastBackup->label()
        );

        $this->assertNotNull($lastBackup);
        $this->assertSame('-', $lastBackup->nextRun);
        $this->assertSame('-', $lastBackup->lastRun);
    }

    public function testLastBackupHasTimestampWhenAutoBackupEnabled(): void
    {
        $this->settings->method('getBool')
            ->with('auto_backup')
            ->willReturn(true);

        $this->settings->method('getString')->willReturn((string) time());

        /** @var TaskData $lastBackup */
        $lastBackup = $this->service->getTasks()->first(
            fn (TaskData $t) => $t->name === AdminTask::LastBackup->label()
        );

        $this->assertNotNull($lastBackup);
        $this->assertNotSame('-', $lastBackup->nextRun);
        $this->assertNotSame('-', $lastBackup->lastRun);
    }

    public function testStatLastUpdateIsAlwaysScheduled(): void
    {
        $this->settings->method('getBool')->willReturn(false);
        $this->settings->method('getString')->willReturn((string) time());

        /** @var TaskData $task */
        $task = $this->service->getTasks()->first(
            fn (TaskData $t) => $t->name === AdminTask::StatLastUpdate->label()
        );

        $this->assertNotNull($task);
        $this->assertNotSame('-', $task->nextRun);
    }

    /**
     * Pre-load the Options singleton with given values, bypassing the DB.
     *
     * @param array<string, string> $values
     */
    private function bootOptionsWithoutDb(array $values): void
    {
        $ref = new ReflectionClass(Options::class);

        $stub = $ref->newInstanceWithoutConstructor();

        $optionsProp = $ref->getProperty('options');
        $optionsProp->setValue($stub, $values);

        $initializedProp = $ref->getProperty('initialized');
        $initializedProp->setValue($stub, true);

        $instanceProp = $ref->getProperty('instance');
        $instanceProp->setValue(null, $stub);
    }
}
