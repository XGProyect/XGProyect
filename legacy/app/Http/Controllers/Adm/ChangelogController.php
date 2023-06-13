<?php

declare(strict_types=1);

namespace Xgp\App\Http\Controllers\Adm;

use DateTime;
use Exception;
use Illuminate\Routing\Controller as BaseController;
use Xgp\App\Core\Template;
use Xgp\App\Libraries\Adm\AdministrationLib as Administration;
use Xgp\App\Libraries\Functions;
use Xgp\App\Models\Adm\Changelog;

class ChangelogController extends BaseController
{
    private Changelog $changelogModel;

    public function __invoke(): void
    {
        Administration::checkSession();

        if (!Administration::authorization(__CLASS__)) {
            Administration::noAccessMessage(__('admin/global.no_permissions'));
            exit;
        }

        $this->changelogModel = new Changelog();

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
        $sub_page = filter_input(INPUT_GET, 'action');
        $changelog_id = filter_input(INPUT_GET, 'changelogId', FILTER_VALIDATE_INT);

        if (isset($sub_page) && isset($changelog_id)) {
            $this->{$sub_page . 'Action'}($changelog_id);
        }

        if (isset($sub_page) && !isset($changelog_id)) {
            $this->{$sub_page . 'Action'}();
        }
    }

    private function buildListOfEntries(): array
    {
        $entries = $this->changelogModel->getAllEntries();
        $entries_list = [];

        foreach ($entries as $entry) {
            $entries_list[] = $entry;
        }

        return $entries_list;
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

    private function getActionData(string $action, int $changelog_id = 0): array
    {
        $changelog_lang_id = 0;
        $changelog_version = '';
        $changelog_date = date('Y-m-d');
        $changelog_description = '';

        if ($action == 'edit') {
            if ($result = $this->changelogModel->getSingleEntry($changelog_id)) {
                $changelog_lang_id = $result->getChangelogLangId();
                $changelog_version = $result->getChangelogVersion();
                $changelog_date = $result->getChangelogDate();
                $changelog_description = $result->getChangelogDescription();
            } else {
                Functions::redirect('admin.php?page=changelog');
            }
        }

        return [
            'action' => $action,
            'changelog_id' => $changelog_id,
            'current_action' => strtr(
                __('admin/changelog.ch_' . $action . '_action'),
                ['%s' => $changelog_date]
            ),
            'changelog_date' => $changelog_date,
            'changelog_version' => $changelog_version,
            'languages' => $this->getAllLanguages($changelog_lang_id),
            'changelog_description' => $changelog_description,
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

        if ($data) {
            $valid = true;

            foreach ($data as $value) {
                if ($value === false or $value === null) {
                    $valid = false;
                    break;
                }
            }

            if ($valid) {
                if ($data['action'] == 'add') {
                    $this->changelogModel->addEntry($data);
                }

                if ($data['action'] == 'edit') {
                    $this->changelogModel->updateEntry($data);
                }

                Functions::redirect('admin.php?page=changelog&success=' . $data['action']);
            }
        }
    }

    private function deleteAction(int $changelog_id): void
    {
        $this->changelogModel->deleteEntry($changelog_id);

        Functions::redirect('admin.php?page=changelog&success=delete');
    }

    private function getAllLanguages(int $default_language): array
    {
        $languages = $this->changelogModel->getAllLanguages();
        $list_of_languages = [];

        foreach ($languages as $language) {
            $list_of_languages[] = array_merge(
                $language,
                [
                    'selected' => ($default_language == $language['language_id'] ? 'selected' : ''),
                ]
            );
        }

        return $list_of_languages;
    }

    private function isValidAction(?string $action): ?string
    {
        if (\in_array($action, ['add', 'edit'])) {
            return $action;
        }

        return null;
    }

    private function isValidDate(?string $date): ?string
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
