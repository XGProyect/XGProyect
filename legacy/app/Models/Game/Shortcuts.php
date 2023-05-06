<?php

namespace Xgp\App\Models\Game;

use Xgp\App\Core\Model;

class Shortcuts extends Model
{
    public function updateShortcuts(int $userId, string $shortcuts): void
    {
        $this->db->query(
            'UPDATE `' . USERS . "` u SET
                u.`user_fleet_shortcuts` = '" . $shortcuts . "'
            WHERE u.`user_id` = '" . $userId . "'"
        );
    }
}
