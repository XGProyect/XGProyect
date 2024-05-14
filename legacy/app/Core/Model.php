<?php

declare(strict_types=1);

namespace Xgp\App\Core;

/**
 * @deprecated v4.0.0 use laravel instead
 */
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
