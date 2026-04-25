<?php

declare(strict_types=1);

namespace Xgp\App\Http\Controllers\Game;

use App\Enums\Module;
use Illuminate\Routing\Controller as BaseController;
use Xgp\App\Core\Enumerators\BuildingsEnumerator as Buildings;
use Xgp\App\Core\Enumerators\ResearchEnumerator as Research;
use App\Services\SettingsService;
use App\Services\Game\Formulas\FleetsService;
use App\Services\FormatService;
use App\Services\Game\Formulas\DevelopmentsService;
use App\Services\Game\Formulas\OfficerService;
use App\Services\Game\Formulas\ProductionService;
use Xgp\App\Core\Objects;
use Xgp\App\Core\Template;
use Xgp\App\Helpers\StringsHelper;
use Xgp\App\Helpers\UrlHelper;
use Xgp\App\Libraries\Formulas;
use Xgp\App\Libraries\Functions;
use Xgp\App\Libraries\Users;
use Illuminate\Support\Facades\DB;
use Xgp\App\Core\Concerns\PreparesLegacySql;

/**
 * @SuppressWarnings("PHPMD.StaticAccess")
 */
class InfosController extends BaseController
{
    use PreparesLegacySql;

    private array $user = [];
    private array $planet = [];
    private $_element_id;
    private $_resource;
    private $_pricelist;
    private $_combat_caps;
    private $_prod_grid;

    public function __construct(
        private ProductionService $productionService,
        private FormatService $formatService,
        private OfficerService $officerService,
        private DevelopmentsService $developmentsService,
        private FleetsService $fleetsService
    ) {
    }

    public function __invoke(): void
    {
        Functions::moduleMessage(Functions::isModuleAccesible(Module::Information));

        $this->user = Users::getInstance()->getUserData();
        $this->planet = Users::getInstance()->getPlanetData();
        $this->_resource = Objects::getInstance()->getObjects();
        $this->_pricelist = Objects::getInstance()->getPrice();
        $this->_combat_caps = Objects::getInstance()->getCombatSpecs();
        $this->_prod_grid = Objects::getInstance()->getProduction();
        $this->_element_id = isset($_GET['gid']) ? (int) $_GET['gid'] : null;

        $this->buildPage();
    }

