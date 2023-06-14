<?php

namespace Xgp\App\Models\Adm;

use Xgp\App\Core\Model;

class Announcement extends Model
{
    public function getAllPlayers(): array
    {
        return $this->db->queryFetchAll(
            'SELECT
                `id`,
                `name`,
                `email`
            FROM `' . USERS . '`;'
        );
    }
}
