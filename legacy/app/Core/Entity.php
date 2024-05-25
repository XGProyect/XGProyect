<?php

declare(strict_types=1);

namespace Xgp\App\Core;

/**
 * @deprecated v4.0.0 use laravel instead
 */
class Entity
{
    protected array $data = [];

    public function __construct(array $data)
    {
        $this->setData($data);
    }

    private function setData(array $data): void
    {
        $this->data = $data;
    }
}
