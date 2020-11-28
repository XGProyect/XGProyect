<?php

namespace App\Providers;

use App\Models\Option;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // view global variables
        view()->share('baseUrl', url('/'));

        // set global settings
        $this->setSettings();
    }

    /**
     * Check if connection exists and load settings
     *
     * @return void
     */
    private function setSettings(): void
    {
        try {
            if (DB::connection()->getPdo() && Schema::hasTable('options')) {
                config([
                    'settings' => Option::all(['option_name', 'option_value'])
                        ->keyBy('name')
                        ->transform(function ($setting) {
                            return $setting->value; // return only the value
                        })
                        ->toArray(), // make it an array
                ]);
                var_dump(config('settings'));
            }
        } catch (\Exception $e) {
            // do nothing
        }
    }
}
