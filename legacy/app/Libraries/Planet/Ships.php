<?php

declare(strict_types=1);

namespace Xgp\App\Libraries\Planet;

use Xgp\App\Core\Entity\ShipsEntity;

class Ships
{
    private array $_ships = [];

    public function __construct($planet_data)
    {
        if (is_array($planet_data)) {
            $this->setUp($planet_data);
        }
    }

    /**
     * Get all the ships
     *
     * @return array
     */
    public function getShips()
    {
        $list_of_ships = [];

        foreach ($this->_ships as $ship) {
            if (($ship instanceof ShipsEntity)) {
                $list_of_ships[] = $ship;
            }
        }

        return $list_of_ships;
    }

    /**
     * Return current alliance data
     *
     * @return array
     */
    public function getCurrentShips()
    {
        return $this->getShips()[0];
    }

    /**
     * Set up the list of alliances
     *
     * @param array $ships Ships
     *
     * @return void
     */
    private function setUp($ships)
    {
        foreach ($ships as $ship) {
            $this->_ships[] = $this->createNewShipsEntity($ship);
        }
    }

    private function createNewShipsEntity(array $ships): ShipsEntity
    {
        return new ShipsEntity($ships);
    }
}
