<?php

declare(strict_types=1);

namespace Xgp\App\Http\Controllers\Game;

use App\Enums\Module;
use App\Services\FormatService;
use App\Services\Game\Formulas\OfficerService;
use App\Services\SettingsService;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;
use Xgp\App\Core\Concerns\PreparesLegacySql;
use Xgp\App\Core\Enumerators\MessagesEnumerator;
use Xgp\App\Core\Enumerators\SwitchIntEnumerator as SwitchInt;
use Xgp\App\Core\Template;
use Xgp\App\Helpers\ArraysHelper;
use Xgp\App\Libraries\Functions;
use Xgp\App\Libraries\Users;

/**
 * @SuppressWarnings("PHPMD.StaticAccess")
 */
class MessagesController extends BaseController
{
    use PreparesLegacySql;

    private array $user = [];
    private array $message_type = [
        MessagesEnumerator::ESPIO => ['type_name' => 'espioopen'],
        MessagesEnumerator::COMBAT => ['type_name' => 'combatopen'],
        MessagesEnumerator::EXP => ['type_name' => 'expopen'],
        MessagesEnumerator::ALLY => ['type_name' => 'allyopen'],
        MessagesEnumerator::USER => ['type_name' => 'useropen'],
        MessagesEnumerator::GENERAL => ['type_name' => 'generalopen'],
    ];

    public function __construct(private OfficerService $officerService)
    {
    }

    public function __invoke(): void
    {
        Functions::moduleMessage(Functions::isModuleAccesible(Module::Messages));

        $this->user = Users::getInstance()->getUserData();

        $this->runAction();

        $this->getCurrentSection();
    }

    private function getCurrentSection(): void
    {
        if ($this->officerService->isOfficerActive((int) $this->user['premium_officier_commander'], time())) {
            $this->getPremiumSection();
        }

        $this->getDefaultSection();
    }

    private function runAction(): void
    {
        $delete = filter_input(INPUT_POST, 'deletemessages');

        if (in_array($delete, ['deleteall', 'deletemarked', 'deleteunmarked', 'deleteallshown'])) {
            $this->doDeleteAction();
        }
    }

    private function getDefaultSection(): void
    {
        $userId = (int) $this->user['id'];

        if ($userId > 0) {
            DB::statement(
                $this->prepareSql(
                    'UPDATE `' . MESSAGES . "` SET
                        `message_read` = '1'
                    WHERE `message_receiver` = " . $userId . ';'
                )
            );
        }

        $messages = $userId > 0 ? array_map(
            fn ($row) => (array) $row,
            DB::select(
                $this->prepareSql(
                    'SELECT *
                    FROM `' . MESSAGES . "`
                    WHERE `message_receiver` = '" . $userId . "'
                    ORDER BY `message_time` DESC;"
                )
            )
        ) : null;

        Template::legacyView(
            'messages.default',
            [
                'message_list' => $this->getMessagesList($messages),
                'operators_list' => $this->getOperatorsAddressBook(),
            ]
        );
    }

    private function getPremiumSection(): void
    {
        // display an specific category of items
        $active = [];
        $messages = false;
        $message_list = [];
        $deleteOptions = false;
        $data = filter_input_array(INPUT_GET, FILTER_VALIDATE_INT);

        if (isset($data['dsp']) && $data['dsp'] == 1) {
            $get_messages = '';

            foreach ($data as $field => $value) {
                if (ArraysHelper::inMultiArray($field, $this->message_type)) {
                    $type_id = ArraysHelper::multiArraySearch($field, $this->message_type);
                    $get_messages .= $type_id . ',';
                    $active[$type_id] = 1;
                }
            }

            // get list of messages
            $messages = true;
            $deleteOptions = true;

            $userId = (int) $this->user['id'];
            $message_list = $this->getMessagesList(
                ($userId > 0 && !empty($get_messages)) ? array_map(
                    fn ($row) => (array) $row,
                    DB::select(
                        $this->prepareSql(
                            'SELECT *
                            FROM `' . MESSAGES . '`
                            WHERE `message_receiver` = ' . $userId . '
                                AND `message_type` IN (' . rtrim($get_messages, ',') . ')
                            ORDER BY `message_time` DESC;'
                        )
                    )
                ) : null
            );

            if ($userId > 0 && !empty($get_messages)) {
                DB::statement(
                    $this->prepareSql(
                        'UPDATE `' . MESSAGES . "` SET
                            `message_read` = '1'
                        WHERE `message_receiver` = " . $userId . '
                            AND `message_type` IN (' . rtrim($get_messages, ',') . ');'
                    )
                );
            }
        }

        Template::legacyView(
            'messages.premium',
            array_merge(
                [
                    'form_submit' => 'game.php?' . $_SERVER['QUERY_STRING'],
                    'message_type_list' => $this->getMessagesTypesList($active),
                    'messages' => $messages,
                    'messages_list' => $message_list,
                    'deleteOptions' => $deleteOptions,
                ],
                $this->getExtraBlocksDisplay()
            )
        );
    }

