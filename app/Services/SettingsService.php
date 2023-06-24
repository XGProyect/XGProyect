<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Options;
use Exception;
use Illuminate\Support\Facades\DB;
use Throwable;

class SettingsService
{
    private array $settings = [];

    public function all(): array
    {
        $this->init();

        return $this->settings;
    }

    public function one(string $setting): Options
    {
        $this->init();

        try {
            return $this->settings[$setting];
        } catch (Throwable $e) {
            throw new Exception('The request setting: "' . $setting . '" is not defined!', $e->getCode());
        }
    }

    public function getArray(string $setting): array
    {
        return json_decode($this->one($setting)->value, null, 512, JSON_THROW_ON_ERROR);
    }

    public function getBool(string $setting): bool
    {
        return (bool) $this->one($setting)->value;
    }

    public function getFloat(string $setting): float
    {
        return (float) $this->one($setting)->value;
    }

    public function getInt(string $setting): int
    {
        return (int) $this->one($setting)->value;
    }

    public function getString(string $setting): string
    {
        return (string) $this->one($setting)->value;
    }

    public function write(string $key, mixed $value): bool
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

    private function init(): void
    {
        if (!$this->settings) {
            $settings = Options::all(['name', 'value']);

            foreach ($settings as $item) {
                $this->settings[$item->name] = $item;
            }
        }
    }
}
