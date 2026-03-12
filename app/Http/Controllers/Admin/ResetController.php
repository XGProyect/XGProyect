<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\ResetRequest;
use App\Services\Admin\ResetService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\View\View;

class ResetController extends BaseController
{
    public function __construct(private readonly ResetService $resetService)
    {
    }

    public function index(): View
    {
        return view('admin.reset');
    }

    public function reset(ResetRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if (isset($data['resetall'])) {
            $this->resetService->resetAll();

            return redirect()->route('admin.reset')->with('success', __('admin/reset.re_reset_excess'));
        }

        isset($data['defenses']) && $this->resetService->resetDefenses();
        isset($data['ships']) && $this->resetService->resetShips();
        isset($data['h_d']) && $this->resetService->resetShipyardQueues();
        isset($data['edif_p']) && $this->resetService->resetPlanetBuildings();
        isset($data['edif_l']) && $this->resetService->resetMoonBuildings();
        isset($data['edif']) && $this->resetService->resetBuildingsQueues();
        isset($data['inves']) && $this->resetService->resetResearch();
        isset($data['inves_c']) && $this->resetService->resetResearchQueues();
        isset($data['ofis']) && $this->resetService->resetOfficiers();
        isset($data['dark']) && $this->resetService->resetDarkMatter();
        isset($data['resources']) && $this->resetService->resetResources();
        isset($data['notes']) && $this->resetService->resetNotes();
        isset($data['rw']) && $this->resetService->resetReports();
        isset($data['friends']) && $this->resetService->resetFriends();
        isset($data['alliances']) && $this->resetService->resetAlliances();
        isset($data['fleets']) && $this->resetService->resetFleets();
        isset($data['banneds']) && $this->resetService->resetBanned();
        isset($data['messages']) && $this->resetService->resetMessages();
        isset($data['statpoints']) && $this->resetService->resetStatistics();
        isset($data['moons']) && $this->resetService->resetMoons();

        return redirect()->route('admin.reset')->with('success', __('admin/reset.re_reset_excess'));
    }
}
