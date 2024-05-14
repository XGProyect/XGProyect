<?php

namespace Xgp\App\Models\Game;

use Xgp\App\Core\Model;

/**
 * @deprecated v4.0.0 use laravel instead
 */
class Officier extends Model
{
    /**
     * Set premium access to the current user
     *
     * @param integer $userId
     * @param integer $price
     * @param string $officier
     * @param integer $time_to_add
     * @return void
     */
    public function setPremium(int $userId, int $price, string $officier, int $time_to_add): void
    {
        if ($userId > 0) {
            $this->db->query(
                'UPDATE `' . PREMIUM . "` SET
                    `premium_dark_matter` = `premium_dark_matter` - '" . $price . "',
                    `" . $officier . "` = '" . $time_to_add . "'
                WHERE `premium_user_id` = '" . $userId . "';"
            );
        }
    }
}
