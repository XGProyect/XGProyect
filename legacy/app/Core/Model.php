<?php

declare(strict_types=1);

namespace Xgp\App\Core;

use Xgp\App\Core\Database;

abstract class Model
{
    protected Database $db;

    public function __construct()
    {
        $this->setNewDb();
    }

    public function __destruct()
    {
        $this->db->closeConnection();
    }

    private function setNewDb(): void
    {
        $this->db = new Database();
    }
}
