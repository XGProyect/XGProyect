<?php

namespace Xgp\App\Models\Install;

use Xgp\App\Core\Database;
use Xgp\App\Core\Model;

class Installation extends Model
{
    public function getListOfTables($dbName): array
    {
        return $this->db->queryFetchAll(
            'SHOW TABLES FROM ' . $dbName
        );
    }

    public function getAdmin(): array
    {
        return $this->db->queryFetch(
            'SELECT COUNT(`id`) as count FROM ' . USERS . "
                WHERE `id` = '1' OR `authlevel` = '3';"
        );
    }

    public function tryConnection(string $host, string $port, string $user, string $password): bool
    {
        return $this->db->tryConnection($host, $port, $user, $password);
    }

    /**
     * Check if the database name exists
     *
     * @param string $db_name DB Name
     *
     * @return Database
     */
    public function tryDatabase($db_name)
    {
        return $this->db->tryDatabase($db_name);
    }

    /**
     * Set for windows sql mode to MYSQL40
     *
     * @return void
     */
    public function setWindowsSqlMode()
    {
        // Store the current sql_mode
        $this->db->query('set @orig_mode = @@global.sql_mode');

        // Set sql_mode to one that won't trigger errors...
        $this->db->query('set @@global.sql_mode = "MYSQL40"');
    }

    /**
     * Run a simple insert query
     *
     * @param string $query Query
     *
     * @return int
     */
    public function runSimpleQuery($query)
    {
        return $this->db->query($query);
    }

    /**
     * Set for windows sql mode to normal
     *
     * @return void
     */
    public function setNormalMode()
    {
        // Change it back to original sql_mode
        $this->db->query('set @@global.sql_mode = @orig_mode');
    }

    /**
     * Escape a value
     *
     * @param string $var
     * @return string
     */
    public function escapeValue($var): string
    {
        return $this->db->escapeValue($var);
    }
}