    private function buildPage(): void
    {
        if (!array_key_exists($this->_element_id, $this->_resource)) {
            Functions::redirect('game.php?page=technologytree');
        }

        $GateTPL = '';
        $DestroyTPL = '';
        $TableHeadTPL = '';
        $TableFooterTPL = '';
        $TableTPL = '';

        $parse['name'] = $this->langs->language[$this->_resource[$this->_element_id]];
        $parse['image'] = $this->_element_id;
        $parse['description'] = __('game/infos.info')[$this->_resource[$this->_element_id]];
        $parse['table_head'] = '';
        $parse['table_data'] = '';

        if ($this->_element_id < 13 or ($this->_element_id == 43 && $this->planet[$this->_resource[43]] > 0)) {
            $PageTPL = 'infos/info_buildings_table';
        } elseif ($this->_element_id < 200) {
            $PageTPL = 'infos/info_buildings_general';
        } elseif ($this->_element_id < 400) {
            $PageTPL = 'infos/info_buildings_fleet';
        } elseif ($this->_element_id < 600) {
            $PageTPL = 'infos/info_buildings_defense';
        } else {
            $PageTPL = 'infos/info_officiers_general';
        }

        // only destroy on < 200 and not some moon buildings
        if ($this->_element_id < 200 && $this->_element_id != 33 && $this->_element_id != 41) {
            $DestroyTPL = 'infos/info_buildings_destroy';
        }

        if ($this->_element_id >= 1 && $this->_element_id <= 3) {
            $PageTPL = 'infos/info_buildings_table';
            $TableHeadTPL = 'infos/info_production_header';
            $TableTPL = 'infos/info_production_body';
        } elseif ($this->_element_id == 4) {
            $PageTPL = 'infos/info_buildings_table';
            $TableHeadTPL = 'infos/info_production_simple_header';
            $TableTPL = 'infos/info_production_simple_body';
        } elseif ($this->_element_id >= 22 && $this->_element_id <= 24) {
            $PageTPL = 'infos/info_buildings_table';
            $TableHeadTPL = 'infos/info_storage_header';
            $TableTPL = 'infos/info_storage_table';
        } elseif ($this->_element_id == 12) {
            $TableHeadTPL = 'infos/info_energy_header';
            $TableTPL = 'infos/info_energy_body';
        } elseif ($this->_element_id == 42) {
            $PageTPL = 'infos/info_buildings_table';
            $TableHeadTPL = 'infos/info_range_header';
            $TableTPL = 'infos/info_range_body';
        } elseif ($this->_element_id == 43) {
            $GateTPL = 'infos/info_gate_table';

            if ($_POST) {
                Functions::message($this->doFleetJump(), 'game.php?page=infos&gid=43', 2);
            }
        } elseif ($this->_element_id == 124) {
            $PageTPL = 'infos/info_buildings_table';
            $TableHeadTPL = 'infos/info_astrophysics_header';
            $TableTPL = 'infos/info_astrophysics_table';
            $TableFooterTPL = 'infos/info_astrophysics_footer';
        } elseif ($this->_element_id >= 202 && $this->_element_id <= 250) {
            $PageTPL = 'infos/info_buildings_fleet';
            $parse['element_typ'] = __('game/ships.ships');
            $parse['rf_info_to'] = $this->ShowRapidFireTo();
            $parse['rf_info_fr'] = $this->ShowRapidFireFrom();
            $parse['hull_pt'] = $this->formatService->prettyNumber($this->_pricelist[$this->_element_id]['metal'] + $this->_pricelist[$this->_element_id]['crystal']);
            $parse['shield_pt'] = $this->formatService->prettyNumber((int) $this->_combat_caps[$this->_element_id]['shield']);
            $parse['attack_pt'] = $this->formatService->prettyNumber((int) $this->_combat_caps[$this->_element_id]['attack']);
            $parse['capacity_pt'] = $this->formatService->prettyNumber((int) $this->_pricelist[$this->_element_id]['capacity']);
            $parse['base_speed'] = $this->formatService->prettyNumber((int) $this->_pricelist[$this->_element_id]['speed']);
            $parse['base_conso'] = $this->formatService->prettyNumber((int) $this->_pricelist[$this->_element_id]['consumption']);

            $parse['upd_speed'] = '';
            $parse['upd_conso'] = '';

            if ($this->_element_id == 202) {
                $parse['upd_speed'] = '<font color="yellow">(' . $this->formatService->prettyNumber((int) $this->_pricelist[$this->_element_id]['speed2']) . ')</font>';
                $parse['upd_conso'] = '<font color="yellow">(' . $this->formatService->prettyNumber((int) $this->_pricelist[$this->_element_id]['consumption2']) . ')</font>';
            } elseif ($this->_element_id == 211) {
                $parse['upd_speed'] = '<font color="yellow">(' . $this->formatService->prettyNumber((int) $this->_pricelist[$this->_element_id]['speed2']) . ')</font>';
            }
        } elseif ($this->_element_id >= 401 && $this->_element_id <= 550) {
            $PageTPL = 'infos/info_buildings_defense';
            $parse['element_typ'] = __('game/defenses.defenses');
            $parse['rf_info_to'] = '';
            $parse['rf_info_fr'] = '';

            if ($this->_element_id < 500) {
                $parse['rf_info_to'] = $this->ShowRapidFireTo();
                $parse['rf_info_fr'] = $this->ShowRapidFireFrom();
            }

            $parse['hull_pt'] = $this->formatService->prettyNumber($this->_pricelist[$this->_element_id]['metal'] + $this->_pricelist[$this->_element_id]['crystal']);
            $parse['shield_pt'] = $this->formatService->prettyNumber((int) $this->_combat_caps[$this->_element_id]['shield']);
            $parse['attack_pt'] = $this->formatService->prettyNumber((int) $this->_combat_caps[$this->_element_id]['attack']);
        }

        if ($TableHeadTPL != '') {
            $parse['table_head'] = Template::render($TableHeadTPL);

            if ($this->_element_id >= 22 && $this->_element_id <= 24) {
                $parse['table_data'] = $this->storage_table($TableTPL);
            } elseif ($this->_element_id == 124) {
                $parse['table_data'] = $this->astrophysics_table($TableTPL);
            } elseif ($this->_element_id == 42) {
                $parse['table_data'] = $this->phalanxRange($TableTPL);
            } else {
                $parse['table_data'] = $this->showProductionTable($TableTPL);
            }
        }

        $parse['table_footer'] = '';
        if ($TableFooterTPL != '') {
            $parse['table_footer'] = Template::render($TableFooterTPL);
        }

        $page = Template::render($PageTPL, $parse);

        if ($GateTPL != '') {
            if ($this->planet[$this->_resource[$this->_element_id]] > 0) {
                $RestString = $this->GetNextJumpWaitTime($this->planet);
                $parse['gate_start_link'] = $this->planet_link($this->planet);
                if ($RestString['value'] != 0) {
                    $parse['gate_time_script'] = Functions::chronoApplet('Gate', '1', $RestString['value'], true);
                    $parse['gate_wait_time'] = '<div id="bxx' . 'Gate' . '1' . '"></div>';
                    $parse['gate_script_go'] = Functions::chronoApplet('Gate', '1', $RestString['value'], false);
                } else {
                    $parse['gate_time_script'] = '';
                    $parse['gate_wait_time'] = '';
                    $parse['gate_script_go'] = '';
                }
                $parse['gate_dest_moons'] = $this->BuildJumpableMoonCombo();
                $parse['gate_fleet_rows'] = $this->BuildFleetListRows();
                $page .= Template::render($GateTPL, $parse);
            }
        }

        if ($DestroyTPL != '') {
            $page .= $this->buildTearDownBlock();
        }

        Template::legacyView(
            $page
        );
    }

