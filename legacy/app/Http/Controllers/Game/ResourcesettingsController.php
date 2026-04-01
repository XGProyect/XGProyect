<?php

declare(strict_types=1);

namespace Xgp\App\Http\Controllers\Game;

use Illuminate\Routing\Controller as BaseController;
use Xgp\App\Core\Enumerators\PlanetTypesEnumerator;
use App\Services\SettingsService;
use App\Services\FormatService;
use App\Services\Game\Formulas\OfficerService;
use App\Services\Game\Formulas\ProductionService;
use Xgp\App\Core\Objects;
use Xgp\App\Core\Template;
use Xgp\App\Libraries\Formulas;
use Xgp\App\Libraries\Functions;
use Xgp\App\Libraries\Users;
use App\Enums\Module;
use Xgp\App\Models\Game\Resources;

/**
 * @SuppressWarnings("PHPMD.StaticAccess")
 */
class ResourcesettingsController extends BaseController
{
    private array $user = [];
    private array $planet = [];
    private $resource;
    private $prodGrid;
    private $reslist;
    private Resources $resourcesModel;
    private Users $userLibrary;

    public function __construct(
        private ProductionService $productionService,
        private FormatService $formatService,
        private OfficerService $officerService
    ) {
    }

    public function __invoke(): void
    {
        Functions::moduleMessage(Functions::isModuleAccesible(Module::ResourceSettings));

        $this->user = Users::getInstance()->getUserData();
        $this->planet = Users::getInstance()->getPlanetData();
        $this->resourcesModel = new Resources();
        $this->resource = Objects::getInstance()->getObjects();
        $this->prodGrid = Objects::getInstance()->getProduction();
        $this->reslist = Objects::getInstance()->getObjectsList();
        $this->userLibrary = new Users();

        $this->buildPage();
    }

