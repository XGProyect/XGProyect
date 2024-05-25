<?php

declare(strict_types=1);

namespace Xgp\App\Http\Controllers\Adm;

use App\Models\Changelog;
use App\Models\Languages;
use App\Services\AdministrationService;
use App\Services\SettingsService;
use DateTime;
use Exception;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;
use Xgp\App\Core\Template;
use Xgp\App\Libraries\Functions;

class ChangelogController extends BaseController
{
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

        $this->runAction();
        $this->getAlertMessage();

        Template::legacyView(
            'admin.changelog',
            [
                'changelog' => $this->buildListOfEntries(),
            ]
        );
    }

    private function runAction(): void
    {
        // route to the right page
        $subPage = filter_input(INPUT_GET, 'action');
        $changelogId = filter_input(INPUT_GET, 'changelogId', FILTER_VALIDATE_INT);

        if ($subPage === 'add') {
            $this->addAction();
        }

        if ($subPage === 'edit' && isset($changelogId)) {
            $this->editAction((int) $changelogId);
        }

        if ($subPage === 'delete' && isset($changelogId)) {
            $this->deleteAction((int) $changelogId);
        }
    }

    private function buildListOfEntries(): array
    {
        $entries = Changelog::orderBy('changelog_date', 'DESC')
            ->orderBy('changelog_version', 'DESC')
            ->join('languages', 'languages.id', '=', 'changelog.changelog_lang_id')
            ->get();

        $entriesList = [];

        foreach ($entries as $entry) {
            $entriesList[] = [
                'changelog_id' => $entry->changelog_id,
                'changelog_date' => $entry->changelog_date->toDateString(),
                'changelog_version' => $entry->changelog_version,
                'changelog_language' => $entry->name,
                'changelog_description' => $entry->changelog_description,
            ];
        }

        return $entriesList;
    }

    private function getAlertMessage(): void
    {
        $action_type = filter_input(INPUT_GET, 'success');

        if ($action_type) {
            session()->flash(
                'success',
                __('admin/changelog.ch_action_' . $action_type . '_done')
            );
        }
    }

    private function addAction(): void
    {
        $this->saveAction();

        Template::legacyView(
            'admin.changelog_form',
            array_merge(
                $this->getActionData('add')
            )
        );
    }

    private function editAction(int $changelog_id): void
    {
        $this->saveAction();

        Template::legacyView(
            'admin.changelog_form',
            $this->getActionData('edit', $changelog_id)
        );
    }

    private function getActionData(string $action, int $changelogId = 0): array
    {
        $changelogLangId = 0;
        $changelogVersion = '';
        $changelogDate = date('Y-m-d');
        $changelogDescription = '';

        if ($action == 'edit') {
            $result = Changelog::find($changelogId);

            if ($result) {
                $changelogLangId = $result->changelog_lang_id;
                $changelogVersion = $result->changelog_version;
                $changelogDate = $result->changelog_date->toDateString();
                $changelogDescription = $result->changelog_description;
            } else {
                Functions::redirect('admin.php?page=changelog');
            }
        }

        return [
            'action' => $action,
            'changelog_id' => $changelogId,
            'current_action' => strtr(
                __('admin/changelog.ch_' . $action . '_action'),
                ['%s' => $changelogDate]
            ),
            'changelog_date' => $changelogDate,
            'changelog_version' => $changelogVersion,
            'languages' => $this->getAllLanguages($changelogLangId),
            'changelog_description' => $changelogDescription,
        ];
    }

    private function saveAction(): void
    {
        $data = filter_input_array(INPUT_POST, [
            'changelog_id' => [
                'filter' => FILTER_VALIDATE_INT,
            ],
            'action' => [
                'filter' => FILTER_CALLBACK,
                'options' => [$this, 'isValidAction'],
            ],
            'changelog_date' => [
                'filter' => FILTER_CALLBACK,
                'options' => [$this, 'isValidDate'],
            ],
            'changelog_version' => [
                'filter' => FILTER_CALLBACK,
                'options' => [$this, 'isValidVersion'],
            ],
            'changelog_language' => [
                'filter' => FILTER_VALIDATE_INT,
                'options' => [
                    'default' => 1,
                    'min_range' => 1,
                ],
            ],
            'text' => [
                'filter' => FILTER_UNSAFE_RAW,
            ],
        ]);

        if (count($data) > 0) {
            $valid = true;

            foreach ($data as $value) {
                if ($value === false or $value === null) {
                    $valid = false;
                    break;
                }
            }

            if ($valid) {
                if ($data['action'] == 'add') {
                    DB::transaction(function () use ($data) {
                        Changelog::create([
                            'changelog_lang_id' => $data['changelog_language'],
                            'changelog_version' => $data['changelog_version'],
                            'changelog_description' => $data['text'],
                            'changelog_date' => $data['changelog_date'],
                        ]);
                    });
                }

                if ($data['action'] == 'edit') {
                    DB::transaction(function () use ($data) {
                        Changelog::where('changelog_id', $data['changelog_id'])->update([
                            'changelog_lang_id' => $data['changelog_language'],
                            'changelog_version' => $data['changelog_version'],
                            'changelog_description' => $data['text'],
                            'changelog_date' => $data['changelog_date'],
                        ]);
                    });
                }

                Functions::redirect('admin.php?page=changelog&success=' . $data['action']);
            }
        }
    }

    private function deleteAction(int $changelogId): void
    {
        Changelog::where('changelog_id', $changelogId)->delete();

        Functions::redirect('admin.php?page=changelog&success=delete');
    }

    /**
     * @return array<int<0, max>, array<string, int|string>>
     */
    private function getAllLanguages(int $defaultLanguageId): array
    {
        $languages = [];

        foreach (Languages::orderBy('name')->get() as $language) {
            $languages[] = [
                'id' => $language->id,
                'name' => $language->name,
                'code' => $language->code,
                'selected' => ($defaultLanguageId == $language->id ? 'selected' : ''),
            ];
        }

        return $languages;
    }

    private function isValidAction(?string $action): ?string
    {
        if (\in_array($action, ['add', 'edit'])) {
            return $action;
        }

        return null;
    }

    private function isValidDate(string $date): ?string
    {
        try {
            $datetime = new DateTime($date);

            return $datetime->format('Y-m-d');
        } catch (Exception $e) {
            return null;
        }
    }

    private function isValidVersion(?string $version): ?string
    {
        preg_match_all(
            '/^(0|[1-9]\d*)\.((0|[1-9]\d*)\.)?(0|[1-9]\d*)(-(0|[1-9]\d*|\d*[a-zA-Z][0-9a-zA-Z]*))?$/',
            $version,
            $matches
        );

        if (isset($matches[0][0])) {
            return $matches[0][0];
        }

        return null;
    }
}
