<?php

declare(strict_types=1);

namespace App\Http\Controllers\Game;

use App\Core\GameObjects\GameObjectInterface;
use App\Core\GameObjects\GameObjectRegistry;
use App\Enums\Module;
use App\Models\Fleets;
use App\Models\Planets;
use App\Models\Preferences;
use App\Models\User;
use App\Services\FormatService;
use App\Services\Game\PreferencesService;
use App\Services\SettingsService;
use App\Services\TimingService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Xgp\App\Core\Enumerators\PreferencesEnumerator as PrefEnum;
use Xgp\App\Libraries\Functions;

/**
 * @SuppressWarnings("PHPMD.StaticAccess")
 */
class PreferencesController extends BaseController
{
    public function __construct(
        private FormatService $formatService,
        private TimingService $timingService,
        private SettingsService $settingsService,
        private GameObjectRegistry $registry,
        private PreferencesService $preferencesService,
    ) {
    }

    public function __invoke(Request $request): View
    {
        Functions::moduleMessage(Functions::isModuleAccesible(Module::Options));

        /** @var User $user */
        $user = Auth::user();
        $preferences = $this->preferencesService->preferencesFor($user);
        $submitted = $request->isMethod('post');
        $error = '';

        if ($submitted) {
            if ($request->has('preference_vacation_mode')) {
                $this->updateVacationMode($user, $preferences);
                $preferences->refresh();
            }

            $error = $this->savePreferences($request, $user, $preferences);
            $user->refresh();
            $preferences->refresh();
        }

        return view('preferences.view', $this->buildViewData($user, $preferences, $submitted, $error));
    }

    /**
     * @return array<string, mixed>
     */
    private function buildViewData(User $user, Preferences $preferences, bool $submitted, string $error): array
    {
        return array_merge(
            [
                'gameTitle' => $this->settingsService->getString('game_name'),
            ],
            $this->messageData($submitted, $error),
            $this->userData($user, $preferences),
            [
                'preference_spy_probes' => $preferences->preference_spy_probes,
                'sort_planet' => $this->sortPlanetOptions($preferences),
                'sort_sequence' => $this->sortSequenceOptions($preferences),
            ],
            $this->vacationModeData($user, $preferences),
            $this->deleteModeData($preferences)
        );
    }

    /**
     * @return array{color: string, message: string}
     */
    private function messageData(bool $submitted, string $error): array
    {
        if (!$submitted) {
            return [
                'color' => '',
                'message' => '',
            ];
        }

        return [
            'color' => $error === '' ? '#00ff00' : '#ff0000',
            'message' => $error === '' ? __('game/preferences.pr_ok_settings_saved') : $error,
        ];
    }

    /**
     * @return array{name: string, hide_nickname_change: string, email: string}
     */
    private function userData(User $user, Preferences $preferences): array
    {
        return [
            'name' => $user->name,
            'hide_nickname_change' => $this->preferencesService->isNicknameChangeAllowed($preferences) ? '' : 'style="display: none"',
            'email' => $user->email,
        ];
    }

