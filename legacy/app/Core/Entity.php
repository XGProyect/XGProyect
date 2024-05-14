<?php

namespace Xgp\App\Core;

use Exception;

/**
 * @deprecated v4.0.0 use laravel instead
 */
class Entity
{
    protected array $data = [];

    public function __construct($data)
    {
        $this->setData($data);
    }

    /**
     * Set the current data
     *
     * @param array $data data
     *
     * @throws Exception
     *
     * @return void
     */
    private function setData($data)
    {
        try {
            if (!is_array($data)) {
                return null;
            }

            $this->data = $data;
        } catch (Exception $e) {
            die('Caught exception: ' . $e->getMessage() . "\n");
        }
    }
}
