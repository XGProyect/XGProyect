<?php

namespace App\Providers;

use App\Models\Option;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class SettingsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        try {
            if (DB::connection()->getPdo() && Schema::hasTable('options')) {
                config()->set('settings', Option::pluck('option_value', 'option_name')->all());
            }
        } catch (\Exception $e) {
            // do nothing
        }
    }
}
