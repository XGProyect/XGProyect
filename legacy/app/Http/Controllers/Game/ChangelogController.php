<?php

namespace Xgp\App\Http\Controllers\Game;

use Illuminate\Routing\Controller as BaseController;
use Xgp\App\Libraries\Functions;
use Xgp\App\Libraries\TimingLibrary as Timing;
use Xgp\App\Libraries\Users;
use Xgp\App\Models\Game\Changelog;

class ChangelogController extends BaseController
{
    public const MODULE_ID = 0;

    private Changelog $changelogModel;

    public function __construct()
    {
        parent::__construct();

        Users::checkSession();

        $this->changelogModel = new Changelog();
    }

    public function __invoke(): void
    {
        // Check module access
        Functions::moduleMessage(Functions::isModuleAccesible(self::MODULE_ID));

        // build the page
        $this->buildPage();
    }

    private function buildPage(): void
    {
        $changes = [];
        $entries = $this->changelogModel->getAllChangelogEntries();

        if ($entries) {
            foreach ($entries as $entry) {
                $changes[] = [
                    'version_number' => $entry['changelog_version'],
                    'description' => nl2br(
                        Timing::formatShortDate($entry['changelog_date']) . '<br>' . $entry['changelog_description']
                    ),
                ];
            }
        }

        $this->page->display(
            Template::getInstance()->set('game/changelog_view', array_merge(
                $this->langs->language,
                ['list_of_changes' => $changes]
            ))
        );
    }
}