    private function getMessagesList(array $messages): array
    {
        $messages_list = [];

        if ($messages) {
            foreach ($messages as $message) {
                $messages_list[] = [
                    'message_id' => $message['message_id'],
                    'message_time' => date(
                        strtr(app(SettingsService::class)->getString('date_format_extended'), ['.Y' => '']),
                        (int)$message['message_time']
                    ),
                    'message_from' => $message['message_from'],
                    'message_subject' => $message['message_subject'],
                    'message_text' => nl2br($message['message_text']),
                    'message_reply' => $this->setMessageReply((int)$message['message_sender']),
                ];
            }
        }

        return $messages_list;
    }

    private function setMessageReply(int $from): string
    {
        if ($from > 0) {
            return app(FormatService::class)->link(
                'game.php?page=chat&playerId=' . $from,
                Functions::setImage(DPATH . '/img/m.gif', __('game/messages.mg_send_message')),
                __('game/messages.mg_send_message')
            );
        }

        return '';
    }

    private function getOperatorsAddressBook(): array
    {
        $userId = (int) $this->user['id'];
        $operators = $userId > 0 ? array_map(
            fn ($row) => (array) $row,
            DB::select(
                $this->prepareSql(
                    'SELECT `name`, `email`
                    FROM ' . USERS . "
                    WHERE authlevel > '0'
                        AND `id` <> '" . $userId . "';"
                )
            )
        ) : null;
        $operators_list = [];

        if ($operators) {
            foreach ($operators as $operator) {
                $operators_list[] = [
                    'name' => $operator['name'],
                    'email' => $operator['email'],
                ];
            }
        }

        return $operators_list;
    }

    private function getMessagesTypesList(array $active): array
    {
        $userId = (int) $this->user['id'];
        $messages_types = $userId > 0 ? array_map(
            fn ($row) => (array) $row,
            DB::select(
                $this->prepareSql(
                    'SELECT
                        `message_type`,
                        COUNT(`message_type`) AS message_type_count,
                        SUM(`message_read` = 0) AS unread_count
                    FROM `' . MESSAGES . "`
                    WHERE `message_receiver` = '" . $userId . "'
                    GROUP BY `message_type`"
                )
            )
        ) : [];
        $messages_types_list = [];

        if ($messages_types) {
            foreach ($messages_types as $message_type) {
                $this->message_type[$message_type['message_type']]['count'] = $message_type['message_type_count'];
                $this->message_type[$message_type['message_type']]['unread'] = $message_type['unread_count'];

                $messages_types_list[] = [
                    'message_type' => $this->message_type[$message_type['message_type']]['type_name'],
                    'checked' => (isset($active[$message_type['message_type']]) ? 'checked' : ''),
                    'checked_status' => (isset($active[$message_type['message_type']]) ? SwitchInt::on : SwitchInt::off),
                    'message_type_name' => __('game/messages.mg_type')[$message_type['message_type']],
                    'message_amount' => isset($message_type['message_type_count']) ? $message_type['message_type_count'] : 0,
                    'message_unread' => isset($message_type['unread_count']) ? $message_type['unread_count'] : 0,
                ];
            }
        }

        return $messages_types_list;
    }

