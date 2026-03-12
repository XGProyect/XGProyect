<?php

declare(strict_types=1);

namespace Tests\Unit\App\Services\Admin;

use App\Services\Admin\LanguagesService;
use Illuminate\Filesystem\Filesystem;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class LanguagesServiceTest extends TestCase
{
    public function testLoadTranslationsReturnsFlatKeyValuePairs(): void
    {
        $service = new LanguagesService(new Filesystem());

        $result = $service->loadTranslations('en/admin/login.php');

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);

        foreach ($result as $pair) {
            $this->assertArrayHasKey('key', $pair);
            $this->assertArrayHasKey('value', $pair);
        }
    }

    public function testLoadTranslationsFlattensNestedArraysWithDotNotation(): void
    {
        $service = new LanguagesService(new Filesystem());

        $result = $service->loadTranslations('en/admin/global.php');

        $this->assertIsArray($result);

        $keys = array_column($result, 'key');
        $nestedKeys = array_filter($keys, fn (string $key) => str_contains($key, '.'));

        $this->assertNotEmpty($nestedKeys, 'Expected at least one dot-notation key from a nested array');
    }

    /**
     * @return array<string, array{string}>
     */
    public static function langFileProvider(): array
    {
        return [
            'login' => ['en/admin/login.php'],
            'repair' => ['en/admin/repair.php'],
        ];
    }

    #[DataProvider('langFileProvider')]
    public function testLoadTranslationsKeysAreNonEmptyStrings(string $path): void
    {
        $service = new LanguagesService(new Filesystem());

        $result = $service->loadTranslations($path);

        $this->assertNotEmpty($result);
        $this->assertIsArray($result);

        foreach ($result as $pair) {
            $this->assertNotEmpty($pair['key']);
        }
    }

    public function testGetGroupedFilesReturnsNonEmptyArray(): void
    {
        $this->assertNotEmpty((new LanguagesService(new Filesystem()))->getGroupedFiles());
    }

    public function testGetGroupedFilesHasExpectedNestedStructure(): void
    {
        foreach ((new LanguagesService(new Filesystem()))->getGroupedFiles() as $files) {
            foreach ($files as $filename => $locales) {
                $this->assertStringEndsWith('.php', $filename);
                $this->assertNotEmpty($locales);
            }
        }
    }

    public function testGetGroupedFilesContainsEnLocale(): void
    {
        $allLocales = [];

        foreach ((new LanguagesService(new Filesystem()))->getGroupedFiles() as $files) {
            foreach ($files as $locales) {
                $allLocales = array_merge($allLocales, array_keys($locales));
            }
        }

        $this->assertContains('en', array_unique($allLocales));
    }

    public function testSaveTranslationsIsIdempotent(): void
    {
        $service = new LanguagesService(new Filesystem());
        $source = 'en/admin/repair.php';

        $original = $service->loadTranslations($source);
        $this->assertNotNull($original);
        $this->assertNotEmpty($original);

        // Save and re-load — keys must be preserved in order
        $service->saveTranslations($source, $original);
        $reloaded = $service->loadTranslations($source);
        $this->assertNotNull($reloaded);

        $this->assertSame(
            array_column($original, 'key'),
            array_column($reloaded, 'key'),
        );

        $this->assertSame(
            array_column($original, 'value'),
            array_column($reloaded, 'value'),
        );
    }
}
