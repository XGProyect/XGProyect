<?php

namespace Xgp\App\Models\Game;

use Xgp\App\Core\Model;

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
