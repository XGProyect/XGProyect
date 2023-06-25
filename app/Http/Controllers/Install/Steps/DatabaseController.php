<?php

declare(strict_types=1);

namespace App\Http\Controllers\Install\Steps;

use App\Http\Requests\Install\DatabaseRequest;
use Exception;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Connectors\ConnectionFactory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Xgp\App\Helpers\StringsHelper;

class DatabaseController extends BaseController
{
    public function __invoke(Request $request): View|Factory
    {
        // @phpstan-ignore-next-line
        session(['last_step' => $request->route()->getName()]);

        return view(
            'install.steps.database',
            [
                'hideForm' => session()->has('connect_success') && session('connect_success', false),
            ]
        );
    }

    public function doCheck(DatabaseRequest $request): RedirectResponse
    {
        try {
            // Create a new connection factory instance
            $factory = new ConnectionFactory(app());

            // Create a new connection using the configuration
            $connection = $factory->make($request->all());

            // try the connection
            $connection->getPdo();

            if (!$this->writeConfigFile($request->all())) {
                throw new Exception();
            }
        } catch (Exception $e) {
            return back()
                ->withInput()
                ->with('danger', __('install/install.db_connect_fail'));
        }

        session()->put('connect_success', true);

        return back()
            ->with('success', __('install/install.db_connect_success'));
    }

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

        $connectionData = "<?php\n";
        $connectionData .= "\n";
        $connectionData .= "putenv('DB_HOST=" . $config['host'] . "');\n";
        $connectionData .= "putenv('DB_PORT=" . ($config['port'] ?? 3306) . "');\n";
        $connectionData .= "putenv('DB_USERNAME=" . $config['username'] . "');\n";
        $connectionData .= "putenv('DB_PASSWORD=" . ($config['password'] ?? '') . "');\n";
        $connectionData .= "putenv('DB_DATABASE=" . $config['database'] . "');\n";
        $connectionData .= "putenv('DB_PREFIX=" . ($config['prefix'] ?? '') . "');\n";
        $connectionData .= "putenv('SECRETWORD=xgp-" . StringsHelper::randomString(16) . "');\n";
        $connectionData .= "\n";
        $connectionData .= "config([\n";
        $connectionData .= "    'DB_HOST' => '" . $config['host'] . "',\n";
        $connectionData .= "    'DB_PORT' => '" . ($config['port'] ?? 3306) . "',\n";
        $connectionData .= "    'DB_USERNAME' => '" . $config['username'] . "',\n";
        $connectionData .= "    'DB_PASSWORD' => '" . ($config['password'] ?? '') . "',\n";
        $connectionData .= "    'DB_DATABASE' => '" . $config['database'] . "',\n";
        $connectionData .= "    'DB_PREFIX' => '" . ($config['prefix'] ?? '') . "',\n";
        $connectionData .= "    'SECRETWORD' => 'xgp-" . StringsHelper::randomString(16) . "',\n";
        $connectionData .= ']);';
        $connectionData .= "\n";

        $disk->put($file, $connectionData);

        Artisan::call('cache:clear');
        Artisan::call('config:clear');

        return true;
    }
}