    /**
     * @return array<int, array{value: int, selected: bool, text: string}>
     */
    private function sortPlanetOptions(Preferences $preferences): array
    {
        return collect(PrefEnum::order)
            ->map(fn (int $value, string $order) => [
                'value' => $value,
                'selected' => $value === (int) $preferences->preference_planet_sort,
                'text' => __('game/preferences.pr_order_' . $order),
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<int, array{value: int, selected: bool, text: string}>
     */
    private function sortSequenceOptions(Preferences $preferences): array
    {
        return collect(PrefEnum::sequence)
            ->map(fn (int $value, string $sequence) => [
                'value' => $value,
                'selected' => $value === (int) $preferences->preference_planet_sort_sequence,
                'text' => __('game/preferences.pr_sorting_sequence_' . $sequence),
            ])
            ->values()
            ->all();
    }

    /**
     * @return array{hide_no_vacation: string, pr_vacation_mode_active: string, hide_vacation_invalid: string, disabled: string}
     */
    private function vacationModeData(User $user, Preferences $preferences): array
    {
        if ($this->isVacationModeOn($preferences)) {
            return [
                'hide_no_vacation' => '',
                'pr_vacation_mode_active' => $this->formatService->strongText(
                    $this->formatService->colorRed(__('game/preferences.pr_vacation_mode_active'))
                ),
                'hide_vacation_invalid' => 'style="display: none"',
                'disabled' => $this->isVacationModeRemovalAllowed($preferences) ? '' : 'style="display: none"',
            ];
        }

        if ($this->isEmpireActive($user)) {
            return [
                'hide_no_vacation' => '',
                'pr_vacation_mode_active' => $this->formatService->strongText(
                    $this->formatService->colorRed(__('game/preferences.pr_empire_active') . __('game/preferences.pr_empire_active_fleet'))
                ),
                'hide_vacation_invalid' => '',
                'disabled' => 'style="display: none"',
            ];
        }

        return [
            'hide_no_vacation' => 'style="display: none"',
            'pr_vacation_mode_active' => '',
            'hide_vacation_invalid' => '',
            'disabled' => '',
        ];
    }

    /**
     * @return array{pr_delete_account: string, preference_delete_mode: string, hide_delete: string}
     */
    private function deleteModeData(Preferences $preferences): array
    {
        if ((int) $preferences->preference_delete_mode > 0) {
            return [
                'pr_delete_account' => $this->formatService->colorRed(strtr(
                    $this->translation('game/preferences.pr_delete_mode_active'),
                    [
                        '%s' => $this->timingService->formatExtendedDate(
                            (int) $preferences->preference_delete_mode + ONE_WEEK
                        ),
                    ]
                )),
                'preference_delete_mode' => 'checked="checked"',
                'hide_delete' => 'style="display: none"',
            ];
        }

        return [
            'pr_delete_account' => $this->translation('game/preferences.pr_delete_account'),
            'preference_delete_mode' => '',
            'hide_delete' => '',
        ];
    }

    private function savePreferences(Request $request, User $user, Preferences $preferences): string
    {
        $userUpdates = collect();
        $preferenceUpdates = $this->preferenceUpdates($request);

        foreach ($this->validateAccountUpdates($request, $user, $preferences, $userUpdates, $preferenceUpdates) as $error) {
            if ($error !== null) {
                return $error;
            }
        }

        DB::transaction(function () use ($user, $preferences, $userUpdates, $preferenceUpdates): void {
            if ($userUpdates->isNotEmpty()) {
                $userUpdates->each(fn (mixed $value, string $key) => $user->setAttribute($key, $value));
                $user->save();
            }

            if ($preferenceUpdates->isNotEmpty()) {
                $preferences->fill($preferenceUpdates->all());
                $preferences->save();
            }
        });

        return '';
    }

    /**
    * @return Collection<string, mixed>
     */
    private function preferenceUpdates(Request $request): Collection
    {
        /** @var Collection<string, mixed> $updates */
        $updates = collect([
            'preference_delete_mode' => $request->input('preference_delete_mode') === 'on' ? time() : null,
            'preference_spy_probes' => $this->boundedInteger($request, 'preference_spy_probes', 1, 1, 99),
            'preference_planet_sort' => $this->boundedInteger(
                $request,
                'preference_planet_sort',
                0,
                0,
                max(PrefEnum::order)
            ),
            'preference_planet_sort_sequence' => $this->boundedInteger(
                $request,
                'preference_planet_sort_sequence',
                0,
                0,
                max(PrefEnum::sequence)
            ),
        ]);

        return $updates;
    }

    /**
     * @param Collection<string, mixed> $userUpdates
     * @param Collection<string, mixed> $preferenceUpdates
     *
     * @return array<int, string|null>
     */
    private function validateAccountUpdates(
        Request $request,
        User $user,
        Preferences $preferences,
        Collection $userUpdates,
        Collection $preferenceUpdates,
    ): array {
        return [
            $this->validateNewUserName($request, $user, $preferences, $userUpdates, $preferenceUpdates),
            $this->validateNewPassword($request, $user, $userUpdates),
            $this->validateNewEmail($request, $user, $userUpdates),
        ];
    }

    /**
     * @param Collection<string, mixed> $userUpdates
     * @param Collection<string, mixed> $preferenceUpdates
     */
    private function validateNewUserName(
        Request $request,
        User $user,
        Preferences $preferences,
        Collection $userUpdates,
        Collection $preferenceUpdates,
    ): ?string {
        $newUserName = $this->filledString($request, 'new_user_name');
        $password = $this->filledString($request, 'confirmation_user_password');

        if ($newUserName === null || $password === null || !$this->preferencesService->isNicknameChangeAllowed($preferences)) {
            return null;
        }

        if (!Hash::check($password, $user->password)) {
            return $this->translation('game/preferences.pr_error_wrong_password');
        }

        $usernameLength = strlen($newUserName);

        if ($usernameLength <= 3 || $usernameLength > 20) {
            return strtr(
                $this->translation('game/preferences.pr_error_user_invalid_characters'),
                ['%s' => $newUserName]
            );
        }

        $nameExists = User::where('name', $newUserName)
            ->where('id', '<>', $user->id)
            ->exists();

        if ($nameExists) {
            return $this->translation('game/preferences.pr_error_nick_in_use');
        }

        $userUpdates->put('name', $newUserName);
        $preferenceUpdates->put('preference_nickname_change', time());

        return null;
    }

    /**
     * @param Collection<string, mixed> $userUpdates
     */
    private function validateNewPassword(Request $request, User $user, Collection $userUpdates): ?string
    {
        $currentPassword = $this->filledString($request, 'current_user_password');
        $newPassword = $this->filledString($request, 'new_user_password');

        if ($currentPassword === null || $newPassword === null) {
            return null;
        }

        if (!Hash::check($currentPassword, $user->password)) {
            return $this->translation('game/preferences.pr_error_wrong_password');
        }

        $userUpdates->put('password', $newPassword);

        return null;
    }

    /**
     * @param Collection<string, mixed> $userUpdates
     */
    private function validateNewEmail(Request $request, User $user, Collection $userUpdates): ?string
    {
        $newEmail = $this->filledString($request, 'new_user_email');
        $password = $this->filledString($request, 'confirmation_email_password');

        if ($newEmail === null || $password === null) {
            return null;
        }

        if (!Hash::check($password, $user->password)) {
            return $this->translation('game/preferences.pr_error_wrong_password');
        }

        $emailLength = strlen($newEmail);

        if ($emailLength <= 4 || $emailLength > 64 || filter_var($newEmail, FILTER_VALIDATE_EMAIL) === false) {
            return strtr(
                $this->translation('game/preferences.pr_error_email_invalid_characters'),
                ['%s' => $newEmail]
            );
        }

        $emailExists = User::where('email', $newEmail)
            ->where('id', '<>', $user->id)
            ->exists();

        if ($emailExists) {
            return $this->translation('game/preferences.pr_error_email_in_use');
        }

        $userUpdates->put('email', $newEmail);

        return null;
    }

    private function updateVacationMode(User $user, Preferences $preferences): void
    {
        if ($this->isVacationModeOn($preferences)) {
            if ($this->isVacationModeRemovalAllowed($preferences)) {
                DB::transaction(function () use ($user, $preferences): void {
                    $preferences->update(['preference_vacation_mode' => null]);
                    Planets::where('planet_user_id', $user->id)->update($this->producerPercentUpdate(10, true));
                });
            }

            return;
        }

        if ($this->isEmpireActive($user)) {
            return;
        }

        DB::transaction(function () use ($user, $preferences): void {
            $preferences->update(['preference_vacation_mode' => time()]);
            Planets::where('planet_user_id', $user->id)->update($this->producerPercentUpdate(0));
        });
    }

    private function isEmpireActive(User $user): bool
    {
        return Fleets::where('fleet_owner', $user->id)->exists() ||
            Planets::where('planet_user_id', $user->id)
                ->where(function ($query): void {
                    $query->where('planet_b_building', '<>', 0)
                        ->orWhere('planet_b_tech', '<>', 0)
                        ->orWhere('planet_b_hangar', '<>', 0);
                })
                ->exists();
    }

    /**
     * @return array<string, int>
     */
    private function producerPercentUpdate(int $level, bool $includeLastUpdate = false): array
    {
        $updates = $this->registry->producers()
            ->mapWithKeys(fn (GameObjectInterface $object) => [
                'planet_' . $object->getName() . '_percent' => $level,
            ]);

        if ($includeLastUpdate) {
            $updates->put('planet_last_update', time());
        }

        return $updates->all();
    }

    private function isVacationModeOn(Preferences $preferences): bool
    {
        return (int) $preferences->preference_vacation_mode > 0;
    }

    private function isVacationModeRemovalAllowed(Preferences $preferences): bool
    {
        return ((int) $preferences->preference_vacation_mode + ONE_DAY * 2) < time();
    }

    private function boundedInteger(Request $request, string $key, int $default, int $min, int $max): int
    {
        return min(max($request->integer($key, $default), $min), $max);
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
