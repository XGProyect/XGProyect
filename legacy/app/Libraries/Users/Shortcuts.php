<?php

declare(strict_types=1);

namespace Xgp\App\Libraries\Users;

use Exception;
use JsonException;
use Xgp\App\Helpers\StringsHelper;

class Shortcuts
{
    private array $shortcuts = [];

    public function __construct(?string $shortcuts)
    {
        if (!empty($shortcuts)) {
            $this->setShortcuts($shortcuts);
        }
    }

    private function setShortcuts(string $shortcuts): void
    {
        try {
            if (!empty($shortcuts)) {
                $this->shortcuts = json_decode($shortcuts, true, 512, JSON_THROW_ON_ERROR);
            }
        } catch (JsonException $e) {
            die('JSON Error - ' . $e->getMessage() . ' on ' . __CLASS__ . ', line: ' . $e->getLine());
        }
    }

    private function getShortcuts(): array
    {
        return $this->shortcuts;
    }

    public function addNew(string $name, int $g, int $s, int $p, int $pt): array
    {
        try {
            if (empty($name) or empty($g) or empty($s) or empty($p) or empty($pt)) {
                throw new Exception('Name cannot be empty or null');
            }

            $filtered_name = StringsHelper::escapeString(strip_tags($name));

            $this->shortcuts[] = [
                'name' => $filtered_name,
                'g' => $g,
                's' => $s,
                'p' => $p,
                'pt' => $pt,
            ];

            return $this->getShortcuts();
        } catch (Exception $e) {
            die('Caught exception: ' . $e->getMessage() . "\n");
        }
    }

    public function editById(int $shortcut_id, string $name, int $g, int $s, int $p, int $pt): array
    {
        try {
            if (!isset($this->getShortcuts()[$this->validateShortcutId($shortcut_id)])) {
                throw new Exception('Shortcut ID doesn\'t exists');
            }

            $filtered_name = StringsHelper::escapeString(strip_tags($name));

            $this->shortcuts[$shortcut_id] = [
                'name' => $filtered_name,
                'g' => $g,
                's' => $s,
                'p' => $p,
                'pt' => $pt,
            ];

            return $this->getShortcuts();
        } catch (Exception $e) {
            die('Caught exception: ' . $e->getMessage() . "\n");
        }
    }

    public function deleteById(int $shortcut_id): array
    {
        array_splice($this->shortcuts, $this->validateShortcutId($shortcut_id), 1);

        return $this->getShortcuts();
    }

    public function getAllAsArray(): array
    {
        return $this->shortcuts;
    }

    public function getAllAsJsonString(): string
    {
        try {
            return json_encode($this->shortcuts, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            die('JSON Error - ' . $e->getMessage() . ' on ' . __CLASS__ . ', line: ' . $e->getLine());
        }
    }

    public function getById(int $shortcut_id): array
    {
        return isset($this->shortcuts[$shortcut_id]) ? $this->shortcuts[$shortcut_id] : 0;
    }

    private function validateShortcutId(int $shortcut_id): int
    {
        if ($shortcut_id < 0) {
            return 0;
        }

        if ($shortcut_id > count($this->shortcuts)) {
            return count($this->shortcuts) - 1;
        }

        return $shortcut_id;
    }
}
