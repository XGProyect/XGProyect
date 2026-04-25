<?php

declare(strict_types=1);

namespace Xgp\App\Http\Controllers\Game;

use App\Services\Game\Formulas\DevelopmentsService;
use App\Services\FormatService;
use App\Services\Game\Formulas\OfficerService;
use App\Services\TimingService;
use Illuminate\Routing\Controller as BaseController;
use Xgp\App\Core\Enumerators\BuildingsEnumerator;
use Xgp\App\Core\Enumerators\ResearchEnumerator as Research;
use Xgp\App\Core\Objects;
use Xgp\App\Core\Template;
use Xgp\App\Helpers\UrlHelper;
use Xgp\App\Libraries\Buildings\Building;
use Xgp\App\Libraries\DevelopmentsLib as Developments;
use Xgp\App\Libraries\Functions;
use Xgp\App\Libraries\UpdatesLibrary;
use App\Enums\Module;
use Illuminate\Support\Facades\DB;
use Xgp\App\Core\Concerns\PreparesLegacySql;
use Xgp\App\Libraries\Users;

/**
 * @SuppressWarnings("PHPMD.StaticAccess")
 */
class BuildingsController extends BaseController
{
    use PreparesLegacySql;

    protected array $user = [];
    protected array $planet = [];
    protected string $page = '';
    protected array $allowedBuildings = [];

    private $_building = null;
    private bool $_commander_active = false;
    private Users $userLibrary;
    private Objects $objects;

    public function __construct(
        private FormatService $formatService,
        private OfficerService $officerService,
        private DevelopmentsService $developmentsService,
        private TimingService $timingService,
    ) {
    }

    public function __invoke(): void
    {
        Functions::moduleMessage(Functions::isModuleAccesible(Module::Buildings));

        $this->user = Users::getInstance()->getUserData();
        $this->planet = Users::getInstance()->getPlanetData();
        $this->objects = Objects::getInstance();

        $this->userLibrary = new Users();

        $this->setUpBuildings();

        $this->runAction();

        Template::legacyView(
            'buildings.body',
            array_merge(
                [
                    'list_of_buildings' => $this->buildListOfBuildings(),
                ],
                $this->buildQueueBlock()
            )
        );
    }

    private function setUpBuildings(): void
    {
        $this->_building = new Building(
            $this->planet,
            $this->user,
            $this->objects
        );

        $this->setAllowedBuildings();

        $this->_commander_active = $this->officerService->isOfficerActive((int) $this->user['premium_officier_commander'], time());
    }

    private function runAction(): void
    {
        $action = filter_input(INPUT_GET, 'cmd');
        $reload = filter_input(INPUT_GET, 'r');
        $building = filter_input(INPUT_GET, 'building', FILTER_VALIDATE_INT);
        $listId = filter_input(INPUT_GET, 'listid', FILTER_VALIDATE_INT);
        $allowed_actions = ['cancel', 'destroy', 'insert', 'remove'];

        if (!is_null($action)) {
            if (in_array($action, $allowed_actions)) {
                if ($this->canInitBuildAction($building, $listId)) {
                    switch ($action) {
                        case 'cancel':
                            $this->cancelCurrent();
                            break;

                        case 'destroy':
                            $this->addToQueue($building, false);
                            break;

                        case 'insert':
                            $this->addToQueue($building, true);
                            break;

                        case 'remove':
                            $this->removeFromQueue($listId);
                            break;
                    }

                    // start building
                    UpdatesLibrary::setFirstElement($this->planet, $this->user);

                    // start building
                    DB::statement(
                        $this->prepareSql(
                            'UPDATE `' . PLANETS . "` SET
                                `planet_b_building` = '" . $this->planet['planet_b_building'] . "',
                                `planet_b_building_id` = '" . $this->planet['planet_b_building_id'] . "'
                            WHERE `planet_id` = '" . $this->planet['planet_id'] . "';"
                        )
                    );
                }

                if ($reload == 'overview') {
                    header('location:game.php?page=overview');
                } else {
                    header('location:game.php?page=' . $this->page);
                }
                exit;
            }
        }
    }

