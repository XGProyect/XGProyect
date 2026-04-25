<?php

declare(strict_types=1);

namespace Xgp\App\Http\Controllers\Game;

use App\Enums\Module;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;
use Xgp\App\Core\Concerns\PreparesLegacySql;
use Xgp\App\Core\Template;
use Xgp\App\Libraries\Combatreport\Report;
use Xgp\App\Libraries\Functions;
use Xgp\App\Libraries\Users;

/**
 * @SuppressWarnings("PHPMD.StaticAccess")
 */
class CombatreportController extends BaseController
{
    use PreparesLegacySql;

    private array $user = [];
    private ?Report $report = null;

    public function __invoke(): void
    {
        Functions::moduleMessage(Functions::isModuleAccesible(Module::CombatReports));

        $this->user = Users::getInstance()->getUserData();

        $this->setUpReport();

        $this->runAction();

        Template::legacyView(
            'combatreport.view',
            [
                'report' => $this->getReportTemplate(),
            ]
        );
    }

    private function setUpReport(): void
    {
        $row = DB::selectOne($this->prepareSql('SELECT * FROM `' . REPORTS . '` WHERE `report_rid` = ?'), [filter_input(INPUT_GET, 'report')]);
        $this->report = new Report(
            [$row !== null ? (array) $row : null],
            $this->user['id']
        );
    }

    private function runAction(): void
    {
        $owners = $this->report->getFirstReportOwnersAsArray();

        if (!in_array($this->user['id'], $owners)) {
            Functions::message(__('game/combatreport.cr_no_access'), '', 0, false, false, false);
        }
    }

    /**
     * Get report template based on different conditions
     *
     * @return string The template
     */
    private function getReportTemplate()
    {
        // any other case
        $content = stripslashes($this->report->getAllReports()[0]->getReportContent());
        /*
        foreach (__('game/combatreport.cr_tech_short') as $id => $s_name) {
        $search = [$id];
        $replace = [$s_name];
        $content = str_replace($search, $replace, $content);
        }

        $no_fleet = Template::render('combatreport/combatreport_no_fleet_view');
        $destroyed = Template::render('combatreport/combatreport_destroyed_view');

        $search = [$no_fleet];
        $replace = [$destroyed];
        $content = str_replace($search, $replace, $content);*/

        return $content;
    }
}
