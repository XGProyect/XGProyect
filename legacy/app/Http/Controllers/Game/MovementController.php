<?php

declare(strict_types=1);

namespace Xgp\App\Http\Controllers\Game;

use App\Enums\Module;
use App\Services\Game\Formulas\FleetsService;
use App\Services\FormatService;
use App\Services\Game\Formulas\OfficerService;
use App\Services\TimingService;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;
use Xgp\App\Core\Concerns\PreparesLegacySql;
use Xgp\App\Core\Entity\FleetEntity;
use Xgp\App\Core\Enumerators\MissionsEnumerator as Missions;
use Xgp\App\Core\Objects;
use Xgp\App\Core\Template;
use Xgp\App\Helpers\UrlHelper;
use Xgp\App\Libraries\FleetsLib;
use Xgp\App\Libraries\Functions;
use Xgp\App\Libraries\Game\Fleets;
use Xgp\App\Libraries\Premium\Premium;
use Xgp\App\Libraries\Research\Researches;
use Xgp\App\Libraries\Users;

/**
 * @SuppressWarnings("PHPMD.CouplingBetweenObjects")
 * @SuppressWarnings("PHPMD.StaticAccess")
 */
class MovementController extends BaseController
{
    use PreparesLegacySql;

    public const REDIRECT_TARGET = 'game.php?page=movement';

    private array $user = [];
    private ?Fleets $fleets = null;
    private ?Researches $research = null;
    private ?Premium $premium = null;

    public function __construct(
        private FormatService $formatService,
        private FleetsService $fleetsService,
        private OfficerService $officerService,
        private TimingService $timingService,
    ) {
    }

    public function __invoke(): void
    {
        Functions::moduleMessage(Functions::isModuleAccesible(Module::Fleet));

        $this->user = Users::getInstance()->getUserData();

        $this->setUpFleets();
        $this->runAction();
        $this->buildPage();
    }

    private function setUpFleets(): void
    {
        $userId = (int) $this->user['id'];
        $this->fleets = new Fleets(
            $userId > 0 ? array_map(
                fn ($row) => (array) $row,
                DB::select(
                    $this->prepareSql(
                        'SELECT f.*
                        FROM `' . FLEETS . "` f
                        WHERE f.`fleet_owner` = '" . $userId . "';"
                    )
                )
            ) : [],
            $userId
        );

        $this->research = new Researches(
            [$this->user],
            (int) $this->user['id']
        );

        $this->premium = new Premium(
            [$this->user],
            (int) $this->user['id']
        );
    }

    private function runAction(): void
    {
        $fleet_action = filter_input(INPUT_GET, 'action');

        if (in_array($fleet_action, ['return'])) {
            $this->{'execFleet' . ucfirst($fleet_action)}();
        }
    }

    private function buildPage(): void
    {
        $page = [
            'fleets' => $this->fleets->getFleetsCount(),
            'max_fleets' => $this->fleetsService->getMaxFleets(
                $this->research->getCurrentResearch()->getResearchComputerTechnology(),
                $this->officerService->isOfficerActive($this->premium->getCurrentPremium()->getPremiumOfficierAdmiral(), time())
            ),
            'expeditions' => $this->fleets->getExpeditionsCount(),
            'max_expeditions' => $this->fleetsService->getMaxExpeditions(
                $this->research->getCurrentResearch()->getResearchAstrophysics()
            ),
            'list_of_movements' => $this->buildMovements(),
        ];

        Template::legacyView(
            'movement.view',
            $page
        );
    }

    private function buildMovements(): array
    {
        $list_of_movements[] = [
            'num' => '-',
            'fleet_mission' => '-',
            'title' => '',
            'tooltip' => '',
            'fleet_amount' => '-',
            'fleet' => '',
            'fleet_start' => '-',
            'fleet_start_time' => '-',
            'fleet_end' => '-',
            'fleet_end_time' => '-',
            'fleet_arrival' => '-',
            'fleet_actions' => '-',
        ];

        if ($this->fleets->getFleetsCount() > 0) {
            // reset
            $list_of_movements = [];
            $fleet_count = 0;

            foreach ($this->fleets->getFleets() as $fleet) {
                $list_of_movements[] = [
                    'num' => ++$fleet_count,
                    'fleet_mission' => __('game/missions.type_mission')[$fleet->getFleetMission()],
                    'title' => $this->buildTitleBlock($fleet->getFleetMess()),
                    'tooltip' => $this->buildToolTipBlock($fleet->getFleetMess()),
                    'fleet_amount' => $this->formatService->prettyNumber((int) $fleet->getFleetAmount()),
                    'fleet' => $this->buildShipsBlock($fleet->getFleetArray()),
                    'fleet_start' => $this->formatService->prettyCoords(
                        (int) $fleet->getFleetStartGalaxy(),
                        (int) $fleet->getFleetStartSystem(),
                        (int) $fleet->getFleetStartPlanet()
                    ),
                    'fleet_start_time' => $this->timingService->formatExtendedDate($fleet->getFleetCreation()),
                    'fleet_end' => $this->formatService->prettyCoords(
                        (int) $fleet->getFleetEndGalaxy(),
                        (int) $fleet->getFleetEndSystem(),
                        (int) $fleet->getFleetEndPlanet()
                    ),
                    'fleet_end_time' => $this->timingService->formatExtendedDate($fleet->getFleetStartTime()),
                    'fleet_arrival' => $this->timingService->formatExtendedDate($fleet->getFleetEndTime()),
                    'fleet_actions' => $this->buildActionsBlock($fleet),
                ];
            }
        }

        return $list_of_movements;
    }

