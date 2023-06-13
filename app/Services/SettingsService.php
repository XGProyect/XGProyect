<?php

namespace App\Services;

use App\Models\Options;
use Illuminate\Support\Facades\DB;

class SettingsService
{
    private array $settings = [];

    public function get(?string $setting = null): mixed
    {
        if (!$this->settings) {
            $settings = Options::all(['name', 'value'])->toArray();

            foreach ($settings as $item) {
                $this->settings[$item['name']] = $item['value'];
            }
        }

        return $this->settings[$setting] ?? $this->settings;
    }

    public function write(string $key, string $value): bool
    {
        if (!empty($key)) {
            DB::table('options')
                ->updateOrInsert(
                    ['name' => $key],
                    ['value' => $value]
                );

            return true;
        }

        return false;
    }
}
