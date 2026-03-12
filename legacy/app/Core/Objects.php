<?php

declare(strict_types=1);

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
        $collection = new ObjectsCollection();

        $this->objects = $collection->resources();
        $this->relations = $collection->requirements();
        $this->price = $collection->priceList();
        $this->combatSpecs = $collection->combatSpecs();
        $this->production = $collection->productionGrid();
        $this->objectsList = $collection->objectsList();
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