    private function buildPage(): void
    {
        $langFile = [
            'build' => 'constructions',
            'tech' => 'technologies',
            'fleet' => 'ships',
            'defenses' => 'defenses',
            'missiles' => 'defenses',
        ];

        $settings = app(SettingsService::class);
        $game_metal_basic_income = $settings->getInt('metal_basic_income');
        $game_crystal_basic_income = $settings->getInt('crystal_basic_income');
        $game_deuterium_basic_income = $settings->getInt('deuterium_basic_income');
        $game_energy_basic_income = $settings->getInt('energy_basic_income');
        $game_resource_multiplier = $settings->getInt('resource_multiplier');

        if ($this->user['preference_vacation_mode'] > 0 or $this->planet['planet_type'] == PlanetTypesEnumerator::MOON) {
            $game_metal_basic_income = 0;
            $game_crystal_basic_income = 0;
            $game_deuterium_basic_income = 0;
        }

        $this->planet['planet_metal_max'] = $this->productionService->maxStorable((int) $this->planet[$this->resource[22]]);
        $this->planet['planet_crystal_max'] = $this->productionService->maxStorable((int) $this->planet[$this->resource[23]]);
        $this->planet['planet_deuterium_max'] = $this->productionService->maxStorable((int) $this->planet[$this->resource[24]]);

        $parse['production_level'] = 100;
        $post_percent = $this->productionService->maxProductionPercentage((int) $this->planet['planet_energy_max'], (int) $this->planet['planet_energy_used']);

        $parse['resource_row'] = '';
        $this->planet['planet_metal_perhour'] = 0;
        $this->planet['planet_crystal_perhour'] = 0;
        $this->planet['planet_deuterium_perhour'] = 0;
        $this->planet['planet_energy_max'] = 0;
        $this->planet['planet_energy_used'] = 0;

        $BuildTemp = $this->planet['planet_temp_max'];

        $plasmaBoost = [
            'metal' => 0,
            'crystal' => 0,
            'deuterium' => 0,
        ];

        foreach ($this->reslist['prod'] as $ProdID) {
            if ($this->planet[$this->resource[$ProdID]] > 0 && isset($this->prodGrid[$ProdID])) {
                $resourcesTotal = [
                    'metal' => 0,
                    'crystal' => 0,
                    'deuterium' => 0,
                ];

                $BuildLevelFactor = $this->planet['planet_' . $this->resource[$ProdID] . '_percent'];
                $BuildLevel = $this->planet[$this->resource[$ProdID]];
                $BuildEnergy = $this->user['research_energy_technology'];

                // BOOST
                $geologe_boost = 1 + (1 * ($this->officerService->isOfficerActive((int) $this->user['premium_officier_geologist'], time()) ? GEOLOGUE : 0));
                $engineer_boost = 1 + (1 * ($this->officerService->isOfficerActive((int) $this->user['premium_officier_engineer'], time()) ? ENGINEER_ENERGY : 0));

                // PRODUCTION FORMULAS
                $metal_prod = ($this->prodGrid[$ProdID]['formule']['metal'])($BuildLevel, $BuildLevelFactor, $BuildTemp, $BuildEnergy);
                $crystal_prod = ($this->prodGrid[$ProdID]['formule']['crystal'])($BuildLevel, $BuildLevelFactor, $BuildTemp, $BuildEnergy);
                $deuterium_prod = ($this->prodGrid[$ProdID]['formule']['deuterium'])($BuildLevel, $BuildLevelFactor, $BuildTemp, $BuildEnergy);
                $energy_prod = ($this->prodGrid[$ProdID]['formule']['energy'])($BuildLevel, $BuildLevelFactor, $BuildTemp, $BuildEnergy);

                // PRODUCTION
                $resourcesTotal['metal'] += $this->productionService->productionAmount($metal_prod, $geologe_boost, $game_resource_multiplier);
                $resourcesTotal['crystal'] += $this->productionService->productionAmount($crystal_prod, $geologe_boost, $game_resource_multiplier);
                $resourcesTotal['deuterium'] += $this->productionService->productionAmount($deuterium_prod, $geologe_boost, $game_resource_multiplier);

                // PLASMA BOOST
                $metalBoost = Formulas::getPlasmaTechnologyBonus((int) $this->user['research_plasma_technology'], 'metal');
                $crystalBoost = Formulas::getPlasmaTechnologyBonus((int) $this->user['research_plasma_technology'], 'crystal');
                $deuteriumBoost = Formulas::getPlasmaTechnologyBonus((int) $this->user['research_plasma_technology'], 'deuterium');

                // PRODUCTION
                $plasmaBoostMetal = $this->productionService->productionAmount($metal_prod, $metalBoost, $game_resource_multiplier);
                $plasmaBoostCrystal = $this->productionService->productionAmount($crystal_prod, $crystalBoost, $game_resource_multiplier);
                $plasmaBoostDeuterium = $this->productionService->productionAmount($deuterium_prod, $deuteriumBoost, $game_resource_multiplier);

                $resourcesTotal['metal'] += $plasmaBoostMetal;
                $resourcesTotal['crystal'] += $plasmaBoostCrystal;
                $resourcesTotal['deuterium'] += $plasmaBoostDeuterium;

                $plasmaBoost['metal'] += $plasmaBoostMetal;
                $plasmaBoost['crystal'] += $plasmaBoostCrystal;
                $plasmaBoost['deuterium'] += $plasmaBoostDeuterium;

                if ($ProdID >= 4) {
                    $energy = $this->productionService->productionAmount($energy_prod, $engineer_boost, 0, true);
                } else {
                    $energy = $this->productionService->productionAmount($energy_prod, 1, 0, true);
                }

                if ($energy > 0) {
                    $this->planet['planet_energy_max'] += $energy;
                } else {
                    $this->planet['planet_energy_used'] += $energy;
                }

                $this->planet['planet_metal_perhour'] += $resourcesTotal['metal'];
                $this->planet['planet_crystal_perhour'] += $resourcesTotal['crystal'];
                $this->planet['planet_deuterium_perhour'] += $resourcesTotal['deuterium'];

                $metal = $this->productionService->currentProduction($metal_prod, $post_percent);
                $crystal = $this->productionService->currentProduction($crystal_prod, $post_percent);
                $deuterium = $this->productionService->currentProduction($deuterium_prod, $post_percent);
                $energy = $this->productionService->currentProduction($energy, $post_percent);
                $Field = 'planet_' . $this->resource[$ProdID] . '_percent';

                $CurrRow = [];
                $CurrRow['name'] = $this->resource[$ProdID];
                $CurrRow['percent'] = $this->planet[$Field];
                $CurrRow['option'] = $this->build_options($CurrRow['percent']);
                $CurrRow['type'] = $this->setLangLine($this->resource[$ProdID]);
                $CurrRow['level'] = ($ProdID > 200) ? __('game/resources.rs_amount') : __('game/global.level');
                $CurrRow['level_type'] = $this->planet[$this->resource[$ProdID]];
                $CurrRow['metal_type'] = $this->formatService->prettyNumber((int) $metal);
                $CurrRow['crystal_type'] = $this->formatService->prettyNumber((int) $crystal);
                $CurrRow['deuterium_type'] = $this->formatService->prettyNumber((int) $deuterium);
                $CurrRow['energy_type'] = $this->formatService->prettyNumber((int) $energy);
                $CurrRow['metal_type'] = $this->formatService->colorNumber((int) $metal, $CurrRow['metal_type']);
                $CurrRow['crystal_type'] = $this->formatService->colorNumber((int) $crystal, $CurrRow['crystal_type']);
                $CurrRow['deuterium_type'] = $this->formatService->colorNumber((int) $deuterium, $CurrRow['deuterium_type']);
                $CurrRow['energy_type'] = $this->formatService->colorNumber((int) $energy, $CurrRow['energy_type']);

                $parse['resource_row'] .= Template::render(
                    'resourcesettings.resources_row',
                    $CurrRow
                );
            }
        }

        $parse['Production_of_resources_in_the_planet'] = str_replace('%s', $this->planet['planet_name'], __('game/resources.rs_production_on_planet'));

        $parse['production_level'] = $this->prod_level($this->planet['planet_energy_used'], $this->planet['planet_energy_max']);
        $parse['metal_basic_income'] = $game_metal_basic_income;
        $parse['crystal_basic_income'] = $game_crystal_basic_income;
        $parse['deuterium_basic_income'] = $game_deuterium_basic_income;
        $parse['energy_basic_income'] = $game_energy_basic_income;

        $parse['plasma_level'] = $this->user['research_plasma_technology'];
        $parse['plasma_metal'] = $this->formatService->colorNumber((int) $plasmaBoost['metal'], $this->formatService->prettyNumber((int) $plasmaBoost['metal']));
        $parse['plasma_crystal'] = $this->formatService->colorNumber((int) $plasmaBoost['crystal'], $this->formatService->prettyNumber((int) $plasmaBoost['crystal']));
        $parse['plasma_deuterium'] = $this->formatService->colorNumber((int) $plasmaBoost['deuterium'], $this->formatService->prettyNumber((int) $plasmaBoost['deuterium']));

        $parse['planet_metal_max'] = $this->resource_color($this->planet['planet_metal'], $this->planet['planet_metal_max']);
        $parse['planet_crystal_max'] = $this->resource_color($this->planet['planet_crystal'], $this->planet['planet_crystal_max']);
        $parse['planet_deuterium_max'] = $this->resource_color($this->planet['planet_deuterium'], $this->planet['planet_deuterium_max']);

        $metal_total_raw = floor((($this->planet['planet_metal_perhour'] * 0.01 * $parse['production_level']) + $parse['metal_basic_income']));
        $crystal_total_raw = floor((($this->planet['planet_crystal_perhour'] * 0.01 * $parse['production_level']) + $parse['crystal_basic_income']));
        $deuterium_total_raw = floor((($this->planet['planet_deuterium_perhour'] * 0.01 * $parse['production_level']) + $parse['deuterium_basic_income']));
        $energy_total_raw = floor(($this->planet['planet_energy_max'] + $parse['energy_basic_income']) + $this->planet['planet_energy_used']);
        $parse['metal_total'] = $this->formatService->colorNumber($metal_total_raw, $this->formatService->prettyNumber($metal_total_raw));
        $parse['crystal_total'] = $this->formatService->colorNumber($crystal_total_raw, $this->formatService->prettyNumber($crystal_total_raw));
        $parse['deuterium_total'] = $this->formatService->colorNumber($deuterium_total_raw, $this->formatService->prettyNumber($deuterium_total_raw));
        $parse['energy_total'] = $this->formatService->colorNumber($energy_total_raw, $this->formatService->prettyNumber($energy_total_raw));

        $parse['daily_metal'] = $this->calculate_daily($this->planet['planet_metal_perhour'], $parse['production_level'], $parse['metal_basic_income']);
        $parse['weekly_metal'] = $this->calculate_weekly($this->planet['planet_metal_perhour'], $parse['production_level'], $parse['metal_basic_income']);

        $parse['daily_crystal'] = $this->calculate_daily($this->planet['planet_crystal_perhour'], $parse['production_level'], $parse['crystal_basic_income']);
        $parse['weekly_crystal'] = $this->calculate_weekly($this->planet['planet_crystal_perhour'], $parse['production_level'], $parse['crystal_basic_income']);

        $parse['daily_deuterium'] = $this->calculate_daily($this->planet['planet_deuterium_perhour'], $parse['production_level'], $parse['deuterium_basic_income']);
        $parse['weekly_deuterium'] = $this->calculate_weekly($this->planet['planet_deuterium_perhour'], $parse['production_level'], $parse['deuterium_basic_income']);

        $parse['daily_metal'] = $this->formatService->colorNumber((int) $parse['daily_metal'], $this->formatService->prettyNumber((int) $parse['daily_metal']));
        $parse['weekly_metal'] = $this->formatService->colorNumber((int) $parse['weekly_metal'], $this->formatService->prettyNumber((int) $parse['weekly_metal']));

        $parse['daily_crystal'] = $this->formatService->colorNumber((int) $parse['daily_crystal'], $this->formatService->prettyNumber((int) $parse['daily_crystal']));
        $parse['weekly_crystal'] = $this->formatService->colorNumber((int) $parse['weekly_crystal'], $this->formatService->prettyNumber((int) $parse['weekly_crystal']));

        $parse['daily_deuterium'] = $this->formatService->colorNumber((int) $parse['daily_deuterium'], $this->formatService->prettyNumber((int) $parse['daily_deuterium']));
        $parse['weekly_deuterium'] = $this->formatService->colorNumber((int) $parse['weekly_deuterium'], $this->formatService->prettyNumber((int) $parse['weekly_deuterium']));

        $ValidList['percent'] = [0, 10, 20, 30, 40, 50, 60, 70, 80, 90, 100];
        $SubQry = '';

        if ($_POST && !$this->userLibrary->isOnVacations($this->user)) {
            foreach ($_POST as $Field => $Value) {
                $FieldName = 'planet_' . $Field . '_percent';
                if (isset($this->planet[$FieldName])) {
                    if (!in_array($Value, $ValidList['percent'])) {
                        Functions::redirect('game.php?page=resourceSettings');
                    }

                    $Value = $Value / 10;
                    $this->planet[$FieldName] = $Value;
                    $SubQry .= ', `' . $FieldName . "` = '" . $Value . "'";
                }
            }

            $this->resourcesModel->updateCurrentPlanet($this->planet, $SubQry);

            Functions::redirect('game.php?page=resourceSettings');
        }

        Template::legacyView(
            'resourcesettings.view',
            $parse
        );
    }

