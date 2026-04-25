<?php

declare(strict_types=1);

namespace App\Http\Controllers\Game;

use App\Enums\Module;
use App\Models\Reports;
use App\Models\User;
use App\Services\SettingsService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;
use Xgp\App\Libraries\Functions;

class CombatreportController extends BaseController
{
    public function __construct(
        private SettingsService $settings,
    ) {
    }

    /**
     * @SuppressWarnings("PHPMD.StaticAccess")
     */
    public function __invoke(Request $request): View
    {
        Functions::moduleMessage(Functions::isModuleAccesible(Module::CombatReports));

        /** @var User $user */
        $user = Auth::user();
        $report = Reports::where('report_rid', $request->query('report'))->firstOrFail();

        if (!in_array((string) $user->id, explode(',', $report->report_owners))) {
            abort(403);
        }

        $content = stripslashes($report->report_content);

        // legacy stuff (just keeping it around for reference.)
        // foreach (__('game/combatreport.cr_tech_short') as $id => $s_name) {
        //     $search = [$id];
        //     $replace = [$s_name];
        //     $content = str_replace($search, $replace, $content);
        // }

        // $no_fleet = Template::render('combatreport/combatreport_no_fleet_view');
        // $destroyed = Template::render('combatreport/combatreport_destroyed_view');

        // $search = [$no_fleet];
        // $replace = [$destroyed];
        // $content = str_replace($search, $replace, $content);

        return view('combatreport.view', [
            'gameTitle' => $this->settings->getString('game_name'),
            'report' => $content,
        ]);
    }
}
