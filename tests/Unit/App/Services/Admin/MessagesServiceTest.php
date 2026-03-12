<?php

declare(strict_types=1);

namespace Tests\Unit\App\Services\Admin;

use App\Services\Admin\MessagesService;
use Tests\TestCase;
use Xgp\App\Core\Enumerators\MessagesEnumerator;

class MessagesServiceTest extends TestCase
{
    private MessagesService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new MessagesService();
    }

    public function testBuildTypeOptionsReturnsOneEntryPerKnownType(): void
    {
        $options = $this->service->buildTypeOptions();

        $this->assertNotEmpty($options);
        $this->assertCount(6, $options); // ESPIO, COMBAT, EXP, ALLY, USER, GENERAL
    }

    public function testBuildTypeOptionsEachEntryHasRequiredKeys(): void
    {
        foreach ($this->service->buildTypeOptions() as $option) {
            $this->assertArrayHasKey('value', $option);
            $this->assertArrayHasKey('name', $option);
            $this->assertIsInt($option['value']);
            $this->assertIsString($option['name']);
        }
    }

    public function testBuildTypeOptionsValuesMatchMessagesEnumeratorConstants(): void
    {
        $values = array_column($this->service->buildTypeOptions(), 'value');

        $this->assertContains(MessagesEnumerator::ESPIO, $values);
        $this->assertContains(MessagesEnumerator::COMBAT, $values);
        $this->assertContains(MessagesEnumerator::EXP, $values);
        $this->assertContains(MessagesEnumerator::ALLY, $values);
        $this->assertContains(MessagesEnumerator::USER, $values);
        $this->assertContains(MessagesEnumerator::GENERAL, $values);
    }

    public function testTypeNameReturnsStringForKnownTypes(): void
    {
        $knownTypes = [
            MessagesEnumerator::ESPIO,
            MessagesEnumerator::COMBAT,
            MessagesEnumerator::EXP,
            MessagesEnumerator::ALLY,
            MessagesEnumerator::USER,
            MessagesEnumerator::GENERAL,
        ];

        foreach ($knownTypes as $type) {
            $name = $this->service->typeName($type);
            $this->assertIsString($name);
            $this->assertNotEmpty($name);
        }
    }

    public function testTypeNameReturnsDashForUnknownType(): void
    {
        $this->assertSame('-', $this->service->typeName(9999));
    }
}
