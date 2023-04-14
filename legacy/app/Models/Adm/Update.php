<?php

namespace Xgp\App\Models\Adm;

use Xgp\App\Core\Model;

class Update extends Model
{
    public function runQuery(string $query): string
    {
        return $this->db->query($query);
    }
}