    /**
     * method storage_table
     * param
     * return builds the storage table
     */
    private function storage_table($template)
    {
        $current_built_lvl = $this->planet[$this->_resource[$this->_element_id]];
        $BuildStartLvl = max(1, $current_built_lvl - 2);
        $Table = '';
        $ProdFirst = 0;
        $ActualProd = $this->productionService->maxStorable((int) $current_built_lvl);

        for ($BuildLevel = $BuildStartLvl; $BuildLevel < $BuildStartLvl + 15; ++$BuildLevel) {
            $Prod = $this->productionService->maxStorable($BuildLevel);

            $bloc['build_lvl'] = ($current_built_lvl == $BuildLevel) ? '<font color="#ff0000">' . $BuildLevel . '</font>' : $BuildLevel;
            $bloc['build_prod'] = $this->formatService->prettyNumber((int) $Prod);
            $bloc['build_prod_diff'] = $this->formatService->colorNumber($Prod - $ActualProd, $this->formatService->prettyNumber($Prod - $ActualProd));

            if ($ProdFirst == 0) {
                $ProdFirst = floor($Prod);
            }

            $Table .= Template::render($template, $bloc);
        }

        return $Table;
    }

    /**
     * method astrophysics_table
     * param
     * return builds the astrophysics table
     */
    private function astrophysics_table($template)
    {
        $current_built_lvl = $this->user[$this->_resource[$this->_element_id]];
        $BuildStartLvl = max(1, $current_built_lvl - 2);
        $Table = '';

        for ($BuildLevel = $BuildStartLvl; $BuildLevel < $BuildStartLvl + 15; ++$BuildLevel) {
            $bloc['tech_lvl'] = ($current_built_lvl == $BuildLevel) ? '<font color="#ff0000">' . $BuildLevel . '</font>' : $BuildLevel;
            $bloc['tech_colonies'] = $this->formatService->prettyNumber((int) $this->fleetsService->getMaxColonies((int) $BuildLevel));
            $bloc['tech_expeditions'] = $this->formatService->prettyNumber((int) $this->fleetsService->getMaxExpeditions((int) $BuildLevel));

            $Table .= Template::render($template, $bloc);
        }

        return $Table;
    }

