<?php

namespace Xgp\App\Http\Controllers\Game;

use Illuminate\Routing\Controller as BaseController;
use Xgp\App\Core\Enumerators\AllianceRanksEnumerator as AllianceRanks;
use Xgp\App\Core\Enumerators\SwitchIntEnumerator as SwitchInt;
use Xgp\App\Core\Template;
use Xgp\App\Helpers\StringsHelper;
use Xgp\App\Helpers\UrlHelper;
use Xgp\App\Libraries\Alliance\Alliances;
use Xgp\App\Libraries\BBCodeLib;
use Xgp\App\Libraries\FormatLib;
use Xgp\App\Libraries\Functions;
use Xgp\App\Libraries\TimingLibrary as Timing;
use Xgp\App\Libraries\Users;
use Xgp\App\Models\Game\Alliance;

class AllianceController extends BaseController
{
    public const MODULE_ID = 13;
    public const DEFAULT_RANKS = [
        'founder' => 0,
        'newcomer' => 1,
    ];

    private array $user = [];
    private ?BBCodeLib $bbcode = null;
    private ?Alliances $alliance = null;
    private Alliance $allianceModel;

    public function __invoke(): void
    {
        Functions::moduleMessage(Functions::isModuleAccesible(self::MODULE_ID));

        $this->user = Users::getInstance()->getUserData();
        $this->bbcode = new BBCodeLib();
        $this->allianceModel = new Alliance();

        $this->setUpAlliances();
        $this->buildPage();
    }

    private function setUpAlliances(): void
    {
        $this->alliance = new Alliances(
            $this->allianceModel->getAllianceDataById($this->getAllianceId()),
            $this->user['id'],
            $this->user['ally_rank_id']
        );
    }

    private function isPageAllowed(): bool
    {
        $allowed_pages = [
            'public' => [
                'default', 'ainfo', 'make', 'search', 'apply',
            ],
            'awaitingApproval' => [
                'default', 'ainfo',
            ],
            'isMember' => [
                'default', 'ainfo', 'exit', 'memberslist', 'circular', 'admin',
            ],
        ];

        return in_array(
            $this->getCurrentSection(),
            array_filter($allowed_pages[$this->getUserAccess()], function ($value) {
                return $value;
            })
        );
    }

    private function getUserAccess(): string
    {
        // not in an alliance
        if ((int) $this->user['ally_id'] === 0) {
            // doesn't have a request
            if ((int) $this->user['ally_request'] === 0) {
                // it's public then
                return 'public';
            }

            // supposedly a request was sent
            return 'awaitingApproval';
        }

        // any other case
        return 'isMember';
    }

    private function getAllianceId(): ?int
    {
        $alliance_id = filter_input(INPUT_GET, 'allyid', FILTER_VALIDATE_INT);

        if (!empty($alliance_id) && (int) $alliance_id != 0) {
            return $alliance_id;
        }

        if ($this->user['ally_id'] != 0) {
            return $this->user['ally_id'];
        }

        if ($this->user['ally_request'] != 0) {
            return $this->user['ally_request'];
        }

        return $alliance_id;
    }

    private function getCurrentSection(): string
    {
        $mode = filter_input(INPUT_GET, 'mode');

        return (isset($mode) ? $mode : 'default');
    }

    private function buildPage(): void
    {
        if (!$this->isPageAllowed()) {
            Functions::redirect('game.php?page=alliance');
        }

        $this->{'get' . ucfirst($this->getCurrentSection()) . 'Section'}();
    }

    /**
     *
     * PUBLIC / MEMBERS SECTIONS
     *
     */
    private function getDefaultSection(): void
    {
        $this->{'getDefault' . ucfirst($this->getUserAccess()) . 'Section'}();
    }

    private function getDefaultPublicSection(): void
    {
        Template::legacyView('alliance.start');
    }

    private function getDefaultAwaitingApprovalSection(): void
    {
        $cancel = filter_input(INPUT_POST, 'bcancel');
        $request_text = __('game/alliance.al_request_wait_message');
        $button_text = __('game/alliance.al_delete_request');

        if (!empty($cancel)) {
            $this->allianceModel->cancelUserRequestById($this->user['id']);
            $request_text = __('game/alliance.al_request_deleted');
            $button_text = __('game/alliance.al_continue');
        }

        Template::legacyView(
            'alliance.awaiting',
            [
                'request_text' => str_replace('%s', $this->alliance->getCurrentAlliance()->getAllianceTag(), $request_text),
                'button_text' => $button_text,
            ]
        );
    }

    private function getDefaultIsMemberSection(): void
    {
        $blocks = [
            'tag', 'name', 'members', 'rank', 'requests', 'circular', 'web',
        ];
        $details = [];

        foreach ($blocks as $block) {
            $data = $this->{'build' . ucfirst($block) . 'Block'}();

            if (empty($data['detail_content'])) {
                continue;
            }

            $details[] = $data;
        }

        Template::legacyView(
            'alliance.front',
            [
                'image' => $this->buildImageBlock(),
                'details' => $details,
                'description' => $this->buildDescriptionBlock(),
                'text' => $this->buildTextBlock(),
                'leave' => !$this->alliance->isOwner(),
            ]
        );
    }

