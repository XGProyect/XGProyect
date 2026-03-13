<?php

declare(strict_types=1);

namespace App\Core\GameObjects;

use App\Enums\Game\BuildingCategory;
use Illuminate\Support\Collection;

class Building extends GameObject
{
    /**
     * @param Collection<int, int> $requirements
     */
    public function __construct(
        int $id,
        string $name,
        Price $price,
        Collection $requirements,
        private BuildingCategory $category,
        private ?ProductionFormula $production = null,
    ) {
        parent::__construct($id, $name, $price, $requirements);
    }

    public function getCategory(): BuildingCategory
    {
        return $this->category;
    }

    public function getProduction(): ?ProductionFormula
    {
        return $this->production;
    }

    public function hasProduction(): bool
    {
        return $this->production !== null;
    }
}
