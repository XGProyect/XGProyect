<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Models\Messages;
use App\Services\AdministrationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Xgp\App\Core\Enumerators\MessagesEnumerator;
use Xgp\App\Libraries\TimingLibrary as Timing;

class MessagesController extends BaseController
{
    public function __construct(
        private readonly AdministrationService $administrationService,
    ) {
    }

    public function index(Request $request): View
    {
        $this->administrationService->checkSession();
        $this->administrationService->authorization(__CLASS__);

        $searchFields = ['message_sender', 'message_receiver', 'message_subject', 'message_date', 'message_type', 'message_text'];
        $hasSearch = $request->hasAny($searchFields);
        $results = [];

        if ($hasSearch) {
            $results = $this->search($request);

            if (empty($results)) {
                session()->flash('warning', __('admin/messages.mg_no_results'));
            }
        }

        return view('admin.messages', [
            'results' => $results,
            'hasSearch' => $hasSearch,
            'type_options' => $this->buildMessageTypeOptions(),
            'search' => [
                'message_sender' => $request->string('message_sender')->toString(),
                'message_receiver' => $request->string('message_receiver')->toString(),
                'message_subject' => $request->string('message_subject')->toString(),
                'message_date' => $request->string('message_date')->toString(),
                'message_type' => $request->string('message_type')->toString(),
                'message_text' => $request->string('message_text')->toString(),
            ],
        ]);
    }

    public function destroy(Messages $message): RedirectResponse
    {
        $this->administrationService->checkSession();
        $this->administrationService->authorization(__CLASS__);

        $message->delete();

        session()->flash('success', __('admin/messages.mg_delete_ok'));

        return redirect()->route('admin.messages');
    }

    public function destroyBatch(Request $request): RedirectResponse
    {
        $this->administrationService->checkSession();
        $this->administrationService->authorization(__CLASS__);

        /** @var array<int|string, string> $selected */
        $selected = (array) $request->input('delete_messages', []);

        $ids = collect($selected)
            ->filter(fn ($status, $id) => $status === 'on' && is_numeric($id) && (int) $id > 0)
            ->keys()
            ->map(fn ($id) => (int) $id)
            ->all();

        if (!empty($ids)) {
            Messages::whereIn('message_id', $ids)->delete();
            session()->flash('success', __('admin/messages.mg_delete_ok'));
        }

        return redirect()->route('admin.messages');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function search(Request $request): array
    {
        $query = Messages::query()
            ->with('senderUser:id,name', 'receiverUser:id,name');

        $sender = trim($request->string('message_sender')->toString());
        if ($sender !== '') {
            $query->whereHas('senderUser', fn ($sub) => $sub->where('name', $sender));
        }

        $receiver = trim($request->string('message_receiver')->toString());
        if ($receiver !== '') {
            $query->whereHas('receiverUser', fn ($sub) => $sub->where('name', $receiver));
        }

        $subject = trim($request->string('message_subject')->toString());
        if ($subject !== '') {
            $query->where('message_subject', 'LIKE', '%' . $subject . '%');
        }

        $date = trim($request->string('message_date')->toString());
        if ($date !== '' && strtotime($date)) {
            $startDate = strtotime($date . ' 00:00:00');
            $endDate = strtotime($date . ' 23:59:59');
            $query->whereBetween('message_time', [$startDate, $endDate]);
        }

        $type = $request->string('message_type')->toString();
        if ($type !== '' && (int) $type >= 0) {
            $query->where('message_type', (int) $type);
        }

        $text = trim($request->string('message_text')->toString());
        if ($text !== '') {
            $query->where('message_text', 'LIKE', '%' . $text . '%');
        }

        return $query->limit(100)->get()->map(fn (Messages $msg) => [
            'message_id' => $msg->message_id,
            'sender' => $msg->senderUser?->name ?? '-',
            'receiver' => $msg->receiverUser?->name ?? '-',
            'message_time' => Timing::formatExtendedDate($msg->message_time),
            'message_type' => __('admin/messages.mg_types')[$msg->message_type] ?? '-',
            'message_from' => $msg->message_from,
            'message_subject' => $msg->message_subject,
            'message_text' => nl2br($msg->message_text),
        ])->all();
    }

    /**
     * @return array<int, array{value: int, name: string}>
     */
    private function buildMessageTypeOptions(): array
    {
        $types = [
            MessagesEnumerator::ESPIO,
            MessagesEnumerator::COMBAT,
            MessagesEnumerator::EXP,
            MessagesEnumerator::ALLY,
            MessagesEnumerator::USER,
            MessagesEnumerator::GENERAL,
        ];

        return array_map(fn (int $type) => [
            'value' => $type,
            'name' => __('admin/messages.mg_types')[$type],
        ], $types);
    }
}
