<?php

declare(strict_types=1);

namespace App\Http\Controllers\Install\Steps;

use App\Http\Requests\Install\DatabaseRequest;
use App\Services\InstallRequirements;
use Exception;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Connectors\ConnectionFactory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use PDO;
use Xgp\App\Helpers\StringsHelper;

class DatabaseController extends BaseController
{
    public function __invoke(Request $request): View | Factory
    {
        $route = $request->route();

        session(['last_step' => $route !== null ? $route->getName() : null]);

        return view(
            'install.steps.database',
            [
                'hideForm' => session()->has('connect_success') && session('connect_success', false),
            ]
        );
    }

    public function doCheck(DatabaseRequest $request): RedirectResponse
    {
        /** @var array<string, scalar|null> $config */
        $config = $request->validated();

        try {
            // Create a new connection factory instance
            $factory = new ConnectionFactory(app());

            // Create a new connection using the configuration
            $connection = $factory->make($config);

            // try the connection
            $pdo = $connection->getPdo();
            /** @var scalar|null $serverAttribute */
            $serverAttribute = $pdo->getAttribute(PDO::ATTR_SERVER_VERSION);
            $serverVersion = (string) $serverAttribute;

            if (!InstallRequirements::isSupportedDatabaseVersion($serverVersion)) {
                return back()
                    ->withInput()
                    ->with('danger', __('install/install.db_version_fail', [
                        'version' => InstallRequirements::MINIMUM_DATABASE_VERSION,
                        'current' => InstallRequirements::extractDatabaseVersion($serverVersion) ?? $serverVersion,
                    ]));
            }

            if (!$this->writeConfigFile($config)) {
                throw new Exception();
            }
        } catch (Exception $e) {
            return back()
                ->withInput()
                ->with('danger', __('install/install.db_connect_fail'));
        }

        session()->put('connect_success', true);
        session()->put('db_config', $config);

        return back()
            ->with('success', __('install/install.db_connect_success'));
    }

    /** @param array<string, scalar|null> $config */
    private function writeConfigFile(array $config): bool
    {
        $file = 'xgp-db-config.php';
        $disk = Storage::build([
            'driver' => 'local',
            'root' => config_path(),
        ]);

        if (
            !$disk->exists($file) ||
            $disk->getVisibility($file) !== 'public'
        ) {
            return false;
        }

        $driver = (string) ($config['driver'] ?? 'mysql');

        // The application reads database settings through config()/env() (config/database.php),
        // never getenv(), so values are written as config() overrides instead of putenv().
        // This file is loaded after config/database.php (natural sort order), so these
        // overrides win over the env() defaults that file resolves at bootstrap and keep the
        // default connection correct for the whole request lifecycle (validation, controllers
        // and the running game) without binding it manually in each entry point.
        $connectionData = "<?php\n";
        $connectionData .= "\n";
        $connectionData .= "config([\n";
        $connectionData .= '    ' . var_export('database.default', true) . ' => ' . var_export($driver, true) . ",\n";
        $connectionData .= '    ' . var_export("database.connections.{$driver}.driver", true) . ' => ' . var_export($driver, true) . ",\n";
        $connectionData .= '    ' . var_export("database.connections.{$driver}.host", true) . ' => ' . var_export((string) $config['host'], true) . ",\n";
        $connectionData .= '    ' . var_export("database.connections.{$driver}.port", true) . ' => ' . var_export((string) ($config['port'] ?? 3306), true) . ",\n";
        $connectionData .= '    ' . var_export("database.connections.{$driver}.database", true) . ' => ' . var_export((string) $config['database'], true) . ",\n";
        $connectionData .= '    ' . var_export("database.connections.{$driver}.username", true) . ' => ' . var_export((string) $config['username'], true) . ",\n";
        $connectionData .= '    ' . var_export("database.connections.{$driver}.password", true) . ' => ' . var_export((string) ($config['password'] ?? ''), true) . ",\n";
        $connectionData .= '    ' . var_export("database.connections.{$driver}.prefix", true) . ' => ' . var_export((string) ($config['prefix'] ?? ''), true) . ",\n";
        $connectionData .= '    ' . var_export('SECRETWORD', true) . ' => ' . var_export('xgp-' . StringsHelper::randomString(16), true) . ",\n";
        $connectionData .= '    ' . var_export('INSTALLED', true) . ' => ' . var_export('false', true) . ",\n";
        $connectionData .= "]);\n";

        $disk->put($file, $connectionData);

        // OPcache may keep serving the previous compiled version of this file to other
        // processes (e.g. php-fpm); config:clear does not touch it, so invalidate it here.
        $this->invalidateOpcache(config_path($file));

        Artisan::call('cache:clear');
        Artisan::call('config:clear');

        return true;
    }

    private function invalidateOpcache(string $path): void
    {
        if (function_exists('opcache_invalidate')) {
            opcache_invalidate($path, true);
        }
    }
}