    /**
     * Build the list of buildings
     *
     * @return string
     */
    private function buildListOfBuildings()
    {
        $buildings_list = [];

        if (!is_null($this->allowedBuildings)) {
            foreach ($this->allowedBuildings as $building_id) {
                $buildings_list[] = $this->setListOfBuildingsItem($building_id);
            }
        }

        return $buildings_list;
    }

    /**
     * Build the list of queued elements
     *
     * @return array
     */
    private function buildQueueBlock()
    {
        $return['BuildListScript'] = '';
        $return['BuildList'] = '';

        $queue = $this->showQueue();

        if ($this->_commander_active && $queue['lenght'] > 0) {
            $return['BuildListScript'] = Developments::currentBuilding($this->page);
            $return['BuildList'] = $queue['buildlist'];
        }

        return $return;
    }

    private function setListOfBuildingsItem(int $building_id): array
    {
        $item_to_parse = [];

        $item_to_parse['i'] = $building_id;
        $item_to_parse['nivel'] = $this->getBuildingLevelWithFormat($building_id);
        $item_to_parse['n'] = __('game/constructions.' . $this->objects->getObjects()[$building_id]);
        $item_to_parse['descriptions'] = __('game/buildings.descriptions')[$this->objects->getObjects()[$building_id]];
        $item_to_parse['price'] = $this->getBuildingPriceWithFormat($building_id);
        $item_to_parse['time'] = $this->getBuildingTimeWithFormat($building_id);
        $item_to_parse['click'] = $this->getActionButton($building_id);

        return $item_to_parse;
    }

    private function getBuildingLevelWithFormat(int $building_id): string
    {
        return Developments::setLevelFormat($this->getBuildingLevel($building_id));
    }

    private function getBuildingPriceWithFormat(int $building_id): string
    {
        return Developments::formatedDevelopmentPrice(
            $this->user,
            $this->planet,
            $building_id,
            true,
            $this->getBuildingLevel($building_id)
        );
    }

    private function getBuildingTimeWithFormat(int $building_id): string
    {
        return Developments::formatedDevelopmentTime(
            $this->getBuildingTime($building_id),
            __('game/buildings.bd_time')
        );
    }

    private function getBuildingLevel(int $building_id): int
    {
        return (int) $this->planet[$this->objects->getObjects()[$building_id]];
    }

    private function getBuildingTime(int $building_id): int
    {
        $resource = $this->objects->getObjects();

        return $this->developmentsService->developmentTime(
            $building_id,
            $this->getBuildingLevel($building_id),
            (int) $this->planet[$resource[BuildingsEnumerator::BUILDING_ROBOT_FACTORY]],
            (int) $this->planet[$resource[BuildingsEnumerator::BUILDING_NANO_FACTORY]],
            0,
            0,
            false
        );
    }

    private function getActionButton(int $building_id): string
    {
        $build_url = 'game.php?page=' . $this->page . '&cmd=insert&building=' . $building_id;

        // validations
        $is_development_payable = $this->developmentsService->isDevelopmentPayable($this->planet, $building_id, $this->getBuildingLevel($building_id), true, false);
        $is_on_vacations = $this->userLibrary->isOnVacations($this->user);
        $have_fields = $this->developmentsService->areFieldsAvailable((int) $this->planet['planet_field_current'], (int) $this->planet['planet_field_max'], (int) $this->planet[$this->objects->getObjects()[33]]);
        $is_queue_full = $this->_building->isQueueFull();
        $queue_element = $this->_building->getCountElementsOnQueue();

        // check fields
        if (!$have_fields) {
            // block all if we don't have any
            return $this->buildButton('all_occupied');
        }

        // check if there's any work in progress
        if ($this->isWorkInProgress($building_id)) {
            // block some
            return $this->buildButton('work_in_progress');
        }

        // check vacations
        if ($is_on_vacations) {
            // block all or some
            return $this->buildButton('not_allowed');
        }

        // if a queue was already set
        if ($this->_commander_active) {
            if ($is_queue_full) {
                return $this->buildButton('not_allowed');
            }

            if ($queue_element > 0) {
                return UrlHelper::setUrl($build_url, $this->buildButton('allowed_for_queue'));
            }
        }

        // if something is being build
        if (!$this->_commander_active) {
            if ($queue_element > 0) {
                return $this->buildCountDownClock($building_id);
            }
        }

        if (!$is_development_payable) {
            return $this->buildButton('not_allowed');
        }

        return UrlHelper::setUrl($build_url, $this->buildButton('allowed'));
    }

