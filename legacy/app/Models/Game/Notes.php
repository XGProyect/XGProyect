<?php

namespace Xgp\App\Models\Game;

use Xgp\App\Core\Model;

class Notes extends Model
{
    public function getAllNotesByUserId(int $user_id): array
    {
        return $this->db->queryFetchAll(
            'SELECT
                n.*
            FROM `' . NOTES . "` n
            WHERE n.`note_owner` = '" . $user_id . "'
            ORDER BY n.`note_time` DESC;"
        ) ?? [];
    }

    public function getNoteById(int $user_id, int $note_id): array
    {
        return $this->db->queryFetch(
            'SELECT
                n.*
            FROM `' . NOTES . "` n
            WHERE n.`note_id` = '" . $note_id . "'
                AND n.`note_owner` = '" . $user_id . "';"
        ) ?? [];
    }

    public function createNewNote(array $note_data): void
    {
        $sql = [];

        foreach ($note_data as $field => $value) {
            $sql[] = '`' . $field . "` = '" . $value . "'";
        }

        $this->db->query(
            'INSERT INTO `' . NOTES . '` SET '
            . join(', ', $sql)
        );
    }

    public function updateNoteById(int $user_id, int $note_id, array $note_data): void
    {
        $sql = [];

        foreach ($note_data as $field => $value) {
            $sql[] = 'n.`' . $field . "` = '" . $value . "'";
        }

        $this->db->query(
            'UPDATE `' . NOTES . '` n SET '
            . join(', ', $sql) .
            "WHERE n.`note_owner` = '" . $user_id . "'
                AND n.`note_id` = '" . $note_id . "';"
        );
    }

    public function deleteNoteById(int $user_id, string $notes_ids): void
    {
        $this->db->query(
            'DELETE FROM `' . NOTES . "`
            WHERE `note_owner` = '" . $user_id . "'
                AND `note_id` IN (" . $notes_ids . ');'
        );
    }
}
