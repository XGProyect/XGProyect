<?php

declare(strict_types=1);

namespace Xgp\App\Core;

use App\Models\Options as ModelsOptions;
use Illuminate\Support\Facades\DB;

class Options
{
    private static $instance = null;
    private bool $initialized = false;
    private array $options = [];

    public function __construct()
    {
        $this->load();
    }

    public static function getInstance(): Options
    {
        if (self::$instance == null) {
            self::$instance = new Options();
        }

        return self::$instance;
    }

    public function get(?string $option = null): array | string | null
    {
        if (empty($option)) {
            return $this->options;
        } else {
            return $this->options[$option] ?? null;
        }
    }

    public function write(string $option, mixed $value = ''): bool
    {
        if ($option != '') {
            DB::table('options')
                ->updateOrInsert(
                    ['name' => $option],
                    ['value' => $value]
                );

            return true;
        }

        return false;
    }

    private function load(): void
    {
        if (!$this->initialized) {
            $options = ModelsOptions::all(['name', 'value'])->toArray();

            foreach ($options as $option) {
                $this->options[$option['name']] = $option['value'];
            }

            $this->initialized = true;
        }
    }
}
