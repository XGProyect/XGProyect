<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Services\AdministrationService;
use App\Services\SettingsService;
use Illuminate\Routing\Controller as BaseController;
use Xgp\App\Core\Template;
use Xgp\App\Libraries\FleetsLib;
use Xgp\App\Libraries\FormatLib as Format;
use Xgp\App\Libraries\TimingLibrary as Timing;
use Xgp\App\Models\Adm\Fleets;

class FleetsController extends BaseController
{
    private Fleets $fleetsModel;
    private AdministrationService $administrationService;

    public function __construct()
    {
        $this->administrationService = new AdministrationService(
            new SettingsService()
        );
    }

    public function __invoke(): void
    {
        $this->administrationService->checkSession();
        $this->administrationService->authorization(__CLASS__);

        $this->fleetsModel = new Fleets();

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
        $this->fleetsModel->restartFleetById($fleetId);
    }

    private function doEndAction(int $fleetId): void
    {
        $this->fleetsModel->endFleetById($fleetId);
    }

    private function doReturnAction(int $fleetId): void
    {
        $this->fleetsModel->returnFleetById($fleetId);
    }

    private function doDeleteAction(int $fleetId): void
    {
        $this->fleetsModel->deleteFleetById($fleetId);
    }

    private function buildFleetMovementsBlock(): array
    {
        $fleets = $this->fleetsModel->getAllFleets();
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
