<?php

declare(strict_types=1);

namespace App\Core\GameObjects;

interface GameObjectInterface
{
    public function getId(): int;

    public function getName(): string;

    public function getPrice(): Price;
}
