<?php

declare(strict_types=1);

namespace App\Services\Admin;

use App\Http\Requests\Admin\AllianceRankRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Xgp\App\Core\Enumerators\AllianceRanksEnumerator as AllianceRanks;
use Xgp\App\Core\Enumerators\SwitchIntEnumerator as SwitchInt;
use Xgp\App\Core\Options;
use Xgp\App\Libraries\Alliance\Ranks;

/**
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class AlliancesService
{
    public function createAlliance(string $allianceName, string $allianceTag, int $allianceFounder): void
    {
        try {
            DB::transaction(function () use ($allianceName, $allianceTag, $allianceFounder): void {
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

    public function loadAllianceWithStats(int $id): \stdClass
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
     * @param array<int, array<string, mixed>> $rawRanks
     *
     * @return array<int, array<string, mixed>>
     */
    public function buildRanksViewData(array $rawRanks): array
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
    public function buildMembersViewData(int $allianceId, Ranks $ranks): array
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
    public function buildRankOptions(array $rawRanks): array
    {
        $result = [];
        foreach ($rawRanks as $index => $rank) {
            /** @var string $name */
            $name = $rank['rank'];
            $result[] = ['id' => $index, 'name' => $name];
        }

        return $result;
    }

    public function createRank(AllianceRankRequest $request, Ranks $ranks): void
    {
        $ranks->addNew($request->string('rank_name')->toString());
        session()->flash('success', __('admin/alliances.al_rank_added'));
    }

    /**
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function saveRankPermissions(AllianceRankRequest $request, Ranks $ranks): void
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

    public function deleteRanks(AllianceRankRequest $request, Ranks $ranks): void
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
}
