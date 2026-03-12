<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\AllianceInfoRequest;
use App\Http\Requests\Admin\AllianceMembersRequest;
use App\Http\Requests\Admin\AllianceRankRequest;
use App\Models\Alliance;
use App\Models\User;
use App\Services\Admin\AlliancesService;
use App\Services\SettingsService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Xgp\App\Libraries\Alliance\Ranks;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class AlliancesController extends BaseController
{
    public function __construct(
        private readonly AlliancesService $alliancesService,
        private readonly SettingsService $settings,
    ) {
    }

    public function index(Request $request): View
    {
        $search = trim($request->string('alliance')->toString());
        $type = trim($request->string('type', 'info')->toString());

        $alliance = null;

        if ($search !== '') {
            $alliance = Alliance::query()
                ->withCount('members')
                ->with('owner:id,name')
                ->where('alliance_name', $search)
                ->orWhere('alliance_tag', $search)
                ->first();

            if ($alliance === null) {
                session()->flash('danger', __('admin/alliances.al_nothing_found'));
            }
        }

        return view('admin.alliances', [
            'alliance' => $alliance,
            'search' => $search,
            'type' => $type,
        ]);
    }

    public function create(): View
    {
        $usersWithoutAlliance = User::where('ally_id', 0)
            ->where('ally_request', 0)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return view('admin.alliances_create', [
            'users' => $usersWithoutAlliance,
        ]);
    }

    /** @SuppressWarnings(PHPMD.ElseExpression) */
    public function store(Request $request): RedirectResponse
    {
        $allianceName = trim($request->string('name')->toString());
        $allianceTag = trim($request->string('tag')->toString());
        $allianceFounder = $request->integer('founder');

        $allianceExists = Alliance::where('alliance_name', $allianceName)
            ->orWhere('alliance_tag', $allianceTag)
            ->exists();

        if (!$allianceExists && !empty($allianceFounder) && $allianceFounder > 0) {
            $this->alliancesService->createAlliance($allianceName, $allianceTag, $allianceFounder);
            session()->flash('success', __('admin/alliances.al_create_added'));
        } else {
            session()->flash('warning', __('admin/alliances.al_create_all_fields'));
        }

        return redirect()->route('admin.alliances.create');
    }

    public function showInfo(Alliance $alliance): View
    {
        $allianceData = $this->alliancesService->loadAllianceWithStats($alliance->alliance_id);
        $users = User::query()->select('id', 'name')->orderBy('name')->get();
        $dateFormat = $this->settings->getString('date_format_extended') ?: 'Y-m-d H:i:s';

        return view('admin.alliances_information', [
            'alliance' => $allianceData,
            'users' => $users,
            'register_time' => $allianceData->alliance_register_time === 0
                ? '-'
                : date($dateFormat, $allianceData->alliance_register_time),
        ]);
    }

    public function showRanks(Alliance $alliance): View
    {
        $ranks = new Ranks($alliance->alliance_ranks);
        $ranksArray = $this->alliancesService->buildRanksViewData($ranks->getAllRanksAsArray());

        return view('admin.alliances_ranks', [
            'alliance' => $alliance,
            'ranks' => $ranksArray,
        ]);
    }

    public function showMembers(Alliance $alliance): View
    {
        $ranks = new Ranks($alliance->alliance_ranks);
        $members = $this->alliancesService->buildMembersViewData($alliance->alliance_id, $ranks);
        $rankOptions = $this->alliancesService->buildRankOptions($ranks->getAllRanksAsArray());

        return view('admin.alliances_members', [
            'alliance' => $alliance,
            'members' => $members,
            'rank_options' => $rankOptions,
        ]);
    }

    public function updateInfo(AllianceInfoRequest $request, Alliance $alliance): RedirectResponse
    {
        /** @var array<string, mixed> $validated */
        $validated = $request->validated();
        $alliance->update($validated);

        session()->flash('success', __('admin/alliances.al_all_ok_message'));

        return redirect()->route('admin.alliances.info', $alliance->alliance_id);
    }

    /**
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function updateRanks(AllianceRankRequest $request, Alliance $alliance): RedirectResponse
    {
        $ranks = new Ranks($alliance->alliance_ranks);

        if ($request->filled('create_rank')) {
            $this->alliancesService->createRank($request, $ranks);
        } elseif ($request->has('save_ranks')) {
            $this->alliancesService->saveRankPermissions($request, $ranks);
        } elseif ($request->has('delete_ranks')) {
            $this->alliancesService->deleteRanks($request, $ranks);
        }

        $alliance->update(['alliance_ranks' => $ranks->getAllRanksAsJsonString()]);

        return redirect()->route('admin.alliances.ranks', $alliance->alliance_id);
    }

    /**
     * Remove selected members from an alliance.
     */
    public function removeMembers(AllianceMembersRequest $request, Alliance $alliance): RedirectResponse
    {
        /** @var array<int|string, string> $deletions */
        $deletions = (array) $request->input('delete_message', []);

        $ids = collect($deletions)
            ->filter(fn ($status, $userId) => $status === 'on' && $userId > 0)
            ->keys()
            ->map(fn ($id) => (int) $id)
            ->all();

        $memberCount = User::query()->where('ally_id', $alliance->alliance_id)->count();

        if ($memberCount - count($ids) < 1) {
            session()->flash('warning', __('admin/alliances.al_cant_delete_last_one'));

            return redirect()->route('admin.alliances.members', $alliance->alliance_id);
        }

        User::query()->whereIn('id', $ids)->update([
            'ally_id' => 0,
            'ally_request' => 0,
            'ally_request_text' => '',
            'ally_rank_id' => 0,
        ]);

        session()->flash('success', __('admin/alliances.us_all_ok_message'));

        return redirect()->route('admin.alliances.members', $alliance->alliance_id);
    }

    public function updateMemberRanks(Request $request, Alliance $alliance): RedirectResponse
    {
        /** @var array<int|string, int|string> $memberRanks */
        $memberRanks = (array) $request->input('member_rank', []);

        foreach ($memberRanks as $userId => $rankId) {
            User::query()
                ->where('id', (int) $userId)
                ->where('ally_id', $alliance->alliance_id)
                ->update(['ally_rank_id' => (int) $rankId]);
        }

        session()->flash('success', __('admin/alliances.al_all_ok_message'));

        return redirect()->route('admin.alliances.members', $alliance->alliance_id);
    }

    public function destroy(Alliance $alliance): RedirectResponse
    {
        User::query()->where('ally_id', $alliance->alliance_id)->update([
            'ally_id' => 0,
            'ally_request' => 0,
            'ally_request_text' => '',
            'ally_rank_id' => 0,
        ]);

        $alliance->delete();

        session()->flash('success', __('admin/alliances.al_all_ok_message'));

        return redirect()->route('admin.alliances');
    }
}
