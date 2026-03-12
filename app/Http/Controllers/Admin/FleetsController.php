<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Models\Fleets;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;
use Xgp\App\Libraries\FleetsLib;
use Xgp\App\Libraries\FormatLib as Format;
use Xgp\App\Libraries\TimingLibrary as Timing;

class FleetsController extends BaseController
{
    public function index(): View
    {
        return view('admin.fleets', [
            'fleetMovements' => $this->buildFleetMovements(),
        ]);
    }

    public function restart(Fleets $fleet): RedirectResponse
    {
        DB::transaction(function () use ($fleet) {
            $missionTime = $fleet->fleet_end_time - $fleet->fleet_start_time;
            $base = time();

            $fleet->update([
                'fleet_start_time' => $base + $missionTime,
                'fleet_end_stay' => 0,
                'fleet_end_time' => $base + $missionTime * 2,
            ]);
        });

        return redirect()->route('admin.fleets');
    }

    public function end(Fleets $fleet): RedirectResponse
    {
        $fleet->update([
            'fleet_start_time' => time(),
            'fleet_end_time' => time(),
            'fleet_end_stay' => 0,
        ]);

        return redirect()->route('admin.fleets');
    }

    public function returnFleet(Fleets $fleet): RedirectResponse
    {
        $fleet->update([
            'fleet_start_time' => time(),
            'fleet_end_stay' => 0,
            'fleet_end_time' => time() * 2 - $fleet->fleet_creation,
            'fleet_target_owner' => $fleet->fleet_owner,
            'fleet_mess' => 1,
        ]);

        return redirect()->route('admin.fleets');
    }

    public function destroy(Fleets $fleet): RedirectResponse
    {
        $fleet->delete();

        return redirect()->route('admin.fleets');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildFleetMovements(): array
    {
        return Fleets::orderBy('fleet_end_time')
            ->get()
            ->map(function (Fleets $fleet): array {
                $shipLines = collect(FleetsLib::getFleetShipsArray($fleet->fleet_array))
                    ->map(fn ($amount, $ship) => __('admin/objects.objects')[$ship] . ': ' . Format::prettyNumber((int) $amount))
                    ->values()
                    ->implode('<br>');

                return [
                    'fleet_id' => $fleet->fleet_id,
                    'mission' => __('admin/fleets.ff_type_mission')[$fleet->fleet_mission]
                                        . ' '
                                        . (FleetsLib::isFleetReturning((int) $fleet->fleet_mess)
                                            ? __('admin/fleets.ff_r')
                                            : __('admin/fleets.ff_a')),
                    'resources_content' => __('admin/fleets.ff_metal') . ': ' . Format::prettyNumber((int) $fleet->fleet_resource_metal)
                        . '<br>' . __('admin/fleets.ff_crystal') . ': ' . Format::prettyNumber((int) $fleet->fleet_resource_crystal)
                        . '<br>' . __('admin/fleets.ff_deuterium') . ': ' . Format::prettyNumber((int) $fleet->fleet_resource_deuterium),
                    'amount' => __('admin/fleets.ff_ships'),
                    'amount_content' => $shipLines,
                    'beginning' => Format::prettyCoords($fleet->fleet_start_galaxy, $fleet->fleet_start_system, $fleet->fleet_start_planet),
                    'departure' => Timing::formatExtendedDate((string) $fleet->fleet_creation),
                    'objective' => Format::prettyCoords($fleet->fleet_end_galaxy, $fleet->fleet_end_system, $fleet->fleet_end_planet),
                    'arrival' => Timing::formatExtendedDate((string) $fleet->fleet_start_time),
                    'return' => Timing::formatExtendedDate((string) $fleet->fleet_end_time),
                ];
            })
            ->all();
    }
}
