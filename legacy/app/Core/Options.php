<?php

namespace Xgp\App\Core;

use App\Models\Options as ModelsOptions;
use Illuminate\Support\Facades\DB;

class Options
{
    private static $instance = null;

    public static function getInstance(): Options
    {
        if (self::$instance == null) {
            self::$instance = new Options();
        }

        return self::$instance;
    }

    public function getOptions(?string $option)
    {
        if (empty($option)) {
            return ModelsOptions::all()->toArray();
        } else {
            return ModelsOptions::where('option_name', $option)->firstOrFail()->option_value;
        }
    }

    public function writeOptions(string $option, string $value = ''): bool
    {
        if ($option != '') {
            DB::table('options')
                ->updateOrInsert(
                    ['option_name' => $option],
                    ['option_value' => $value]
                );

            return true;
        }

        return false;
    }

    public function insertOption(string $option, string $value = ''): bool
    {
        return $this->writeOptions($option, $value);
    }

    public function deleteOption(string $option): bool
    {
        if ($option != '') {
            ModelsOptions::where('option_name', $option)->delete();

            return true;
        }

        return false;
    }
}
