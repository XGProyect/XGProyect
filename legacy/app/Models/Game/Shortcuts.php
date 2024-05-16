<?php

declare(strict_types=1);

namespace Xgp\App\Models\Game;

use Xgp\App\Core\Model;

/**
 * @deprecated v4.0.0 use laravel instead
 */
class Shortcuts extends Model
{
    public function updateShortcuts(int $userId, string $shortcuts): void
    {
        $this->db->query(
            'UPDATE `' . USERS . "` u SET
                u.`fleet_shortcuts` = '" . $shortcuts . "'
            WHERE u.`id` = '" . $userId . "'"
        );
    }
}