    /**
     * @param $CurMoon
     *
     * @return mixed
     */
    private function GetNextJumpWaitTime($CurMoon)
    {
        $JumpGateLevel = $CurMoon[$this->_resource[43]];
        $LastJumpTime = $CurMoon['planet_last_jump_time'];
        if ($JumpGateLevel > 0) {
            $WaitBetweenJmp = (60 * 60) * (1 / $JumpGateLevel);
            $NextJumpTime = $LastJumpTime + $WaitBetweenJmp;
            if ($NextJumpTime >= time()) {
                $RestWait = $NextJumpTime - time();
                $RestString = ' ' . $this->formatService->prettyTime($RestWait);
            } else {
                $RestWait = 0;
                $RestString = '';
            }
        } else {
            $RestWait = 0;
            $RestString = '';
        }
        $RetValue['string'] = $RestString;
        $RetValue['value'] = $RestWait;

        return $RetValue;
    }

    /**
     * doFleetJump
     *
     * @return string
     */
    private function doFleetJump()
    {
        if ($_POST) {
            $RestString = $this->GetNextJumpWaitTime($this->planet);
            $NextJumpTime = $RestString['value'];
            $JumpTime = time();

            if ($NextJumpTime == 0) {
                $TargetPlanet = isset($_POST['jmpto']) ? $_POST['jmpto'] : '';

                if (!is_int($TargetPlanet)) {
                    $RetMessage = __('game/infos.in_jump_gate_error_data');
                }

                $gateRow = DB::selectOne(
                    $this->prepareSql(
                        'SELECT
                            p.`planet_id`,
                            b.`building_jump_gate`,
                            p.`planet_last_jump_time`
                        FROM `' . PLANETS . '` AS p
                        INNER JOIN `' . BUILDINGS . "` AS b
                            ON b.`building_planet_id` = p.`planet_id`
                        WHERE p.`planet_id` = '" . $TargetPlanet . "';"
                    )
                );
                $TargetGate = $gateRow !== null ? (array) $gateRow : [];

                if ($TargetGate['building_jump_gate'] > 0) {
                    $RestString = $this->GetNextJumpWaitTime($TargetGate);
                    $NextDestTime = $RestString['value'];

                    if ($NextDestTime == 0) {
                        $ShipArray = [];
                        $SubQueryOri = '';
                        $SubQueryDes = '';

                        for ($Ship = 200; $Ship < 300; $Ship++) {
                            $ShipLabel = 'c' . $Ship;
                            $gemi_kontrol = isset($_POST[$ShipLabel]) ? $_POST[$ShipLabel] : null;

                            if (is_numeric($gemi_kontrol)) {
                                if ($gemi_kontrol > $this->planet[$this->_resource[$Ship]]) {
                                    $ShipArray[$Ship] = $this->planet[$this->_resource[$Ship]];
                                } else {
                                    $ShipArray[$Ship] = $gemi_kontrol;
                                }

                                if ($ShipArray[$Ship] > 0) {
                                    $SubQueryOri .= '`' . $this->_resource[$Ship] . '` = `' . $this->_resource[$Ship] . "` - '" . $ShipArray[$Ship] . "', ";
                                    $SubQueryDes .= '`' . $this->_resource[$Ship] . '` = `' . $this->_resource[$Ship] . "` + '" . $ShipArray[$Ship] . "', ";
                                }
                            }
                        }
                        if ($SubQueryOri != '') {
                            DB::transaction(function () use ($SubQueryOri, $SubQueryDes, $JumpTime, $TargetGate): void {
                                DB::statement(
                                    $this->prepareSql(
                                        'UPDATE `' . PLANETS . '`, `' . USERS . '`, `' . SHIPS . "` SET
                                            $SubQueryOri
                                            `planet_last_jump_time` = '" . $JumpTime . "',
                                            `current_planet` = '" . $TargetGate['planet_id'] . "'
                                        WHERE `planet_id` = '" . $this->planet['planet_id'] . "'
                                            AND `ship_planet_id` = '" . $this->planet['planet_id'] . "'
                                            AND `id` = '" . $this->user['id'] . "';"
                                    )
                                );

                                DB::statement(
                                    $this->prepareSql(
                                        'UPDATE `' . PLANETS . '`, `' . SHIPS . "` SET
                                        $SubQueryDes
                                        `planet_last_jump_time` = '" . $JumpTime . "'
                                        WHERE `planet_id` = '" . $TargetGate['planet_id'] . "'
                                            AND `ship_planet_id` = '" . $TargetGate['planet_id'] . "';"
                                    )
                                );
                            });

                            $this->planet['planet_last_jump_time'] = $JumpTime;

                            $RestString = $this->GetNextJumpWaitTime($this->planet);
                            $RetMessage = __('game/infos.in_jump_gate_done') . $RestString['string'];
                        } else {
                            $RetMessage = __('game/infos.in_jump_gate_error_data');
                        }
                    } else {
                        $RetMessage = __('game/infos.in_jump_gate_not_ready_target') . $RestString['string'];
                    }
                } else {
                    $RetMessage = __('game/infos.in_jump_gate_doesnt_have_one');
                }
            } else {
                $RetMessage = __('game/infos.in_jump_gate_already_used') . $RestString['string'];
            }
        } else {
            $RetMessage = __('game/infos.in_jump_gate_error_data');
        }

        return $RetMessage;
    }