    private function getAinfoSection(): void
    {
        Template::legacyView(
            'alliance.ainfo',
            [
                'image' => $this->buildImageBlock(),
                'tag' => $this->alliance->getCurrentAlliance()->getAllianceTag(),
                'name' => $this->alliance->getCurrentAlliance()->getAllianceName(),
                'members' => $this->alliance->getCurrentAlliance()->getAllianceMembers(),
                'description' => $this->buildDescriptionBlock(),
                'web' => $this->buildWebBlock(),
                'requests' => $this->buildPublicRequestsBlock(),
            ]
        );
    }

    private function getSearchSection(): void
    {
        $searchString = filter_input(INPUT_POST, 'searchtext');
        $searchResults = [];

        if (!empty($searchString)) {
            $searchResults = [];
            $results = new Alliances(
                $this->allianceModel->searchAllianceByNameTag($searchString),
                $this->user['id']
            );

            foreach ($results->getAlliances() as $result) {
                $searchResults[] = [
                    'ally_tag' => UrlHelper::setUrl('game.php?page=alliance&mode=apply&allyid=' . $result->getAllianceId(), $result->getAllianceTag()),
                    'alliance_name' => $result->getAllianceName(),
                    'ally_members' => $result->getAllianceMembers(),
                ];
            }
        }

        Template::legacyView(
            'alliance.search',
            [
                'searchtext' => $searchString,
                'searchResults' => $searchResults,
            ]
        );
    }

    private function getMakeSection(): void
    {
        $action = filter_input_array(INPUT_POST);

        if (is_array($action)) {
            $alliance_tag = $action['atag'];
            $alliance_name = $action['aname'];

            if (strlen($alliance_tag) < 3 or strlen($alliance_tag) > 8) {
                Functions::message(__('game/alliance.al_tag_required'), 'game.php?page=alliance&mode=make', 3);
            }

            if ($this->allianceTagExists($alliance_tag)) {
                Functions::message(strtr(__('game/alliance.al_tag_already_exists'), ['%s' => $alliance_tag]), 'game.php?page=alliance&mode=make', 3);
            }

            if (strlen($alliance_name) < 3 or strlen($alliance_name) > 30) {
                Functions::message(__('game/alliance.al_name_required'), 'game.php?page=alliance&mode=make', 3);
            }

            if ($this->allianceNameExists($alliance_name)) {
                Functions::message(strtr(__('game/alliance.al_name_already_exists'), ['%s' => $alliance_name]), 'game.php?page=alliance&mode=make', 3);
            }

            $this->allianceModel->createNewAlliance(
                $alliance_name,
                $alliance_tag,
                $this->user['id'],
                __('game/alliance.al_founder_rank_text'),
                __('game/alliance.al_new_member_rank_text')
            );

            $message = str_replace(['%s', '%d'], [$alliance_name, $alliance_tag], __('game/alliance.al_created'));
            Functions::messageBox(
                $message,
                $message . '<br><br>',
                'game.php?page=alliance',
                __('game/alliance.al_continue')
            );
        } else {
            Template::legacyView('alliance.make');
        }
    }

    private function getApplySection(): void
    {
        if (!$this->alliance->getCurrentAlliance()->getAllianceRequestNotAllow()) {
            Functions::message(__('game/alliance.al_alliance_closed'), 'game.php?page=alliance', 3);
        }

        $request = filter_input_array(INPUT_POST);

        if (isset($request)) {
            if ($request['send'] != null && !empty($request['text'])) {
                $this->allianceModel->createNewUserRequest(
                    $this->getAllianceId(),
                    $request['text'],
                    $this->user['id']
                );

                Functions::message(__('game/alliance.al_request_confirmation_message'), 'game.php?page=alliance', 3);
            }
        }

        Template::legacyView(
            'alliance.apply',
            [
                'allyid' => $this->getAllianceId(),
                'text_apply' => (!empty($this->alliance->getCurrentAlliance()->getAllianceRequest())) ? $this->alliance->getCurrentAlliance()->getAllianceRequest() : __('game/alliance.al_default_request_text'),
                'write_to_alliance' => strtr(
                    __('game/alliance.al_write_request'),
                    ['%s' => $this->alliance->getCurrentAlliance()->getAllianceTag()]
                ),
            ]
        );
    }

