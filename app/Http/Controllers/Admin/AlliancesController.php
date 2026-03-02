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

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
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
        $this->administrationService->checkSession();
        $this->administrationService->authorization(__CLASS__);

        /** @phpstan-ignore staticMethod.notFound */
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
        $this->administrationService->checkSession();
        $this->administrationService->authorization(__CLASS__);

        $allianceName = trim($request->string('name')->toString());
        $allianceTag = trim($request->string('tag')->toString());
        $allianceFounder = $request->integer('founder');

        /** @phpstan-ignore staticMethod.notFound */
        $allianceExists = Alliance::where('alliance_name', $allianceName)
            ->orWhere('alliance_tag', $allianceTag)
            ->exists();

        if (!$allianceExists && !empty($allianceFounder) && $allianceFounder > 0) {
            $this->createAlliance($allianceName, $allianceTag, $allianceFounder);
            session()->flash('success', __('admin/alliances.al_create_added'));
        } else {
            session()->flash('warning', __('admin/alliances.al_create_all_fields'));
        }

        return redirect()->route('admin.alliances.create');
    }

    public function showInfo(Alliance $alliance): View
    {
        $this->administrationService->checkSession();
        $this->administrationService->authorization(__CLASS__);

        $allianceData = $this->loadAllianceWithStats($alliance->alliance_id);
        $users = User::query()->select('id', 'name')->orderBy('name')->get();
        $dateFormat = (string) (Options::getInstance()->get('date_format_extended') ?? 'Y-m-d H:i:s');

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
        $this->administrationService->checkSession();
        $this->administrationService->authorization(__CLASS__);

        $ranks = new Ranks($alliance->alliance_ranks);
        $ranksArray = $this->buildRanksViewData($ranks->getAllRanksAsArray());

        return view('admin.alliances_ranks', [
            'alliance' => $alliance,
            'ranks' => $ranksArray,
        ]);
    }

    public function showMembers(Alliance $alliance): View
    {
        $this->administrationService->checkSession();
        $this->administrationService->authorization(__CLASS__);

        $ranks = new Ranks($alliance->alliance_ranks);
        $members = $this->buildMembersViewData($alliance->alliance_id, $ranks);
        $rankOptions = $this->buildRankOptions($ranks->getAllRanksAsArray());

        return view('admin.alliances_members', [
            'alliance' => $alliance,
            'members' => $members,
            'rank_options' => $rankOptions,
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

    /**
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function updateRanks(AllianceRankRequest $request, Alliance $alliance): RedirectResponse
    {
        $this->administrationService->checkSession();
        $this->administrationService->authorization(__CLASS__);

        $ranks = new Ranks($alliance->alliance_ranks);

        if ($request->filled('create_rank')) {
            $this->createRank($request, $ranks);
        } elseif ($request->has('save_ranks')) {
            $this->saveRankPermissions($request, $ranks);
        } elseif ($request->has('delete_ranks')) {
            $this->deleteRanks($request, $ranks);
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
        $this->administrationService->checkSession();
        $this->administrationService->authorization(__CLASS__);

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
        $this->administrationService->checkSession();
        $this->administrationService->authorization(__CLASS__);

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

    private function createRank(AllianceRankRequest $request, Ranks $ranks): void
    {
        $ranks->addNew($request->string('rank_name')->toString());
        session()->flash('success', __('admin/alliances.al_rank_added'));
    }

    /**
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    private function saveRankPermissions(AllianceRankRequest $request, Ranks $ranks): void
    {
        /** @var array<int, int|string> $ids */
        $ids = (array) $request->input('id', []);

        $availableRanks = [
            AllianceRanks::DELETE => AllianceRanks::DELETE,
            AllianceRanks::KICK => AllianceRanks::KICK,
            AllianceRanks::APPLICATIONS => AllianceRanks::APPLICATIONS,
            AllianceRanks::VIEW_MEMBER_LIST => AllianceRanks::VIEW_MEMBER_LIST,
            AllianceRanks::APPLICATION_MANAGEMENT => AllianceRanks::APPLICATION_MANAGEMENT,
            AllianceRanks::ADMINISTRATION => AllianceRanks::ADMINISTRATION,
            AllianceRanks::ONLINE_STATUS => AllianceRanks::ONLINE_STATUS,
            AllianceRanks::SEND_CIRCULAR => AllianceRanks::SEND_CIRCULAR,
            AllianceRanks::RIGHT_HAND => AllianceRanks::RIGHT_HAND,
        ];

        foreach ($ids as $id) {
            $id = (int) $id;
            $rights = [];

            foreach ($availableRanks as $rank) {
                $rights[$rank] = $request->has('u' . $id . 'r' . $rank)
                    ? SwitchInt::on
                    : SwitchInt::off;
            }

            $ranks->editRankById($id, $rights);

            $nameKey = 'rank_name_' . $id;
            $rankName = trim($request->string($nameKey)->toString());
            if ($rankName !== '') {
                $ranks->editRankNameById($id, $rankName);
            }
        }

        session()->flash('success', __('admin/alliances.al_rank_saved'));
    }

    private function deleteRanks(AllianceRankRequest $request, Ranks $ranks): void
    {
        /** @var array<int, int|string> $toDelete */
        $toDelete = (array) $request->input('delete_message', []);

        $ids = array_map('intval', $toDelete);
        $ids = array_filter($ids, fn (int $id) => $id >= 2);
        rsort($ids);

        foreach ($ids as $rankId) {
            $ranks->deleteRankById($rankId);
        }

        session()->flash('success', __('admin/alliances.al_rank_removed'));
    }

    /** @SuppressWarnings(PHPMD.StaticAccess) */
    private function createAlliance(string $allianceName, string $allianceTag, int $allianceFounder): void
    {
        try {
            DB::transaction(function () use ($allianceName, $allianceTag, $allianceFounder) {
                $time = time();
                $rightsString = '[{"rank":"' . (string) __('admin/alliances.al_create_founder_rank') . '","rights":{"1":1,"2":1,"3":1,"4":1,"5":1,"6":1,"7":1,"8":1,"9":1}},{"rank":"Newcomer","rights":{"1":0,"2":0,"3":0,"4":0,"5":0,"6":0,"7":0,"8":0,"9":0}}]'; // @phpstan-ignore cast.string

                $allianceId = DB::table('alliance')->insertGetId([
                    'alliance_name' => $allianceName,
                    'alliance_tag' => $allianceTag,
                    'alliance_owner' => $allianceFounder,
                    'alliance_register_time' => $time,
                    'alliance_ranks' => $rightsString,
                ]);

                DB::table('alliance_statistics')->insert(['alliance_statistic_alliance_id' => $allianceId]);

                /** @phpstan-ignore staticMethod.notFound */
                User::where('id', $allianceFounder)->update([
                    'ally_id' => $allianceId,
                    'ally_register_time' => $time,
                ]);
            });
        } catch (\Exception $e) {
            // transaction rolled back automatically
        }
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
        $dateFormat = (string) (Options::getInstance()->get('date_format_extended') ?? 'Y-m-d H:i:s');

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
                    'rank_id' => (int) $row->ally_rank_id,
                ];
            })
            ->all();
    }

    /**
     * @param array<int, array<string, mixed>> $rawRanks
     *
     * @return array<int, array{id: int, name: string}>
     */
    private function buildRankOptions(array $rawRanks): array
    {
        $result = [];
        foreach ($rawRanks as $index => $rank) {
            /** @var string $name */
            $name = $rank['rank'];
            $result[] = ['id' => $index, 'name' => $name];
        }

        return $result;
    }
}