    private function BuildFleetListRows(): string
    {
        $RowsTPL = 'infos/info_gate_rows';
        $CurrIdx = 1;
        $Result = '';
        for ($Ship = 200; $Ship < 250; $Ship++) {
            if (isset($this->_resource[$Ship]) && $this->_resource[$Ship] != '') {
                if ($this->planet[$this->_resource[$Ship]] > 0) {
                    $bloc['idx'] = $CurrIdx;
                    $bloc['fleet_id'] = $Ship;
                    $bloc['fleet_name'] = __('game/ships.' . $this->_resource[$Ship]);
                    $bloc['fleet_max'] = $this->formatService->prettyNumber((int) $this->planet[$this->_resource[$Ship]]);
                    $bloc['gate_ship_dispo'] = __('game/infos.in_jump_gate_available');
                    $Result .= Template::render($RowsTPL, $bloc);
                    $CurrIdx++;
                }
            }
        }
        return $Result;
    }

    private function BuildJumpableMoonCombo(): string
    {
        $MoonList = array_map(
            fn ($row) => (array) $row,
            DB::select(
                $this->prepareSql(
                    'SELECT
                        m.`planet_id`,
                        m.`planet_galaxy`,
                        m.`planet_system`,
                        m.`planet_planet`,
                        m.`planet_name`,
                        m.`planet_last_jump_time`,
                        b.`building_jump_gate`
                    FROM `' . PLANETS . '` AS m
                    INNER JOIN `' . BUILDINGS . "` AS b ON b.building_planet_id = m.planet_id
                    WHERE m.`planet_type` = '3'
                        AND m.`planet_user_id` = '" . $this->user['id'] . "';"
                )
            )
        );

        $Combo = '';

        foreach ($MoonList as $CurMoon) {
            if ($CurMoon['planet_id'] != $this->planet['planet_id']) {
                $RestString = $this->GetNextJumpWaitTime($CurMoon);
                if ($CurMoon[$this->_resource[43]] >= 1) {
                    $Combo .= '<option value="' . $CurMoon['planet_id'] . '">[' . $CurMoon['planet_galaxy'] . ':' . $CurMoon['planet_system'] . ':' . $CurMoon['planet_planet'] . '] ' . $CurMoon['planet_name'] . $RestString['string'] . "</option>\n";
                }
            }
        }
        return $Combo;
    }

