<?php

declare(strict_types=1);

namespace Xgp\App\Http\Controllers\Game;

use App\Enums\Module;
use App\Services\TimingService;
use Exception;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;
use Xgp\App\Core\Concerns\PreparesLegacySql;
use Xgp\App\Core\Entity\BuddyEntity;
use Xgp\App\Core\Enumerators\BuddiesStatusEnumerator as BuddiesStatus;
use Xgp\App\Core\Template;
use Xgp\App\Libraries\Buddies\Buddy;
use Xgp\App\Libraries\Functions;
use Xgp\App\Libraries\Users;

/**
 * @SuppressWarnings("PHPMD.StaticAccess")
 */
class BuddiesController extends BaseController
{
    use PreparesLegacySql;

    private array $user = [];
    private ?Buddy $buddy = null;

    public function __construct(private TimingService $timingService)
    {
    }

    public function __invoke(): void
    {
        Functions::moduleMessage(Functions::isModuleAccesible(Module::Buddies));

        $this->user = Users::getInstance()->getUserData();

        // init a new buddy object
        $this->setUpBudies();

        $this->runAction();

        Template::legacyView(
            'buddies.view',
            [
                'list_of_requests_received' => $this->buildListOfRequestsReceived(),
                'list_of_requests_sent' => $this->buildListOfRequestsSent(),
                'list_of_buddies' => $this->buildListOfBuddies()
            ]
        );
    }

    private function setUpBudies(): void
    {
        $userId = (int) $this->user['id'];
        $this->buddy = new Buddy(
            array_map(
                fn ($row) => (array) $row,
                DB::select(
                    $this->prepareSql(
                        'SELECT *
                        FROM `' . BUDDY . "`
                        WHERE `buddy_sender` = '" . $userId . "'
                            OR `buddy_receiver` = '" . $userId . "'"
                    )
                )
            ),
            $userId
        );
    }

    private function runAction(): void
    {
        $mode = filter_input(INPUT_GET, 'mode', FILTER_VALIDATE_INT);
        $sm = filter_input(INPUT_GET, 'sm', FILTER_VALIDATE_INT);

        $allowed_modes = [
            1 => 'execAction', // exec one of the allowed actions
            2 => 'buildRequestForm', // show the send request form
        ];

        $allowed_actions = [
            1 => 'removeRequest', // applies for reject or cancel
            2 => 'acceptRequest', // accept an incoming request
            3 => 'sendRequest', // send the request
        ];

        if (isset($allowed_modes[$mode])) {
            if (isset($allowed_actions[$sm])) {
                $this->{$allowed_modes[$mode]}($allowed_actions[$sm]);
            } else {
                if ($allowed_modes[$mode] == 'buildRequestForm') {
                    $this->{$allowed_modes[$mode]}();
                }
            }
        }
    }

    private function execAction($action): void
    {
        try {
            if (empty($action)) {
                throw new Exception('Action cannot be empty');
            }

            $this->{$action}();
            Functions::redirect('game.php?page=buddies');
        } catch (Exception $e) {
            die('Caught exception: ' . $e->getMessage() . "\n");
        }
    }

    private function removeRequest(): void
    {
        $bid = filter_input(INPUT_GET, 'bid', FILTER_VALIDATE_INT);

        $buddyRow = DB::selectOne(
            $this->prepareSql(
                'SELECT *
                FROM `' . BUDDY . "`
                WHERE `buddy_id` = '" . (int) $bid . "'"
            )
        );
        $buddy = new BuddyEntity($buddyRow !== null ? (array) $buddyRow : false);

        if ($buddy->getBuddyStatus() == BuddiesStatus::isNotBuddy) {
            if ($buddy->getBuddySender() != $this->user['id']) {
                $this->sendMessage($buddy->getBuddySender(), 1);
            } elseif ($buddy->getBuddySender() == $this->user['id']) {
                $this->sendMessage($buddy->getBuddyReceiver(), 1);
            }
        } else {
            if ($buddy->getBuddySender() != $this->user['id']) {
                $this->sendMessage($buddy->getBuddySender(), 2);
            } elseif ($buddy->getBuddySender() == $this->user['id']) {
                $this->sendMessage($buddy->getBuddyReceiver(), 2);
            }
        }

        DB::statement(
            $this->prepareSql(
                'DELETE FROM `' . BUDDY . "`
                WHERE `buddy_id` = '" . (int) $bid . "'
                    AND (`buddy_receiver` = '" . (int) $this->user['id'] . "'
                            OR `buddy_sender` = '" . (int) $this->user['id'] . "')"
            )
        );
    }

