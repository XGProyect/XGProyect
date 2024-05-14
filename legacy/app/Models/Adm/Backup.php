<?php

namespace Xgp\App\Models\Adm;

use Xgp\App\Core\Model;

/**
 * @deprecated v4.0.0 use laravel instead
 */
class Backup extends Model
{
    public function performBackup(): string
    {
        return $this->db->backupDb();
    }
}