    private function getFriendsAddressBook(): array
    {
        $userId = (int) $this->user['id'];
        $buddies = $userId > 0 ? array_map(
            fn ($row) => (array) $row,
            DB::select(
                $this->prepareSql(
                    'SELECT u.`id`, u.`name`, u.`email`
                    FROM `' . BUDDY . '` b
                    LEFT JOIN `' . USERS . "` u
                        ON u.id = IF(`buddy_sender` = '" . $userId . "', `buddy_receiver`, `buddy_sender`)
                    WHERE `buddy_sender`='" . $userId . "'
                        OR `buddy_receiver`='" . $userId . "'"
                )
            )
        ) : null;
        $buddies_list = [];

        if ($buddies) {
            foreach ($buddies as $buddy) {
                $buddies_list[] = [
                    'name' => $buddy['name'],
                    'id' => $buddy['id'],
                ];
            }
        }

        return $buddies_list;
    }

    private function getAllinaceAddressBook(): array
    {
        $userId = (int) $this->user['id'];
        $allyId = (int) $this->user['ally_id'];
        $members = ($userId > 0 && $allyId > 0) ? array_map(
            fn ($row) => (array) $row,
            DB::select(
                $this->prepareSql(
                    'SELECT `id`, `name`, `email`
                    FROM ' . USERS . "
                    WHERE ally_id = '" . $allyId . "'
                        AND `id` <> '" . $userId . "';"
                )
            )
        ) : null;
        $members_list = [];

        if ($members) {
            foreach ($members as $member) {
                $members_list[] = [
                    'name' => $member['name'],
                    'id' => $member['id'],
                ];
            }
        }

        return $members_list;
    }

    private function getNotesList(): array
    {
        $userId = (int) $this->user['id'];
        $notes = $userId > 0 ? array_map(
            fn ($row) => (array) $row,
            DB::select(
                $this->prepareSql(
                    'SELECT `note_id`, `note_priority`, `note_title`
                    FROM `' . NOTES . "`
                    WHERE `note_owner` = '" . $userId . "';"
                )
            )
        ) : null;
        $notes_list = [];

        if ($notes) {
            foreach ($notes as $note) {
                $notes_list[] = [
                    'note_id' => $note['note_id'],
                    'note_color' => ($note['note_priority'] == 0) ? 'lime' : (($note['note_priority'] == 1) ? 'yellow' : 'red'),
                    'note_title' => $note['note_title'],
                ];
            }
        }

        return $notes_list;
    }

    private function getExtraBlocksDisplay(): array
    {
        $userId = (int) $this->user['id'];
        $allyId = (int) $this->user['ally_id'];
        $countRow = ($userId > 0 && $allyId >= 0) ? DB::selectOne(
            $this->prepareSql(
                'SELECT
                ( SELECT COUNT(`id`)
                    FROM `' . USERS . "`
                    WHERE `ally_id` = '" . $allyId . "'
                        AND `ally_id` <> 0
                        AND `id` <> '" . $userId . "'
                    ) AS alliance_count,

                    ( SELECT COUNT(`buddy_id`)
                    FROM `" . BUDDY . "`
                    WHERE `buddy_sender` = '" . $userId . "'
                        OR `buddy_receiver` = '" . $userId . "'
                    ) AS buddys_count,

                    ( SELECT COUNT(`note_id`)
                    FROM `" . NOTES . "`
                    WHERE `note_owner` = '" . $userId . "'
                    ) AS notes_count,

                    ( SELECT COUNT(`id`)
                    FROM " . USERS . "
                    WHERE authlevel <> 0
                        AND `id` <> '" . $userId . "'
                    ) AS operators_count"
            )
        ) : null;
        $address_book_notes_counts = $countRow !== null ? (array) $countRow : null;
        $current_extra_block_open = filter_input_array(INPUT_POST, [
            'owncontactsopen' => FILTER_UNSAFE_RAW,
            'ownallyopen' => FILTER_UNSAFE_RAW,
            'gameoperatorsopen' => FILTER_UNSAFE_RAW,
            'noticesopen' => FILTER_UNSAFE_RAW,
        ]);

        $blocks = [
            'owncontactsopen' => [
                'buddy_list' => $this->getFriendsAddressBook(),
            ],
            'ownallyopen' => [
                'members_list' => $this->getAllinaceAddressBook(),
            ],
            'gameoperatorsopen' => [
                'operators_list' => $this->getOperatorsAddressBook(),
            ],
            'noticesopen' => [
                'notes_list' => $this->getNotesList(),
            ],
        ];

        $blocks_set = [
            'owncontactsopen' => '',
            'buddys_count' => $address_book_notes_counts['buddys_count'],
            'buddy_list' => [],
            'ownallyopen' => '',
            'alliance_count' => $address_book_notes_counts['alliance_count'],
            'members_list' => [],
            'gameoperatorsopen' => '',
            'operators_count' => $address_book_notes_counts['operators_count'],
            'operators_list' => [],
            'noticesopen' => '',
            'notes_count' => $address_book_notes_counts['notes_count'],
            'notes_list' => [],
        ];

        if ($current_extra_block_open) {
            foreach ($current_extra_block_open as $key => $value) {
                if ($value == 'on') {
                    $blocks_set = array_merge($blocks_set, $blocks[$key], [$key => 'checked="1"']);
                }
            }
        }

        return $blocks_set;
    }

