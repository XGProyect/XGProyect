<?php

declare(strict_types=1);

namespace App\Http\Controllers\Game;

use App\Enums\BuddyStatus;
use App\Enums\Module;
use App\Http\Requests\Game\BuddyRequestRequest;
use App\Models\Buddys;
use App\Models\User;
use App\Services\Game\InternalMessageService;
use App\Services\SettingsService;
use App\Services\TimingService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Xgp\App\Libraries\Functions;

/**
 * @SuppressWarnings("PHPMD.StaticAccess")
 */
class BuddiesController extends BaseController
{
    private const MODE_ACTION = 1;
    private const MODE_REQUEST_FORM = 2;

    private const ACTION_REMOVE = 1;
    private const ACTION_ACCEPT = 2;
    private const ACTION_SEND = 3;

    private const NOTIFY_REJECTED = 1;
    private const NOTIFY_DELETED = 2;
    private const NOTIFY_ACCEPTED = 3;
    private const NOTIFY_NEW_REQUEST = 4;

    /**
     * @var array<int, array{title: string, text: string}>
     */
    private const NOTIFICATIONS = [
        self::NOTIFY_REJECTED => ['title' => 'bu_rejected_title', 'text' => 'bu_rejected_text'],
        self::NOTIFY_DELETED => ['title' => 'bu_deleted_title', 'text' => 'bu_deleted_text'],
        self::NOTIFY_ACCEPTED => ['title' => 'bu_accepted_title', 'text' => 'bu_accepted_text'],
        self::NOTIFY_NEW_REQUEST => ['title' => 'bu_to_accept_title', 'text' => 'bu_to_accept_text'],
    ];

    public function __construct(
        private TimingService $timingService,
        private SettingsService $settings,
        private InternalMessageService $messenger,
    ) {
    }

    public function __invoke(Request $request): Response | View
    {
        Functions::moduleMessage(Functions::isModuleAccesible(Module::Buddies));

        // POST to game.php?page=buddies&mode=1&sm=3 is the "send buddy request"
        // form submit; delegate to store() so its FormRequest fires (validation
        // + targetUserId() helpers).
        if (
            $request->isMethod('post')
            && (int) $request->query('mode') === self::MODE_ACTION
            && (int) $request->query('sm') === self::ACTION_SEND
        ) {
            return app()->call([$this, 'store']);
        }

        /** @var User $user */
        $user = Auth::user();

        $actionResponse = $this->handleAction($request, $user);
        if ($actionResponse !== null) {
            return $actionResponse;
        }

        if ((int) $request->query('mode') === self::MODE_REQUEST_FORM) {
            return $this->showRequestForm($request, $user);
        }

        return $this->showList($user);
    }

    public function store(BuddyRequestRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $targetId = $request->targetUserId();

        if ($targetId === (int) $user->id) {
            return redirect('game.php?page=buddies')
                ->with('error', __('game/buddies.bu_cannot_request_yourself'));
        }

        $existing = Buddys::query()->between((int) $user->id, $targetId)->exists();

        if ($existing) {
            return redirect('game.php?page=buddies')
                ->with('error', __('game/buddies.bu_request_exists'));
        }

        Buddys::create([
            'buddy_sender' => (int) $user->id,
            'buddy_receiver' => $targetId,
            'buddy_status' => BuddyStatus::Pending->value,
            'buddy_request_text' => $request->messageText(),
        ]);

        $this->notify($targetId, $user, self::NOTIFY_NEW_REQUEST);

        return redirect('game.php?page=buddies');
    }

    private function handleAction(Request $request, User $user): ?Response
    {
        if ((int) $request->query('mode') !== self::MODE_ACTION) {
            return null;
        }

        $action = (int) $request->query('sm');
        $buddyId = (int) $request->query('bid');

        if ($action === self::ACTION_SEND) {
            // Send-request POST is routed through store(); intentionally ignore here.
            return null;
        }

        if ($buddyId <= 0) {
            return redirect('game.php?page=buddies');
        }

        /** @var Buddys|null $buddy */
        $buddy = Buddys::query()
            ->where('buddy_id', $buddyId)
            ->involving((int) $user->id)
            ->first();

        if ($buddy === null) {
            return redirect('game.php?page=buddies');
        }

        if ($action === self::ACTION_ACCEPT) {
            return $this->acceptRequest($buddy, $user);
        }

        if ($action === self::ACTION_REMOVE) {
            return $this->removeRequest($buddy, $user);
        }

        return redirect('game.php?page=buddies');
    }

    private function acceptRequest(Buddys $buddy, User $user): RedirectResponse
    {
        // Only the receiver can accept; ignore otherwise.
        if ($buddy->buddy_receiver !== (int) $user->id) {
            return redirect('game.php?page=buddies');
        }

        $buddy->update(['buddy_status' => BuddyStatus::Accepted->value]);
        $this->notify($buddy->buddy_sender, $user, self::NOTIFY_ACCEPTED);

        return redirect('game.php?page=buddies');
    }

