<?php

declare(strict_types=1);

namespace App\Services\Game;

use App\Services\FormatService;
use App\Services\Game\Formulas\DevelopmentsService;
use App\Services\Game\Formulas\FleetsService;
use App\Services\Game\Formulas\OfficerService;
use App\Services\Game\Formulas\ProductionService;
use App\Services\SettingsService;
use Illuminate\Support\Facades\DB;
use Xgp\App\Core\Concerns\PreparesLegacySql;
use Xgp\App\Core\Enumerators\BuildingsEnumerator as Buildings;
use Xgp\App\Core\Enumerators\ResearchEnumerator as Research;
use Xgp\App\Core\Objects;
use Xgp\App\Helpers\StringsHelper;
use Xgp\App\Libraries\Formulas;
use Xgp\App\Libraries\Functions;
use Xgp\App\Libraries\Users;

/**
 * @SuppressWarnings("PHPMD.CouplingBetweenObjects")
 * @SuppressWarnings("PHPMD.TooManyMethods")
 */
class TechnologyInfoService
{
    use PreparesLegacySql;

    /** @var array<string, mixed> */
    private array $user = [];

    /** @var array<string, mixed> */
    private array $planet = [];

    /** @var array<int, string> */
    private array $resource = [];

    /** @var array<int, array<string, mixed>> */
    private array $priceList = [];

    /** @var array<int, array<string, mixed>> */
    private array $combatCaps = [];

    /** @var array<int, array<string, mixed>> */
    private array $productionGrid = [];

    private int $elementId = 0;

    public function __construct(
        private ProductionService $productionService,
        private FormatService $formatService,
        private OfficerService $officerService,
        private DevelopmentsService $developmentsService,
        private FleetsService $fleetsService,
        private SettingsService $settings,
    ) {
    }

    /**
     * @param array<string, mixed>|null $userData
     * @param array<string, mixed>|null $planetData
     *
     * @return array<string, mixed>
     */
    public function buildViewData(int $elementId, ?array $userData = null, ?array $planetData = null): array
    {
        $this->initializeContext($elementId, $userData, $planetData);

        if (!array_key_exists($this->elementId, $this->resource)) {
            Functions::redirect('game.php?page=technologytree');
        }

        return [
            'summary' => $this->buildSummarySection(),
            'detailTable' => $this->buildDetailTable(),
            'jumpGate' => $this->buildJumpGateSection(),
            'tearDown' => $this->buildTearDownSection(),
        ];
    }

    /**
     * @param array<int, int> $ships
     * @param array<string, mixed>|null $userData
     * @param array<string, mixed>|null $planetData
     *
     * @return array{message: string, color: string}
     */
    public function handleJumpGate(int $elementId, int $targetPlanet, array $ships, ?array $userData = null, ?array $planetData = null): array
    {
        $this->initializeContext($elementId, $userData, $planetData);
        $result = $this->processJumpGate($targetPlanet, $ships);

        return [
            'message' => $result['message'],
            'color' => $result['success'] ? 'lime' : 'red',
        ];
    }

    /**
     * @param array<string, mixed>|null $userData
     * @param array<string, mixed>|null $planetData
     */
    private function initializeContext(int $elementId, ?array $userData, ?array $planetData): void
    {
        $this->user = $userData ?? Users::getInstance()->getUserData();
        $this->planet = $planetData ?? Users::getInstance()->getPlanetData();

        $objects = Objects::getInstance();
        $this->resource = $objects->getObjects();
        $this->priceList = $objects->getPrice();
        $this->combatCaps = $objects->getCombatSpecs();
        $this->productionGrid = $objects->getProduction();
        $this->elementId = $elementId;
    }

