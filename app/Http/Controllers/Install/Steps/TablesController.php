<?php

declare(strict_types=1);

namespace App\Http\Controllers\Install\Steps;

use Exception;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class TablesController extends BaseController
{
    public function __invoke(Request $request): View | Factory
    {
        $route = $request->route();

        session(['last_step' => $route !== null ? $route->getName() : null]);

        return view(
            'install.steps.tables',
            [
                'results' => session()->has('db_installed') ? [] : $this->installSteps(),
                'installed' => session()->has('db_installed'),
            ]
        );
    }

    /** @return array<string, string> */
    private function installSteps(): array
    {
        try {
            // Bind the connection that was validated during the database step so the
            // migrations always run against the same server, regardless of whether the
            // freshly written config file has been (re)loaded or the config is cached.
            $this->bindValidatedConnection();

            /** @var array<string, string> $results */
            $results = [];
            $commands = [
                'wipe' => ['db:wipe', ['--force' => true]],
                'clear_cache' => ['cache:clear', []],
                'prepare_config' => ['config:clear', []],
                'install' => ['migrate:install', []],
                'create' => ['migrate', ['--path' => 'database/migrations/install', '--force' => true]],
                'insert_changelog' => ['db:seed', ['--class' => 'ChangelogTableSeeder', '--force' => true]],
                'insert_languages' => ['db:seed', ['--class' => 'LanguagesTableSeeder', '--force' => true]],
                'insert_options' => ['db:seed', ['--class' => 'OptionsTableSeeder', '--force' => true]],
            ];

            foreach ($commands as $key => $command) {
                $exitCode = Artisan::call($command[0], $command[1]);
                $results[$key] = nl2br(Artisan::output());

                // config:clear wipes the cached config and, with it, the runtime
                // connection we bound above; rebind it before continuing.
                if ($command[0] === 'config:clear') {
                    $this->bindValidatedConnection();
                }

                // Artisan commands report failures through the exit code without
                // throwing, so an unchecked loop would mark a broken install as a
                // success. Abort and surface the real output instead.
                if ($exitCode !== 0) {
                    session()->flash('danger', __('install/install.error_install'));

                    return $results;
                }
            }

            session(['db_installed' => true]);
            session()->flash('success', __('install/install.success_install'));

            return $results;
        } catch (Exception $e) {
            session()->flash('danger', __('install/install.error_install'));
        }

        return [];
    }

    /**
     * Apply the connection validated during the database step to the runtime
     * configuration and reset any already-resolved connection.
     */
    private function bindValidatedConnection(): void
    {
        /** @var array<string, scalar|null>|null $config */
        $config = session('db_config');

        if (!is_array($config)) {
            return;
        }

        $default = (string) config('database.default');

        config([
            "database.connections.{$default}" => array_merge(
                (array) config("database.connections.{$default}"),
                [
                    'driver' => $config['driver'] ?? 'mysql',
                    'host' => $config['host'] ?? null,
                    'port' => $config['port'] ?? 3306,
                    'database' => $config['database'] ?? null,
                    'username' => $config['username'] ?? null,
                    'password' => $config['password'] ?? '',
                    'prefix' => $config['prefix'] ?? '',
                ]
            ),
        ]);

        DB::purge($default);
    }
}