    /**
     * Build the countdown clock for that usually appears
     *
     * @param int $building_id Building ID
     *
     * @return string
     */
    private function buildCountDownClock($building_id)
    {
        $first_queued_element = (int) $this->_building->getNewQueueAsArray()[0][0];

        if ($first_queued_element == $building_id) {
            $block = [
                'build_time' => ($this->planet['planet_b_building'] - time()),
                'call_program' => $this->page,
            ];

            return Template::render(
                'buildings.build_single_script',
                $block
            );
        }

        return '<center>-</center>';
    }

    private function canInitBuildAction($buildingId, $listId): bool
    {
        if (isset($listId)) {
            return true;
        }

        if ($this->_building->isQueueFull()) {
            return false;
        }

        if ($this->isWorkInProgress($buildingId)) {
            return false;
        }

        if (!in_array($buildingId, $this->allowedBuildings)) {
            return false;
        }

        return true;
    }

    private function buildButton(string $buttonCode): string
    {
        $listOfButtons = [
            'all_occupied' => ['color' => 'red', 'lang' => 'bd_no_more_fields'],
            'allowed' => ['color' => 'green', 'lang' => 'bd_build'],
            'not_allowed' => ['color' => 'red', 'lang' => 'bd_build'],
            'allowed_for_queue' => ['color' => 'green', 'lang' => 'bd_add_to_list'],
            'work_in_progress' => ['color' => 'red', 'lang' => 'bd_working'],
        ];

        $color = ucfirst($listOfButtons[$buttonCode]['color']);
        $text = __('game/buildings.' . $listOfButtons[$buttonCode]['lang']);
        $methodName = 'color' . $color;

        return $this->formatService->$methodName($text);
    }

    /**
     * Determine if there's any work in progress
     *
     * @param int $building_id Building ID
     *
     * @return boolean
     */
    private function isWorkInProgress($building_id)
    {
        $working_buildings = [14, 15, 21];

        if ($building_id == 31 && $this->developmentsService->isLabWorking((int) $this->user['research_current_research'])) {
            return true;
        }

        if (in_array($building_id, $working_buildings) && $this->developmentsService->isShipyardWorking((int) $this->planet['planet_b_hangar'])) {
            return true;
        }

        return false;
    }

    private function setAllowedBuildings(): void
    {
        $resource = $this->objects->getObjects();
        $levels = [];
        foreach ($resource as $id => $column) {
            $levels[$id] = (int) ($this->planet[$column] ?? $this->user[$column] ?? 0);
        }

        $this->allowedBuildings = array_filter(
            $this->allowedBuildings[$this->planet['planet_type']],
            function ($value) use ($levels) {
                return $this->developmentsService->isDevelopmentAllowed(
                    $value,
                    $levels
                );
            }
        );
    }
    /**
     * OLD METHODS BELOW
     * OLD METHODS BELOW
     * OLD METHODS BELOW
     * OLD METHODS BELOW
     * OLD METHODS BELOW
     */