    private function buildTitleBlock(int $fleet_mess): string
    {
        if ($this->fleetsService->isFleetReturning($fleet_mess)) {
            return __('game/fleet.fl_r');
        }

        return __('game/fleet.fl_a');
    }

    private function buildToolTipBlock(int $fleet_mess): string
    {
        if ($this->fleetsService->isFleetReturning($fleet_mess)) {
            return __('game/fleet.fl_returning');
        }

        return __('game/fleet.fl_onway');
    }

    private function buildShipsBlock(string $fleet_array): string
    {
        $objects = Objects::getInstance()->getObjects();
        $ships = FleetsLib::getFleetShipsArray($fleet_array);
        $tooltips = [];

        foreach ($ships as $ship => $amount) {
            $tooltips[] = __('game/ships.' . $objects[$ship]) . ' :' . $amount;
        }

        return count($tooltips) > 0 ? join("\n", $tooltips) : '';
    }

    private function buildActionsBlock(FleetEntity $fleet): string
    {
        $actions = '-';

        if ($fleet->getFleetMess() == 0) {
            $actions = '<form action="game.php?page=movement&action=return" method="post">';
            $actions .= '<input type="hidden" name="fleetid" value="' . $fleet->getFleetId() . '">';
            $actions .= '<input type="submit" name="send" value="' . __('game/fleet.fl_send_back') . '">';
            $actions .= '</form>';

            if ($fleet->getFleetMission() == Missions::ATTACK) {
                $content = '<input type="button" value="' . __('game/fleet.fl_acs') . '">';
                $attributes = 'onClick="f(\'game.php?page=federationlayer&fleet=' . $fleet->getFleetId() . '\', \'\')"';

                $actions .= UrlHelper::setUrl('#', $content, '', $attributes);
            }
        }

        return $actions;
    }

    private function execFleetReturn(): void
    {
        $fleet_id = filter_input(INPUT_POST, 'fleetid', FILTER_VALIDATE_INT);

        if ($fleet_id) {
            $fleet = $this->fleets->getOwnFleetById($fleet_id);

            if (!is_null($fleet) && $fleet->getFleetMess() != 1) {
                $this->returnFleet($fleet, (int) $this->user['id']);

                Functions::redirect(self::REDIRECT_TARGET);
            }
        }
    }

    private function returnFleet(FleetEntity $fleet, int $userId): void
    {
        DB::transaction(function () use ($fleet, $userId): void {
            if ($fleet->getFleetGroup() > 0) {
                $acsOwnerRow = DB::selectOne(
                    $this->prepareSql(
                        'SELECT af.`acs_owner`
                        FROM `' . ACS . "` af
                        WHERE af.`acs_id` = '" . $fleet->getFleetGroup() . "';"
                    )
                );
                $acsOwner = $acsOwnerRow !== null ? (int) $acsOwnerRow->acs_owner : 0;

                if ($acsOwner > 0 &&
                    $acsOwner == $fleet->getFleetOwner() &&
                    $fleet->getFleetMission() == Missions::ATTACK) {
                    DB::statement(
                        $this->prepareSql(
                            'DELETE FROM `' . ACS . "`
                            WHERE `acs_id` = '" . $fleet->getFleetGroup() . "';"
                        )
                    );

                    DB::statement(
                        $this->prepareSql(
                            'UPDATE `' . FLEETS . "` f SET
                                f.`fleet_group` = '0'
                            WHERE f.`fleet_group` = '" . $fleet->getFleetGroup() . "';"
                        )
                    );
                }

                if ($fleet->getFleetMission() == Missions::ACS) {
                    DB::statement(
                        $this->prepareSql(
                            'UPDATE `' . FLEETS . "` f SET
                                f.`fleet_group` = '0'
                            WHERE f.`fleet_id` = '" . $fleet->getFleetId() . "';"
                        )
                    );
                }
            }

            $base_time = time();
            $fleet_creation = $fleet->getFleetCreation();
            $current_time = $base_time - $fleet_creation;
            $flight_lenght = $fleet->getFleetStartTime() - $fleet_creation;
            $return_time = $base_time + $current_time;

            if ($fleet->getFleetEndStay() != 0 &&
                $current_time > $flight_lenght) {
                $return_time = $base_time + $flight_lenght;
            }

            DB::statement(
                $this->prepareSql(
                    'UPDATE `' . FLEETS . "` f SET
                        f.`fleet_start_time` = '" . $base_time . "',
                        f.`fleet_end_stay` = '0',
                        f.`fleet_end_time` = '" . $return_time . "',
                        f.`fleet_target_owner` = '" . $userId . "',
                        f.`fleet_mess` = '1'
                    WHERE f.`fleet_id` = '" . $fleet->getFleetId() . "';"
                )
            );
        });
    }
}
