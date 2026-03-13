<?php

declare(strict_types=1);

namespace App\Core\GameObjects;

use Illuminate\Support\Collection;

abstract class GameObject
{
    /**
     * @param Collection<int, int> $requirements
     */
    public function __construct(
        private int $id,
        private string $name,
        private Price $price,
        private Collection $requirements,
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPrice(): Price
    {
        return $this->price;
    }

    /**
     * @return Collection<int, int>
     */
    public function getRequirements(): Collection
    {
        return $this->requirements;
    }
}
