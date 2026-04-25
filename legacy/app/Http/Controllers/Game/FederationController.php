<?php

declare(strict_types=1);

namespace Xgp\App\Http\Controllers\Game;

use App\Enums\Module;
use App\Services\FormatService;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;
use Xgp\App\Core\Concerns\PreparesLegacySql;
use Xgp\App\Core\Template;
use Xgp\App\Libraries\Functions;
use Xgp\App\Libraries\Game\AcsFleets;
use Xgp\App\Libraries\Game\Fleets;
use Xgp\App\Libraries\Users;

/**
 * @SuppressWarnings("PHPMD.StaticAccess")
 */
class FederationController extends BaseController
{
    use PreparesLegacySql;

    public const REDIRECT_TARGET = 'game.php?page=fleet1';

    private array $user = [];
    private ?Fleets $_fleets = null;
    private ?AcsFleets $_group = null;
    private string $_acs_code = '';
    private int $_members_count = 0;
    private string $_message = '';

    public function __construct(private FormatService $formatService)
    {
    }

    public function __invoke(): void
    {
        Functions::moduleMessage(Functions::isModuleAccesible(Module::Fleet));

        $this->user = Users::getInstance()->getUserData();

        // init a new fleets object
        $this->setUpFleets();

        $this->runAction();
        $this->buildPage();
    }