    /**
     * @return array<string, mixed>
     */
    private function buildSummarySection(): array
    {
        if ($this->elementId >= 202 && $this->elementId <= 250) {
            return $this->buildCombatSummary(true);
        }

        if ($this->elementId >= 401 && $this->elementId <= 550) {
            return $this->buildCombatSummary(false);
        }

        return [
            'type' => 'generic',
            'title' => $this->getElementName($this->elementId),
            'imageId' => $this->elementId,
            'description' => $this->getElementDescription($this->elementId),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildCombatSummary(bool $isShip): array
    {
        $rapidFireItems = [];

        if ($isShip) {
            $rapidFireItems = array_merge($this->buildRapidFireFromItems(), $this->buildRapidFireAgainstItems());
        } elseif ($this->elementId < 500) {
            $rapidFireItems = array_merge($this->buildRapidFireAgainstItems(), $this->buildRapidFireFromItems());
        }

        $stats = [
            [
                'label' => __('game/technologydetails.in_struct_pt'),
                'value' => $this->formatService->prettyNumber(($this->priceList[$this->elementId]['metal'] ?? 0) + ($this->priceList[$this->elementId]['crystal'] ?? 0)),
            ],
            [
                'label' => __('game/technologydetails.in_shield_pt'),
                'value' => $this->formatService->prettyNumber((int) ($this->combatCaps[$this->elementId]['shield'] ?? 0)),
            ],
            [
                'label' => __('game/technologydetails.in_attack_pt'),
                'value' => $this->formatService->prettyNumber((int) ($this->combatCaps[$this->elementId]['attack'] ?? 0)),
            ],
        ];

        if ($isShip) {
            $updatedSpeed = '';
            $updatedConsumption = '';

            if ($this->elementId === 202) {
                $updatedSpeed = ' <font color="yellow">(' . $this->formatService->prettyNumber((int) ($this->priceList[$this->elementId]['speed2'] ?? 0)) . ')</font>';
                $updatedConsumption = ' <font color="yellow">(' . $this->formatService->prettyNumber((int) ($this->priceList[$this->elementId]['consumption2'] ?? 0)) . ')</font>';
            } elseif ($this->elementId === 211) {
                $updatedSpeed = ' <font color="yellow">(' . $this->formatService->prettyNumber((int) ($this->priceList[$this->elementId]['speed2'] ?? 0)) . ')</font>';
            }

            $stats[] = [
                'label' => __('game/technologydetails.in_base_speed'),
                'value' => $this->formatService->prettyNumber((int) ($this->priceList[$this->elementId]['speed'] ?? 0)) . $updatedSpeed,
            ];
            $stats[] = [
                'label' => __('game/technologydetails.in_capacity'),
                'value' => $this->formatService->prettyNumber((int) ($this->priceList[$this->elementId]['capacity'] ?? 0)) . '&nbsp;' . __('game/technologydetails.in_units'),
            ];
            $stats[] = [
                'label' => __('game/technologydetails.in_consumption'),
                'value' => $this->formatService->prettyNumber((int) ($this->priceList[$this->elementId]['consumption'] ?? 0)) . $updatedConsumption,
            ];
        }

        return [
            'type' => 'combat',
            'title' => __('game/technologydetails.in_title_head') . ' ' . ($isShip ? __('game/ships.ships') : __('game/defenses.defenses')),
            'nameLabel' => __('game/technologydetails.in_name'),
            'name' => $this->getElementName($this->elementId),
            'imageId' => $this->elementId,
            'description' => $this->getElementDescription($this->elementId),
            'rapidFireItems' => $rapidFireItems,
            'stats' => $stats,
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function buildDetailTable(): ?array
    {
        if ($this->elementId >= 1 && $this->elementId <= 3) {
            return [
                'headers' => [
                    __('game/global.level'),
                    __('game/technologydetails.in_prod_p_hour'),
                    __('game/technologydetails.in_difference'),
                    __('game/technologydetails.in_lvl_difference'),
                    __('game/technologydetails.in_energy_balance'),
                    __('game/technologydetails.in_difference'),
                ],
                'rows' => $this->buildProductionRows(false),
                'footerRows' => [],
            ];
        }

        if ($this->elementId === 4) {
            return [
                'headers' => [
                    __('game/global.level'),
                    __('game/technologydetails.in_energy_balance'),
                    __('game/technologydetails.in_difference'),
                    __('game/technologydetails.in_lvl_difference'),
                ],
                'rows' => $this->buildProductionRows(true),
                'footerRows' => [],
            ];
        }

        if ($this->elementId >= 22 && $this->elementId <= 24) {
            return [
                'headers' => [
                    __('game/global.level'),
                    __('game/technologydetails.in_storage_capacity'),
                    __('game/technologydetails.in_difference'),
                ],
                'rows' => $this->buildStorageRows(),
                'footerRows' => [],
            ];
        }

        if ($this->elementId === 12) {
            return [
                'headers' => [
                    __('game/global.level'),
                    __('game/technologydetails.in_energy_balance'),
                    __('game/technologydetails.in_difference'),
                    __('game/technologydetails.in_lvl_difference'),
                    __('game/technologydetails.in_used_deuter'),
                    __('game/technologydetails.in_difference'),
                ],
                'rows' => $this->buildProductionRows(false),
                'footerRows' => [],
            ];
        }

        if ($this->elementId === 42) {
            return [
                'headers' => [
                    __('game/global.level'),
                    __('game/technologydetails.in_range'),
                ],
                'rows' => $this->buildRangeRows(),
                'footerRows' => [],
            ];
        }

        if ($this->elementId === 124) {
            return [
                'headers' => [
                    __('game/global.level'),
                    __('game/technologydetails.in_max_colonies'),
                    __('game/technologydetails.in_max_expeditions'),
                ],
                'rows' => $this->buildAstrophysicsRows(),
                'footerRows' => [
                    __('game/technologydetails.in_astrophysics_first'),
                    __('game/technologydetails.in_astrophysics_second'),
                    __('game/technologydetails.in_astrophysics_third'),
                ],
            ];
        }

        return null;
    }

    /**
     * @return array<int, array<int, string|int>>
     */
    private function buildStorageRows(): array
    {
        $currentBuiltLevel = (int) ($this->planet[$this->resource[$this->elementId]] ?? 0);
        $buildStartLevel = max(1, $currentBuiltLevel - 2);
        $actualProduction = $this->productionService->maxStorable($currentBuiltLevel);
        $rows = [];

        for ($buildLevel = $buildStartLevel; $buildLevel < $buildStartLevel + 15; ++$buildLevel) {
            $production = $this->productionService->maxStorable($buildLevel);

            $rows[] = [
                $currentBuiltLevel === $buildLevel ? '<font color="#ff0000">' . $buildLevel . '</font>' : (string) $buildLevel,
                $this->formatService->prettyNumber((int) $production),
                $this->formatService->colorNumber($production - $actualProduction, $this->formatService->prettyNumber($production - $actualProduction)),
            ];
        }

        return $rows;
    }

    /**
     * @return array<int, array<int, string|int>>
     */
    private function buildAstrophysicsRows(): array
    {
        $currentBuiltLevel = (int) ($this->user[$this->resource[$this->elementId]] ?? 0);
        $buildStartLevel = max(1, $currentBuiltLevel - 2);
        $rows = [];

        for ($buildLevel = $buildStartLevel; $buildLevel < $buildStartLevel + 15; ++$buildLevel) {
            $rows[] = [
                $currentBuiltLevel === $buildLevel ? '<font color="#ff0000">' . $buildLevel . '</font>' : (string) $buildLevel,
                $this->formatService->prettyNumber((int) $this->fleetsService->getMaxColonies($buildLevel)),
                $this->formatService->prettyNumber((int) $this->fleetsService->getMaxExpeditions($buildLevel)),
            ];
        }

        return $rows;
    }

    /**
     * @return array<int, array<int, string|int>>
     */
    private function buildRangeRows(): array
    {
        $currentBuiltLevel = (int) ($this->planet[$this->resource[$this->elementId]] ?? 0);
        $buildStartLevel = max(1, $currentBuiltLevel - 2);
        $rows = [];

        for ($buildLevel = $buildStartLevel; $buildLevel < $buildStartLevel + 15; $buildLevel++) {
            $rows[] = [
                $currentBuiltLevel === $buildLevel ? '<font color="#ff0000">' . $buildLevel . '</font>' : (string) $buildLevel,
                (string) (($buildLevel * $buildLevel) - 1),
            ];
        }

        return $rows;
    }

    /**
     * @return array<int, array<int, string|int>>
     */
    private function buildProductionRows(bool $simple): array
    {
        $resourceKey = $this->resource[$this->elementId];
        $buildLevelFactor = (int) ($this->planet['planet_' . $resourceKey . '_percent'] ?? 0);
        $buildTemp = (int) ($this->planet['planet_temp_max'] ?? 0);
        $currentBuiltLevel = (int) ($this->planet[$resourceKey] ?? 0);
        $effectiveLevel = $currentBuiltLevel > 0 ? $currentBuiltLevel : 1;
        $buildEnergy = (int) ($this->user['research_energy_technology'] ?? 0);
        $resourceMultiplier = $this->settings->getInt('resource_multiplier');

        $geologistBoost = 1 + (1 * ($this->officerService->isOfficerActive((int) ($this->user['premium_officier_geologist'] ?? 0), time()) ? GEOLOGUE : 0));
        $engineerBoost = 1 + (1 * ($this->officerService->isOfficerActive((int) ($this->user['premium_officier_engineer'] ?? 0), time()) ? ENGINEER_ENERGY : 0));

        $baseline = $this->productionValues($effectiveLevel, $buildLevelFactor, $buildTemp, $buildEnergy, $resourceMultiplier, $geologistBoost, $engineerBoost);
        $actualProduction = $this->elementId >= 4 ? floor($baseline[4]) : floor($baseline[$this->elementId]);
        $actualNeed = $this->elementId !== 12 ? floor($baseline[4]) : floor($baseline[3]);
        $buildStartLevel = max(1, $currentBuiltLevel - 2);
        $firstProduction = 0;
        $rows = [];

        for ($buildLevel = $buildStartLevel; $buildLevel < $buildStartLevel + 15; $buildLevel++) {
            $production = $this->productionValues($buildLevel, $buildLevelFactor, $buildTemp, $buildEnergy, $resourceMultiplier, $geologistBoost, $engineerBoost);
            $levelLabel = $currentBuiltLevel === $buildLevel ? $this->formatService->colorRed((string) $buildLevel) : (string) $buildLevel;

            if ($firstProduction > 0) {
                $levelDiff = $this->elementId !== 12
                    ? $this->formatService->prettyNumber(floor($production[$this->elementId] - $firstProduction))
                    : $this->formatService->prettyNumber(floor($production[4] - $firstProduction));
            } else {
                $levelDiff = 0;

                if ($currentBuiltLevel === 0) {
                    $levelDiff = $this->elementId >= 4 ? $production[4] : $production[3];
                }
            }

            if ($this->elementId !== 12) {
                $productionDiff = floor($production[$this->elementId] - $actualProduction);

                if ($currentBuiltLevel === 0) {
                    $productionDiff = $this->elementId >= 4 ? $production[4] : $production[3];
                }

                $row = [
                    $levelLabel,
                    $this->formatService->prettyNumber(floor($production[$this->elementId])),
                    $this->formatService->colorNumber($productionDiff, $this->formatService->prettyNumber((int) $productionDiff)),
                    $this->formatService->colorGreen((string) $levelDiff),
                ];

                if (!$simple) {
                    $row[] = $this->formatService->colorNumber(floor($production[4]), $this->formatService->prettyNumber(floor($production[4])));
                    $row[] = $this->formatService->colorNumber(floor($production[4] - $actualNeed), $this->formatService->prettyNumber(floor($production[4] - $actualNeed)));
                }

                $rows[] = $row;
            } else {
                $productionDiff = floor($production[4] - $actualProduction);
                $needDiff = floor($production[3] - $actualNeed);

                if ($currentBuiltLevel === 0) {
                    $productionDiff = $production[4];
                    $needDiff = $production[3];
                }

                $rows[] = [
                    $levelLabel,
                    $this->formatService->prettyNumber(floor($production[4])),
                    $this->formatService->colorNumber($productionDiff, $this->formatService->prettyNumber((int) $productionDiff)),
                    $this->formatService->colorGreen((string) $levelDiff),
                    $this->formatService->colorNumber(floor($production[3]), $this->formatService->prettyNumber(floor($production[3]))),
                    $this->formatService->colorNumber($needDiff, $this->formatService->prettyNumber((int) $needDiff)),
                ];
            }

            $firstProduction = $this->elementId !== 12 ? floor($production[$this->elementId]) : floor($production[4]);
        }

        return $rows;
    }

    /**
     * @return array{1: float|int, 2: float|int, 3: float|int, 4: float|int}
     */
    private function productionValues(int $buildLevel, int $buildLevelFactor, int $buildTemp, int $buildEnergy, int $resourceMultiplier, float | int $geologistBoost, float | int $engineerBoost): array
    {
        $metalProduction = ($this->productionGrid[$this->elementId]['formule']['metal'])($buildLevel, $buildLevelFactor, $buildTemp, $buildEnergy);
        $crystalProduction = ($this->productionGrid[$this->elementId]['formule']['crystal'])($buildLevel, $buildLevelFactor, $buildTemp, $buildEnergy);
        $deuteriumProduction = ($this->productionGrid[$this->elementId]['formule']['deuterium'])($buildLevel, $buildLevelFactor, $buildTemp, $buildEnergy);
        $energyProduction = ($this->productionGrid[$this->elementId]['formule']['energy'])($buildLevel, $buildLevelFactor, $buildTemp, $buildEnergy);

        return [
            1 => $this->productionService->productionAmount($metalProduction, $geologistBoost, $resourceMultiplier),
            2 => $this->productionService->productionAmount($crystalProduction, $geologistBoost, $resourceMultiplier),
            3 => $this->productionService->productionAmount($deuteriumProduction, $geologistBoost, $resourceMultiplier),
            4 => $this->productionService->productionAmount(
                $energyProduction,
                $this->elementId >= 4 ? $engineerBoost : 1,
                0,
                true
            ),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function buildJumpGateSection(): ?array
    {
        if ($this->elementId !== 43 || (int) ($this->planet[$this->resource[43]] ?? 0) <= 0) {
            return null;
        }

        $restString = $this->getNextJumpWaitTime($this->planet);

        return [
            'action' => 'game.php?page=technologydetails&technology=' . $this->elementId,
            'startMoonLink' => $this->planetLink($this->planet),
            'destinations' => $this->buildJumpGateDestinations(),
            'ships' => $this->buildJumpGateShips(),
            'countdownScriptHead' => $restString['value'] !== 0 ? Functions::chronoApplet('Gate', '1', $restString['value'], true) : '',
            'waitTimeHtml' => $restString['value'] !== 0 ? '<div id="bxxGate1"></div>' : '',
            'countdownScriptTail' => $restString['value'] !== 0 ? Functions::chronoApplet('Gate', '1', $restString['value'], false) : '',
        ];
    }

    /**
     * @return array<int, array{id: int, label: string}>
     */
    private function buildJumpGateDestinations(): array
    {
        $moonList = array_map(
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
                        AND m.`planet_user_id` = '" . ((int) $this->user['id']) . "';"
                )
            )
        );

        $destinations = [];

        foreach ($moonList as $currentMoon) {
            if ((int) ($currentMoon['planet_id'] ?? 0) === (int) ($this->planet['planet_id'] ?? 0)) {
                continue;
            }

            if ((int) ($currentMoon[$this->resource[43]] ?? 0) < 1) {
                continue;
            }

            $restString = $this->getNextJumpWaitTime($currentMoon);
            $destinations[] = [
                'id' => (int) $currentMoon['planet_id'],
                'label' => '[' . $currentMoon['planet_galaxy'] . ':' . $currentMoon['planet_system'] . ':' . $currentMoon['planet_planet'] . '] ' . $currentMoon['planet_name'] . $restString['string'],
            ];
        }

        return $destinations;
    }

    /**
     * @return array<int, array{id: int, name: string, max: string, availability: string, tabIndex: int}>
     */
    private function buildJumpGateShips(): array
    {
        $ships = [];
        $currentIndex = 1;

        for ($ship = 200; $ship < 250; $ship++) {
            $resourceKey = $this->resource[$ship] ?? '';

            if ($resourceKey === '' || (int) ($this->planet[$resourceKey] ?? 0) <= 0) {
                continue;
            }

            $ships[] = [
                'id' => $ship,
                'name' => (string) __('game/ships.' . $resourceKey),
                'max' => $this->formatService->prettyNumber((int) ($this->planet[$resourceKey] ?? 0)),
                'availability' => __('game/technologydetails.in_jump_gate_available'),
                'tabIndex' => $currentIndex,
            ];
            $currentIndex++;
        }

        return $ships;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function buildTearDownSection(): ?array
    {
        if ($this->elementId >= 100 || in_array($this->elementId, [33, 41], true)) {
            return null;
        }

        $resourceKey = $this->resource[$this->elementId] ?? null;

        if (!is_string($resourceKey) || (int) ($this->planet[$resourceKey] ?? 0) <= 0) {
            return null;
        }

        $techBonus = '';
        $ionTechnologyLevel = (int) ($this->user[$this->resource[Research::research_ionic_technology]] ?? 0);
        $ionTechnologyPercentage = Formulas::getIonTechnologyBonus($ionTechnologyLevel) * 100;

        if ($ionTechnologyPercentage > 0) {
            $techBonus = StringsHelper::parseReplacements(
                __('game/technologydetails.in_ion_tech_bonus'),
                [$this->formatService->colorGreen('-' . $ionTechnologyPercentage . '%')]
            );
        }

        $tearDownResources = $this->developmentsService->developmentPrice(
            $this->elementId,
            (int) ($this->planet[$resourceKey] ?? 0),
            true,
            true,
            $ionTechnologyLevel
        );
        $tearDownTime = $this->developmentsService->tearDownTime(
            $this->elementId,
            (int) ($this->planet[$resourceKey] ?? 0),
            (int) ($this->planet[$this->resource[Buildings::BUILDING_ROBOT_FACTORY]] ?? 0),
            (int) ($this->planet[$this->resource[Buildings::BUILDING_NANO_FACTORY]] ?? 0)
        );

        return [
            'url' => 'game.php?page=' . $this->developmentsService->setBuildingPage($this->elementId) . '&cmd=destroy&building=' . $this->elementId,
            'label' => StringsHelper::parseReplacements(
                __('game/technologydetails.in_destroy'),
                [$this->getElementName($this->elementId)]
            ),
            'ionBonusHtml' => $techBonus,
            'resources' => [
                ['label' => __('game/global.metal'), 'value' => $this->formatService->prettyNumber((int) ($tearDownResources['metal'] ?? 0))],
                ['label' => __('game/global.crystal'), 'value' => $this->formatService->prettyNumber((int) ($tearDownResources['crystal'] ?? 0))],
                ['label' => __('game/global.deuterium'), 'value' => $this->formatService->prettyNumber((int) ($tearDownResources['deuterium'] ?? 0))],
            ],
            'durationLabel' => __('game/technologydetails.in_dest_durati'),
            'duration' => $this->formatService->prettyTime($tearDownTime),
            'costsLabel' => __('game/technologydetails.in_needed'),
        ];
    }

    /**
     * @param array<string, mixed> $moon
     *
     * @return array{string: string, value: int}
     */
    private function getNextJumpWaitTime(array $moon): array
    {
        $jumpGateLevel = (int) ($moon[$this->resource[43]] ?? 0);
        $lastJumpTime = (int) ($moon['planet_last_jump_time'] ?? 0);

        if ($jumpGateLevel <= 0) {
            return ['string' => '', 'value' => 0];
        }

        $waitBetweenJump = (60 * 60) * (1 / $jumpGateLevel);
        $nextJumpTime = $lastJumpTime + $waitBetweenJump;

        if ($nextJumpTime < time()) {
            return ['string' => '', 'value' => 0];
        }

        $restWait = $nextJumpTime - time();

        return [
            'string' => ' ' . $this->formatService->prettyTime($restWait),
            'value' => $restWait,
        ];
    }

    /**
     * @param array<int, int> $ships
     *
     * @return array{success: bool, message: string}
     */
    private function processJumpGate(int $targetPlanet, array $ships): array
    {
        if ($this->elementId !== 43 || (int) ($this->planet[$this->resource[43]] ?? 0) <= 0) {
            return [
                'success' => false,
                'message' => __('game/technologydetails.in_jump_gate_error_data'),
            ];
        }

        $restString = $this->getNextJumpWaitTime($this->planet);

        if ($restString['value'] !== 0) {
            return [
                'success' => false,
                'message' => __('game/technologydetails.in_jump_gate_already_used') . $restString['string'],
            ];
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
                WHERE p.`planet_id` = '" . $targetPlanet . "';"
            )
        );

        if ($gateRow === null) {
            return [
                'success' => false,
                'message' => __('game/technologydetails.in_jump_gate_doesnt_have_one'),
            ];
        }

        /** @var array<string, mixed> $targetGate */
        $targetGate = (array) $gateRow;

        if ((int) ($targetGate['building_jump_gate'] ?? 0) <= 0) {
            return [
                'success' => false,
                'message' => __('game/technologydetails.in_jump_gate_doesnt_have_one'),
            ];
        }

        $restString = $this->getNextJumpWaitTime($targetGate);

        if ($restString['value'] !== 0) {
            return [
                'success' => false,
                'message' => __('game/technologydetails.in_jump_gate_not_ready_target') . $restString['string'],
            ];
        }

        $subQueryOrigin = '';
        $subQueryDestination = '';

        foreach ($ships as $ship => $shipCount) {
            if ($ship < 200 || $ship >= 300) {
                continue;
            }

            $resourceKey = $this->resource[$ship] ?? null;

            if (!is_string($resourceKey) || $resourceKey === '') {
                continue;
            }

            $availableShips = (int) ($this->planet[$resourceKey] ?? 0);
            $shipsToMove = min(max(0, $shipCount), $availableShips);

            if ($shipsToMove <= 0) {
                continue;
            }

            $subQueryOrigin .= '`' . $resourceKey . '` = `' . $resourceKey . "` - '" . $shipsToMove . "', ";
            $subQueryDestination .= '`' . $resourceKey . '` = `' . $resourceKey . "` + '" . $shipsToMove . "', ";
        }

        if ($subQueryOrigin === '') {
            return [
                'success' => false,
                'message' => __('game/technologydetails.in_jump_gate_error_data'),
            ];
        }

        $jumpTime = time();

        DB::transaction(function () use ($subQueryOrigin, $subQueryDestination, $jumpTime, $targetGate): void {
            DB::statement(
                $this->prepareSql(
                    'UPDATE `' . PLANETS . '`, `' . USERS . '`, `' . SHIPS . "` SET
                        $subQueryOrigin
                        `planet_last_jump_time` = '" . $jumpTime . "',
                        `current_planet` = '" . ((int) $targetGate['planet_id']) . "'
                    WHERE `planet_id` = '" . ((int) $this->planet['planet_id']) . "'
                        AND `ship_planet_id` = '" . ((int) $this->planet['planet_id']) . "'
                        AND `id` = '" . ((int) $this->user['id']) . "';"
                )
            );

            DB::statement(
                $this->prepareSql(
                    'UPDATE `' . PLANETS . '`, `' . SHIPS . "` SET
                        $subQueryDestination
                        `planet_last_jump_time` = '" . $jumpTime . "'
                    WHERE `planet_id` = '" . ((int) $targetGate['planet_id']) . "'
                        AND `ship_planet_id` = '" . ((int) $targetGate['planet_id']) . "';"
                )
            );
        });

        $this->planet['planet_last_jump_time'] = $jumpTime;
        $restString = $this->getNextJumpWaitTime($this->planet);

        return [
            'success' => true,
            'message' => __('game/technologydetails.in_jump_gate_done') . $restString['string'],
        ];
    }

    /**
     * @return array<int, array{label: string, chance: string, shots: string, color: string}>
     */
    private function buildRapidFireAgainstItems(): array
    {
        $items = [];

        for ($type = 200; $type < 500; $type++) {
            $shots = (int) ($this->combatCaps[$this->elementId]['sd'][$type] ?? 0);

            if ($shots <= 1) {
                continue;
            }

            $items[] = $this->buildRapidFireItem(__('game/technologydetails.in_rf_again'), $type, $shots, '#00ff00');
        }

        return $items;
    }

    /**
     * @return array<int, array{label: string, chance: string, shots: string, color: string}>
     */
    private function buildRapidFireFromItems(): array
    {
        $items = [];

        for ($type = 200; $type < 500; $type++) {
            $shots = (int) ($this->combatCaps[$type]['sd'][$this->elementId] ?? 0);

            if ($shots <= 1) {
                continue;
            }

            $items[] = $this->buildRapidFireItem(__('game/technologydetails.in_rf_from'), $type, $shots, '#ff0000');
        }

        return $items;
    }

    /**
     * @return array{label: string, chance: string, shots: string, color: string}
     */
    private function buildRapidFireItem(string $prefix, int $type, int $shots, string $color): array
    {
        return [
            'label' => $prefix . ' ' . $this->getElementName($type),
            'chance' => $this->formatRapidFireChance($shots),
            'shots' => $this->formatService->prettyNumber($shots),
            'color' => $color,
        ];
    }

    private function formatRapidFireChance(int $shots): string
    {
        $chance = (($shots - 1) / $shots) * 100;

        return rtrim(rtrim($this->formatService->floatToString($chance, 2, true), '0'), '.');
    }

    /**
     * @param array<string, mixed> $currentPlanet
     */
    private function planetLink(array $currentPlanet): string
    {
        return '<a href="game.php?page=galaxy&mode=3&galaxy=' . $currentPlanet['planet_galaxy'] . '&system=' . $currentPlanet['planet_system'] . '">[' . $currentPlanet['planet_galaxy'] . ':' . $currentPlanet['planet_system'] . ':' . $currentPlanet['planet_planet'] . ']</a>';
    }

    private function getElementName(int $elementId): string
    {
        if ($elementId >= 601) {
            $officiers = __('game/officier.officiers');

            return is_array($officiers) ? ($officiers[$elementId]['name'] ?? '') : '';
        }

        $resourceKey = $this->resource[$elementId] ?? null;

        if (!is_string($resourceKey) || $resourceKey === '') {
            return '';
        }

        return (string) __('game/' . $this->getElementTranslationGroup($elementId) . '.' . $resourceKey);
    }

    private function getElementDescription(int $elementId): string
    {
        if ($elementId >= 601) {
            $officiers = __('game/officier.officiers');

            return is_array($officiers) ? ($officiers[$elementId]['description'] ?? '') : '';
        }

        $resourceKey = $this->resource[$elementId] ?? null;

        if (!is_string($resourceKey) || $resourceKey === '') {
            return '';
        }

        $infoTranslations = __('game/technologydetails.info');

        return is_array($infoTranslations) ? ($infoTranslations[$resourceKey] ?? '') : '';
    }

    private function getElementTranslationGroup(int $elementId): string
    {
        return match (true) {
            $elementId < 100 => 'constructions',
            $elementId < 200 => 'technologies',
            $elementId < 400 => 'ships',
            default => 'defenses',
        };
    }
}