    /**
     * @param $Template
     *
     * @return mixed
     */
    private function phalanxRange($Template)
    {
        $current_built_lvl = $this->planet[$this->_resource[$this->_element_id]];
        $BuildLevel = ($current_built_lvl > 0) ? $current_built_lvl : 1;
        $BuildStartLvl = $current_built_lvl - 2;

        if ($BuildStartLvl < 1) {
            $BuildStartLvl = 1;
        }

        $Table = '';

        for ($BuildLevel = $BuildStartLvl; $BuildLevel < $BuildStartLvl + 15; $BuildLevel++) {
            $bloc['build_lvl'] = ($current_built_lvl == $BuildLevel) ? '<font color="#ff0000">' . $BuildLevel . '</font>' : $BuildLevel;
            $bloc['build_range'] = ($BuildLevel * $BuildLevel) - 1;

            $Table .= Template::render($Template, $bloc);
        }

        return $Table;
    }

    /**
     * @param $Template
     *
     * @return mixed
     */
    private function showProductionTable($Template)
    {
        $BuildLevelFactor = $this->planet['planet_' . $this->_resource[$this->_element_id] . '_percent'];
        $BuildTemp = $this->planet['planet_temp_max'];
        $current_built_lvl = $this->planet[$this->_resource[$this->_element_id]];
        $BuildLevel = ($current_built_lvl > 0) ? $current_built_lvl : 1;
        $BuildEnergy = $this->user['research_energy_technology'];
        $game_resource_multiplier = app(SettingsService::class)->getInt('resource_multiplier');

        // BOOST
        $geologe_boost = 1 + (1 * ($this->officerService->isOfficerActive((int) $this->user['premium_officier_geologist'], time()) ? GEOLOGUE : 0));
        $engineer_boost = 1 + (1 * ($this->officerService->isOfficerActive((int) $this->user['premium_officier_engineer'], time()) ? ENGINEER_ENERGY : 0));

        // PRODUCTION FORMULAS
        $metal_prod = ($this->_prod_grid[$this->_element_id]['formule']['metal'])($BuildLevel, $BuildLevelFactor, $BuildTemp, $BuildEnergy);
        $crystal_prod = ($this->_prod_grid[$this->_element_id]['formule']['crystal'])($BuildLevel, $BuildLevelFactor, $BuildTemp, $BuildEnergy);
        $deuterium_prod = ($this->_prod_grid[$this->_element_id]['formule']['deuterium'])($BuildLevel, $BuildLevelFactor, $BuildTemp, $BuildEnergy);
        $energy_prod = ($this->_prod_grid[$this->_element_id]['formule']['energy'])($BuildLevel, $BuildLevelFactor, $BuildTemp, $BuildEnergy);

        // PRODUCTION
        $Prod[1] = $this->productionService->productionAmount($metal_prod, $geologe_boost, $game_resource_multiplier);
        $Prod[2] = $this->productionService->productionAmount($crystal_prod, $geologe_boost, $game_resource_multiplier);
        $Prod[3] = $this->productionService->productionAmount($deuterium_prod, $geologe_boost, $game_resource_multiplier);

        if ($this->_element_id >= 4) {
            $Prod[4] = $this->productionService->productionAmount($energy_prod, $engineer_boost, 0, true);
            $ActualProd = floor($Prod[4]);
        } else {
            $Prod[4] = $this->productionService->productionAmount($energy_prod, 1, 0, true);
            $ActualProd = floor($Prod[$this->_element_id]);
        }

        if ($this->_element_id != 12) {
            $ActualNeed = floor($Prod[4]);
        } else {
            $ActualNeed = floor($Prod[3]);
        }

        $BuildStartLvl = $current_built_lvl - 2;
        if ($BuildStartLvl < 1) {
            $BuildStartLvl = 1;
        }

        $Table = '';
        $ProdFirst = 0;

        for ($BuildLevel = $BuildStartLvl; $BuildLevel < $BuildStartLvl + 15; $BuildLevel++) {
            // PRODUCTION FORMULAS
            $metal_prod = ($this->_prod_grid[$this->_element_id]['formule']['metal'])($BuildLevel, $BuildLevelFactor, $BuildTemp, $BuildEnergy);
            $crystal_prod = ($this->_prod_grid[$this->_element_id]['formule']['crystal'])($BuildLevel, $BuildLevelFactor, $BuildTemp, $BuildEnergy);
            $deuterium_prod = ($this->_prod_grid[$this->_element_id]['formule']['deuterium'])($BuildLevel, $BuildLevelFactor, $BuildTemp, $BuildEnergy);
            $energy_prod = ($this->_prod_grid[$this->_element_id]['formule']['energy'])($BuildLevel, $BuildLevelFactor, $BuildTemp, $BuildEnergy);

            // PRODUCTION
            $Prod[1] = $this->productionService->productionAmount($metal_prod, $geologe_boost, $game_resource_multiplier);
            $Prod[2] = $this->productionService->productionAmount($crystal_prod, $geologe_boost, $game_resource_multiplier);
            $Prod[3] = $this->productionService->productionAmount($deuterium_prod, $geologe_boost, $game_resource_multiplier);

            if ($this->_element_id >= 4) {
                $Prod[4] = $this->productionService->productionAmount($energy_prod, $engineer_boost, 0, true);
            } else {
                $Prod[4] = $this->productionService->productionAmount($energy_prod, 1, 0, true);
            }

            $bloc['build_lvl'] = ($current_built_lvl == $BuildLevel) ? $this->formatService->colorRed($BuildLevel) : $BuildLevel;

            if ($ProdFirst > 0) {
                if ($this->_element_id != 12) {
                    $level_diff = $this->formatService->prettyNumber(floor($Prod[$this->_element_id] - $ProdFirst));
                } else {
                    $level_diff = $this->formatService->prettyNumber(floor($Prod[4] - $ProdFirst));
                }
            } else {
                $level_diff = 0;

                if ($current_built_lvl == 0) {
                    $level_diff = $Prod[3];

                    if ($this->_element_id >= 4) {
                        $level_diff = $Prod[4];
                    }
                }
            }

            if ($this->_element_id != 12) {
                $prod_diff = floor($Prod[$this->_element_id] - $ActualProd);

                if ($current_built_lvl == 0) {
                    $prod_diff = $Prod[3];

                    if ($this->_element_id >= 4) {
                        $prod_diff = $Prod[4];
                    }
                }

                $bloc['build_prod'] = $this->formatService->prettyNumber(floor($Prod[$this->_element_id]));
                $bloc['build_prod_diff'] = $this->formatService->colorNumber($prod_diff, $this->formatService->prettyNumber((int) $prod_diff));
                $bloc['build_level_diff'] = $this->formatService->colorGreen($level_diff);
                $bloc['build_need'] = $this->formatService->colorNumber(floor($Prod[4]), $this->formatService->prettyNumber(floor($Prod[4])));
                $bloc['build_need_diff'] = $this->formatService->colorNumber(floor($Prod[4] - $ActualNeed), $this->formatService->prettyNumber(floor($Prod[4] - $ActualNeed)));
            } else {
                $prod_diff = floor($Prod[4] - $ActualProd);
                $need_diff = floor($Prod[3] - $ActualNeed);

                if ($current_built_lvl == 0) {
                    $prod_diff = $Prod[4];
                    $need_diff = $Prod[3];
                }

                $bloc['build_prod'] = $this->formatService->prettyNumber(floor($Prod[4]));
                $bloc['build_prod_diff'] = $this->formatService->colorNumber($prod_diff, $this->formatService->prettyNumber((int) $prod_diff));
                $bloc['build_level_diff'] = $this->formatService->colorGreen($level_diff);
                $bloc['build_need'] = $this->formatService->colorNumber(floor($Prod[3]), $this->formatService->prettyNumber(floor($Prod[3])));
                $bloc['build_need_diff'] = $this->formatService->colorNumber($need_diff, $this->formatService->prettyNumber((int) $need_diff));
            }

            if ($this->_element_id != 12) {
                $ProdFirst = floor($Prod[$this->_element_id]);
            } else {
                $ProdFirst = floor($Prod[4]);
            }

            $Table .= Template::render($Template, $bloc);
        }

        return $Table;
    }

