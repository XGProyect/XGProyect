<?php

declare(strict_types=1);

namespace Xgp\App\Models\Game;

use Illuminate\Support\Facades\DB;
use Xgp\App\Core\Concerns\PreparesLegacySql;

/**
 * @deprecated v4.0.0 use laravel instead
 *
 * @SuppressWarnings("PHPMD.StaticAccess")
 */
class Combatreport
{
    use PreparesLegacySql;

    public function getReportById($report_id): ?array
    {
        $row = DB::selectOne(
            $this->prepareSql('SELECT * FROM `' . REPORTS . '` WHERE `report_rid` = ?'),
            [$report_id]
        );

        return $row !== null ? (array) $row : null;
    }
}
