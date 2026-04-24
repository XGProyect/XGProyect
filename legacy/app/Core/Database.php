<?php

declare(strict_types=1);

namespace Xgp\App\Core;

use Exception;
use Illuminate\Support\Facades\DB;
use PDO;
use Xgp\App\Core\Concerns\PreparesLegacySql;

/**
 * @deprecated v4.0.0 use Laravel DB facade / Eloquent directly
 */
class Database
{
    use PreparesLegacySql;
    public function closeConnection(): bool
    {
        return true;
    }

    public function query(string $sql = '')
    {
        if ($sql === '') {
            return false;
        }

        return DB::getPdo()->query($this->prepareSql($sql));
    }

    public function queryFetch(string $sql = '')
    {
        if ($sql === '') {
            return false;
        }

        $stmt = DB::getPdo()->query($this->prepareSql($sql));

        return $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : false;
    }

    public function queryFetchAll($sql = '')
    {
        try {
            if ($sql === '') {
                return false;
            }

            $stmt = DB::getPdo()->query($this->prepareSql($sql));

            return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : false;
        } catch (Exception $e) {
            return false;
        }
    }

    public function queryMulty($sql = '')
    {
        try {
            if ($sql === '') {
                return false;
            }

            DB::unprepared($this->prepareSql($sql));

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function escapeValue($value)
    {
        $quoted = DB::getPdo()->quote((string) $value);

        return substr($quoted, 1, -1);
    }

    public function fetchArray($result_set)
    {
        return $result_set->fetch(PDO::FETCH_ASSOC);
    }

    public function fetchAll($result_set)
    {
        return $result_set->fetchAll(PDO::FETCH_ASSOC);
    }

    public function fetchAssoc($result_set)
    {
        return $result_set->fetch(PDO::FETCH_ASSOC);
    }

    public function fetchRow($result_set)
    {
        return $result_set->fetch(PDO::FETCH_NUM);
    }

    public function numFields($result_set)
    {
        return $result_set->columnCount();
    }

    public function insertId()
    {
        return DB::getPdo()->lastInsertId();
    }

    public function beginTransaction(): void
    {
        DB::beginTransaction();
    }

    public function commitTransaction(): void
    {
        DB::commit();
    }

    public function rollbackTransaction(): void
    {
        DB::rollback();
    }

}
