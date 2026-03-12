<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Users;

use App\Http\Requests\Admin\Users\UserPremiumRequest;
use App\Http\Requests\Admin\Users\UserResearchRequest;
use App\Models\User;
use App\Services\SettingsService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;
use Xgp\App\Libraries\StatisticsLibrary;

class UserProgressController extends BaseController
{
    public function __construct(private readonly SettingsService $settings)
    {
    }

    public function showResearch(User $user): View
    {
        $research = DB::table('research')->where('research_user_id', $user->id)->first();

        return view('admin.users_research', [
            'user' => $user,
            'technologies' => $this->buildResearchList((array) ($research ?? [])),
        ]);
    }

    public function updateResearch(UserResearchRequest $request, User $user): RedirectResponse
    {
        /** @var array<string, mixed> $validated */
        $validated = $request->validated();

        $updates = collect($validated)
            ->filter(fn (mixed $v, string $k): bool => str_starts_with($k, 'research_'))
            ->map(fn (mixed $v): int => intval($v)) // @phpstan-ignore argument.type
            ->all();

        if (!empty($updates)) {
            DB::table('research')->where('research_user_id', $user->id)->update($updates);
        }

        (new StatisticsLibrary())->rebuildPoints($user->id, 0, 'research');

        session()->flash('success', __('admin/users.us_all_ok_message'));

        return redirect()->route('admin.users.research', $user->id);
    }

    public function showPremium(User $user): View
    {
        $premium = DB::table('premium')->where('premium_user_id', $user->id)->first();
        $dateFormat = $this->dateFormat();

        return view('admin.users_premium', [
            'user' => $user,
            'dark_matter' => (int) ($premium->premium_dark_matter ?? 0),
            'officers' => $this->buildPremiumList((array) ($premium ?? []), $dateFormat),
        ]);
    }

    public function updatePremium(UserPremiumRequest $request, User $user): RedirectResponse
    {
        $currentPremium = (array) (DB::table('premium')->where('premium_user_id', $user->id)->first() ?? []);
        $updates = [];

        if ($request->filled('premium_dark_matter')) {
            $updates['premium_dark_matter'] = $request->integer('premium_dark_matter');
        }

        foreach ($request->all() as $key => $value) {
            if (is_string($key) && str_starts_with($key, 'premium_') && $key !== 'premium_dark_matter') {
                $updates[$key] = match ((int) $value) {
                    1 => 0,
                    2 => time() + (3600 * 24 * 7),
                    3 => time() + (3600 * 24 * 30 * 3),
                    default => $currentPremium[$key] ?? 0,
                };
            }
        }

        if (!empty($updates)) {
            DB::table('premium')->where('premium_user_id', $user->id)->update($updates);
        }

        session()->flash('success', __('admin/users.us_all_ok_message'));

        return redirect()->route('admin.users.premium', $user->id);
    }

    /**
     * @param array<string, mixed> $row
     *
     * @return array<int, array<string, mixed>>
     */
    private function buildResearchList(array $row): array
    {
        $list = [];
        $skip = 3;

        foreach ($row as $key => $value) {
            if (!is_string($key) || !str_starts_with($key, 'research_')) {
                continue;
            }
            if ($skip-- > 0) {
                continue;
            }

            $list[] = [
                'field' => $key,
                'label' => (string) __('admin/users.us_user_' . $key),
                'level' => intval($value), // @phpstan-ignore argument.type
            ];
        }

        return $list;
    }

    /**
     * @param array<string, mixed> $row
     *
     * @return array<int, array<string, mixed>>
     */
    private function buildPremiumList(array $row, string $dateFormat): array
    {
        $list = [];

        foreach ($row as $key => $value) {
            if (!is_string($key) || !str_starts_with($key, 'premium_') || in_array($key, ['premium_dark_matter', 'premium_user_id'], true)) {
                continue;
            }

            $labelKey = 'admin/users.us_user_' . $key;
            $label = __($labelKey);

            if ($label === $labelKey) {
                continue;
            }

            $expire = intval($value); // @phpstan-ignore argument.type

            $list[] = [
                'field' => $key,
                'label' => (string) $label,
                'expire' => $expire,
                'active' => $expire > 0 && $expire > time(),
                'status_text' => ($expire === 0 || $expire < time())
                    ? (string) __('admin/users.us_user_premium_inactive')
                    : (string) __('admin/users.us_user_premium_active_until') . date($dateFormat, $expire),
            ];
        }

        return $list;
    }

    private function dateFormat(): string
    {
        return $this->settings->getString('date_format') ?: 'Y-m-d';
    }
}
