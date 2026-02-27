<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Models\Messages;
use App\Services\AdministrationService;
use App\Services\SettingsService;
use Illuminate\Database\Query\Builder;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;
use Xgp\App\Core\Enumerators\MessagesEnumerator;
use Xgp\App\Core\Template;
use Xgp\App\Libraries\TimingLibrary as Timing;

class MessagesController extends BaseController
{
    private array $results = [];
    private AdministrationService $administrationService;

    public function __construct()
    {
        $this->administrationService = new AdministrationService(
            new SettingsService()
        );
    }

    public function __invoke(): void
    {
        $this->administrationService->checkSession();
        $this->administrationService->authorization(__CLASS__);

        $this->runAction();

        Template::legacyView(
            'admin.messages',
            array_merge(
                $this->buildMessageTypeBlock(),
                [
                    'results' => $this->results,
                    'show_search' => $this->results ? '' : 'show',
                    'show_results' => $this->results ? 'show' : '',
                ]
            )
        );
    }

    private function runAction(): void
    {
        $action = filter_input_array(INPUT_POST);
        $single_delete = filter_input_array(INPUT_GET, [
            'action' => FILTER_UNSAFE_RAW,
            'messageId' => [
                'filter' => FILTER_VALIDATE_INT,
                'options' => ['min_range' => 0],
            ],
        ]);

        if ($action) {
            $filtered_action = array_filter(
                $action,
                function ($value) {
                    return !is_null($value) && $value !== false && $value !== '';
                }
            );

            if (isset($filtered_action['search'])) {
                $this->doSearch($filtered_action);
            }

            if (isset($filtered_action['delete_messages'])) {
                $this->deleteMessages($filtered_action['delete_messages']);
            }
        }

        if (isset($single_delete['action']) == 'delete' &&
            isset($single_delete['messageId'])) {
            $this->deleteMessage($single_delete['messageId']);
        }
    }

    private function doSearch(array $to_search): void
    {
        $query = DB::table('messages AS m')
            ->select('m.*', 'u1.name AS sender', 'u2.name AS receiver')
            ->leftJoin('users AS u1', 'u1.id', '=', 'm.message_sender')
            ->leftJoin('users AS u2', 'u2.id', '=', 'm.message_receiver');

        // search by username or user id
        if (!empty($to_search['message_user'])) {
            $username = $to_search['message_user'];
            $query->where(function (Builder $q) use ($username) {
                $q->whereIn('m.message_sender', function ($sub) use ($username) {
                    $sub->select('id')->from('users')->where('name', $username);
                })->orWhereIn('m.message_receiver', function ($sub) use ($username) {
                    $sub->select('id')->from('users')->where('name', $username);
                });
            });
        }

        // search by subject
        if (!empty($to_search['message_subject'])) {
            $query->where('m.message_subject', 'LIKE', '%' . $to_search['message_subject'] . '%');
        }

        // search by date
        if (!empty($to_search['message_date']) && strtotime($to_search['message_date'])) {
            $startDate = strtotime($to_search['message_date'] . ' 00:00:00');
            $endDate = strtotime($to_search['message_date'] . ' 23:59:59');
            $query->whereBetween('m.message_time', [$startDate, $endDate]);
        }

        // search by message type
        if (!empty($to_search['message_type']) && (int) $to_search['message_type'] > 0) {
            $query->where('m.message_type', (int) $to_search['message_type']);
        }

        // search by message text
        if (!empty($to_search['message_text'])) {
            $query->where('m.message_text', 'LIKE', '%' . $to_search['message_text'] . '%');
        }

        $search_results = $query->get()->map(fn($r) => (array) $r)->toArray();

        if ($search_results) {
            $results_list = [];

            foreach ($search_results as $result) {
                $results_list[] = array_merge(
                    $result,
                    [
                        'message_time' => Timing::formatExtendedDate($result['message_time']),
                        'message_type' => __('admin/messages.mg_types')[$result['message_type']],
                        'message_text' => nl2br($result['message_text']),
                    ]
                );
            }

            $this->results = $results_list;
        } else {
            session()->flash('warning', __('admin/messages.mg_no_results'));
        }
    }

    private function deleteMessage(int $message_id): void
    {
        Messages::whereIn('message_id', [$message_id])->delete();

        session()->flash('success', __('admin/messages.mg_delete_ok'));
    }

    private function deleteMessages(array $messages): void
    {
        $ids = [];

        foreach ($messages as $message_id => $delete_status) {
            if ($delete_status == 'on' && $message_id > 0 && is_numeric($message_id)) {
                $ids[] = $message_id;
            }
        }

        Messages::whereIn('message_id', $ids)->delete();

        session()->flash('success', __('admin/messages.mg_delete_ok'));
    }

    private function buildMessageTypeBlock(): array
    {
        $options_list = [];
        $message_types = [
            MessagesEnumerator::ESPIO,
            MessagesEnumerator::COMBAT,
            MessagesEnumerator::EXP,
            MessagesEnumerator::ALLY,
            MessagesEnumerator::USER,
            MessagesEnumerator::GENERAL,
        ];

        foreach ($message_types as $type) {
            $options_list[] = [
                'value' => $type,
                'name' => __('admin/messages.mg_types')[$type],
            ];
        }

        return [
            'type_options' => $options_list,
        ];
    }
}
