<?php

namespace Xgp\App\Http\Controllers\Game;

use Illuminate\Routing\Controller as BaseController;
use Xgp\App\Core\Template;
use Xgp\App\Libraries\FormatLib;
use Xgp\App\Libraries\Functions;
use Xgp\App\Libraries\Game\AcsFleets;
use Xgp\App\Libraries\Game\Fleets;
use Xgp\App\Libraries\Users;
use Xgp\App\Models\Game\Buddies;
use Xgp\App\Models\Game\Fleet;

class FederationController extends BaseController
{
    public const MODULE_ID = 8;
    public const REDIRECT_TARGET = 'game.php?page=fleet1';

    private array $user = [];
    private ?Fleets $_fleets = null;
    private ?AcsFleets $_group = null;
    private string $_acs_code = '';
    private int $_members_count = 0;
    private string $_message = '';
    private Fleet $fleetModel;
    private Buddies $buddiesModel;

    public function __invoke()
    {
        Users::checkSession();

        Functions::moduleMessage(Functions::isModuleAccesible(self::MODULE_ID));

        $this->user = Users::getInstance()->getUserData();
        $this->fleetModel = new Fleet();
        $this->buddiesModel = new Buddies();

        // init a new fleets object
        $this->setUpFleets();

        $this->runAction();
        $this->buildPage();
    }

    /**
     * Creates a new fleets object that will handle all the fleets
     * creation methods and actions
     *
     * @return void
     */
    private function setUpFleets()
    {
        $this->_fleets = new Fleets(
            $this->fleetModel->getAllFleetsByUserId($this->user['user_id']),
            $this->user['user_id']
        );
    }

    /**
     * Run an action
     *
     * @return void
     */
    private function runAction()
    {
        $data = filter_input_array(INPUT_POST);

        if (isset($data['add']) && isset($data['friends_list'])) {
            $this->addAcsMember($data['friends_list']);
        }

        if (isset($data['remove']) && isset($data['members_list'])) {
            $this->removeAcsMember($data['members_list']);
        }

        if (isset($data['search']) && isset($data['addtogroup'])) {
            $this->searchUser($data['addtogroup']);
        }

        if (isset($data['save']) && isset($data['name_acs'])) {
            $this->saveAcsName($data['name_acs']);
        }
    }

    private function buildPage(): void
    {
        $this->validateData();

        $page = [
            'acs_code' => $this->_acs_code,
            'buddies_list' => $this->buildBuddiesList(),
            'members_list' => $this->buildMembersList(),
            'invited_count' => $this->_members_count,
            'add_error_messages' => $this->_message,
        ];

        // view with no topvar and no leftmenu
        Template::legacyView(
            'fleet/fleet_federation_view',
            $page
        );
    }

    /**
     * Add an ACS member
     *
     * @param int $member
     *
     * @return void
     */
    private function addAcsMember(int $member): void
    {
        if ((int) $member > 0) {
            $fleet_id = filter_input(INPUT_GET, 'fleet', FILTER_VALIDATE_INT);

            if ($fleet_id) {
                $own_fleet = $this->_fleets->getOwnValidFleetById($fleet_id);

                $acs = $this->fleetModel->getAcsDataByGroupId(
                    $own_fleet->getFleetGroup()
                );

                if ($acs['acs_members'] < 5
                    && $member != $this->user['user_id']) {
                    $this->fleetModel->insertNewAcsMember(
                        $member,
                        $own_fleet->getFleetGroup()
                    );

                    $invite_message = __('game/fleet.fl_player') . $this->user['user_name'] . __('game/fleet.fl_acs_invitation_message');
                    Functions::sendMessage(
                        $member,
                        $this->user['user_id'],
                        0,
                        5,
                        $this->user['user_name'],
                        __('game/fleet.fl_acs_invitation_title'),
                        $invite_message
                    );
                }
            }
        }
    }

    /**
     * Remove an ACS member
     *
     * @param int $member
     *
     * @return void
     */
    private function removeAcsMember(int $member): void
    {
        if ((int) $member > 0) {
            $fleet_id = filter_input(INPUT_GET, 'fleet', FILTER_VALIDATE_INT);

            if ($fleet_id) {
                $own_fleet = $this->_fleets->getOwnValidFleetById($fleet_id);

                $acs = $this->fleetModel->getAcsDataByGroupId(
                    $own_fleet->getFleetGroup()
                );

                if ($acs['acs_members'] >= 1
                    && $member != $this->user['user_id']) {
                    $this->fleetModel->removeAcsMember(
                        $member,
                        $own_fleet->getFleetGroup()
                    );
                }
            }
        }
    }

