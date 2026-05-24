<?php

declare(strict_types=1);

namespace App\Http\Controllers\Game;

use App\Models\Preferences;
use App\Models\User;
use App\Services\SettingsService;
use App\Services\TimingService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ChangenickController extends BaseController
{
    public function __construct(
        private SettingsService $settings,
        private TimingService $timingService,
    ) {
    }

    public function __invoke(Request $request): View
    {
        /** @var User $user */
        $user = Auth::user();
        $preferences = $this->preferencesFor($user);
        $message = '';
        $color = '';

        if ($request->isMethod('post')) {
            $result = $this->changeName($request, $user, $preferences);
            $message = $result['message'];
            $color = $result['success'] ? '#00ff00' : '#ff0000';
            $user->refresh();
            $preferences->refresh();
        }

        return view('changenick.view', [
            'gameTitle' => $this->settings->getString('game_name'),
            'noLeftMenu' => true,
            'noTopnav' => true,
            'color' => $color,
            'message' => $message,
            'currentName' => $user->name,
            'canChangeName' => $this->isNickNameChangeAllowed($preferences),
            'nextChangeAt' => $this->timingService->formatExtendedDate($this->nextNickNameChangeAt($preferences)),
        ]);
    }

    private function preferencesFor(User $user): Preferences
    {
        $preferences = Preferences::firstOrCreate(
            ['preference_user_id' => $user->id],
            [
                'preference_spy_probes' => 1,
                'preference_planet_sort' => 0,
                'preference_planet_sort_sequence' => 0,
            ]
        );

        $user->setRelation('preferences', $preferences);

        return $preferences;
    }

    /**
     * @return array{success: bool, message: string}
     */
    private function changeName(Request $request, User $user, Preferences $preferences): array
    {
        if (!$this->isNickNameChangeAllowed($preferences)) {
            return [
                'success' => false,
                'message' => strtr(
                    $this->translation('game/changenick.cn_error_week_wait'),
                    ['%s' => $this->timingService->formatExtendedDate($this->nextNickNameChangeAt($preferences))]
                ),
            ];
        }

        $newUserName = $this->filledString($request, 'new_user_name');
        $password = $this->filledString($request, 'confirmation_user_password');

        if ($newUserName === null || $password === null) {
            return ['success' => false, 'message' => $this->translation('game/changenick.cn_error_required')];
        }

        if (!Hash::check($password, $user->password)) {
            return ['success' => false, 'message' => $this->translation('game/preferences.pr_error_wrong_password')];
        }

        $usernameLength = strlen($newUserName);

        if ($usernameLength <= 3 || $usernameLength > 20) {
            return [
                'success' => false,
                'message' => strtr(
                    $this->translation('game/preferences.pr_error_user_invalid_characters'),
                    ['%s' => $newUserName]
                ),
            ];
        }

        $nameExists = User::where('name', $newUserName)
            ->where('id', '<>', $user->id)
            ->exists();

        if ($nameExists) {
            return ['success' => false, 'message' => $this->translation('game/preferences.pr_error_nick_in_use')];
        }

        DB::transaction(function () use ($user, $preferences, $newUserName): void {
            $user->name = $newUserName;
            $user->save();

            $preferences->preference_nickname_change = time();
            $preferences->save();
        });

        return ['success' => true, 'message' => $this->translation('game/changenick.cn_name_changed')];
    }

    private function isNickNameChangeAllowed(Preferences $preferences): bool
    {
        return $this->nextNickNameChangeAt($preferences) < time();
    }

    private function nextNickNameChangeAt(Preferences $preferences): int
    {
        return (int) $preferences->preference_nickname_change + ONE_WEEK;
    }

    private function filledString(Request $request, string $key): ?string
    {
        $value = $request->input($key);

        if (!is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }

    private function translation(string $key): string
    {
        $line = __($key);

        return is_string($line) ? $line : '';
    }
}
