<?php

declare(strict_types=1);

namespace Xgp\App\Models\Game;

use Illuminate\Support\Facades\DB;
use Xgp\App\Core\Concerns\PreparesLegacySql;

/**
 * @deprecated v4.0.0 use laravel instead
 *
 * @SuppressWarnings("PHPMD.StaticAccess")
 */
class Notes
{
    use PreparesLegacySql;

    public function getAllNotesByUserId(int $userId): array
    {
        return array_map(
            fn ($row) => (array) $row,
            DB::select(
                $this->prepareSql(
                    'SELECT n.*
                    FROM `' . NOTES . "` n
                    WHERE n.`note_owner` = '" . $userId . "'
                    ORDER BY n.`note_time` DESC;"
                )
            )
        );
    }

    public function getNoteById(int $userId, int $note_id): array
    {
        $row = DB::selectOne(
            $this->prepareSql(
                'SELECT n.*
                FROM `' . NOTES . "` n
                WHERE n.`note_id` = '" . $note_id . "'
                    AND n.`note_owner` = '" . $userId . "';"
            )
        );

        return $row !== null ? (array) $row : [];
    }

    public function createNewNote(array $note_data): void
    {
        $sql = [];

        foreach ($note_data as $field => $value) {
            $sql[] = '`' . $field . "` = '" . $value . "'";
        }

        DB::statement(
            $this->prepareSql('INSERT INTO `' . NOTES . '` SET ' . join(', ', $sql))
        );
    }

    public function updateNoteById(int $userId, int $note_id, array $note_data): void
    {
        $sql = [];

        foreach ($note_data as $field => $value) {
            $sql[] = 'n.`' . $field . "` = '" . $value . "'";
        }

        DB::statement(
            $this->prepareSql(
                'UPDATE `' . NOTES . '` n SET '
                . join(', ', $sql)
                . "WHERE n.`note_owner` = '" . $userId . "'
                    AND n.`note_id` = '" . $note_id . "';"
            )
        );
    }

    public function deleteNoteById(int $userId, string $notes_ids): void
    {
        DB::statement(
            $this->prepareSql(
                'DELETE FROM `' . NOTES . "`
                WHERE `note_owner` = '" . $userId . "'
                    AND `note_id` IN (" . $notes_ids . ');'
            )
        );
    }
}