    private function getMemberslistSection(): void
    {
        if (!$this->alliance->hasAccess(AllianceRanks::VIEW_MEMBER_LIST)) {
            Functions::redirect('game.php?page=alliance');
        }

        $sort_by_field = filter_input(INPUT_GET, 'sort1');
        $sort_by_order = filter_input(INPUT_GET, 'sort2');
        $sort_by_order_rules = [1 => 2, 2 => 1];

        $members = $this->allianceModel->getAllianceMembers(
            $this->user['ally_id'],
            $sort_by_field,
            $sort_by_order
        );

        $position = 0;
        $members_list = [];

        foreach ($members as $member) {
            $position++;

            $members_list[] = [
                'position' => $position,
                'name' => $member['name'],
                'id' => $member['id'],
                'write_message' => __('game/global.write_message'),
                'ally_range' => $this->getUserRank($member['id'], $member['ally_rank_id']),
                'points' => FormatLib::prettyNumber($member['user_statistic_total_points']),
                'galaxy' => $member['galaxy'],
                'system' => $member['system'],
                'coords' => FormatLib::prettyCoords($member['galaxy'], $member['system'], $member['planet']),
                'ally_register_time' => Timing::formatExtendedDate($member['ally_register_time']),
                'online_time' => $this->alliance->hasAccess(AllianceRanks::ONLINE_STATUS) ? Timing::setOnlineStatus($member['onlinetime']) : '-',
            ];
        }

        Template::legacyView(
            'alliance.members',
            [
                'total' => $position,
                's' => isset($sort_by_order_rules[$sort_by_order]) ? $sort_by_order_rules[$sort_by_order] : 1,
                'list_of_members' => $members_list,
            ]
        );
    }

    private function getCircularSection(): void
    {
        if (!$this->alliance->hasAccess(AllianceRanks::SEND_CIRCULAR)) {
            Functions::redirect('game.php?page=alliance');
        }

        if ((bool) filter_input(INPUT_GET, 'sendmail', FILTER_VALIDATE_INT)) {
            $post = filter_input_array(INPUT_POST, [
                'r' => FILTER_SANITIZE_NUMBER_INT,
                'text' => FILTER_UNSAFE_RAW,
            ]);

            $members_list = [];

            if (!(bool) $post['r']) {
                $members = $this->allianceModel->getAllianceMembersById(
                    $this->user['ally_id']
                );
            } else {
                $members = $this->allianceModel->getAllianceMembersByIdAndRankId(
                    $this->user['ally_id'],
                    $post['r']
                );
            }

            if (count($members) > 0) {
                foreach ($members as $member) {
                    Functions::sendMessage(
                        $member['id'],
                        $this->user['id'],
                        0,
                        3,
                        $this->alliance->getCurrentAlliance()->getAllianceTag(),
                        $this->user['name'],
                        $post['text']
                    );

                    $members_list[] = $member['name'];
                }
            }

            Functions::messageBox(
                __('game/alliance.al_circular_sended'),
                join('<br>', $members_list),
                'game.php?page=alliance',
                __('game/alliance.al_continue'),
                true
            );
        }

        $ranks = $this->alliance->getCurrentAllianceRankObject();
        $list_of_ranks = $ranks->getAllRanksAsArray();
        $ranks_list = [];

        if (is_array($list_of_ranks)) {
            foreach ($list_of_ranks as $id => $rank) {
                $ranks_list[] = [
                    'value' => $id + 1,
                    'name' => $rank['rank'],
                ];
            }
        }

        Template::legacyView(
            'alliance.circular',
            [
                'ranks_list' => $ranks_list,
            ]
        );
    }

    private function getExitSection(): void
    {
        if ($this->alliance->isOwner()) {
            Functions::message(__('game/alliance.al_founder_cant_leave_alliance'), 'game.php?page=alliance', 3);
        }

        if ((bool) filter_input(INPUT_GET, 'yes', FILTER_VALIDATE_INT)) {
            $this->allianceModel->exitAlliance(
                $this->getAllianceId(),
                $this->user['id']
            );

            Functions::messageBox(
                strtr(__('game/alliance.al_leave_sucess'), ['%s' => $this->alliance->getCurrentAlliance()->getAllianceName()]),
                '<br>',
                'game.php?page=alliance',
                __('game/alliance.al_continue')
            );
        }

        Functions::messageBox(
            strtr(__('game/alliance.al_do_you_really_want_to_go_out'), ['%s' => $this->alliance->getCurrentAlliance()->getAllianceName()]),
            '<br>',
            'game.php?page=alliance&mode=exit&yes=1',
            __('game/alliance.al_go_out_yes')
        );
    }

    /**
     *
     * ADMINS SECTION
     *
     */
    private function getAdminSection(): string
    {
        $edit = filter_input(INPUT_GET, 'edit');

        $admin_sections = [
            'ally' => AllianceRanks::ADMINISTRATION,
            'exit' => AllianceRanks::DELETE,
            'members' => AllianceRanks::ADMINISTRATION,
            'name' => AllianceRanks::ADMINISTRATION,
            'requests' => AllianceRanks::APPLICATION_MANAGEMENT,
            'rights' => AllianceRanks::RIGHT_HAND,
            'tag' => AllianceRanks::ADMINISTRATION,
            'transfer' => AllianceRanks::ADMINISTRATION,
        ];

        if (isset($admin_sections[$edit]) && $this->alliance->hasAccess($admin_sections[$edit])) {
            return $this->{'getAdmin' . ucfirst($edit) . 'Section'}();
        }

        Functions::redirect('game.php?page=alliance');
    }

