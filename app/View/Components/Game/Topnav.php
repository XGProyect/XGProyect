<?php

declare(strict_types=1);

namespace App\View\Components\Game;

use App\Models\Planets;
use App\Models\User;
use App\Services\FormatService;
use App\Services\Game\Formulas\OfficerService;
use App\Services\Game\Formulas\ProductionService;
use App\Services\TimingService;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Xgp\App\Core\Enumerators\PlanetTypesEnumerator;

class Topnav extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(
        private ProductionService $productionService,
        private TimingService $timingService,
        private FormatService $formatService,
        private OfficerService $officerService
    ) {
        //
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View | Htmlable | \Closure | string
    {
        $rawId = session('user_id');
        $user = User::findOrFail(is_int($rawId) ? $rawId : 0);
        $planet = Planets::where([
            ['planet_user_id', '=', $user->id],
            ['planet_id', '=', $user->current_planet],
            ['planet_destroyed', '=', 0],
        ])->firstOrFail();

        $metal = $this->formatService->prettyNumber((int) $planet->planet_metal);
        $crystal = $this->formatService->prettyNumber((int) $planet->planet_crystal);
        $deuterium = $this->formatService->prettyNumber((int) $planet->planet_deuterium);
        $darkmatter = $this->formatService->prettyNumber((int) $user->premium->premium_dark_matter);
        $energy = $this->formatService->prettyNumber(
            $planet->planet_energy_max + $planet->planet_energy_used
        ) . ' / ' . $this->formatService->prettyNumber($planet->planet_energy_max);

        if ($planet->planet_metal >= $this->productionService->maxStorable((int) ($planet->building_metal_store ?? 0))) {
            $metal = $this->formatService->colorRed($metal);
        }

        if ($planet->planet_crystal >= $this->productionService->maxStorable((int) ($planet->building_crystal_store ?? 0))) {
            $crystal = $this->formatService->colorRed($crystal);
        }

        if ($planet->planet_deuterium >= $this->productionService->maxStorable((int) ($planet->building_deuterium_tank ?? 0))) {
            $deuterium = $this->formatService->colorRed($deuterium);
        }

        if (($planet->planet_energy_max + $planet->planet_energy_used) < 0) {
            $energy = $this->formatService->colorRed($energy);
        }

        return view(
            'components.game.topnav',
            array_merge(
                [
                    'planetImage' => $planet->planet_image,
                    'planetList' => $this->buildPlanetList($user),
                    'resources' => ['metal', 'crystal', 'deuterium', 'darkmatter', 'energy'],
                    'resourcesAmount' => [$metal, $crystal, $deuterium, $darkmatter, $energy],
                    'officers' => $this->getOfficersDetails($user),
                ],
                $this->buildNotices($user)
            )
        );
    }

    /**
     * @return array<int, array{selected: string, value: string, text: string}>
     */
    private function buildPlanetList(User $user): array
    {
        $page = (string) request()->string('page');
        $technology = (string) request()->string('technology');
        $mode = (string) request()->string('mode');

        $allUserPlanets = Planets::where([
            ['planet_user_id', '=', $user->id],
            ['planet_destroyed', '=', 0],
        ]);

        switch ($user->preferences->preference_planet_sort) {
            case 0: // emergence
            default:
                $orderBy = ['planet_id'];
                break;
            case 1: // coordinates
                $orderBy = ['planet_galaxy', 'planet_system', 'planet_planet', 'planet_type'];
                break;
            case 2: // alphabet
                $orderBy = ['planet_name'];
                break;
            case 3: // size
                $orderBy = ['planet_diameter'];
                break;
            case 4: // used_fields
                $orderBy = ['planet_field_current'];
                break;
        }

        foreach ($orderBy as $order) {
            if ($user->preferences->preference_planet_sort_sequence) {
                $allUserPlanets->orderByDesc($order);
            } else {
                $allUserPlanets->orderBy($order);
            }
        }

        $planetList = [];

        foreach ($allUserPlanets->get()->getIterator() as $planet) {
            $link = sprintf(
                'game.php?page=%s&technology=%d&cp=%s&mode=%s&re=0',
                $page,
                $technology,
                $planet->planet_id,
                $mode
            );

            $text =
                ($planet->planet_type !== PlanetTypesEnumerator::MOON ? $planet->planet_name : $planet->planet_name . ' (' . __('game/global.moon') . ')') .
                ' ' .
                $this->formatService->formatCoords($planet->planet_galaxy, $planet->planet_system, $planet->planet_planet);

            $planetList[] = [
                'selected' => $planet->planet_id === $user->current_planet ? 'selected="selected"' : '',
                'value' => $link,
                'text' => $text,
            ];
        }
        return $planetList;
    }

    /**
     * @return array<int, mixed>
     */
    private function getOfficersDetails(User $user): array
    {
        $officers = [];

        foreach (['commander', 'admiral', 'engineer', 'geologist', 'technocrat'] as $officer) {
            $status = __('game/officier.of_add_premium_officier_' . $officer);
            $expires = $user->premium->{'premium_officier_' . $officer};
            $isActive = false;

            if ($this->officerService->isOfficerActive((int) $expires, time())) {
                $status = (string) $this->officerService->getDaysLeft((int) $expires, time());
                $isActive = true;
            }

            $officers[] = [
                'name' => __('game/officier.of_hire_' . $officer),
                'status' => $status,
                'icon' => $officer . '_icon' . ($isActive ? '' : '_un'),
            ];
        }

        return $officers;
    }

    /**
     * @return array<string, mixed>
     */
    private function buildNotices(User $user): array
    {
        $notice = ['color' => '', 'message' => ''];

        if ($user->preferences->preference_vacation_mode > 0) {
            $notice = [
                'color' => '#1df0f0',
                'message' => __('game/navigation.tn_vacation_mode') . $this->timingService->formatExtendedDate($user->preferences->preference_vacation_mode),
            ];
        }

        if ($user->preferences->preference_delete_mode > 0) {
            $notice = [
                'color' => '#ff0000',
                'message' => __('game/navigation.tn_delete_mode') . $this->timingService->formatExtendedDate($user->preferences->preference_delete_mode + (60 * 60 * 24 * 7)),
            ];
        }

        return $notice;
    }
}
