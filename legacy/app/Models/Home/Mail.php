<?php

declare(strict_types=1);

namespace Xgp\App\Models\Home;

use Xgp\App\Core\Model;
use Xgp\App\Libraries\Functions;

class Mail extends Model
{
    public function getEmailUsername(string $email): ?string
    {
        return $this->db->queryFetch(
            "SELECT
                `user_name`
            FROM `" . USERS . "`
            WHERE `user_email` = '" . $this->db->escapeValue($email) . "'
            LIMIT 1;"
        )['user_name'];
    }

    public function setUserNewPassword(string $email, string $new_password): void
    {
        $this->db->query(
            "UPDATE `" . USERS . "` SET
                `user_password` = '" . Functions::hash($new_password) . "'
            WHERE `user_email` = '" . $this->db->escapeValue($email) . "'
            LIMIT 1;"
        );
    }
}