    /**
     * method cancelCurrent
     * param
     * return (bool) confirmation
     */
    private function cancelCurrent()
    {
        $resource = $this->objects->getObjects();
        $CurrentQueue = $this->planet['planet_b_building_id'];

        if ($CurrentQueue != 0) {
            $QueueArray = explode(';', $CurrentQueue);
            $ActualCount = count($QueueArray);
            $CanceledIDArray = explode(',', $QueueArray[0]);
            $building = $CanceledIDArray[0];
            $BuildMode = $CanceledIDArray[4];

            if ($ActualCount > 1) {
                array_shift($QueueArray);
                $NewCount = count($QueueArray);
                $BuildEndTime = time();

                for ($ID = 0; $ID < $NewCount; $ID++) {
                    $ListIDArray = explode(',', $QueueArray[$ID]);

                    if ($ListIDArray[0] == $building) {
                        $ListIDArray[1] -= 1;
                    }

                    $current_build_time = $this->developmentsService->developmentTime(
                        (int) $ListIDArray[0],
                        (int) ($this->planet[$resource[(int) $ListIDArray[0]]] ?? 0),
                        (int) $this->planet[$resource[BuildingsEnumerator::BUILDING_ROBOT_FACTORY]],
                        (int) $this->planet[$resource[BuildingsEnumerator::BUILDING_NANO_FACTORY]],
                        0,
                        0,
                        false
                    );
                    $BuildEndTime += $current_build_time;
                    $ListIDArray[2] = $current_build_time;
                    $ListIDArray[3] = $BuildEndTime;
                    $QueueArray[$ID] = join(',', $ListIDArray);
                }
                $NewQueue = join(';', $QueueArray);
                $ReturnValue = true;
                $BuildEndTime = '0';
            } else {
                $NewQueue = '0';
                $ReturnValue = false;
                $BuildEndTime = '0';
            }

            if ($BuildMode == 'destroy') {
                $ForDestroy = true;
            } else {
                $ForDestroy = false;
            }

            if ($building != false) {
                $Needed = $this->developmentsService->developmentPrice(
                    (int) $building,
                    (int) ($this->planet[$resource[(int) $building]] ?? 0),
                    true,
                    $ForDestroy,
                    $ForDestroy ? (int) ($this->user[$resource[Research::research_ionic_technology]] ?? 0) : 0
                );
                $this->planet['planet_metal'] += $Needed['metal'] ?? 0;
                $this->planet['planet_crystal'] += $Needed['crystal'] ?? 0;
                $this->planet['planet_deuterium'] += $Needed['deuterium'] ?? 0;
            }
        } else {
            $NewQueue = '0';
            $BuildEndTime = '0';
            $ReturnValue = false;
        }

        $this->planet['planet_b_building_id'] = $NewQueue;
        $this->planet['planet_b_building'] = $BuildEndTime;

        return $ReturnValue;
    }

    private function removeFromQueue(int $QueueID): int
    {
        if ($QueueID > 1) {
            $CurrentQueue = $this->planet['planet_b_building_id'];
            $NewQueue = '';

            if (!empty($CurrentQueue)) {
                $QueueArray = explode(';', $CurrentQueue);
                $ActualCount = count($QueueArray);
                if ($ActualCount < 2) {
                    Functions::redirect('game.php?page=' . $this->page);
                }

                //  finding the buildings time
                $ListIDArrayToDelete = explode(',', $QueueArray[$QueueID - 1]);
                $lastB = $ListIDArrayToDelete;
                $lastID = $QueueID - 1;

                //search for biggest element
                for ($ID = $QueueID; $ID < $ActualCount; $ID++) {
                    //next buildings
                    $nextListIDArray = explode(',', $QueueArray[$ID]);
                    //if same type of element
                    if ($nextListIDArray[0] == $ListIDArrayToDelete[0]) {
                        $lastB = $nextListIDArray;
                        $lastID = $ID;
                    }
                }

                // update the rest of buildings queue
                for ($ID = $lastID; $ID < $ActualCount - 1; $ID++) {
                    $nextListIDArray = explode(',', $QueueArray[$ID + 1]);
                    $nextBuildEndTime = $nextListIDArray[3] - $lastB[2];
                    $nextListIDArray[3] = $nextBuildEndTime;
                    $QueueArray[$ID] = join(',', $nextListIDArray);
                }

                unset($QueueArray[$ActualCount - 1]);
                $NewQueue = join(';', $QueueArray);
            }

            $this->planet['planet_b_building_id'] = $NewQueue;
        }

        return $QueueID;
    }

