<?php

declare(strict_types=1);

namespace Xgp\App\Libraries\Game;

use App\Services\Game\Formulas\ProductionService;
use Xgp\App\Core\Entity\BuildingsEntity;
use Xgp\App\Core\Entity\PlanetEntity;
use Xgp\App\Core\Entity\PremiumEntity;
use Xgp\App\Core\Entity\UserEntity;

class ResourceMarket
{
    private UserEntity $user;
    private PremiumEntity $premium;
    private PlanetEntity $planet;
    private BuildingsEntity $buildings;

    public function __construct(array $user, array $planet, private ProductionService $productionService)
    {
        $this->setUpUser($user);
        $this->setUpPremium($user);
        $this->setUpPlanet($planet);
        $this->setUpBuildings($planet);
    }

    /**
     * Calculate the base price
     *
     * @param integer $max_storage
     * @param integer $base_dm
     *
     * @return float
     */
    public function calculateBasePriceToRefill(int $max_storage, int $base_dm): float
    {
        // (max_storage_capacity * 0.10) * base_dark_maatter / (max_initial_storage * 0.10)
        return ($max_storage * 0.10) * $base_dm / ($this->productionService->maxStorable(0) * 0.10);
    }

    /**
     * Get the price to refill the storage a 10%
     *
     * @param string $resource
     *
     * @return float
     */
    public function getPriceToFill10Percent(string $resource): float
    {
        return $this->calculateRefillStoragePrice(
            $resource,
            10
        );
    }

    /**
     * Get the price to refill the storage a 50%
     *
     * @param string $resource
     *
     * @return float
     */
    public function getPriceToFill50Percent(string $resource): float
    {
        return $this->calculateRefillStoragePrice(
            $resource,
            50
        );
    }

    /**
     * Get the price to completely refill the storage
     *
     * @param string $resource
     *
     * @return float
     */
    public function getPriceToFill100Percent(string $resource): float
    {
        return $this->calculateRefillStoragePrice(
            $resource,
            100,
            $this->planet->{'getPlanetAmountOf' . ucfirst($resource)}()
        );
    }

    public function calculateRefillStoragePrice(string $resource, int $percentage, float $current_resources = 0): float
    {
        $max_storage = $this->productionService->maxStorable($this->buildings->{'getBuilding' . ucfirst($resource) . 'Store'}());
        $base_price = $this->calculateBasePriceToRefill($max_storage, BASIC_RESOURCE_MARKET_DM[$resource]);

        return floor((($max_storage - $current_resources) * $percentage / $max_storage) * $base_price / 10);
    }

    public function isMetalStorageFull(): bool
    {
        return ($this->productionService->maxStorable($this->buildings->getBuildingMetalStore()) <= $this->planet->getPlanetAmountOfMetal());
    }

    public function isCrystalStorageFull(): bool
    {
        return ($this->productionService->maxStorable($this->buildings->getBuildingCrystalStore()) <= $this->planet->getPlanetAmountOfCrystal());
    }

    public function isDeuteriumStorageFull(): bool
    {
        return ($this->productionService->maxStorable($this->buildings->getBuildingDeuteriumStore()) <= $this->planet->getPlanetAmountOfDeuterium());
    }

    public function getProjectedResouces(string $resource, int $percentage): float
    {
        $amount_to_fill = $this->productionService->maxStorable(
            $this->buildings->{'getBuilding' . ucfirst($resource) . 'Store'}()
        ) * $percentage / 100;

        if ($percentage != 100) {
            return $this->planet->{'getPlanetAmountOf' . ucfirst($resource)}() + $amount_to_fill;
        }

        return $amount_to_fill;
    }

    public function isMetalStorageFillable(int $percentage): bool
    {
        return $this->isStorageFillable('metal', $percentage);
    }

    public function isCrystalStorageFillable(int $percentage): bool
    {
        return $this->isStorageFillable('crystal', $percentage);
    }

    public function isDeuteriumStorageFillable(int $percentage): bool
    {
        return $this->isStorageFillable('deuterium', $percentage);
    }

    public function isRefillPayable(string $resource, int $percentage): bool
    {
        return ($this->premium->getPremiumDarkMatter() >= $this->{'getPriceToFill' . $percentage . 'Percent'}($resource));
    }

    private function isStorageFillable(string $resource, int $percentage): bool
    {
        if ($this->{'is' . ucfirst($resource) . 'StorageFull'}()) {
            return false;
        }

        return ($this->productionService->maxStorable($this->buildings->{'getBuilding' . ucfirst($resource) . 'Store'}()) >= $this->getProjectedResouces($resource, $percentage));
    }

    private function setUpUser(array $user): void
    {
        $this->user = $this->createNewUserEntity($user);
    }

    private function setUpPremium(array $user): void
    {
        $this->premium = $this->createNewPremiumEntity($user);
    }

    private function setUpPlanet(array $planet): void
    {
        $this->planet = $this->createNewPlanetEntity($planet);
    }

    private function setUpBuildings(array $planet): void
    {
        $this->buildings = $this->createNewBuildingsEntity($planet);
    }

    private function createNewUserEntity(array $user): UserEntity
    {
        return new UserEntity($user);
    }

    private function createNewPremiumEntity(array $user): PremiumEntity
    {
        return new PremiumEntity($user);
    }

    private function createNewPlanetEntity(array $planet): PlanetEntity
    {
        return new PlanetEntity($planet);
    }

    private function createNewBuildingsEntity(array $planet): BuildingsEntity
    {
        return new BuildingsEntity($planet);
    }
}
