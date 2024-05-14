<?php

namespace Xgp\App\Models\Game;

use Xgp\App\Core\Model;

/**
 * @deprecated v4.0.0 use laravel instead
 */
class Changelog extends Model
{
    public function getAllChangelogEntries(): null|array|bool
    {
        return $this->db->queryFetchAll(
            'SELECT
                c.`changelog_version`,
                c.`changelog_date`,
                c.`changelog_description`
            FROM
                `' . CHANGELOG . '` c
            LEFT JOIN `' . LANGUAGES . '` l
                ON l.`id` = c.`changelog_lang_id`
            WHERE l.`code` = (
                SELECT o.`value` FROM `' . OPTIONS . "` o WHERE `name` = 'lang'
            )
            ORDER BY c.`changelog_date` DESC"
        );
    }
}
