<?php

declare(strict_types=1);

namespace Xgp\App\Models\Game;

use Xgp\App\Core\Model;

/**
 * @deprecated v4.0.0 use laravel instead
 */
class Trader extends Model
{
    public function refillStorage(int $dark_matter, string $resource, float $amount, int $userId, int $planet_id): void
    {
        $this->db->query(
            'UPDATE `' . PREMIUM . '` pr, `' . PLANETS . "` p SET
            pr.`premium_dark_matter` = pr.`premium_dark_matter` - '" . $dark_matter . "',
            p.`planet_" . $resource . "` = '" . $amount . "'
            WHERE pr.`premium_user_id` = '" . $userId . "'
                AND p.`planet_id` = '" . $planet_id . "';"
        );
    }
}
