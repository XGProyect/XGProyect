<?php

namespace Xgp\App\Http\Controllers\Game;

use Illuminate\Routing\Controller as BaseController;
use Xgp\App\Core\Template;
use Xgp\App\Libraries\Functions;
use Xgp\App\Libraries\TimingLibrary as Timing;
use Xgp\App\Libraries\Users;
use Xgp\App\Models\Game\Changelog;

class ChangelogController extends BaseController
{
    public const MODULE_ID = 0;

    private Changelog $changelogModel;

    public function __invoke(): void
    {
        Users::checkSession();

        Functions::moduleMessage(Functions::isModuleAccesible(self::MODULE_ID));

        $this->changelogModel = new Changelog();

        $changes = [];
        $entries = $this->changelogModel->getAllChangelogEntries();

        foreach ($entries as $entry) {
            $changes[] = [
                'version_number' => $entry['changelog_version'],
                'description' => nl2br(
                    Timing::formatShortDate($entry['changelog_date']) . '<br>' . $entry['changelog_description']
                ),
            ];
        }

        Template::getInstance()->view(
            'changelog.view',
            [
                'changes' => $changes
            ]
        );
    }
}