    private function searchUser(string $username): void
    {
        if (!empty($username)) {
            $fleet_id = filter_input(INPUT_GET, 'fleet', FILTER_VALIDATE_INT);

            $userId = $this->fleetModel->getUserIdByName($username, $fleet_id);
            if ($userId > 0 && $userId != $this->user['user_id']) {
                $this->addAcsMember($userId);

                $this->_message = FormatLib::customColor(
                    __('game/fleet.fl_player') . ' ' . $username . ' ' . __('game/fleet.fl_add_to_attack'),
                    'lime'
                );
            } else {
                $this->_message = FormatLib::colorRed(
                    __('game/fleet.fl_player') . ' ' . $username . ' ' . __('game/fleet.fl_dont_exist')
                );
            }
        }
    }

    /**
     * Save the ACS Name
     *
     * @param string $acs_name
     *
     * @return void
     */
    private function saveAcsName(string $acs_name): void
    {
        $name_len = strlen($acs_name);

        if ($name_len >= 3 && $name_len <= 20) {
            $fleet_id = filter_input(INPUT_GET, 'fleet', FILTER_VALIDATE_INT);

            if ($fleet_id) {
                $own_fleet = $this->_fleets->getOwnValidFleetById($fleet_id);

                $acs = $this->fleetModel->getAcsDataByGroupId(
                    $own_fleet->getFleetGroup()
                );

                $this->fleetModel->updateAcsName(
                    $acs_name,
                    $acs['acs_id'],
                    $this->user['user_id']
                );
            }
        }
    }

    /**
     * Validate data
     *
     * @return void
     */
    private function validateData()
    {
        $fleet_id = filter_input(INPUT_GET, 'fleet', FILTER_VALIDATE_INT);

        if ($fleet_id) {
            $own_fleet = $this->_fleets->getOwnValidFleetById($fleet_id);

            if (!is_null($own_fleet)) {
                if ($own_fleet->getFleetGroup() <= 0) {
                    // create a new acs, and get its group ID
                    $group_id = $this->fleetModel->createNewAcs(
                        $this->generateRandomAcsCode(),
                        $own_fleet
                    );
                } else {
                    $group_id = $own_fleet->getFleetGroup();
                }

                $this->_group = new AcsFleets(
                    [$this->fleetModel->getAcsDataByGroupId($group_id)],
                    $this->user['user_id']
                );

                $this->_acs_code = $this->_group->getFirstAcs()->getAcsFleetName();
            }
        } else {
            Functions::redirect(self::REDIRECT_TARGET);
        }
    }

    /**
     * Generates a random ACS code
     *
     * @return string
     */
    private function generateRandomAcsCode(): string
    {
        return 'AG' . mt_rand(100000, 999999999);
    }

    /**
     * Build the list of friends
     *
     * @return array
     */
    private function buildBuddiesList(): array
    {
        $list_of_buddies = [];

        $buddies = $this->buddiesModel->getBuddiesDetailsForAcsById(
            $this->user['user_id'],
            $this->_group->getFirstAcs()->getAcsFleetId()
        );

        if (count($buddies) > 0) {
            foreach ($buddies as $buddy) {
                if ($buddy['user_id'] != $this->user['user_id']) {
                    $list_of_buddies[] = [
                        'value' => $buddy['user_id'],
                        'title' => $buddy['user_name'],
                    ];
                }
            }
        }

        return $list_of_buddies;
    }

    /**
     * Build the list of members
     *
     * @return array
     */
    private function buildMembersList(): array
    {
        $list_of_members = [];

        $members = $this->fleetModel->getListOfAcsMembers(
            $this->_group->getFirstAcs()->getAcsFleetId()
        );

        if (count($members) > 0) {
            foreach ($members as $member) {
                ++$this->_members_count;

                $list_of_members[] = [
                    'value' => $member['user_id'],
                    'title' => $member['user_name'],
                ];
            }
        }

        return $list_of_members;
    }
}