    private function getAdminAllySection(): void
    {
        $t = filter_input(INPUT_GET, 't', FILTER_VALIDATE_INT, [
            'options' => [
                'default' => 1,
                'min_range' => 1,
                'max_range' => 3,
            ],
        ]);

        $post = filter_input_array(INPUT_POST, [
            't' => [
                'filter' => FILTER_SANITIZE_NUMBER_INT,
                'options' => ['default' => 1, 'min_range' => 1, 'max_range' => 3],
            ],
            'text' => FILTER_UNSAFE_RAW,
            'options' => FILTER_UNSAFE_RAW,
            'owner_range' => FILTER_UNSAFE_RAW,
            'newcomer_range' => FILTER_UNSAFE_RAW,
            'web' => FILTER_VALIDATE_URL,
            'image' => FILTER_VALIDATE_URL,
            'request_notallow' => [
                'filter' => FILTER_SANITIZE_NUMBER_INT,
                'options' => ['default' => 1, 'min_range' => 0, 'max_range' => 1],
            ],
        ]);

        if (isset($post['options'])) {
            $this->allianceModel->updateAllianceSettings(
                $this->getAllianceId(),
                [
                    'alliance_owner_range' => ($post['owner_range'] ? StringsHelper::escapeString($post['owner_range']) : ''),
                    'alliance_web' => ($post['web'] ? StringsHelper::escapeString($post['web']) : ''),
                    'alliance_image' => ($post['image'] ? StringsHelper::escapeString($post['image']) : ''),
                    'alliance_request_notallow' => $post['request_notallow'],
                ]
            );

            $ranks = $this->alliance->getCurrentAllianceRankObject();

            // edit owner rank name
            if (isset($post['owner_range'])) {
                $ranks->editRankNameById(
                    self::DEFAULT_RANKS['founder'],
                    $post['owner_range']
                );
            }

            // edit newcomer rank name
            if (isset($post['newcomer_range'])) {
                $ranks->editRankNameById(
                    self::DEFAULT_RANKS['newcomer'],
                    $post['newcomer_range']
                );
            }

            $this->allianceModel->updateAllianceRanks(
                $this->getAllianceId(),
                $ranks->getAllRanksAsJsonString()
            );

            Functions::redirect('game.php?page=alliance&mode=admin&edit=ally');
        }

        if (isset($post['t'])) {
            $callback = [
                1 => 'Description',
                2 => 'Text',
                3 => 'RequestText',
            ];

            $this->allianceModel->{'updateAlliance' . $callback[$t]}(
                $this->getAllianceId(),
                StringsHelper::escapeString($post['text'])
            );

            Functions::redirect('game.php?page=alliance&mode=admin&edit=ally&t=' . $t);
        }

        $request_type = [
            1 => __('game/alliance.al_outside_text'),
            2 => __('game/alliance.al_inside_text'),
            3 => __('game/alliance.al_request_text'),
        ];

        $text = [
            1 => $this->alliance->getCurrentAlliance()->getAllianceDescription(),
            2 => $this->alliance->getCurrentAlliance()->getAllianceText(),
            3 => $this->alliance->getCurrentAlliance()->getAllianceRequest(),
        ];

        $ranks = $this->alliance->getCurrentAllianceRankObject();

        Template::legacyView(
            'alliance.admin.view',
            [
                't' => $t,
                'request_type' => $request_type[$t],
                'text' => $text[$t],
                'alliance_web' => $this->alliance->getCurrentAlliance()->getAllianceWeb(),
                'alliance_image' => $this->alliance->getCurrentAlliance()->getAllianceImage(),
                'alliance_request_notallow_0' => $this->alliance->getCurrentAlliance()->getAllianceRequestNotAllow() == SwitchInt::off ? 'selected' : '',
                'alliance_request_notallow_1' => $this->alliance->getCurrentAlliance()->getAllianceRequestNotAllow() == SwitchInt::on ? 'selected' : '',
                'alliance_owner_range' => $ranks->getRankById(self::DEFAULT_RANKS['founder'])['rank'],
                'alliance_newcomer_range' => $ranks->getRankById(self::DEFAULT_RANKS['newcomer'])['rank'],
            ]
        );
    }

    private function getAdminExitSection(): void
    {
        $this->allianceModel->deleteAlliance($this->getAllianceId());

        Functions::redirect('game.php?page=alliance');
    }