    private function addToQueue(int $building, bool $AddMode = true)
    {
        $resource = $this->objects->getObjects();
        $CurrentQueue = $this->planet['planet_b_building_id'];
        $queue = $this->showQueue();
        $max_fields = $this->developmentsService->maxFields((int) $this->planet['planet_field_max'], (int) $this->planet[$resource[33]]);
        $QueueArray = [];

        if ($AddMode) {
            if (($this->planet['planet_field_current'] >= ($max_fields - $queue['lenght']))) {
                Functions::redirect('game.php?page=' . $this->page);
            }
        }

        if ($CurrentQueue != 0) {
            $QueueArray = explode(';', $CurrentQueue);
            $ActualCount = count($QueueArray);
        } else {
            $QueueArray = '';
            $ActualCount = 0;
        }

        if ($AddMode == true) {
            $BuildMode = 'build';
        } else {
            $BuildMode = 'destroy';
        }

        if ($ActualCount < MAX_BUILDING_QUEUE_SIZE) {
            $QueueID = $ActualCount + 1;
        } else {
            $QueueID = false;
        }

        $continue = false;

        $levels = [];
        foreach ($resource as $id => $column) {
            $levels[$id] = (int) ($this->planet[$column] ?? $this->user[$column] ?? 0);
        }

        if ($QueueID != false && $this->developmentsService->isDevelopmentAllowed($building, $levels)) {
            if ($QueueID <= 1) {
                if ($this->developmentsService->isDevelopmentPayable(
                    $this->planet,
                    $building,
                    (int) $this->planet[$resource[$building]],
                    true,
                    !$AddMode,
                    !$AddMode ? (int) ($this->user[$resource[Research::research_ionic_technology]] ?? 0) : 0
                ) && !$this->userLibrary->isOnVacations($this->user)) {
                    $continue = true;
                }
            } else {
                $continue = true;
            }

            if ($continue) {
                if ($QueueID > 1) {
                    $InArray = 0;
                    for ($QueueElement = 0; $QueueElement < $ActualCount; $QueueElement++) {
                        $QueueSubArray = explode(',', $QueueArray[$QueueElement]);
                        if ($QueueSubArray[0] == $building) {
                            $InArray++;
                        }
                    }
                } else {
                    $InArray = 0;
                }

                if ($InArray != 0) {
                    $ActualLevel = $this->planet[$resource[$building]];
                    if ($AddMode == true) {
                        $BuildLevel = $ActualLevel + 1 + $InArray;
                        $this->planet[$resource[$building]] += $InArray;
                        $BuildTime = $this->developmentsService->developmentTime(
                            $building,
                            (int) $this->planet[$resource[$building]],
                            (int) $this->planet[$resource[BuildingsEnumerator::BUILDING_ROBOT_FACTORY]],
                            (int) $this->planet[$resource[BuildingsEnumerator::BUILDING_NANO_FACTORY]],
                            0,
                            0,
                            false
                        );
                        $this->planet[$resource[$building]] -= $InArray;
                    } else {
                        $BuildLevel = $ActualLevel - 1 - $InArray;
                        $this->planet[$resource[$building]] -= $InArray;
                        $BuildTime = $this->developmentsService->tearDownTime(
                            $building,
                            (int) $this->planet[$resource[$building]],
                            (int) $this->planet[$resource[BuildingsEnumerator::BUILDING_ROBOT_FACTORY]],
                            (int) $this->planet[$resource[BuildingsEnumerator::BUILDING_NANO_FACTORY]]
                        );

                        $this->planet[$resource[$building]] += $InArray;
                    }
                } else {
                    $ActualLevel = $this->planet[$resource[$building]];
                    if ($AddMode == true) {
                        $BuildLevel = $ActualLevel + 1;
                        $BuildTime = $this->developmentsService->developmentTime(
                            $building,
                            (int) $this->planet[$resource[$building]],
                            (int) $this->planet[$resource[BuildingsEnumerator::BUILDING_ROBOT_FACTORY]],
                            (int) $this->planet[$resource[BuildingsEnumerator::BUILDING_NANO_FACTORY]],
                            0,
                            0,
                            false
                        );
                    } else {
                        $BuildLevel = $ActualLevel - 1;
                        $BuildTime = $this->developmentsService->tearDownTime(
                            $building,
                            (int) $this->planet[$resource[$building]],
                            (int) $this->planet[$resource[BuildingsEnumerator::BUILDING_ROBOT_FACTORY]],
                            (int) $this->planet[$resource[BuildingsEnumerator::BUILDING_NANO_FACTORY]]
                        );
                    }
                }

                if ($QueueID == 1) {
                    $QueueArray = [];
                    $BuildEndTime = time() + $BuildTime;
                } else {
                    $PrevBuild = explode(',', $QueueArray[$ActualCount - 1]);
                    $BuildEndTime = $PrevBuild[3] + $BuildTime;
                }

                $QueueArray[$ActualCount] = $building . ',' . $BuildLevel . ',' . $BuildTime . ',' . $BuildEndTime . ',' . $BuildMode;
                $NewQueue = join(';', $QueueArray);

                $this->planet['planet_b_building_id'] = $NewQueue;
            }
        }
        return $QueueID;
    }

