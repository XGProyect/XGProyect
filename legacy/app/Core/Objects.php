<?php

namespace Xgp\App\Core;

class Objects
{
    private static ?Objects $instance = null;
    private array $objects = [];
    private array $relations = [];
    private array $price = [];
    private array $combatSpecs = [];
    private array $production = [];
    private array $objectsList = [];

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new Objects();
        }

        return self::$instance;
    }

    public function __construct()
    {
        // require this damn file
        require CORE_PATH . 'objects_collection.php';

        $this->objects = $resource; // @phpstan-ignore-line
        $this->relations = $requeriments; // @phpstan-ignore-line
        $this->price = $pricelist; // @phpstan-ignore-line
        $this->combatSpecs = $CombatCaps; // @phpstan-ignore-line
        $this->production = $ProdGrid; // @phpstan-ignore-line
        $this->objectsList = $reslist; // @phpstan-ignore-line
    }

    public function getObjects(?int $objectId = null)
    {
        if (!empty($objectId)) {
            return $this->objects[$objectId];
        } else {
            return $this->objects;
        }
    }

    public function getRelations(?int $objectId = null)
    {
        if (!empty($objectId)) {
            return $this->relations[$objectId];
        } else {
            return $this->relations;
        }
    }

    public function getPrice(?int $objectId = null, string $resource = '')
    {
        if (!empty($objectId)) {
            if (empty($resource)) {
                return $this->price[$objectId];
            } else {
                return $this->price[$objectId][$resource];
            }
        } else {
            return $this->price;
        }
    }

    public function getCombatSpecs(?int $objectId = null, string $type = '')
    {
        if (!empty($objectId)) {
            if (empty($type)) {
                return $this->combatSpecs[$objectId];
            } else {
                return $this->combatSpecs[$objectId][$type];
            }
        } else {
            return $this->combatSpecs;
        }
    }

    public function getProduction(?int $objectId = null)
    {
        if (!empty($objectId)) {
            return $this->production[$objectId];
        } else {
            return $this->production;
        }
    }

    public function getObjectsList(?string $objectId = null)
    {
        if (!empty($objectId)) {
            return $this->objectsList[$objectId];
        } else {
            return $this->objectsList;
        }
    }
}