    private function getAdminMembersSection(): void
    {
        $kick = filter_input(INPUT_GET, 'kick', FILTER_VALIDATE_INT);
        $rank = filter_input(INPUT_GET, 'rank', FILTER_VALIDATE_INT);
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        $new_rank = filter_input(INPUT_POST, 'newrang', FILTER_VALIDATE_INT, ['options' => ['default' => 0]]);

        if (isset($kick)
            && $this->alliance->hasAccess(AllianceRanks::KICK)
            && $kick != $this->alliance->getCurrentAlliance()->getAllianceOwner()) {
            $this->allianceModel->exitAlliance(
                $this->getAllianceId(),
                $kick
            );
        }

        if (
            isset($new_rank)
            && isset($id)
            && $id != $this->alliance->getCurrentAlliance()->getAllianceOwner()
        ) {
            $ranks = $this->alliance->getCurrentAllianceRankObject();

            if ($ranks->getRankById($new_rank) != null or $new_rank == 0) {
                $this->allianceModel->updateUserRank($id, $new_rank);
            }
        }

        $sort_by_field = filter_input(INPUT_GET, 'sort1');
        $sort_by_order = filter_input(INPUT_GET, 'sort2');
        $sort_by_order_rules = [1 => 2, 2 => 1];

        $members = $this->allianceModel->getAllianceMembers(
            $this->user['ally_id'],
            $sort_by_field,
            $sort_by_order
        );

        $position = 0;
        $members_list = [];

        foreach ($members as $member) {
            $position++;

            $members_list[] = [
                'position' => $position,
                'name' => $member['name'],
                'id' => $member['id'],
                'write_message' => __('game/global.write_message'),
                'ally_range' => $this->buildAdminMembersRankBlock((int) $member['id'], (int) $member['ally_rank_id'], $rank),
                'points' => FormatLib::prettyNumber($member['user_statistic_total_points']),
                'galaxy' => $member['galaxy'],
                'system' => $member['system'],
                'coords' => FormatLib::prettyCoords($member['galaxy'], $member['system'], $member['planet']),
                'ally_register_time' => Timing::formatExtendedDate($member['ally_register_time']),
                'online_time' => Timing::formatDaysTime($member['onlinetime']),
                'actions' => $this->buildAdminMembersActionBlock((int) $member['id'], (int) $member['name'], $rank),
            ];
        }

        Template::legacyView(
            'alliance.admin.members',
            [
                'total' => $position,
                's' => isset($sort_by_order_rules[$sort_by_order]) ? $sort_by_order_rules[$sort_by_order] : 1,
                'list_of_members' => $members_list,
            ]
        );
    }

    private function getAdminNameSection(): void
    {
        $name = filter_input(INPUT_POST, 'nametag', FILTER_UNSAFE_RAW);

        if (isset($name)) {
            if (strlen($name) < 3 or strlen($name) > 30) {
                Functions::message(__('game/alliance.al_name_required'), 'game.php?page=alliance&mode=admin&edit=name', 3);
            }

            if ($this->allianceNameExists($name)) {
                Functions::message(strtr(__('game/alliance.al_name_already_exists'), ['%s' => $name]), 'game.php?page=alliance&mode=admin&edit=name', 3);
            }

            $this->allianceModel->updateAllianceName(
                $this->getAllianceId(),
                $name
            );

            Functions::redirect('game.php?page=alliance&mode=admin&edit=ally');
        }

        Template::legacyView(
            'alliance.admin.edit',
            [
                'case' => strtr(
                    __('game/alliance.al_change_title'),
                    ['%s' => $this->alliance->getCurrentAlliance()->getAllianceName()]
                ),
                'title' => __('game/alliance.al_new_name'),
            ]
        );
    }

    private function getAdminRequestsSection(): void
    {
        $show = filter_input(INPUT_GET, 'show', FILTER_VALIDATE_INT);
        $accept = filter_input(INPUT_POST, 'accept');
        $cancel = filter_input(INPUT_POST, 'cancel');
        $text = filter_input(INPUT_POST, 'text');

        if (isset($accept) && $show != 0) {
            $this->allianceModel->addUserToAlliance($show, $this->getAllianceId());

            Functions::sendMessage(
                $show,
                $this->user['id'],
                0,
                3,
                $this->alliance->getCurrentAlliance()->getAllianceTag(),
                __('game/alliance.al_you_was_acceted') . $this->alliance->getCurrentAlliance()->getAllianceName(),
                __('game/alliance.al_hi_the_alliance') . $this->alliance->getCurrentAlliance()->getAllianceName() . __('game/alliance.al_has_accepted') . $text
            );

            Functions::redirect('game.php?page=alliance&mode=admin&edit=requests');
        }

        if (isset($cancel) && $show != 0) {
            $this->allianceModel->removeUserFromAlliance($show);

            Functions::sendMessage(
                $show,
                $this->user['id'],
                0,
                3,
                $this->alliance->getCurrentAlliance()->getAllianceTag(),
                __('game/alliance.al_you_was_declined') . $this->alliance->getCurrentAlliance()->getAllianceName(),
                __('game/alliance.al_hi_the_alliance') . $this->alliance->getCurrentAlliance()->getAllianceName() . __('game/alliance.al_has_declined') . $text
            );

            Functions::redirect('game.php?page=alliance&mode=admin&edit=requests');
        }

        $requests = $this->allianceModel->getAllianceRequests($this->getAllianceId());

        $requestsAmount = count($requests);
        $requestsList = [];
        $requestForm = [];

        if ($requests) {
            foreach ($requests as $request) {
                $requestsList[$request['id']] = [
                    'id' => $request['id'],
                    'username' => $request['name'],
                    'time' => Timing::formatExtendedDate($request['ally_register_time']),
                    'ally_request_text' => nl2br($request['ally_request_text']),
                ];
            }

            if (isset($show) && isset($requestsList[$show])) {
                $requestForm = [
                    'id' => $requestsList[$show]['id'],
                    'request_from' => strtr(__('game/alliance.al_request_from'), ['%s' => $requestsList[$show]['username']]),
                    'request_text' => $requestsList[$show]['ally_request_text'],
                ];
            }
        }

        Template::legacyView(
            'alliance.admin.applications',
            array_merge(
                $requestForm,
                [
                    'pending_message' => strtr(__('game/alliance.al_no_request_pending'), ['%n' => $requestsAmount]),
                    'requestsList' => $requestsList,
                    'noRequests' => $requestsAmount === 0 ? true : false,
                    'showForm' => !empty($requestForm) ? true : false,
                ]
            )
        );
    }

