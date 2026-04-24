<?php

declare(strict_types=1);

namespace Xgp\App\Core;

use Exception;
use Illuminate\Support\Facades\DB;
use PDO;

/**
 * @deprecated v4.0.0 use Laravel DB facade / Eloquent directly
 */
class Database
{
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

    public function backupDb($tables = '*')
    {
        if ($tables === '*') {
            $tables = [];
            $result = $this->query('SHOW TABLES');

            while ($row = $this->fetchRow($result)) {
                $tables[] = $row[0];
            }
        } else {
            $tables = is_array($tables) ? $tables : explode(',', $tables);
        }

        $return = '';

        foreach ($tables as $table) {
            $result = $this->query('SELECT * FROM ' . $table);
            $num_fields = $this->numFields($result);

            $return .= 'DROP TABLE ' . $table . ';';
            $row2 = $this->fetchRow($this->query('SHOW CREATE TABLE ' . $table));
            $return .= "\n\n" . $row2[1] . ";\n\n";

            for ($i = 0; $i < $num_fields; $i++) {
                while ($row = $this->fetchRow($result)) {
                    $return .= 'INSERT INTO ' . $table . ' VALUES(';

                    for ($j = 0; $j < $num_fields; $j++) {
                        $row[$j] = addslashes((string) $row[$j]);
                        $row[$j] = str_replace("\n", '\\n', $row[$j]);

                        if (isset($row[$j])) {
                            $return .= '"' . $row[$j] . '"';
                        } else {
                            $return .= '""';
                        }

                        if ($j < ($num_fields - 1)) {
                            $return .= ',';
                        }
                    }

                    $return .= ");\n";
                }
            }

            $return .= "\n\n\n";
        }

        $file_name = 'db-backup-' . date('Ymd') . '-' . time() . '-' . (sha1(join(',', $tables))) . '.sql';
        $handle = fopen(storage_path('backups') . DIRECTORY_SEPARATOR . $file_name, 'w+');
        $writed = fwrite($handle, $return);
        fclose($handle);

        return $writed;
    }

    private function prepareSql(string $query): string
    {
        return strtr($query, ['{xgp_prefix}' => DB::getTablePrefix()]);
    }
}