    private function acceptRequest(): void
    {
        $bid = filter_input(INPUT_GET, 'bid', FILTER_VALIDATE_INT);

        $buddyRow = DB::selectOne(
            $this->prepareSql(
                'SELECT *
                FROM `' . BUDDY . "`
                WHERE `buddy_id` = '" . (int) $bid . "'"
            )
        );
        $buddy = new BuddyEntity($buddyRow !== null ? (array) $buddyRow : false);

        $this->sendMessage($buddy->getBuddySender(), 3);

        DB::statement(
            $this->prepareSql(
                'UPDATE `' . BUDDY . "`
                    SET `buddy_status` = '1'
                WHERE `buddy_id` = '" . (int) $bid . "'
                    AND `buddy_receiver` = '" . (int) $this->user['id'] . "'"
            )
        );
    }

    private function sendRequest(): void
    {
        $user = filter_input(INPUT_POST, 'user', FILTER_VALIDATE_INT);
        $text = filter_input(INPUT_POST, 'text');

        $buddy = null;

        $buddyRow = DB::selectOne(
            $this->prepareSql(
                'SELECT `buddy_id`
                FROM `' . BUDDY . "`
                WHERE (
                    `buddy_receiver` = '" . (int) $this->user['id'] . "'
                    AND `buddy_sender` = '" . (int) $user . "'
                ) OR (
                    `buddy_receiver` = '" . (int) $user . "'
                    AND `buddy_sender` = '" . (int) $this->user['id'] . "'
                )"
            )
        );

        if ($buddyRow !== null) {
            $buddy = new BuddyEntity((array) $buddyRow);
        }

        if (!is_null($buddy) && $buddy->getBuddyId() != 0) {
            Functions::message(__('game/buddies.bu_request_exists'), 'game.php?page=buddies', 3, true);
        }

        $this->sendMessage($user, 4);

        DB::statement(
            $this->prepareSql(
                'INSERT INTO `' . BUDDY . "` SET
                    `buddy_sender` = '" . (int) $this->user['id'] . "',
                    `buddy_receiver` = '" . (int) $user . "',
                    `buddy_status` = '0',
                    `buddy_request_text` = ?"
            ),
            [strip_tags($text)]
        );
    }

    private function sendMessage(int $to, int $type): void
    {
        $types = [
            1 => [
                'title' => 'bu_rejected_title',
                'text' => 'bu_rejected_text',
            ],
            2 => [
                'title' => 'bu_deleted_title',
                'text' => 'bu_deleted_text',
            ],
            3 => [
                'title' => 'bu_accepted_title',
                'text' => 'bu_accepted_text',
            ],
            4 => [
                'title' => 'bu_to_accept_title',
                'text' => 'bu_to_accept_text',
            ],
        ];

        Functions::sendMessage(
            $to,
            $this->user['id'],
            0,
            5,
            $this->user['name'],
            __('game/buddies.' . $types[$type]['title']),
            str_replace(
                '%u',
                $this->user['name'],
                __('game/buddies.' . $types[$type]['text'])
            )
        );
    }

