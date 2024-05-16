<?php

declare(strict_types=1);

namespace Xgp\App\Models\Adm;

use Xgp\App\Core\Model;

/**
 * @deprecated v4.0.0 use laravel instead
 */
class Repair extends Model
{
    /**
     * Get all server users
     *
     * @return array
     */
    public function getAllTables(): array
    {
        return $this->db->queryFetchAll(
            "SELECT
                `table_name`,
                `data_length`,
                `index_length`,
                `data_free`
            FROM information_schema.TABLES
            WHERE table_schema = '" . config('DB_DATABASE') . "';"
        );
    }

    /**
     * Check a table
     *
     * @param string $table
     *
     * @return void
     */
    public function checkTable(string $table): void
    {
        $this->db->query('CHECK TABLE ' . $table);
    }

    /**
     * Optimize a table
     *
     * @param string $table
     *
     * @return void
     */
    public function optimizeTable(string $table): void
    {
        $this->db->query('OPTIMIZE TABLE ' . $table);
    }

    /**
     * Repair a table
     *
     * @param string $table
     *
     * @return void
     */
    public function repairTable(string $table): void
    {
        $this->db->query('REPAIR TABLE ' . $table);
    }
}
