<?php

declare(strict_types=1);

namespace App\Services\Admin;

use App\Models\Changelog;
use App\Models\Languages;
use Illuminate\Database\Eloquent\Collection;

class ChangelogService
{
    public function __construct(
        private readonly Changelog $changelog,
        private readonly Languages $languages,
    ) {
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getEntries(): array
    {
        /** @var Collection<int, Changelog> $entries */
        $entries = $this->changelog
            ->newQuery()
            ->with('language')
            ->orderByDesc('changelog_date')
            ->get();

        $groups = [];

        foreach ($entries as $entry) {
            $key = $entry->changelog_date->toDateString() . '|' . $entry->changelog_version;

            if (!isset($groups[$key])) {
                $groups[$key] = [
                    'changelog_date' => $entry->changelog_date->toDateString(),
                    'changelog_version' => $entry->changelog_version,
                    'translations' => [],
                ];
            }

            $groups[$key]['translations'][] = [
                'changelog_id' => $entry->changelog_id,
                'changelog_language' => $entry->language->name,
                'changelog_lang_code' => strtolower($entry->language->code),
            ];
        }

        return array_values($groups);
    }

    public function create(int $langId, string $version, string $date, string $description): void
    {
        $entry = $this->changelog->newInstance([
            'changelog_lang_id' => $langId,
            'changelog_version' => $version,
            'changelog_date' => $date,
            'changelog_description' => $description,
        ]);

        $entry->save();
    }

    public function update(Changelog $changelog, int $langId, string $version, string $date, string $description): void
    {
        $changelog->update([
            'changelog_lang_id' => $langId,
            'changelog_version' => $version,
            'changelog_date' => $date,
            'changelog_description' => $description,
        ]);
    }

    public function delete(Changelog $changelog): void
    {
        $changelog->delete();
    }

    /**
     * @return array<string, mixed>
     */
    public function getFormData(string $action, ?Changelog $entry = null): array
    {
        $entryDate = $entry !== null
            ? $entry->changelog_date->toDateString()
            : now()->toDateString();

        return [
            'action' => $action,
            'changelog_id' => $entry !== null ? $entry->changelog_id : 0,
            'changelog_date' => $entryDate,
            'changelog_version' => $entry !== null ? $entry->changelog_version : '',
            'languages' => $this->getLanguageOptions($entry !== null ? $entry->changelog_lang_id : 0),
            'changelog_description' => $entry !== null ? $entry->changelog_description : '',
        ];
    }

    /**
     * @return array<int, array<string, int|string>>
     */
    private function getLanguageOptions(int $selectedId): array
    {
        /** @var Collection<int, Languages> $languages */
        $languages = $this->languages
            ->newQuery()
            ->orderBy('name')
            ->get();

        return $languages
            ->map(fn (Languages $language) => [
                'id' => $language->id,
                'name' => $language->name,
                'code' => $language->code,
                'selected' => ($selectedId === $language->id ? 'selected' : ''),
            ])
            ->all();
    }
}
