<?php

declare(strict_types=1);

namespace App\View\Components\Game;

use App\Models\Planets;
use App\Models\User;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Xgp\App\Core\Enumerators\PlanetTypesEnumerator;
use Xgp\App\Libraries\FormatLib;
use Xgp\App\Libraries\OfficiersLib;
use Xgp\App\Libraries\ProductionLib;
use Xgp\App\Libraries\TimingLibrary;

class Topnav extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View | Closure | string
    {
        $user = User::find(session('user_id'));
        $planet = Planets::where([
            ['planet_user_id', '=', $user->id],
            ['planet_id', '=', $user->current_planet],
            ['planet_destroyed', '=', 0],
        ])->firstOrFail();

        $metal = FormatLib::prettyNumber((int) $planet->planet_metal);
        $crystal = FormatLib::prettyNumber((int) $planet->planet_crystal);
        $deuterium = FormatLib::prettyNumber((int) $planet->planet_deuterium);
        $darkmatter = FormatLib::prettyNumber((int) $user->premium->premium_dark_matter);
        $energy = FormatLib::prettyNumber(
            $planet->planet_energy_max + $planet->planet_energy_used
        ) . ' / ' . FormatLib::prettyNumber($planet->planet_energy_max);

        if ($planet->planet_metal >= ProductionLib::maxStorable($planet->building_metal_store)) {
            $metal = FormatLib::colorRed($metal);
        }

        if ($planet->planet_crystal >= ProductionLib::maxStorable($planet->building_crystal_store)) {
            $crystal = FormatLib::colorRed($crystal);
        }

        if ($planet->planet_deuterium >= ProductionLib::maxStorable($planet->building_deuterium_tank)) {
            $deuterium = FormatLib::colorRed($deuterium);
        }

        if (($planet->planet_energy_max + $planet->planet_energy_used) < 0) {
            $energy = FormatLib::colorRed($energy);
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

    private function buildPlanetList(User $user): array
    {
        $page = isset($_GET['page']) ? $_GET['page'] : '';
        $technology = isset($_GET['technology']) ? $_GET['technology'] : '';
        $mode = isset($_GET['mode']) ? $_GET['mode'] : '';

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
            if ($user->preferences->preference_planet_sort_sequence === 1) {
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
                FormatLib::formatCoords($planet->planet_galaxy, $planet->planet_system, $planet->planet_planet);

            $planetList[] = [
                'selected' => $planet->planet_id === $user->current_planet ? 'selected="selected"' : '',
                'value' => $link,
                'text' => $text,
            ];
        }
        return $planetList;
    }

    private function getOfficersDetails(User $user): array
    {
        $officers = [];

        foreach (['commander', 'admiral', 'engineer', 'geologist', 'technocrat'] as $officer) {
            $status = __('game/officier.of_add_premium_officier_' . $officer);
            $expires = $user->premium->{'premium_officier_' . $officer};
            $isActive = false;

            if (OfficiersLib::isOfficierActive($expires)) {
                $status = OfficiersLib::getOfficierTimeLeft($expires);
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

    private function buildNotices(User $user): array
    {
        $notice = ['color' => '', 'message' => ''];

        if ($user->preferences->preference_vacation_mode > 0) {
            $notice = [
                'color' => '#1df0f0',
                'message' => __('game/navigation.tn_vacation_mode') . TimingLibrary::formatExtendedDate($user->preferences->preference_vacation_mode),
            ];
        }

        if ($user->preferences->preference_delete_mode > 0) {
            $notice = [
                'color' => '#ff0000',
                'message' => __('game/navigation.tn_delete_mode') . TimingLibrary::formatExtendedDate($user->preferences->preference_delete_mode + (60 * 60 * 24 * 7)),
            ];
        }

        return $notice;
    }
}
