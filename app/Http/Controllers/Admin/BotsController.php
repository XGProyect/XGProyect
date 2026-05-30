<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\BotsRequest;
use App\Services\Admin\BotService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller as BaseController;

/**
 * Bots module.
 *
 * Currently lets an admin populate the universe with bot planets. The module
 * is designed to grow into full AI players that act on their own; the
 * generation logic lives in {@see BotService} to keep that behaviour layer
 * separate from this controller.
 */
class BotsController extends BaseController
{
    public function __construct(
        private readonly BotService $bots,
    ) {
    }

    public function index(): View
    {
        return view('admin.bots', [
            'max_galaxy' => MAX_GALAXY_IN_WORLD,
            'max_system' => MAX_SYSTEM_IN_GALAXY,
            'max_planet' => MAX_PLANET_IN_SYSTEM,
        ]);
    }

    public function generate(BotsRequest $request): RedirectResponse
    {
        $amount = $request->integer('amount');
        $galaxyFrom = max(1, $request->filled('galaxy_from') ? $request->integer('galaxy_from') : 1);
        $galaxyTo = min(MAX_GALAXY_IN_WORLD, $request->filled('galaxy_to') ? $request->integer('galaxy_to') : MAX_GALAXY_IN_WORLD);

        if ($galaxyTo < $galaxyFrom) {
            $galaxyTo = $galaxyFrom;
        }

        $created = $this->bots->createBots($amount, $galaxyFrom, $galaxyTo);

        if ($created === 0) {
            session()->flash('danger', __('admin/bots.bo_none'));

            return redirect()->route('admin.bots');
        }

        if ($created < $amount) {
            session()->flash('warning', __('admin/bots.bo_partial', ['count' => $created, 'requested' => $amount]));

            return redirect()->route('admin.bots');
        }

        session()->flash('success', __('admin/bots.bo_created', ['count' => $created]));

        return redirect()->route('admin.bots');
    }
}
