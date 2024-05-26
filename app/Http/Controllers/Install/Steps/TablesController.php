<?php

declare(strict_types=1);

namespace App\Http\Controllers\Install\Steps;

use Exception;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Artisan;

class TablesController extends BaseController
{
    public function __invoke(Request $request): View | Factory
    {
        session(['last_step' => $request->route()->getName()]);

        return view(
            'install.steps.tables',
            [
                'results' => session()->has('db_installed') ? [] : $this->installSteps(),
                'installed' => session()->has('db_installed'),
            ]
        );
    }

    private function installSteps(): array
    {
        try {
            $commands = [
                'wipe' => ['db:wipe', []],
                'clear_cache' => ['cache:clear', []],
                'prepare_config' => ['config:clear', []],
                'install' => ['migrate:install', []],
                'create' => ['migrate', ['--path' => 'database/migrations/install', '--force' => true]],
                'insert_changelog' => ['db:seed', ['--class' => 'ChangelogTableSeeder', '--force' => true]],
                'insert_languages' => ['db:seed', ['--class' => 'LanguagesTableSeeder', '--force' => true]],
                'insert_options' => ['db:seed', ['--class' => 'OptionsTableSeeder', '--force' => true]],
            ];

            foreach ($commands as $key => $command) {
                Artisan::call($command[0], $command[1]);

                $results[$key] = nl2br(Artisan::output());
            }

            session(['db_installed' => true]);
            session()->flash('success', __('install/install.success_install'));

            return $results;
        } catch (Exception $e) {
            session()->flash('danger', __('install/install.error_install'));
        }

        return [];
    }
}