    private function removeRequest(Buddys $buddy, User $user): RedirectResponse
    {
        $other = $buddy->otherUserId((int) $user->id);
        $notification = $buddy->isAccepted() ? self::NOTIFY_DELETED : self::NOTIFY_REJECTED;

        $this->notify($other, $user, $notification);
        $buddy->delete();

        return redirect('game.php?page=buddies');
    }

    private function showRequestForm(Request $request, User $user): View
    {
        $targetId = (int) $request->query('u');

        if ($targetId === (int) $user->id) {
            // Same message the legacy showed — "you can't friend yourself".
            return view('buddies.message', [
                'gameTitle' => $this->settings->getString('game_name'),
                'message' => __('game/buddies.bu_cannot_request_yourself'),
            ]);
        }

        $target = User::query()->find($targetId, ['id', 'name']);

        if ($target === null) {
            return view('buddies.message', [
                'gameTitle' => $this->settings->getString('game_name'),
                'message' => __('game/buddies.bu_request_exists'),
            ]);
        }

        return view('buddies.request', [
            'gameTitle' => $this->settings->getString('game_name'),
            'id' => (int) $target->id,
            'name' => (string) $target->name,
        ]);
    }

    private function showList(User $user): View
    {
        $userId = (int) $user->id;

        $allBuddies = Buddys::query()
            ->involving($userId)
            ->get();

        // Eager-load the "other" user data for every row, joined with alliance.
        $otherUserIds = $allBuddies
            ->map(fn (Buddys $b) => $b->otherUserId($userId))
            ->unique()
            ->values()
            ->all();

        $others = User::query()
            ->leftJoin('alliance', 'alliance.alliance_id', '=', 'users.ally_id')
            ->whereIn('users.id', $otherUserIds)
            ->get([
                'users.id',
                'users.name',
                'users.galaxy',
                'users.system',
                'users.planet',
                'users.onlinetime',
                'alliance.alliance_id',
                'alliance.alliance_name',
            ])
            ->keyBy('id');

        $received = collect();
        $sent = collect();
        $accepted = collect();

        foreach ($allBuddies as $buddy) {
            $other = $others->get($buddy->otherUserId($userId));
            if ($other === null) {
                continue;
            }

            $row = $this->buildRow($buddy, $other, $userId);

            if ($buddy->isAccepted()) {
                $accepted->push($row);
            } elseif ($buddy->wasSentBy($userId)) {
                $sent->push($row);
            } else {
                $received->push($row);
            }
        }

        return view('buddies.view', [
            'gameTitle' => $this->settings->getString('game_name'),
            'list_of_requests_received' => $received->all(),
            'list_of_requests_sent' => $sent->all(),
            'list_of_buddies' => $accepted->all(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function buildRow(Buddys $buddy, object $other, int $viewerId): array
    {
        return [
            'id' => (int) $other->id,
            'username' => (string) $other->name,
            'ally_id' => (int) ($other->alliance_id ?? 0),
            'alliance_name' => (string) ($other->alliance_name ?? ''),
            'galaxy' => (int) $other->galaxy,
            'system' => (int) $other->system,
            'planet' => (int) $other->planet,
            'text' => $buddy->isAccepted()
                ? $this->timingService->getOnlineStatus((int) $other->onlinetime, time())
                : $buddy->buddy_request_text,
            'action' => $this->actionLinks($buddy, $viewerId),
        ];
    }

    private function actionLinks(Buddys $buddy, int $viewerId): string
    {
        $bid = (int) $buddy->buddy_id;

        if ($buddy->isAccepted()) {
            return $this->link($bid, self::ACTION_REMOVE, __('game/buddies.bu_delete'));
        }

        if ($buddy->wasSentBy($viewerId)) {
            return $this->link($bid, self::ACTION_REMOVE, __('game/buddies.bu_cancel_request'));
        }

        return $this->link($bid, self::ACTION_ACCEPT, __('game/buddies.bu_accept'))
            . '<br>'
            . $this->link($bid, self::ACTION_REMOVE, __('game/buddies.bu_decline'));
    }

    private function link(int $bid, int $sm, string $label): string
    {
        return \sprintf(
            '<a href="game.php?page=buddies&mode=%d&sm=%d&bid=%d">%s</a>',
            self::MODE_ACTION,
            $sm,
            $bid,
            $label,
        );
    }

    private function notify(int $receiverId, User $from, int $notificationType): void
    {
        $template = self::NOTIFICATIONS[$notificationType];

        $this->messenger->send(
            $receiverId,
            (int) $from->id,
            (string) $from->name,
            __('game/buddies.' . $template['title']),
            str_replace('%u', (string) $from->name, __('game/buddies.' . $template['text'])),
        );
    }
}
