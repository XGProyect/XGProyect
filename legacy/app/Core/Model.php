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
        $this->db = new Database();
    }
}