    private function buildRequestForm(): void
    {
        $user = filter_input(INPUT_GET, 'u', FILTER_VALIDATE_INT);

        if ($user == $this->user['id']) {
            Functions::message(__('game/buddies.bu_cannot_request_yourself'), 'game.php?page=buddies', 2, true);
        }

        $userRow = DB::selectOne(
            $this->prepareSql(
                'SELECT `id`, `name`
                FROM `' . USERS . "`
                WHERE `id` = '" . (int) $user . "'"
            )
        );
        $user = $userRow !== null ? (array) $userRow : [];

        if (!$user) {
            Functions::redirect('game.php?page=buddies');
        }

        Template::legacyView(
            'buddies.request',
            $user
        );
    }

    private function buildListOfRequestsReceived(): array
    {
        $received_requests = $this->buddy->getReceivedRequests();
        $rows = [];

        if ($this->hasAny($received_requests)) {
            foreach ($received_requests as $received) {
                $rows[] = $this->extractPlayerData($received);
            }
        }

        return $rows;
    }

    private function buildListOfRequestsSent(): array
    {
        $requests_sent = $this->buddy->getSentRequests();
        $rows = [];

        if ($this->hasAny($requests_sent)) {
            foreach ($requests_sent as $sent) {
                $rows[] = $this->extractPlayerData($sent);
            }
        }

        return $rows;
    }

    private function buildListOfBuddies(): array
    {
        $buddies = $this->buddy->getBuddies();
        $rows = [];

        if ($this->hasAny($buddies)) {
            foreach ($buddies as $buddy) {
                $rows[] = $this->extractPlayerData($buddy);
            }
        }

        return $rows;
    }

    private function extractPlayerData(BuddyEntity $buddy): array
    {
        if ($buddy->getBuddySender() == $this->user['id']) {
            $id_to_get = $buddy->getBuddyReceiver();
        } else {
            $id_to_get = $buddy->getBuddySender();
        }

        $userRow = DB::selectOne(
            $this->prepareSql(
                'SELECT u.`id`,
                    u.`name`,
                    u.`galaxy`,
                    u.`system`,
                    u.`planet`,
                    u.`onlinetime`,
                    a.`alliance_id`,
                    a.`alliance_name`
                FROM ' . USERS . ' AS u
                LEFT JOIN `' . ALLIANCE . "` AS a ON a.`alliance_id` = u.`ally_id`
                WHERE u.`id` = '" . $id_to_get . "'"
            )
        );
        $user_data = $userRow !== null ? (array) $userRow : null;

        return [
            'id' => $user_data['id'],
            'username' => $user_data['name'],
            'ally_id' => $user_data['alliance_id'],
            'alliance_name' => $user_data['alliance_name'],
            'galaxy' => $user_data['galaxy'],
            'system' => $user_data['system'],
            'planet' => $user_data['planet'],
            'text' => $this->setText($buddy, $user_data['onlinetime']),
            'action' => $this->setAction($buddy),
        ];
    }

    private function setText(BuddyEntity $buddy, int $onlineTime): string
    {
        if ($buddy->getBuddyStatus() == BuddiesStatus::isBuddy) {
            return $this->timingService->getOnlineStatus((int) $onlineTime, time());
        } else {
            return $buddy->getRequestText();
        }
    }

    private function setAction(BuddyEntity $buddy): string
    {
        $bid = $buddy->getBuddyId();

        if ($buddy->getBuddyStatus() == BuddiesStatus::isBuddy) {
            $url = $this->generateUrl($bid, 1, __('game/buddies.bu_delete'));
        } else {
            if ($buddy->getBuddySender() == $this->user['id']) {
                $url = $this->generateUrl($bid, 1, __('game/buddies.bu_cancel_request'));
            } else {
                $url = $this->generateUrl($bid, 2, __('game/buddies.bu_accept'));
                $url .= '<br>';
                $url .= $this->generateUrl($bid, 1, __('game/buddies.bu_decline'));
            }
        }

        return $url;
    }

    private function generateUrl(int $buddyId, int $sm, string $text): string
    {
        return '<a href="game.php?page=buddies&mode=1&sm=' . $sm . '&bid=' . $buddyId . '">' . $text . '</a>';
    }

    private function hasAny(array $array): bool
    {
        return (count($array) > 0);
    }
}
