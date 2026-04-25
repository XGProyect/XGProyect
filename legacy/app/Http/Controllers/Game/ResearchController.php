<?php

declare(strict_types=1);

namespace Xgp\App\Http\Controllers\Game;

use App\Enums\Module;
use App\Models\Buildings;
use App\Services\Game\Formulas\DevelopmentsService;
use App\Services\FormatService;
use App\Services\Game\Formulas\OfficerService;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;
use Xgp\App\Core\Concerns\PreparesLegacySql;
use Xgp\App\Core\Enumerators\BuildingsEnumerator;
use Xgp\App\Core\Enumerators\ResearchEnumerator as Research;
use Xgp\App\Core\Objects;
use Xgp\App\Core\Template;
use Xgp\App\Helpers\UrlHelper;
use Xgp\App\Libraries\DevelopmentsLib;
use Xgp\App\Libraries\Functions;
use Xgp\App\Libraries\Users;

/**
 * @SuppressWarnings("PHPMD.CouplingBetweenObjects")
 * @SuppressWarnings("PHPMD.StaticAccess")
 */
class ResearchController extends BaseController
{
    use PreparesLegacySql;

    private array $user = [];
    private array $planet = [];
    private $_resource;
    private $_reslist;
    private $_is_working;
    private $_lab_level;
    private Users $userLibrary;

    public function __construct(
        private FormatService $formatService,
        private DevelopmentsService $developmentsService,
        private OfficerService $officerService
    ) {
    }

    public function __invoke(): void
    {
        Functions::moduleMessage(Functions::isModuleAccesible(Module::Research));

        $this->user = Users::getInstance()->getUserData();
        $this->planet = Users::getInstance()->getPlanetData();
        $this->_resource = Objects::getInstance()->getObjects();
        $this->_reslist = Objects::getInstance()->getObjectsList();
        $this->userLibrary = new Users();

        $this->setLabsAmount();
        $this->handleTechnologieBuild();

        if ($this->planet[$this->_resource[31]] == 0) {
            Functions::message(__('game/research.re_lab_required'), '', 0, true);
        }

        $this->buildPage();
    }

    /**
     * @SuppressWarnings("PHPMD.CyclomaticComplexity")
     */
    private function buildPage(): void
    {
        $technology_list = [];

        $this->doCommand();

        $levels = [];
        foreach ($this->_resource as $id => $column) {
            $levels[$id] = (int) ($this->planet[$column] ?? $this->user[$column] ?? 0);
        }

        $intergalLabLevel = (int) ($this->user[$this->_resource[Research::research_intergalactic_research_network]] ?? 0);
        $labLevel = $intergalLabLevel >= 1 ? $this->_lab_level : (int) $this->planet[$this->_resource[BuildingsEnumerator::BUILDING_LABORATORY]];
        $astrophysicsLevel = (int) ($this->user[$this->_resource[Research::research_astrophysics]] ?? 0);
        $technocrateActive = $this->officerService->isOfficerActive((int) $this->user['premium_officier_technocrat'], time());

        foreach ($this->_reslist['tech'] as $tech) {
            if ($this->developmentsService->isDevelopmentAllowed($tech, $levels)) {
                $RowParse['tech_id'] = $tech;
                $building_level = (int) $this->user[$this->_resource[$tech]];
                $RowParse['tech_level'] = DevelopmentsLib::setLevelFormat($building_level, $tech, $this->user);
                $RowParse['tech_name'] = __('game/technologies.' . $this->_resource[$tech]);
                $RowParse['tech_descr'] = __('game/research.descriptions')[$this->_resource[$tech]];
                $RowParse['tech_price'] = DevelopmentsLib::formatedDevelopmentPrice($this->user, $this->planet, $tech);
                $SearchTime = $this->developmentsService->developmentTime(
                    $tech,
                    (int) $this->user[$this->_resource[$tech]],
                    0,
                    0,
                    $labLevel,
                    $astrophysicsLevel,
                    $technocrateActive
                );
                $RowParse['search_time'] = DevelopmentsLib::formatedDevelopmentTime($SearchTime, __('game/research.re_time'));

                if (!$this->_is_working['is_working']) {
                    if ($this->developmentsService->isDevelopmentPayable($this->planet, $tech, (int) $this->user[$this->_resource[$tech]]) && !$this->userLibrary->isOnVacations($this->user)) {
                        if (!$this->isLaboratoryInQueue()) {
                            $action_link = $this->formatService->colorRed(__('game/research.re_research'));
                        } else {
                            $action_link = UrlHelper::setUrl('game.php?page=research&cmd=search&tech=' . $tech, $this->formatService->colorGreen(__('game/research.re_research')));
                        }
                    } else {
                        $action_link = $this->formatService->colorRed(__('game/research.re_research'));
                    }
                } else {
                    if ($this->_is_working['working_on']['planet_b_tech_id'] == $tech) {
                        if ($this->_is_working['working_on']['planet_id'] != $this->planet['planet_id']) {
                            $bloc['tech_time'] = $this->_is_working['working_on']['planet_b_tech'] - time();
                            $bloc['tech_name'] = __('game/research.re_from') . $this->_is_working['working_on']['planet_name'] . '<br> ' . $this->formatService->prettyCoords((int)$this->_is_working['working_on']['planet_galaxy'], (int)$this->_is_working['working_on']['planet_system'], (int)$this->_is_working['working_on']['planet_planet']);
                            $bloc['tech_home'] = $this->_is_working['working_on']['planet_id'];
                            $bloc['tech_id'] = $this->_is_working['working_on']['planet_b_tech_id'];
                        } else {
                            $bloc['tech_time'] = $this->planet['planet_b_tech'] - time();
                            $bloc['tech_name'] = '';
                            $bloc['tech_home'] = $this->planet['planet_id'];
                            $bloc['tech_id'] = $this->planet['planet_b_tech_id'];
                        }
                        $action_link = Template::render(
                            'research.script',
                            $bloc
                        );
                    } else {
                        $action_link = '<center>-</center>';
                    }
                }
                $RowParse['tech_link'] = $action_link;

                $technology_list[] = $RowParse;
            }
        }

        $parse['noresearch'] = (!$this->isLaboratoryInQueue() ? __('game/research.re_building_lab') : '');
        $parse['technologies'] = $technology_list;

        Template::legacyView(
            'research.view',
            $parse
        );
    }

