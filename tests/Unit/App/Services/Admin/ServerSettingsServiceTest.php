<?php

declare(strict_types=1);

namespace Tests\Unit\App\Services\Admin;

use App\Services\Admin\ServerSettingsService;
use App\Services\SettingsService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings("PHPMD.TooManyPublicMethods")
 */
class ServerSettingsServiceTest extends TestCase
{
    private ServerSettingsService $service;
    private SettingsService & MockObject $settings;

    protected function setUp(): void
    {
        parent::setUp();
        $this->settings = $this->createMock(SettingsService::class);
        $this->service = new ServerSettingsService($this->settings);
    }

    public function testPercentageOptionsReturnsElevenEntries(): void
    {
        $this->assertCount(11, $this->service->percentageOptions(0));
    }

    public function testPercentageOptionsValuesAreMultiplesOfTen(): void
    {
        $options = $this->service->percentageOptions(0);

        foreach ($options as $i => $option) {
            $this->assertSame($i * 10, $option['value']);
        }
    }

    public function testPercentageOptionsLabelsMatchValues(): void
    {
        $options = $this->service->percentageOptions(0);

        foreach ($options as $option) {
            $this->assertSame($option['value'] . '%', $option['label']);
        }
    }

    public function testPercentageOptionsMarksExactMatchAsSelected(): void
    {
        $options = $this->service->percentageOptions(50);

        $selected = array_values(array_filter($options, fn ($opt) => $opt['selected']));

        $this->assertCount(1, $selected);
        $this->assertSame(50, $selected[0]['value']);
    }

    /**
     * @return array<string, array{int}>
     */
    public static function nonOptionValueProvider(): array
    {
        return [
            'odd number' => [15],
            'negative' => [-10],
            'above max' => [110],
        ];
    }

    #[DataProvider('nonOptionValueProvider')]
    public function testPercentageOptionsNoEntrySelectedForNonOptionValue(int $current): void
    {
        $selected = array_filter($this->service->percentageOptions($current), fn ($opt) => $opt['selected']);

        $this->assertEmpty($selected);
    }

    public function testTimezoneOptionsReturnsNonEmptyArray(): void
    {
        $this->settings->method('getString')->willReturn('UTC');

        $this->assertNotEmpty($this->service->timezoneOptions());
    }

    public function testTimezoneOptionsEachGroupHasRequiredKeys(): void
    {
        $this->settings->method('getString')->willReturn('UTC');

        foreach ($this->service->timezoneOptions() as $group) {
            $this->assertArrayHasKey('group', $group);
            $this->assertArrayHasKey('zones', $group);
        }
    }

    public function testTimezoneOptionsGroupLabelStartsWithGmt(): void
    {
        $this->settings->method('getString')->willReturn('UTC');

        foreach ($this->service->timezoneOptions() as $group) {
            $this->assertMatchesRegularExpression('/^GMT/', $group['group']);
        }
    }

    public function testTimezoneOptionsZoneEntriesHaveRequiredKeys(): void
    {
        $this->settings->method('getString')->willReturn('UTC');

        foreach ($this->service->timezoneOptions() as $group) {
            foreach ($group['zones'] as $zone) {
                $this->assertArrayHasKey('value', $zone);
                $this->assertArrayHasKey('label', $zone);
                $this->assertArrayHasKey('selected', $zone);
            }
        }
    }

    public function testTimezoneOptionsMarksCurrentTimezoneAsSelected(): void
    {
        $this->settings->method('getString')->willReturn('Europe/London');

        $allZones = array_merge(
            ...array_map(fn ($group) => $group['zones'], $this->service->timezoneOptions())
        );

        $selected = array_values(array_filter($allZones, fn ($zone) => $zone['selected']));

        $this->assertNotEmpty($selected);
        $this->assertSame('Europe/London', $selected[0]['value']);
    }
}