    private function getAdminRightsSection(): void
    {
        $post = filter_input_array(INPUT_POST);
        $delete = filter_input(INPUT_GET, 'd', FILTER_VALIDATE_INT);

        $ranks = $this->alliance->getCurrentAllianceRankObject();

        // Create a new rank
        if (isset($post['newrangname'])) {
            $ranks->addNew(
                $post['newrangname']
            );

            $this->allianceModel->updateAllianceRanks(
                $this->getAllianceId(),
                $ranks->getAllRanksAsJsonString()
            );
        }

        // edit rights for each rank
        if (isset($post['id'])) {
            foreach ($post['id'] as $id) {
                $ranks->editRankById(
                    $id,
                    [
                        AllianceRanks::DELETE => (isset($post['u' . $id . 'r1']) && $this->alliance->isOwner()) ? SwitchInt::on : SwitchInt::off,
                        AllianceRanks::KICK => isset($post['u' . $id . 'r2']) ? SwitchInt::on : SwitchInt::off,
                        AllianceRanks::APPLICATIONS => isset($post['u' . $id . 'r3']) ? SwitchInt::on : SwitchInt::off,
                        AllianceRanks::VIEW_MEMBER_LIST => isset($post['u' . $id . 'r4']) ? SwitchInt::on : SwitchInt::off,
                        AllianceRanks::APPLICATION_MANAGEMENT => isset($post['u' . $id . 'r5']) ? SwitchInt::on : SwitchInt::off,
                        AllianceRanks::ADMINISTRATION => isset($post['u' . $id . 'r6']) ? SwitchInt::on : SwitchInt::off,
                        AllianceRanks::ONLINE_STATUS => isset($post['u' . $id . 'r7']) ? SwitchInt::on : SwitchInt::off,
                        AllianceRanks::SEND_CIRCULAR => isset($post['u' . $id . 'r8']) ? SwitchInt::on : SwitchInt::off,
                        AllianceRanks::RIGHT_HAND => isset($post['u' . $id . 'r9']) ? SwitchInt::on : SwitchInt::off,
                    ]
                );
            }

            $this->allianceModel->updateAllianceRanks(
                $this->getAllianceId(),
                $ranks->getAllRanksAsJsonString()
            );
        }

        // delete a rank
        if (isset($delete)) {
            $ranks->deleteRankById($delete);

            $this->allianceModel->updateAllianceRanks(
                $this->getAllianceId(),
                $ranks->getAllRanksAsJsonString()
            );
        }

        // build the UI
        $list_of_ranks = [];

        if (is_array($ranks->getAllRanksAsArray())) {
            foreach ($ranks->getAllRanksAsArray() as $rank_id => $details) {
                $delete = '<a href="game.php?page=alliance&mode=admin&edit=rights&d=' . $rank_id . '"><img src="' . DPATH . 'alliance/abort.gif" border="0" alt="' . __('game/alliance.al_rank_delete') . '"/></a>';
                $disabled = '';
                if ($rank_id == 0 or $rank_id == 1) {
                    $delete = '';
                    $disabled = ' disabled="disabled"';
                }

                $right_hand = '<b>-</b>';

                if ($this->alliance->isOwner()) {
                    $right_hand = '<input type="checkbox" name="u' . $rank_id . 'r1"' . (($details['rights'][AllianceRanks::DELETE] == SwitchInt::on) ? ' checked="checked"' : '') . $disabled . '>';
                }

                $list_of_ranks[] = [
                    'rank_id' => $rank_id,
                    'rank_delete' => $delete,
                    'rank_name' => $details['rank'],
                    'r1' => $right_hand,
                    'checked_r2' => (($details['rights'][AllianceRanks::KICK] == SwitchInt::on) ? ' checked="checked"' : ''),
                    'checked_r3' => (($details['rights'][AllianceRanks::APPLICATIONS] == SwitchInt::on) ? ' checked="checked"' : ''),
                    'checked_r4' => (($details['rights'][AllianceRanks::VIEW_MEMBER_LIST] == SwitchInt::on) ? ' checked="checked"' : ''),
                    'checked_r5' => (($details['rights'][AllianceRanks::APPLICATION_MANAGEMENT] == SwitchInt::on) ? ' checked="checked"' : ''),
                    'checked_r6' => (($details['rights'][AllianceRanks::ADMINISTRATION] == SwitchInt::on) ? ' checked="checked"' : ''),
                    'checked_r7' => (($details['rights'][AllianceRanks::ONLINE_STATUS] == SwitchInt::on) ? ' checked="checked"' : ''),
                    'checked_r8' => (($details['rights'][AllianceRanks::SEND_CIRCULAR] == SwitchInt::on) ? ' checked="checked"' : ''),
                    'checked_r9' => (($details['rights'][AllianceRanks::RIGHT_HAND] == SwitchInt::on) ? ' checked="checked"' : ''),
                    'edit_check' => $disabled,
                ];
            }
        }

        Template::legacyView(
            'alliance..admin.rights',
            [
                'list_of_ranks' => $list_of_ranks,
            ]
        );
    }