    /**
     * @return mixed
     */
    private function ShowRapidFireTo()
    {
        $ResultString = '';
        for ($Type = 200; $Type < 500; $Type++) {
            if (isset($this->_combat_caps[$this->_element_id]['sd'][$Type]) && $this->_combat_caps[$this->_element_id]['sd'][$Type] > 1) {
                $ResultString .= __('game/infos.in_rf_again') . ' ' . $this->langs->language[$this->_resource[$Type]] . ' <font color="#00ff00">' . $this->_combat_caps[$this->_element_id]['sd'][$Type] . '</font><br>';
            }
        }
        return $ResultString;
    }

    /**
     * @return mixed
     */
    private function ShowRapidFireFrom()
    {
        $ResultString = '';
        for ($Type = 200; $Type < 500; $Type++) {
            if (isset($this->_combat_caps[$Type]['sd'][$this->_element_id]) && $this->_combat_caps[$Type]['sd'][$this->_element_id] > 1) {
                $ResultString .= __('game/infos.in_rf_from') . ' ' . $this->langs->language[$this->_resource[$Type]] . ' <font color="#ff0000">' . $this->_combat_caps[$Type]['sd'][$this->_element_id] . '</font><br>';
            }
        }
        return $ResultString;
    }

    /**
     * @param $current_planet
     */
    private function planet_link($current_planet)
    {
        return '<a href="game.php?page=galaxy&mode=3&galaxy=' . $current_planet['planet_galaxy'] . '&system=' . $current_planet['planet_system'] . '">[' . $current_planet['planet_galaxy'] . ':' . $current_planet['planet_system'] . ':' . $current_planet['planet_planet'] . ']</a>';
    }