    private function setUpFleets(): void
    {
        $userId = (int) $this->user['id'];
        $this->_fleets = new Fleets(
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
    }

    private function runAction(): void
    {
        $data = filter_input_array(INPUT_POST);

        if (isset($data['add']) && isset($data['friends_list'])) {
            $this->addAcsMember((int) $data['friends_list']);
        }

        if (isset($data['remove']) && isset($data['members_list'])) {
            $this->removeAcsMember((int) $data['members_list']);
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

                $acs = $this->getAcsDataByGroupId($own_fleet->getFleetGroup());

                if ($acs['acs_members'] < 5 &&
                    $member != $this->user['id']) {
                    DB::statement(
                        $this->prepareSql(
                            'INSERT INTO `' . ACS_MEMBERS . "` SET
                                `acs_group_id` = '" . (int) $own_fleet->getFleetGroup() . "',
                                `acs_user_id` = '" . $member . "'"
                        )
                    );

                    $invite_message = __('game/fleet.fl_player') . $this->user['name'] . __('game/fleet.fl_acs_invitation_message');
                    Functions::sendMessage(
                        $member,
                        $this->user['id'],
                        0,
                        5,
                        $this->user['name'],
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

                $acs = $this->getAcsDataByGroupId($own_fleet->getFleetGroup());

                if ($acs['acs_members'] >= 1 &&
                    $member != $this->user['id']) {
                    DB::statement(
                        $this->prepareSql(
                            'DELETE FROM `' . ACS_MEMBERS . "`
                            WHERE `acs_group_id` = '" . (int) $own_fleet->getFleetGroup() . "'
                                AND `acs_user_id` = '" . $member . "'"
                        )
                    );
                }
            }
        }
    }

    private function searchUser(string $username): void
    {
        if (!empty($username)) {
            $fleet_id = filter_input(INPUT_GET, 'fleet', FILTER_VALIDATE_INT);

            $userRow = DB::selectOne(
                $this->prepareSql(
                    'SELECT u.`id`
                    FROM `' . USERS . "` u
                    WHERE u.`name` = '" . $username . "'
                    AND u.`id` NOT IN (
                        SELECT acs.`acs_user_id`
                        FROM `" . ACS_MEMBERS . "` acs
                        WHERE acs.`acs_group_id` = '" . (int) $fleet_id . "'
                    )"
                )
            );
            $userId = $userRow !== null ? (int) $userRow->id : 0;
            if ($userId > 0 && $userId != $this->user['id']) {
                $this->addAcsMember($userId);

                $this->_message = $this->formatService->customColor(
                    __('game/fleet.fl_player') . ' ' . $username . ' ' . __('game/fleet.fl_add_to_attack'),
                    'lime'
                );
            } else {
                $this->_message = $this->formatService->colorRed(
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

                $acs = $this->getAcsDataByGroupId($own_fleet->getFleetGroup());

                DB::statement(
                    $this->prepareSql(
                        'UPDATE `' . ACS . "` acs SET
                            acs.`acs_name` = ?
                        WHERE acs.`acs_id` = '" . (int) $acs['acs_id'] . "'
                            AND acs.`acs_owner` = '" . (int) $this->user['id'] . "';"
                    ),
                    [$acs_name]
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
                    $acs_code = $this->generateRandomAcsCode();
                    $group_id = DB::transaction(function () use ($acs_code, $own_fleet): int {
                        DB::statement(
                            $this->prepareSql(
                                'INSERT INTO `' . ACS . "` SET
                                    `acs_name` = ?,
                                    `acs_owner` = '" . $own_fleet->getFleetOwner() . "',
                                    `acs_galaxy` = '" . $own_fleet->getFleetEndGalaxy() . "',
                                    `acs_system` = '" . $own_fleet->getFleetEndSystem() . "',
                                    `acs_planet` = '" . $own_fleet->getFleetEndPlanet() . "',
                                    `acs_planet_type` = '" . $own_fleet->getFleetEndType() . "'"
                            ),
                            [$acs_code]
                        );

                        $group_id = (int) DB::getPdo()->lastInsertId();

                        DB::statement(
                            $this->prepareSql(
                                'UPDATE `' . FLEETS . "` SET
                                    `fleet_group` = '" . $group_id . "'
                                WHERE `fleet_id` = '" . $own_fleet->getFleetId() . "'"
                            )
                        );

                        DB::statement(
                            $this->prepareSql(
                                'INSERT INTO `' . ACS_MEMBERS . "` SET
                                    `acs_group_id` = '" . $group_id . "',
                                    `acs_user_id` = '" . $own_fleet->getFleetOwner() . "'"
                            )
                        );

                        return $group_id;
                    });
                } else {
                    $group_id = $own_fleet->getFleetGroup();
                }

                $this->_group = new AcsFleets(
                    [$this->getAcsDataByGroupId($group_id)],
                    $this->user['id']
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

    private function getAcsDataByGroupId(int $groupId): array
    {
        if ($groupId > 0) {
            $row = DB::selectOne(
                $this->prepareSql(
                    'SELECT
                        acs.*,
                        (
                            SELECT COUNT(*)
                            FROM `' . ACS_MEMBERS . '` am
                            WHERE am.`acs_group_id` = acs.`acs_id`
                        ) AS `acs_members`
                    FROM `' . ACS . "` acs
                    WHERE acs.`acs_id` = '" . $groupId . "';"
                )
            );

            return $row !== null ? (array) $row : [];
        }

        return [];
    }

    /**
     * Build the list of friends
     *
     * @return array
     */
    private function buildBuddiesList(): array
    {
        $list_of_buddies = [];

        $userId = (int) $this->user['id'];
        $groupId = (int) $this->_group->getFirstAcs()->getAcsFleetId();

        $buddies = array_map(
            fn ($row) => (array) $row,
            DB::select(
                $this->prepareSql(
                    'SELECT DISTINCT u.`id`, u.`name`
                    FROM `' . BUDDY . '` AS b
                    LEFT JOIN `' . USERS . "` AS u
                        ON ((u.id = b.buddy_sender) OR (u.id = b.buddy_receiver))
                    WHERE (
                        b.`buddy_sender` = '" . $userId . "'
                        OR b.`buddy_receiver` = '" . $userId . "'
                    )
                    AND b.`buddy_status` = '1'
                    AND u.`id` NOT IN (
                        SELECT acs.`acs_user_id`
                        FROM `" . ACS_MEMBERS . "` acs
                        WHERE acs.`acs_group_id` = '" . $groupId . "'
                    )"
                )
            )
        );

        if (count($buddies) > 0) {
            foreach ($buddies as $buddy) {
                if ($buddy['id'] != $this->user['id']) {
                    $list_of_buddies[] = [
                        'value' => $buddy['id'],
                        'title' => $buddy['name'],
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

        $members = array_map(
            fn ($row) => (array) $row,
            DB::select(
                $this->prepareSql(
                    'SELECT u.`id`, u.`name`
                    FROM `' . ACS_MEMBERS . '` am
                    INNER JOIN `' . USERS . "` u ON u.`id` = am.`acs_user_id`
                    WHERE am.`acs_group_id` = '" . (int) $this->_group->getFirstAcs()->getAcsFleetId() . "'"
                )
            )
        );

        if (count($members) > 0) {
            foreach ($members as $member) {
                ++$this->_members_count;

                $list_of_members[] = [
                    'value' => $member['id'],
                    'title' => $member['name'],
                ];
            }
        }

        return $list_of_members;
    }
}
