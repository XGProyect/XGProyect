<?php

namespace Xgp\App\Http\Controllers\Game;

use Illuminate\Routing\Controller as BaseController;
use Xgp\App\Core\Template;
use Xgp\App\Libraries\Combatreport\Report;
use Xgp\App\Libraries\Functions;
use Xgp\App\Libraries\Users;
use Xgp\App\Models\Game\Combatreport;

class CombatreportController extends BaseController
{
    public const MODULE_ID = 23;

    private array $user = [];
    private ?Report $report = null;
    private Combatreport $combatreportModel;

    public function __invoke()
    {
        Functions::moduleMessage(Functions::isModuleAccesible(self::MODULE_ID));

        $this->user = Users::getInstance()->getUserData();
        $this->combatreportModel = new Combatreport();

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
        $this->report = new Report(
            [$this->combatreportModel->getReportById(filter_input(INPUT_GET, 'report'))],
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

        $no_fleet = Template::getInstance()->render('combatreport/combatreport_no_fleet_view');
        $destroyed = Template::getInstance()->render('combatreport/combatreport_destroyed_view');

        $search = [$no_fleet];
        $replace = [$destroyed];
        $content = str_replace($search, $replace, $content);*/

        return $content;
    }
}
