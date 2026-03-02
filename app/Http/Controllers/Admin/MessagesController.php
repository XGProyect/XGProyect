<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\MessagesSearchRequest;
use App\Models\Messages;
use App\Services\Admin\MessagesService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller as BaseController;

class MessagesController extends BaseController
{
    public function __construct(
        private readonly MessagesService $messagesService,
    ) {
    }

    public function index(MessagesSearchRequest $request): View
    {
        $hasSearch = $request->hasFilters();
        $results = [];

        if ($hasSearch) {
            $results = $this->messagesService->search($request);

            if (empty($results)) {
                session()->flash('warning', __('admin/messages.mg_no_results'));
            }
        }

        return view('admin.messages', [
            'results' => $results,
            'hasSearch' => $hasSearch,
            'type_options' => $this->messagesService->buildTypeOptions(),
            'search' => $request->searchValues(),
        ]);
    }

    public function destroy(Messages $message): RedirectResponse
    {
        $message->delete();

        session()->flash('success', __('admin/messages.mg_delete_ok'));

        return redirect()->route('admin.messages');
    }

    public function destroyBatch(MessagesSearchRequest $request): RedirectResponse
    {
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
}
