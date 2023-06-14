<?php

namespace Xgp\App\Models\Adm;

use Xgp\App\Core\Model;

class Login extends Model
{
    public function getLoginData(string $userEmail): array
    {
        $result = $this->db->queryFetch(
            'SELECT
                `id`,
                `name`,
                `password`
            FROM `' . USERS . "`
            WHERE `email` = '" . $this->db->escapeValue($userEmail) . "'
                AND `authlevel` >= '1'
            LIMIT 1"
        );

        if ($result) {
            return $result;
        }

        return [];
    }
}
