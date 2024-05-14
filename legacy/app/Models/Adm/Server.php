<?php

declare(strict_types=1);

namespace Xgp\App\Models\Adm;

use Xgp\App\Core\Model;
use Xgp\App\Core\Options;

/**
 * @deprecated v4.0.0 use laravel instead
 */
class Server extends Model
{
    public function readAllConfigs(): array
    {
        return Options::getInstance()->get();
    }

    public function readConfig(string $config_name): string
    {
        return Options::getInstance()->get($config_name);
    }

    public function updateConfigs(array $configs): void
    {
        foreach ($configs as $config_name => $config_value) {
            Options::getInstance()->write($config_name, $config_value);
        }
    }
}
