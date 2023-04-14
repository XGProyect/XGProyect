<?php

namespace Xgp\App\Models\Game;

use Xgp\App\Core\Model;

class Banned extends Model
{
    /**
     * Get banned users
     *
     * @return array
     */
    public function getBannedUsers()
    {
        return $this->db->queryFetchAll(
            "SELECT *
            FROM " . BANNED . "
            ORDER BY `banned_id`;"
        );
    }
}