    /**
     * method build_options
     * param $current_percentage
     * return percentage options for the select element
     */
    private function build_options($current_porcentage)
    {
        $option_row = '';

        for ($option = 10; $option >= 0; $option--) {
            $opt_value = $option * 10;

            if ($option == $current_porcentage) {
                $opt_selected = ' selected=selected';
            } else {
                $opt_selected = '';
            }

            $option_row .= '<option value="' . $opt_value . '"' . $opt_selected . '>' . $opt_value . '%</option>';
        }

        return $option_row;
    }

    /**
     * method calculate_daily
     * param1 $prod_per_hour
     * param2 $prod_level
     * param3 $basic_income
     * return production per day
     */
    private function calculate_daily($prod_per_hour, $prod_level, $basic_income)
    {
        return floor(($basic_income + ($prod_per_hour * 0.01 * $prod_level)) * 24);
    }

    /**
     * method calculate_weekly
     * param1 $prod_per_hour
     * param2 $prod_level
     * param3 $basic_income
     * return production per week
     */
    private function calculate_weekly($prod_per_hour, $prod_level, $basic_income)
    {
        return floor(($basic_income + ($prod_per_hour * 0.01 * $prod_level)) * 24 * 7);
    }

    /**
     * method resource_color
     * param1 $current_amount
     * param2 $max_amount
     * return color depending on the current storage capacity
     */
    private function resource_color($current_amount, $max_amount)
    {
        if ($max_amount < $current_amount) {
            return ($this->formatService->colorRed($this->formatService->prettyNumber($max_amount / 1000) . 'k'));
        } else {
            return ($this->formatService->colorGreen($this->formatService->prettyNumber($max_amount / 1000) . 'k'));
        }
    }

    /**
     * method prod_level
     * param1 $energy_used
     * param2 $energy_max
     * return the production level based on the energy consumption
     */
    private function prod_level($energy_used, $energy_max)
    {
        if ($energy_max == 0 && $energy_used > 0) {
            $prod_level = 0;
        } elseif ($energy_max > 0 && abs($energy_used) > $energy_max) {
            $prod_level = floor(($energy_max) / ($energy_used * -1) * 100);
        } elseif ($energy_max == 0 && abs($energy_used) > $energy_max) {
            $prod_level = 0;
        } else {
            $prod_level = 100;
        }

        if ($prod_level > 100) {
            $prod_level = 100;
        }

        return $prod_level;
    }

    private function setLangLine(string $langLine): string
    {
        $prefix = '';
        $langTypeMap = [
            'building_' => 'constructions',
            'research_' => 'technologies',
            'ship_' => 'ships',
            'defense_' => 'defenses',
        ];

        foreach ($langTypeMap as $type => $lang) {
            if (strpos($langLine, $type) !== false) {
                $prefix = $lang;
                break;
            }
        }

        return __('game/' . $prefix . '.' . $langLine);
    }
}