    /**
        * @SuppressWarnings("PHPMD.NPathComplexity")
        * @SuppressWarnings("PHPMD.ExcessiveMethodLength")
     */
    private function doCommand(): void
    {
        $cmd = isset($_GET['cmd']) ? $_GET['cmd'] : null;

        if (!is_null($cmd)) {
            $technology = (int) $_GET['tech'];

            if (in_array($technology, $this->_reslist['tech'])) {
                $update_data = false;

                if (is_array($this->_is_working['working_on'])) {
                    $working_planet = $this->_is_working['working_on'];
                } else {
                    $working_planet = $this->planet;
                }

                switch ($cmd) {
                    // cancel a research
                    case 'cancel':
                        if (!empty($this->_is_working['working_on'])) {
                            if ($this->_is_working['working_on']['planet_b_tech_id'] == $technology) {
                                $costs = $this->developmentsService->developmentPrice(
                                    $technology,
                                    (int) $this->user[$this->_resource[$technology]]
                                );
                                $working_planet['planet_metal'] += $costs['metal'];
                                $working_planet['planet_crystal'] += $costs['crystal'];
                                $working_planet['planet_deuterium'] += $costs['deuterium'];
                                $working_planet['planet_b_tech_id'] = 0;
                                $working_planet['planet_b_tech'] = 0;
                                $this->user['research_current_research'] = 0;
                                $update_data = true;
                                $this->_is_working['is_working'] = false;
                            }
                        }

                        break;

                        // start a research
                    case 'search':
                        $searchLevels = [];
                        foreach ($this->_resource as $id => $column) {
                            $searchLevels[$id] = (int) ($working_planet[$column] ?? $this->user[$column] ?? 0);
                        }

                        $intergalLevel = (int) ($this->user[$this->_resource[Research::research_intergalactic_research_network]] ?? 0);
                        $searchLabLevel = $intergalLevel >= 1 ? $this->_lab_level : (int) ($working_planet[$this->_resource[BuildingsEnumerator::BUILDING_LABORATORY]] ?? 0);
                        $searchAstroLevel = (int) ($this->user[$this->_resource[Research::research_astrophysics]] ?? 0);
                        $searchTechnoActive = $this->officerService->isOfficerActive((int) $this->user['premium_officier_technocrat'], time());

                        if ($this->developmentsService->isDevelopmentAllowed($technology, $searchLevels) &&
                            $this->developmentsService->isDevelopmentPayable($working_planet, $technology, (int) $this->user[$this->_resource[$technology]]) &&
                            !$this->userLibrary->isOnVacations($this->user)
                        ) {
                            $costs = $this->developmentsService->developmentPrice(
                                $technology,
                                (int) $this->user[$this->_resource[$technology]]
                            );

                            $working_planet['planet_metal'] -= $costs['metal'];
                            $working_planet['planet_crystal'] -= $costs['crystal'];
                            $working_planet['planet_deuterium'] -= $costs['deuterium'];
                            $working_planet['planet_b_tech_id'] = $technology;
                            $working_planet['planet_b_tech'] = time() + $this->developmentsService->developmentTime(
                                $technology,
                                (int) $this->user[$this->_resource[$technology]],
                                0,
                                0,
                                $searchLabLevel,
                                $searchAstroLevel,
                                $searchTechnoActive
                            );

                            $this->user['research_current_research'] = $working_planet['planet_id'];
                            $update_data = true;
                            $this->_is_working['is_working'] = true;
                        }

                        break;
                }

                if ($update_data == true) {
                    DB::statement(
                        $this->prepareSql(
                            'UPDATE `' . PLANETS . '` AS p, `' . RESEARCH . "` AS r SET
                                p.`planet_b_tech_id` = '" . $working_planet['planet_b_tech_id'] . "',
                                p.`planet_b_tech` = '" . $working_planet['planet_b_tech'] . "',
                                p.`planet_metal` = '" . $working_planet['planet_metal'] . "',
                                p.`planet_crystal` = '" . $working_planet['planet_crystal'] . "',
                                p.`planet_deuterium` = '" . $working_planet['planet_deuterium'] . "',
                                r.`research_current_research` = '" . $this->user['research_current_research'] . "'
                            WHERE p.`planet_id` = '" . $working_planet['planet_id'] . "'
                                AND r.`research_user_id` = '" . $this->user['id'] . "';"
                        )
                    );
                }

                $this->planet = $working_planet;

                if (is_array($this->_is_working['working_on'])) {
                    $this->_is_working['working_on'] = $working_planet;
                } else {
                    $this->planet = $working_planet;

                    if ($cmd == 'search') {
                        $this->_is_working['working_on'] = $this->planet;
                    }
                }
            }

            Functions::redirect('game.php?page=research');
        }
    }