    /**
     * Build the tear down block
     *
     * @return string
     */
    private function buildTearDownBlock(): string
    {
        $page = '';

        if (isset($this->planet[$this->_resource[$this->_element_id]]) && $this->planet[$this->_resource[$this->_element_id]] > 0) {
            // calculate bonus
            $tech_bonus = '';
            $ion_tech_percentage = Formulas::getIonTechnologyBonus(
                (int) $this->user[$this->_resource[Research::research_ionic_technology]]
            ) * 100;

            if ($ion_tech_percentage > 0) {
                $tech_bonus = StringsHelper::parseReplacements(
                    __('game/infos.in_ion_tech_bonus'),
                    [$this->formatService->colorGreen('-' . $ion_tech_percentage . '%')]
                );
            }

            // resources and time
            $tear_down_resources = $this->developmentsService->developmentPrice(
                $this->_element_id,
                (int) $this->planet[$this->_resource[$this->_element_id]],
                true,
                true,
                (int) $this->user[$this->_resource[Research::research_ionic_technology]]
            );
            $tear_down_time = $this->developmentsService->tearDownTime(
                $this->_element_id,
                (int) $this->planet[$this->_resource[$this->_element_id]],
                (int) $this->planet[$this->_resource[Buildings::BUILDING_ROBOT_FACTORY]],
                (int) $this->planet[$this->_resource[Buildings::BUILDING_NANO_FACTORY]]
            );

            $tear_down_url = 'game.php?page=' . $this->developmentsService->setBuildingPage($this->_element_id) . '&cmd=destroy&building=' . $this->_element_id;

            $page .= Template::render(
                'infos/info_buildings_destroy',
                [
                    'tear_down_url' => UrlHelper::setUrl(
                        $tear_down_url,
                        StringsHelper::parseReplacements(
                            __('game/infos.in_destroy'),
                            [$this->langs->language[$this->_resource[$this->_element_id]]]
                        )
                    ),
                    'ion_tech_bonus' => $tech_bonus,
                    'nfo_metal' => $this->formatService->prettyNumber((int) $tear_down_resources['metal']),
                    'nfo_crystal' => $this->formatService->prettyNumber((int) $tear_down_resources['crystal']),
                    'nfo_deuterium' => $this->formatService->prettyNumber((int) $tear_down_resources['deuterium']),
                    'destroytime' => $this->formatService->prettyTime($tear_down_time),
                ]
            );
        }

        return $page;
    }
}
