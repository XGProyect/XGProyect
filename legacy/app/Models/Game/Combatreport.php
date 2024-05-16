<?php

declare(strict_types=1);

namespace Xgp\App\Models\Game;

use Xgp\App\Core\Model;

/**
 * @deprecated v4.0.0 use laravel instead
 */
class Combatreport extends Model
{
    /**
     * Get report by its Id
     *
     * @param string $report_id Report ID
     *
     * @return array
     */
    public function getReportById($report_id): ?array
    {
        return $this->db->queryFetch(
            'SELECT
                *
            FROM `' . REPORTS . "`
            WHERE `report_rid` = '" . $this->db->escapeValue($report_id) . "';"
        );
    }
}
