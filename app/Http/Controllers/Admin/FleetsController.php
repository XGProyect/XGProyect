<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Models\Fleets;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;
use Xgp\App\Core\Template;
use Xgp\App\Libraries\FleetsLib;
use Xgp\App\Libraries\FormatLib as Format;
use Xgp\App\Libraries\TimingLibrary as Timing;

class FleetsController extends BaseController
{
    public function __invoke(): void
    {
        $this->runAction();

        Template::legacyView(
            'admin.fleets',
            $this->buildFleetMovementsBlock()
        );
    }

    private function runAction(): void
    {
        $action = filter_input(INPUT_GET, 'action');
        $fleetId = filter_input(INPUT_GET, 'fleetId', FILTER_VALIDATE_INT);

        if (in_array($action, ['restart', 'end', 'return', 'delete']) && $fleetId) {
            $this->{'do' . ucfirst($action) . 'Action'}($fleetId);
        }
    }

    private function doRestartAction(int $fleetId): void
    {
        DB::transaction(function () use ($fleetId) {
            $times = DB::table('fleets')
                ->selectRaw('(fleet_end_time - fleet_start_time) AS mission_time')
                ->where('fleet_id', $fleetId)
                ->first();

            if ($times) {
                $baseTime = time();
                $startTime = $baseTime + $times->mission_time;
                $endTime = $baseTime + $times->mission_time * 2;

                Fleets::where('fleet_id', $fleetId)->update([
                    'fleet_start_time' => $startTime,
                    'fleet_end_stay' => 0,
                    'fleet_end_time' => $endTime,
                ]);
            }
        });
    }

    private function doEndAction(int $fleetId): void
    {
        Fleets::where('fleet_id', $fleetId)->update([
            'fleet_start_time' => time(),
            'fleet_end_time' => time(),
            'fleet_end_stay' => 0,
        ]);
    }

    private function doReturnAction(int $fleetId): void
    {
        $fleet = Fleets::where('fleet_id', $fleetId)->first();

        if ($fleet) {
            Fleets::where('fleet_id', $fleetId)->update([
                'fleet_start_time' => time(),
                'fleet_end_stay' => 0,
                'fleet_end_time' => time() * 2 - $fleet->fleet_creation,
                'fleet_target_owner' => $fleet->fleet_owner,
                'fleet_mess' => 1,
            ]);
        }
    }

    private function doDeleteAction(int $fleetId): void
    {
        Fleets::where('fleet_id', $fleetId)->delete();
    }

    private function buildFleetMovementsBlock(): array
    {
        $prefix = DB::getTablePrefix();

        $fleets = DB::table(DB::raw("`{$prefix}fleets` AS f"))
            ->selectRaw("f.*, (SELECT name FROM {$prefix}users WHERE id = f.fleet_owner) AS fleet_username, (SELECT name FROM {$prefix}users WHERE id = f.fleet_target_owner) AS target_username")
            ->orderByRaw('f.fleet_end_time ASC')
            ->get()
            ->map(fn ($row) => (array) $row)
            ->toArray();

        $fleetMovements = [];

        foreach ($fleets as $fleet) {
            $fleetMovements[] = array_merge(
                $this->buildMissionBlock($fleet),
                $this->buildAmountBlock($fleet),
                $this->buildBeginningBlock($fleet),
                $this->buildDepartureBlock($fleet),
                $this->buildObjectiveBlock($fleet),
                $this->buildArrivalBlock($fleet),
                $this->buildReturnBlock($fleet),
                $this->buildActionsBlock($fleet)
            );
        }

        return ['fleetMovements' => $fleetMovements];
    }

    private function buildMissionBlock(array $fleet): array
    {
        return [
            'mission' => __('admin/fleets.ff_type_mission')[$fleet['fleet_mission']] . ' ' . (FleetsLib::isFleetReturning($fleet['fleet_mess']) ? __('admin/fleets.ff_r') : __('admin/fleets.ff_a')),
            'metal' => Format::prettyNumber((int) $fleet['fleet_resource_metal']),
            'crystal' => Format::prettyNumber((int) $fleet['fleet_resource_crystal']),
            'deuterium' => Format::prettyNumber((int) $fleet['fleet_resource_deuterium']),
        ];
    }

    private function buildAmountBlock(array $fleet): array
    {
        $pop_up = [];

        foreach (FleetsLib::getFleetShipsArray($fleet['fleet_array']) as $ship => $amount) {
            $pop_up[] = __('admin/objects.objects')[$ship] . ': ' . Format::prettyNumber((int) $amount);
        }

        return [
            'amount' => __('admin/fleets.ff_ships'),
            'amount_content' => join('<br>', $pop_up),
        ];
    }

    private function buildBeginningBlock(array $fleet): array
    {
        return [
            'beginning' => Format::prettyCoords(
                (int) $fleet['fleet_start_galaxy'],
                (int) $fleet['fleet_start_system'],
                (int) $fleet['fleet_start_planet']
            ),
        ];
    }

    private function buildDepartureBlock(array $fleet): array
    {
        return ['departure' => Timing::formatExtendedDate($fleet['fleet_creation'])];
    }

    private function buildObjectiveBlock(array $fleet): array
    {
        return [
            'objective' => Format::prettyCoords(
                (int) $fleet['fleet_end_galaxy'],
                (int) $fleet['fleet_end_system'],
                (int) $fleet['fleet_end_planet']
            ),
        ];
    }

    private function buildArrivalBlock(array $fleet): array
    {
        return ['arrival' => Timing::formatExtendedDate($fleet['fleet_start_time'])];
    }

    private function buildReturnBlock(array $fleet): array
    {
        return ['return' => Timing::formatExtendedDate($fleet['fleet_end_time'])];
    }

    private function buildActionsBlock(array $fleet): array
    {
        return ['fleet_id' => $fleet['fleet_id']];
    }
}
