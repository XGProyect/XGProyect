<?php

require '../../RunnableTest.php';
class Fazi extends RunnableTest
{
    public function getAttachers()
    {
        $ships = [];
        $ships[] = $this->getShipType(214, 1);
        $fleet = new Fleet(1, $ships);
        $player = new Player(1, [$fleet]);
        return new PlayerGroup([$player]);
    }

    public function getDefenders()
    {
        $ships = [];
        $ships[] = $this->getShipType(202, 1000);
        $fleet = new Fleet(2, $ships);
        $player = new Player(2, [$fleet]);
        return new PlayerGroup([$player]);
    }
}
new Fazi();
