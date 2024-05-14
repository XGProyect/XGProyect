<?php

namespace Xgp\App\Models\Adm;

use Xgp\App\Core\Model;

/**
 * @deprecated v4.0.0 use laravel instead
 */
class Update extends Model
{
    public function runQuery(string $query): string
    {
        return $this->db->query($query);
    }
}
