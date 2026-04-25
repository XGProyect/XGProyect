<?php

declare(strict_types=1);

namespace App\Http\Controllers\Game;

use App\Enums\Module;
use App\Models\Notes;
use App\Models\User;
use App\Services\FormatService;
use App\Services\SettingsService;
use App\Services\TimingService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;
use Xgp\App\Libraries\Functions;

class NoticesController extends BaseController
{
    private const VALID_PRIORITIES = [0, 1, 2];
    private const DEFAULT_PRIORITY = 2;

    public function __construct(
        private FormatService $formatService,
        private SettingsService $settingsService,
        private TimingService $timingService,
    ) {
    }

    /**
     * @SuppressWarnings("PHPMD.StaticAccess")
     */
    public function __invoke(Request $request): View | RedirectResponse
    {
        Functions::moduleMessage(Functions::isModuleAccesible(Module::Notes));

        /** @var User $user */
        $user = Auth::user();

        if ($request->isMethod('POST')) {
            return $this->handleAction($request, $user);
        }

        $action = (int) $request->query('a', 0);

        if (in_array($action, [1, 2])) {
            return view('notices.write', $this->buildWriteData($request, $user));
        }

        return view('notices.view', $this->buildListData($user));
    }

    private function handleAction(Request $request, User $user): RedirectResponse
    {
        $formAction = $request->integer('s');
        $delete = $request->input('delnote');

        if ($formAction === 1) {
            $this->createNote($request, $user);
        } elseif ($formAction === 2) {
            $this->updateNote($request, $user);
        } elseif (is_array($delete) && count($delete) > 0) {
            $this->deleteNotes($delete, $user);
        }

        return redirect('game.php?page=notices');
    }

    private function createNote(Request $request, User $user): void
    {
        Notes::create([
            'note_owner' => $user->id,
            'note_time' => time(),
            'note_priority' => $this->sanitizePriority($request->integer('u')),
            'note_title' => $this->sanitizeTitle($request->input('title')),
            'note_text' => strip_tags((string) $request->input('text', '')),
        ]);
    }

    private function updateNote(Request $request, User $user): void
    {
        Notes::where('note_id', $request->integer('n'))
            ->where('note_owner', $user->id)
            ->update([
                'note_time' => time(),
                'note_priority' => $this->sanitizePriority($request->integer('u')),
                'note_title' => $this->sanitizeTitle($request->input('title')),
                'note_text' => strip_tags((string) $request->input('text', '')),
            ]);
    }

    private function deleteNotes(array $delete, User $user): void
    {
        $ids = array_keys(array_filter($delete, fn ($val) => $val === 'y'));

        if (!empty($ids)) {
            Notes::where('note_owner', $user->id)
                ->whereIn('note_id', $ids)
                ->delete();
        }
    }

    private function buildListData(User $user): array
    {
        $notes = Notes::where('note_owner', $user->id)
            ->orderByDesc('note_time')
            ->get();

        return [
            'gameTitle' => $this->settingsService->getString('game_name'),
            'notes' => $notes->map(fn (Notes $note) => [
                'id' => $note->note_id,
                'time' => $this->timingService->formatExtendedDate($note->note_time),
                'color' => $this->formatService->getImportanceColor($note->note_priority),
                'title' => $note->note_title,
            ])->all(),
        ];
    }

    private function buildWriteData(Request $request, User $user): array
    {
        $action = (int) $request->query('a', 0);

        if ($action === 2) {
            $note = Notes::where('note_id', $request->integer('n'))
                ->where('note_owner', $user->id)
                ->first();

            if ($note) {
                return [
                    'gameTitle' => $this->settingsService->getString('game_name'),
                    'formAction' => 2,
                    'noteId' => $note->note_id,
                    'title' => __('game/notices.nt_edit_note'),
                    'subject' => $note->note_title,
                    'text' => $note->note_text,
                    'priority' => $note->note_priority,
                ];
            }
        }

        return [
            'gameTitle' => $this->settingsService->getString('game_name'),
            'formAction' => 1,
            'noteId' => null,
            'title' => __('game/notices.nt_add_note'),
            'subject' => __('game/notices.nt_your_subject'),
            'text' => '',
            'priority' => 1,
        ];
    }

    private function sanitizePriority(int $value): int
    {
        return in_array($value, self::VALID_PRIORITIES) ? $value : self::DEFAULT_PRIORITY;
    }

    private function sanitizeTitle(?string $title): string
    {
        $title = strip_tags(trim((string) $title));

        return $title !== '' ? $title : (string) __('game/notices.nt_your_subject');
    }
}
