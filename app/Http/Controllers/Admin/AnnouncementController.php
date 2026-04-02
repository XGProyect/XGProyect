<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\AnnouncementRequest;
use App\Mail\Announcement;
use App\Models\User;
use App\Services\FormatService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Mail\SentMessage;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Xgp\App\Core\Enumerators\MessagesEnumerator;
use Xgp\App\Core\Enumerators\UserRanksEnumerator as UserRanks;
use Xgp\App\Libraries\Functions;

class AnnouncementController extends BaseController
{
    public function __construct(
        private readonly FormatService $formatService,
    ) {
    }

    public function index(): View
    {
        return view('admin.announcement');
    }

    public function send(AnnouncementRequest $request): RedirectResponse
    {
        $players = User::select('id', 'name', 'email')->get();

        if ($request->filled('message')) {
            $this->sendMessages($request, $players);
        }

        if ($request->filled('mail')) {
            $this->sendEmails($request, $players);
        }

        return redirect()->route('admin.announcement');
    }

    /**
     * @param Collection<int, User> $players
     */
    private function sendMessages(AnnouncementRequest $request, Collection $players): void
    {
        /** @var User $user */
        $user = Auth::user();

        $pickedColor = (string) $request->string('color-picker');
        $color = $this->isValidColor($pickedColor)
            ? $pickedColor
            : $this->getMessageColor()[$user->authlevel];

        $level = (string) ((array) __('admin/global.user_level'))[$user->authlevel];
        $time = time();

        $from = $this->formatService->customColor($level, $color);
        $subject = $this->formatService->customColor(
            (string) $request->string('subject') ?: (string) __('admin/announcement.an_none'),
            $color
        );
        $message = $this->formatService->customColor((string) $request->string('text'), $color);

        foreach ($players as $player) {
            Functions::sendMessage(
                (int) $player->id,
                (int) $user->id,
                $time,
                MessagesEnumerator::GENERAL,
                $from,
                $subject,
                strtr($message, ['%player%' => $this->formatService->strongText($player->name)]),
                true
            );
        }

        session()->flash('success', __('admin/announcement.an_sent'));
    }

    /**
     * @param Collection<int, User> $players
     */
    private function sendEmails(AnnouncementRequest $request, Collection $players): void
    {
        $results = [];

        foreach ($players as $index => $player) {
            $result = Mail::to($player->email, $player->name)->send(new Announcement(
                (string) $request->string('subject'),
                strtr((string) $request->string('text'), ['%player%' => $this->formatService->strongText($player->name)])
            ));

            $results[] = $player->name . ': ' . ($result instanceof SentMessage
                ? __('admin/announcement.an_email_sent')
                : __('admin/announcement.an_email_failed'));

            // Throttle: pause after every 20 emails to prevent flooding
            if ($index > 0 && $index % 20 === 0) {
                sleep(1);
            }
        }

        session()->flash(
            'info',
            strtr(
                __('admin/announcement.an_delivery_result'),
                ['%s' => implode('<br>', $results)]
            )
        );
    }

    private function isValidColor(string $color): bool
    {
        return (bool) preg_match('/^#[0-9a-fA-F]{6}$/', $color);
    }

    /**
     * @return array<int, string>
     */
    private function getMessageColor(): array
    {
        return [
            UserRanks::GO => 'yellow',
            UserRanks::SGO => 'skyblue',
            UserRanks::ADMIN => 'red',
        ];
    }
}
