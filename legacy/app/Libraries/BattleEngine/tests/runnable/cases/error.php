<?php

require '../../RunnableTest.php';
class Fazi extends RunnableTest
{
    public function getAttachers()
    {
        $ships = [];
        $ships[] = $this->getShipType(205, 20);
        $ships[] = $this->getShipType(211, 30);
        $fleet = new Fleet(1, $ships);
        $player = new Player(1, [$fleet], 7, 6, 6);
        return new PlayerGroup([$player]);
    }

    public function getDefenders()
    {
        $ships = [];
        $ships[] = $this->getShipType(401, 1);
        $ships[] = $this->getShipType(402, 2);
        $ships[] = $this->getShipType(403, 1);
        $ships[] = $this->getShipType(407, 1);
        $ships[] = $this->getShipType(408, 1);
        $fleet = new Fleet(2, $ships);
        $player = new Player(2, [$fleet], 5, 5, 5);
        return new PlayerGroup([$player]);
    }
}
new Fazi(false);
