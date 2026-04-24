<?php

declare(strict_types=1);

namespace Xgp\App\Core\Concerns;

use Illuminate\Support\Facades\DB;

trait PreparesLegacySql
{
    private function prepareSql(string $sql): string
    {
        return strtr($sql, ['{xgp_prefix}' => DB::getTablePrefix()]);
    }
}
