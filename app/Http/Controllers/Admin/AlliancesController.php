<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\AllianceInfoRequest;
use App\Http\Requests\Admin\AllianceMembersRequest;
use App\Http\Requests\Admin\AllianceRankRequest;
use App\Models\Alliance;
use App\Models\User;
use App\Services\AdministrationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;
use Xgp\App\Core\Enumerators\AllianceRanksEnumerator as AllianceRanks;
use Xgp\App\Core\Enumerators\SwitchIntEnumerator as SwitchInt;
use Xgp\App\Core\Options;
use Xgp\App\Libraries\Alliance\Ranks;

class AlliancesController extends BaseController
{
    public function __construct(
        private readonly AdministrationService $administrationService,
    ) {
    }

    public function index(Request $request): View
    {
        $this->administrationService->checkSession();
        $this->administrationService->authorization(__CLASS__);

        $search = trim($request->input('alliance', ''));
        $type = trim($request->input('type', 'info'));

        $alliance = null;

        if ($search !== '') {
            $alliance = Alliance::withCount('members')
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

    public function showInfo(Request $request, Alliance $alliance): View
    {
        $this->administrationService->checkSession();
        $this->administrationService->authorization(__CLASS__);

        $allianceData = $this->loadAllianceWithStats($alliance->alliance_id);
        $users = User::select('id', 'name')->orderBy('name')->get();

        return view('admin.alliances_information', [
            'alliance' => $allianceData,
            'users' => $users,
            'register_time' => $allianceData->alliance_register_time === 0
                ? '-'
                : date(Options::getInstance()->get('date_format_extended'), $allianceData->alliance_register_time),
        ]);
    }

    public function showRanks(Request $request, Alliance $alliance): View
    {
        $this->administrationService->checkSession();
        $this->administrationService->authorization(__CLASS__);

        $ranks = new Ranks($alliance->alliance_ranks);
        $ranksArray = $this->buildRanksViewData($ranks->getAllRanksAsArray());

        return view('admin.alliances_ranks', [
            'alliance' => $alliance,
            'ranks' => $ranksArray,
        ]);
    }

    public function showMembers(Request $request, Alliance $alliance): View
    {
        $this->administrationService->checkSession();
        $this->administrationService->authorization(__CLASS__);

        $ranks = new Ranks($alliance->alliance_ranks);
        $members = $this->buildMembersViewData($alliance->alliance_id, $ranks);

        return view('admin.alliances_members', [
            'alliance' => $alliance,
            'members' => $members,
        ]);
    }

    public function updateInfo(AllianceInfoRequest $request, Alliance $alliance): RedirectResponse
    {
        $this->administrationService->checkSession();
        $this->administrationService->authorization(__CLASS__);

        /** @var array<string, mixed> $validated */
        $validated = $request->validated();
        $alliance->update($validated);

        session()->flash('success', __('admin/alliances.al_all_ok_message'));

        return redirect()->route('admin.alliances.info', $alliance->alliance_id);
    }

    public function updateRanks(AllianceRankRequest $request, Alliance $alliance): RedirectResponse
    {
        $this->administrationService->checkSession();
        $this->administrationService->authorization(__CLASS__);

        $ranks = new Ranks($alliance->alliance_ranks);

        if ($request->filled('create_rank')) {
            $ranks->addNew((string) $request->input('rank_name'));
            session()->flash('success', __('admin/alliances.al_rank_added'));
        } elseif ($request->has('save_ranks')) {
            /** @var array<int, int|string> $ids */
            $ids = (array) $request->input('id', []);

            foreach ($ids as $id) {
                $id = (int) $id;
                $ranks->editRankById($id, [
                    AllianceRanks::DELETE => $request->has('u' . $id . 'r1') ? SwitchInt::on : SwitchInt::off,
                    AllianceRanks::KICK => $request->has('u' . $id . 'r2') ? SwitchInt::on : SwitchInt::off,
                    AllianceRanks::APPLICATIONS => $request->has('u' . $id . 'r3') ? SwitchInt::on : SwitchInt::off,
                    AllianceRanks::VIEW_MEMBER_LIST => $request->has('u' . $id . 'r4') ? SwitchInt::on : SwitchInt::off,
                    AllianceRanks::APPLICATION_MANAGEMENT => $request->has('u' . $id . 'r5') ? SwitchInt::on : SwitchInt::off,
                    AllianceRanks::ADMINISTRATION => $request->has('u' . $id . 'r6') ? SwitchInt::on : SwitchInt::off,
                    AllianceRanks::ONLINE_STATUS => $request->has('u' . $id . 'r7') ? SwitchInt::on : SwitchInt::off,
                    AllianceRanks::SEND_CIRCULAR => $request->has('u' . $id . 'r8') ? SwitchInt::on : SwitchInt::off,
                    AllianceRanks::RIGHT_HAND => $request->has('u' . $id . 'r9') ? SwitchInt::on : SwitchInt::off,
                ]);
            }

            session()->flash('success', __('admin/alliances.al_rank_saved'));
        } elseif ($request->has('delete_ranks')) {
            /** @var array<int, int|string> $toDelete */
            $toDelete = (array) $request->input('delete_message', []);

            foreach ($toDelete as $rankId) {
                $ranks->deleteRankById((int) $rankId);
            }

            session()->flash('success', __('admin/alliances.al_rank_removed'));
        }

        $alliance->update(['alliance_ranks' => $ranks->getAllRanksAsJsonString()]);

        return redirect()->route('admin.alliances.ranks', $alliance->alliance_id);
    }

    /**
     * Remove selected members from an alliance.
     */
    public function removeMembers(AllianceMembersRequest $request, Alliance $alliance): RedirectResponse
    {
        $this->administrationService->checkSession();
        $this->administrationService->authorization(__CLASS__);

        /** @var array<int|string, string> $deletions */
        $deletions = (array) $request->input('delete_message', []);

        $ids = collect($deletions)
            ->filter(fn ($status, $userId) => $status === 'on' && $userId > 0)
            ->keys()
            ->map(fn ($id) => (int) $id)
            ->all();

        $memberCount = User::where('ally_id', $alliance->alliance_id)->count();

        if ($memberCount - count($ids) < 1) {
            session()->flash('warning', __('admin/alliances.al_cant_delete_last_one'));
        } else {
            User::whereIn('id', $ids)->update([
                'ally_id' => 0,
                'ally_request' => 0,
                'ally_request_text' => '',
                'ally_rank_id' => 0,
            ]);

            session()->flash('success', __('admin/alliances.us_all_ok_message'));
        }

        return redirect()->route('admin.alliances.members', $alliance->alliance_id);
    }

    public function destroy(Alliance $alliance): RedirectResponse
    {
        $this->administrationService->checkSession();
        $this->administrationService->authorization(__CLASS__);

        User::where('ally_id', $alliance->alliance_id)->update([
            'ally_id' => 0,
            'ally_request' => 0,
            'ally_request_text' => '',
            'ally_rank_id' => 0,
        ]);

        $alliance->delete();

        session()->flash('success', __('admin/alliances.al_all_ok_message'));

        return redirect()->route('admin.alliances');
    }

    private function loadAllianceWithStats(int $id): \stdClass
    {
        $result = DB::table('alliance AS a')
            ->select('a.*', 'als.*')
            ->join('alliance_statistics AS als', 'als.alliance_statistic_alliance_id', '=', 'a.alliance_id')
            ->where('a.alliance_id', $id)
            ->first();

        if ($result === null) {
            abort(404);
        }

        return $result;
    }

    /**
     * @param array<int, array<string, mixed>>  $rawRanks
     *
     * @return array<int, array<string, mixed>>
     */
    private function buildRanksViewData(array $rawRanks): array
    {
        return array_values(array_map(function (array $details, int $index) {
            /** @var array<int, int> $rights */
            $rights = $details['rights'];

            return [
                'i' => $index,
                'name' => $details['rank'],
                'delete' => $rights[AllianceRanks::DELETE] === SwitchInt::on,
                'kick' => $rights[AllianceRanks::KICK] === SwitchInt::on,
                'applications' => $rights[AllianceRanks::APPLICATIONS] === SwitchInt::on,
                'memberlist' => $rights[AllianceRanks::VIEW_MEMBER_LIST] === SwitchInt::on,
                'app_management' => $rights[AllianceRanks::APPLICATION_MANAGEMENT] === SwitchInt::on,
                'administration' => $rights[AllianceRanks::ADMINISTRATION] === SwitchInt::on,
                'online_status' => $rights[AllianceRanks::ONLINE_STATUS] === SwitchInt::on,
                'send_circular' => $rights[AllianceRanks::SEND_CIRCULAR] === SwitchInt::on,
                'right_hand' => $rights[AllianceRanks::RIGHT_HAND] === SwitchInt::on,
            ];
        }, $rawRanks, array_keys($rawRanks)));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildMembersViewData(int $allianceId, Ranks $ranks): array
    {
        $dateFormat = Options::getInstance()->get('date_format_extended');

        return DB::table('users AS u')
            ->select('u.id', 'u.name', 'u.ally_request', 'u.ally_request_text', 'u.ally_register_time', 'u.ally_rank_id')
            ->where('u.ally_id', $allianceId)
            ->get()
            ->map(function (object $row) use ($ranks, $dateFormat): array {
                $rankData = $ranks->getRankById((int) $row->ally_rank_id);

                return [
                    'id' => $row->id,
                    'name' => $row->name,
                    'pending_request' => (bool) $row->ally_request,
                    'request_text' => $row->ally_request_text ?: '-',
                    'register_time' => date($dateFormat, (int) $row->ally_register_time),
                    'rank' => $rankData['rank'] ?? __('admin/alliances.al_rank_not_defined'),
                ];
            })
            ->all();
    }
}
