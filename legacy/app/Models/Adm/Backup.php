<?php

namespace Xgp\App\Models\Adm;

use Xgp\App\Core\Model;

class Backup extends Model
{
    public function performBackup(): string
    {
        return $this->db->backupDb();
    }
}
