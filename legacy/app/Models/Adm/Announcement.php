<?php

namespace Xgp\App\Models\Adm;

use Xgp\App\Core\Model;

class Announcement extends Model
{
    public function getAllPlayers(): array
    {
        return $this->db->queryFetchAll(
            "SELECT
                `user_id`,
                `user_name`,
                `user_email`
            FROM `" . USERS . "`;"
        );
    }
}
