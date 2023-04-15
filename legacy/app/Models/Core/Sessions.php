<?php

declare(strict_types=1);

namespace Xgp\App\Models\Core;

use Xgp\App\Core\Model;

class Sessions extends Model
{
    public function openConnection(): bool
    {
        return $this->db->testConnection();
    }

    public function closeConnection(): bool
    {
        return $this->db->closeConnection();
    }

    public function getSessionDataById(string $sid): string
    {
        $sessions = $this->db->query(
            "SELECT
                `payload`
            FROM `" . SESSIONS . "`
            WHERE `id` = '" . $this->db->escapeValue($sid) . "'
            LIMIT 1"
        );

        if ($this->db->numRows($sessions) == 1) {
            $fields = $this->db->fetchAssoc($sessions);

            return $fields['payload'];
        } else {
            return '';
        }
    }

    public function insertNewSessionData(string $sid, string $data): bool
    {
        $userId =  null;

        if (!empty($_SESSION['user_id'])) {
            $userId = $this->db->escapeValue($_SESSION['user_id']);
        }

        $this->db->query(
            "REPLACE INTO `" . SESSIONS . "` (
                `id`,
                `user_id`,
                `ip_address`,
                `user_agent`,
                `payload`,
                `last_activity`
            )
            VALUES (
                '" . $this->db->escapeValue($sid) . "',
                " . (empty($userId) ? 'NULL,' : "'" . $userId . "',") .  "
                '" . $this->db->escapeValue($_SERVER['REMOTE_ADDR']) . "',
                '" . $this->db->escapeValue($_SERVER['HTTP_USER_AGENT']) . "',
                '" . $this->db->escapeValue($data) . "',
                '" . time() . "'
            )"
        );

        return ($this->db->affectedRows() > 0);
    }

    public function deleteSessionDataById(string $sid): bool
    {
        $this->db->query(
            "DELETE FROM `" . SESSIONS . "`
            WHERE `id` = '" . $this->db->escapeValue($sid) . "'"
        );

        return ($this->db->affectedRows() > 0);
    }

    public function cleanSessionData(int $expire): bool
    {
        $this->db->query(
            "DELETE FROM `" . SESSIONS . "`
            WHERE DATE_ADD(`last_activity`, INTERVAL " . $expire . " SECOND) < NOW()"
        );

        return ($this->db->affectedRows() > 0);
    }
}