    private function getAdminTagSection(): void
    {
        $tag = filter_input(INPUT_POST, 'nametag', FILTER_UNSAFE_RAW);

        if (isset($tag)) {
            if (strlen($tag) < 3 or strlen($tag) > 8) {
                Functions::message(__('game/alliance.al_tag_required'), 'game.php?page=alliance&mode=admin&edit=tag', 3);
            }

            if ($this->allianceTagExists($tag)) {
                Functions::message(strtr(__('game/alliance.al_tag_already_exists'), ['%s' => $tag]), 'game.php?page=alliance&mode=admin&edit=tag', 3);
            }

            $this->allianceModel->updateAllianceTag(
                $this->getAllianceId(),
                $tag
            );

            Functions::redirect('game.php?page=alliance&mode=admin&edit=ally');
        }

        Template::legacyView(
            'alliance.admin.edit',
            [
                'case' => strtr(
                    __('game/alliance.al_change_title'),
                    ['%s' => $this->alliance->getCurrentAlliance()->getAllianceTag()]
                ),
                'title' => __('game/alliance.al_new_tag'),
            ]
        );
    }

    private function getAdminTransferSection(): void
    {
        $new_leader = filter_input(INPUT_POST, 'newleader', FILTER_VALIDATE_INT);

        if (isset($new_leader) && $new_leader != 0) {
            $this->allianceModel->transferAlliance(
                $this->user['ally_id'],
                $this->user['id'],
                $new_leader
            );

            Functions::redirect('game.php?page=alliance');
        }

        $ranksObject = $this->alliance->getCurrentAllianceRankObject();

        $users = array_filter(
            $this->allianceModel->getAllianceMembersById(
                $this->getAllianceId()
            ),
            function ($user) {
                return $user['ally_rank_id'] != 0;
            }
        );

        $members = [];

        foreach ($users as $user) {
            $rank_name = $ranksObject->getRankById($user['ally_rank_id'])['rank'];
            $rights = $ranksObject->getRankById($user['ally_rank_id'])['rights'];

            if (isset($rights[AllianceRanks::RIGHT_HAND]) && $rights[AllianceRanks::RIGHT_HAND] == SwitchInt::on) {
                $members[] = [
                    'id' => $user['id'],
                    'name' => $user['name'],
                    'rank' => $rank_name,
                ];
            }
        }

        Template::legacyView(
            'alliance.admin.transfer',
            [
                'members' => $members,
            ]
        );
    }

    /**
     *
     * BLOCKS
     *
     */

    /**
     *
     * @return string
     */
    private function buildPublicRequestsBlock()
    {
        if (!$this->user['ally_id']
            && !$this->user['ally_request']
            && $this->alliance->getCurrentAlliance()->getAllianceRequestNotAllow()) {
            $url = UrlHelper::setUrl(
                'game.php?page=alliance&mode=apply&allyid=' . $this->getAllianceId(),
                __('game/alliance.al_click_to_send_request'),
                __('game/alliance.al_click_to_send_request')
            );

            return '<tr><th scope="row">' . __('game/alliance.al_request') . '</th><th role="cell">' . $url . '</th></tr>';
        }

        return '';
    }

    private function buildImageBlock(): string
    {
        $image = $this->alliance->getCurrentAlliance()->getAllianceImage();

        if (!empty($image)) {
            return '<tr><th role="cell" colspan="2">' . Functions::setImage($image, $image) . '</th></tr>';
        }

        return '';
    }

    private function buildTagBlock(): array
    {
        return [
            'detail_title' => __('game/alliance.al_ally_info_tag'),
            'detail_content' => $this->alliance->getCurrentAlliance()->getAllianceTag(),
        ];
    }

    private function buildNameBlock(): array
    {
        return [
            'detail_title' => __('game/alliance.al_ally_info_name'),
            'detail_content' => $this->alliance->getCurrentAlliance()->getAllianceName(),
        ];
    }

    private function buildMembersBlock(): array
    {
        $list_of_members = '';

        if ($this->alliance->hasAccess(AllianceRanks::VIEW_MEMBER_LIST)) {
            $list_of_members = ' (' . UrlHelper::setUrl('game.php?page=alliance&mode=memberslist', __('game/alliance.al_user_list')) . ')';
        }

        return [
            'detail_title' => __('game/alliance.al_ally_info_members'),
            'detail_content' => $this->alliance->getCurrentAlliance()->getAllianceMembers() . $list_of_members,
        ];
    }

    private function buildRankBlock(): array
    {
        $rank = $this->getUserRank($this->user['id'], $this->user['ally_rank_id']);
        $admin_area = '';

        if ($this->alliance->hasAccess(AllianceRanks::ADMINISTRATION)) {
            $admin_area = ' (' . UrlHelper::setUrl('game.php?page=alliance&mode=admin&edit=ally', __('game/alliance.al_manage_alliance')) . ')';
        }

        return [
            'detail_title' => __('game/alliance.al_rank'),
            'detail_content' => $rank . $admin_area,
        ];
    }

