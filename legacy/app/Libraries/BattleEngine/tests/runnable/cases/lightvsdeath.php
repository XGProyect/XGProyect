<?php

require '../../RunnableTest.php';
class lightvsdeath extends RunnableTest
{
    public function getAttachers()
    {
        $ships = [];
        $ships[] = $this->getShipType(204, 1111);
        $fleet = new Fleet(1, $ships);
        $player = new Player(1, [$fleet], 10, 10, 10);
        return new PlayerGroup([$player]);
    }

    public function getDefenders()
    {
        $ships = [];
        $ships[] = $this->getShipType(209, 11);
        $ships[] = $this->getShipType(214, 1);
        $fleet = new Fleet(2, $ships);
        $player = new Player(2, [$fleet], 11, 11, 11);
        return new PlayerGroup([$player]);
    }
}
new lightvsdeath();