    /**
     * method showQueue
     * param $Sprice
     * return (array) the queue to build data
     */
    private function showQueue(&$Sprice = false)
    {
        $CurrentQueue = $this->planet['planet_b_building_id'];
        $QueueID = 0;
        $to_destroy = 0;
        $BuildMode = '';

        if ($CurrentQueue != 0) {
            $QueueArray = explode(';', $CurrentQueue);
            $ActualCount = count($QueueArray);
        } else {
            $QueueArray = '0';
            $ActualCount = 0;
        }

        $ListIDRow = '';

        if ($ActualCount != 0) {
            $PlanetID = $this->planet['planet_id'];
            for ($QueueID = 0; $QueueID < $ActualCount; $QueueID++) {
                $BuildArray = explode(',', $QueueArray[$QueueID]);
                $BuildEndTime = (int) $BuildArray[3];
                $CurrentTime = time();

                if ($BuildMode == 'destroy') {
                    $to_destroy++;
                }

                if ($BuildEndTime >= $CurrentTime) {
                    $ListID = $QueueID + 1;
                    $building = $BuildArray[0];
                    $BuildLevel = $BuildArray[1];
                    $BuildMode = $BuildArray[4];
                    $BuildTime = $BuildEndTime - time();
                    $ElementTitle = __('game/constructions.' . $this->objects->getObjects()[$building]);

                    if (isset($Sprice[$building]) && $Sprice !== false && $BuildLevel > $Sprice[$building]) {
                        $Sprice[$building] = $BuildLevel;
                    }

                    $ListIDRow .= '<tr>';
                    if ($BuildMode == 'build') {
                        $ListIDRow .= '	<td class="l" colspan="2">' . $ListID . '.: ' . $ElementTitle . ' ' . $BuildLevel . '</td>';
                    } else {
                        $ListIDRow .= '	<td class="l" colspan="2">' . $ListID . '.: ' . $ElementTitle . ' ' . $BuildLevel . ' ' . __('game/buildings.bd_dismantle') . '</td>';
                    }
                    $ListIDRow .= '	<td class="k">';

                    if ($ListID == 1) {
                        $ListIDRow .= '		<div id="blc" class="z">' . $BuildTime . '<br>';
                        $ListIDRow .= '		<a href="game.php?page=' . $this->page . '&listid=' . $ListID . '&amp;cmd=cancel&amp;planet=' . $PlanetID . '">' . __('game/buildings.bd_interrupt') . '</a></div>';
                        $ListIDRow .= '		<script language="JavaScript">';
                        $ListIDRow .= '			pp = "' . $BuildTime . "\";\n";
                        $ListIDRow .= '			pk = "' . $ListID . "\";\n";
                        $ListIDRow .= "			pm = \"cancel\";\n";
                        $ListIDRow .= '			pl = "' . $PlanetID . "\";\n";
                        $ListIDRow .= "			t();\n";
                        $ListIDRow .= '		</script>';
                        $ListIDRow .= '		<strong color="lime"><br><font color="lime">' . $this->timingService->formatExtendedDate($BuildEndTime) . '</font></strong>';
                    } else {
                        $ListIDRow .= '		<font color="red">';
                        $ListIDRow .= '		<a href="game.php?page=' . $this->page . '&listid=' . $ListID . '&amp;cmd=remove&amp;planet=' . $PlanetID . '">' . __('game/buildings.bd_cancel') . '</a></font>';
                    }

                    $ListIDRow .= '	</td>';
                    $ListIDRow .= '</tr>';
                }
            }
        }

        $RetValue['to_destoy'] = $to_destroy;
        $RetValue['lenght'] = $ActualCount;
        $RetValue['buildlist'] = $ListIDRow;

        return $RetValue;
    }
}