    private function buildRequestsBlock(): array
    {
        $requests = '';
        $count = $this->allianceModel->getAllianceRequestsCount(
            $this->alliance->getCurrentAlliance()->getAllianceId()
        )['total_requests'];

        if ($this->alliance->hasAccess(AllianceRanks::APPLICATION_MANAGEMENT) && $count != 0) {
            $requests = UrlHelper::setUrl(
                'game.php?page=alliance&mode=admin&edit=requests',
                $count . ' ' . __('game/alliance.al_new_requests')
            );
        }

        return [
            'detail_title' => __('game/alliance.al_requests'),
            'detail_content' => $requests,
        ];
    }

    private function buildCircularBlock(): array
    {
        if ($this->alliance->hasAccess(AllianceRanks::SEND_CIRCULAR)) {
            return [
                'detail_title' => __('game/alliance.al_circular_message'),
                'detail_content' => UrlHelper::setUrl('game.php?page=alliance&mode=circular', __('game/alliance.al_send_circular_message')),
            ];
        }

        return [];
    }

    private function buildDescriptionBlock(): string
    {
        $description = __('game/alliance.al_description_message');
        $alliance_description = $this->alliance->getCurrentAlliance()->getAllianceDescription();

        if ($alliance_description != '') {
            $description = nl2br($this->bbcode->bbCode($alliance_description)) . '</th></tr>';
        }

        return '<tr><th role="cell" colspan="2" height="100px">' . $description . '</th></tr>';
    }

    private function buildWebBlock(): string
    {
        $web = '-';
        $webUrl = $this->alliance->getCurrentAlliance()->getAllianceWeb();

        if ($webUrl != '') {
            $url = UrlHelper::prepUrl($webUrl);
            $web = UrlHelper::setUrl($url, $url, $url, 'target="_blank"');
        }

        return $web;
    }

    private function buildTextBlock(): string
    {
        return nl2br($this->bbcode->bbCode($this->alliance->getCurrentAlliance()->getAllianceText()));
    }

    /**
     *
     * OTHER METHODS
     *
     */
    private function allianceNameExists(string $name): ?string
    {
        return $this->allianceModel->checkAllianceName($name);
    }

    private function allianceTagExists(string $tag): ?string
    {
        return $this->allianceModel->checkAllianceTag($tag);
    }

    private function getUserRank(int $member_id, int $member_rank_id)
    {
        $ranks = $this->alliance->getCurrentAllianceRankObject();

        if (!isset($ranks->getRankById($member_rank_id)['rank'])) {
            if ($this->alliance->getCurrentAlliance()->getAllianceOwner() == $member_id) {
                return __('game/alliance.al_founder_rank_text');
            } else {
                return __('game/alliance.al_new_member_rank_text');
            }
        }

        return $ranks->getRankById($member_rank_id)['rank'];
    }

    private function buildAdminMembersRankBlock(int $member_id, int $member_rank_id, $requested_rank = 0): string
    {
        $rank = $this->getUserRank($member_id, $member_rank_id);

        if ($requested_rank != $member_id) {
            return $rank;
        }

        $ranks = $this->alliance->getCurrentAllianceRankObject();
        $options = [];

        foreach ($ranks->getAllRanksAsArray() as $id => $rank) {
            $options[] = [
                'id' => $id,
                'rank' => $rank['rank'],
                'selected' => $member_rank_id == $id ? ' selected=selected' : '',
            ];
        }

        return Template::render(
            'alliance.admin.members_edit',
            [
                'id' => $member_id,
                'options' => $options,
            ]
        );
    }

    private function buildAdminMembersActionBlock(int $member_id, int $member_name, $requested_rank = 0): string
    {
        $kick_user = '';
        $change_rank = '';

        if (
            $this->alliance->getCurrentAlliance()->getAllianceOwner() == $member_id
            or $requested_rank == $member_id
        ) {
            return '-';
        }

        if ($this->alliance->hasAccess(AllianceRanks::KICK)) {
            $action = 'game.php?page=alliance&mode=admin&edit=members&kick=' . $member_id;
            $content = Functions::setImage(DPATH . 'alliance/abort.gif');
            $attributes = 'onclick="javascript:return confirm(\'' . strtr(__('game/alliance.al_confirm_remove_member'), ['%s' => $member_name]) . '\');"';
            $kick_user = UrlHelper::setUrl($action, $content, '', $attributes);
        }

        if ($this->alliance->hasAccess(AllianceRanks::ADMINISTRATION)) {
            $action = 'game.php?page=alliance&mode=admin&edit=members&rank=' . $member_id;
            $content = Functions::setImage(DPATH . 'alliance/key.gif');
            $change_rank = UrlHelper::setUrl($action, $content);
        }

        if (empty($kick_user) && empty($change_rank)) {
            return '-';
        }

        return $kick_user . $change_rank;
    }
}