    private function isLaboratoryInQueue(): bool
    {
        if ($this->planet['planet_b_building_id'] != 0) {
            $current_queue = $this->planet['planet_b_building_id'];
            $queue = explode(';', $current_queue);

            for ($i = 0; $i < MAX_BUILDING_QUEUE_SIZE; $i++) {
                if (isset($queue[$i])) {
                    if (explode(',', $queue[$i])[0] == 31) {
                        return false;
                    }
                }
            }
        }

        return true;
    }

    private function handleTechnologieBuild(): void
    {
        $this->_is_working['working_on'] = '';
        $this->_is_working['is_working'] = false;

        if ($this->user['research_current_research'] != 0) {
            if ($this->user['research_current_research'] != $this->planet['planet_id']) {
                $row = DB::selectOne(
                    $this->prepareSql(
                        'SELECT
                            `planet_id`,
                            `planet_name`,
                            `planet_b_tech`,
                            `planet_b_tech_id`,
                            `planet_galaxy`,
                            `planet_system`,
                            `planet_planet`
                        FROM `' . PLANETS . "`
                        WHERE `planet_id` = '" . $this->user['research_current_research'] . "';"
                    )
                );
                $working_planet = $row !== null ? (array) $row : [];
            }

            if (isset($working_planet)) {
                $the_planet = $working_planet;
            } else {
                $the_planet = $this->planet;
            }

            if ($the_planet['planet_b_tech'] <= time() && $the_planet['planet_b_tech_id'] != 0) {
                $the_planet['planet_b_tech_id'] = 0;

                if (isset($working_planet)) {
                    $working_planet = $the_planet;
                } else {
                    $this->planet = $the_planet;
                }
            } elseif ($the_planet['planet_b_tech_id'] == 0) {
                $this->_is_working['working_on'] = '';
                $this->_is_working['is_working'] = false;
            } else {
                $this->_is_working['working_on'] = $the_planet;
                $this->_is_working['is_working'] = true;
            }
        }
    }

    private function setLabsAmount(): void
    {
        $labsLimit = $this->user[$this->_resource[123]] + 1;
        $this->_lab_level = (int) Buildings::selectRaw('SUM(building_laboratory) AS total_level')
            ->leftJoin('planets', 'planet_id', '=', 'building_planet_id')
            ->where('planet_user_id', $this->user['id'])
            ->orderBy('building_laboratory', 'DESC')
            ->limit($labsLimit)
            ->value('total_level');
    }
}