    private function doDeleteAction(): void
    {
        $delete = filter_input(INPUT_POST, 'deletemessages');
        $messages_to_delete = filter_input_array(INPUT_POST);
        $type_to_delete = filter_input_array(INPUT_GET);

        $userId = (int) $this->user['id'];

        switch ($delete) {
            case 'deleteall':
                if ($userId > 0) {
                    DB::statement(
                        $this->prepareSql(
                            'DELETE FROM ' . MESSAGES . "
                            WHERE `message_receiver` = '" . $userId . "';"
                        )
                    );
                }
                break;
            case 'deletemarked':
                foreach ($messages_to_delete as $message => $checked) {
                    if (preg_match('/delmes/i', $message) && $checked == 'on') {
                        $message_id = str_replace('delmes', '', $message);

                        $message_ids[] = $message_id;
                    }
                }

                if (isset($message_ids) && $userId > 0) {
                    DB::statement(
                        $this->prepareSql(
                            'DELETE FROM ' . MESSAGES . '
                            WHERE `message_id` IN (' . join(',', $message_ids) . ")
                                AND `message_receiver` = '" . $userId . "';"
                        )
                    );
                }
                break;
            case 'deleteunmarked':
                foreach ($messages_to_delete as $message => $checked) {
                    $message_id = str_replace('showmes', '', $message);
                    $selected = 'delmes' . $message_id;

                    if (preg_match('/showmes/i', $message) && !isset($messages_to_delete[$selected])) {
                        $message_ids[] = $message_id;
                    }
                }

                if (isset($message_ids) && $userId > 0) {
                    DB::statement(
                        $this->prepareSql(
                            'DELETE FROM ' . MESSAGES . '
                            WHERE `message_id` IN (' . join(',', $message_ids) . ")
                                AND `message_receiver` = '" . $userId . "';"
                        )
                    );
                }
                break;
            case 'deleteallshown':
                $data = filter_input_array(INPUT_GET, FILTER_VALIDATE_INT);

                if (isset($data['dsp']) && $data['dsp'] == 1) {
                    foreach ($data as $field => $value) {
                        if (ArraysHelper::inMultiArray($field, $this->message_type)) {
                            $type_id = ArraysHelper::multiArraySearch($field, $this->message_type);
                            break;
                        }
                    }

                    if (isset($type_id) && $userId > 0 && (int) $type_id >= 0) {
                        DB::statement(
                            $this->prepareSql(
                                'DELETE FROM ' . MESSAGES . '
                                WHERE `message_type` IN (' . $type_id . ")
                                    AND `message_receiver` = '" . $userId . "';"
                            )
                        );
                    }
                }
                break;
            default:
                break;
        }

        Functions::redirect('game.php?' . strtr($_SERVER['QUERY_STRING'], ['&amp;' => '&']));
    }
}
