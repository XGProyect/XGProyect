<?php

declare(strict_types=1);

namespace App\Services\Admin;

use App\Http\Requests\Admin\MessagesSearchRequest;
use App\Models\Messages;
use App\Services\TimingService;
use Illuminate\Database\Eloquent\Collection;
use Xgp\App\Core\Enumerators\MessagesEnumerator;

/**
 * @SuppressWarnings("PHPMD.StaticAccess")
 */
class MessagesService
{
    private const SEARCH_LIMIT = 100;

    private const TYPE_LIST = [
        MessagesEnumerator::ESPIO,
        MessagesEnumerator::COMBAT,
        MessagesEnumerator::EXP,
        MessagesEnumerator::ALLY,
        MessagesEnumerator::USER,
        MessagesEnumerator::GENERAL,
    ];

    /**
     * @param class-string<Messages> $messageModel
     */
    public function __construct(
        private readonly TimingService $timingService,
        private readonly string $messageModel = Messages::class,
    ) {
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function search(MessagesSearchRequest $request): array
    {
        $query = ($this->messageModel)::query()
            ->with('senderUser:id,name', 'receiverUser:id,name');

        if ($request->filterSender() !== '') {
            $query->whereHas('senderUser', fn ($q) => $q->where('name', $request->filterSender()));
        }

        if ($request->filterReceiver() !== '') {
            $query->whereHas('receiverUser', fn ($q) => $q->where('name', $request->filterReceiver()));
        }

        if ($request->filterSubject() !== '') {
            $query->where('message_subject', 'LIKE', '%' . $request->filterSubject() . '%');
        }

        $date = $request->filterDate();
        if ($date !== '' && strtotime($date) !== false) {
            $query->whereBetween('message_time', [
                strtotime($date . ' 00:00:00'),
                strtotime($date . ' 23:59:59'),
            ]);
        }

        $type = $request->filterType();
        if ($type !== '' && (int) $type >= 0) {
            $query->where('message_type', (int) $type);
        }

        if ($request->filterText() !== '') {
            $query->where('message_text', 'LIKE', '%' . $request->filterText() . '%');
        }

        /** @var Collection<int, Messages> $results */
        $results = $query->limit(self::SEARCH_LIMIT)->get();

        return $this->mapResults($results);
    }

    /**
     * @return array<int, array{value: int, name: string}>
     */
    public function buildTypeOptions(): array
    {
        return array_map(fn (int $type) => [
            'value' => $type,
            'name' => $this->typeName($type),
        ], self::TYPE_LIST);
    }

    public function typeName(int $type): string
    {
        return (string) (__('admin/messages.mg_types')[$type] ?? '-');
    }

    /**
     * @param  Collection<int, Messages>          $results
     *
     * @return array<int, array<string, mixed>>
     */
    private function mapResults(Collection $results): array
    {
        return $results->map(fn (Messages $msg) => [
            'message_id' => $msg->message_id,
            'sender' => $msg->senderUser->name ?? '-',
            'receiver' => $msg->receiverUser->name ?? '-',
            'message_time' => $this->timingService->formatExtendedDate((string) $msg->message_time),
            'message_type' => $this->typeName($msg->message_type),
            'message_type_key' => $msg->message_type,
            'message_from' => $msg->message_from,
            'message_subject' => $msg->message_subject,
            'message_text' => nl2br($msg->message_text),
            'message_read' => (bool) $msg->message_read,
        ])->all();
    }
}
