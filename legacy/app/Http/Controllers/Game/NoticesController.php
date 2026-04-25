<?php

declare(strict_types=1);

namespace Xgp\App\Http\Controllers\Game;

use App\Enums\Module;
use App\Services\FormatService;
use App\Services\TimingService;
use Illuminate\Routing\Controller as BaseController;
use Xgp\App\Core\Entity\NotesEntity;
use Xgp\App\Core\Enumerators\ImportanceEnumerator as Importance;
use Xgp\App\Core\Template;
use Xgp\App\Libraries\Functions;
use Xgp\App\Libraries\Users;
use Illuminate\Support\Facades\DB;
use Xgp\App\Core\Concerns\PreparesLegacySql;
use Xgp\App\Libraries\Users\Notes as Note;

/**
 * @SuppressWarnings("PHPMD.StaticAccess")
 */
class NoticesController extends BaseController
{
    use PreparesLegacySql;

    public const REDIRECT_TARGET = 'game.php?page=notices';

    private array $user = [];
    private ?Note $notes = null;

    public function __construct(
        private FormatService $formatService,
        private TimingService $timingService,
    ) {
    }

    public function __invoke(): void
    {
        Functions::moduleMessage(Functions::isModuleAccesible(Module::Notes));

        $this->user = Users::getInstance()->getUserData();
        $this->setUpNotes();
        $this->runAction();

        $page = $this->getCurrentPage();

        Template::legacyView(
            $page['template'],
            $page['data']
        );
    }

    private function runAction(): void
    {
        $data = filter_input_array(INPUT_POST, [
            's' => [
                'filter' => FILTER_VALIDATE_INT,
                'options' => ['min_range' => 1, 'max_range' => 2],
            ],
            'u' => [
                'filter' => FILTER_VALIDATE_INT,
                'options' => ['min_range' => Importance::unimportant, 'max_range' => Importance::important],
            ],
            'title' => [
                'filter' => FILTER_UNSAFE_RAW,
                'options' => ['min_range' => 1, 'max_range' => 32],
            ],
            'text' => [
                'filter' => FILTER_UNSAFE_RAW,
                'options' => ['min_range' => 1, 'max_range' => 5000],
            ],
            'n' => FILTER_SANITIZE_NUMBER_INT,
        ]);

        $delete = filter_input(INPUT_POST, 'delnote', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);

        // add a note
        if (isset($data['s']) && $data['s'] == 1) {
            $this->createNewNote($data);
        }

        // edit a note
        if (isset($data['s']) && $data['s'] == 2) {
            $this->editNote($data);
        }

        // delete notes
        if (isset($delete) && count($delete) > 0) {
            $this->deleteNote($delete);
        }
    }

    private function setUpNotes(): void
    {
        $this->notes = new Note(
            array_map(
                fn ($row) => (array) $row,
                DB::select(
                    $this->prepareSql(
                        'SELECT n.*
                        FROM `' . NOTES . "` n
                        WHERE n.`note_owner` = '" . (int) $this->user['id'] . "'
                        ORDER BY n.`note_time` DESC;"
                    )
                )
            )
        );
    }

    private function buildNotesListBlock(): array
    {
        $list_of_notes = [];

        $notes = $this->notes->getNotes();

        if ($this->notes->hasNotes()) {
            foreach ($notes as $note) {
                if ($note instanceof NotesEntity) {
                    $list_of_notes[] = [
                        'note_id' => $note->getNoteId(),
                        'note_time' => $this->timingService->formatExtendedDate($note->getNoteTime()),
                        'note_color' => $this->formatService->getImportanceColor($note->getNotePriority()),
                        'note_title' => $note->getNoteTitle(),
                    ];
                }
            }
        }

        return $list_of_notes;
    }

    private function getCurrentPage(): array
    {
        $edit_view = filter_input(INPUT_GET, 'a', FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1, 'max_range' => 2],
        ]);

        if ($edit_view !== false && !is_null($edit_view)) {
            return [
                'template' => 'notices.write',
                'data' => $this->buildEditBlock($edit_view),
            ];
        }

        return [
            'template' => 'notices.view',
            'data' => [
                'list_of_notes' => $this->buildNotesListBlock(),
                'no_notes' => $this->notes->hasNotes() ? '' : '<tr><th colspan="4">' . __('game/notices.nt_no_notes_found') . '</th>',
            ],
        ];
    }

    private function buildEditBlock(int $edit_view): array
    {
        $note_id = filter_input(INPUT_GET, 'n', FILTER_VALIDATE_INT);
        $selected = [
            'selected_2' => '',
            'selected_1' => 'selected="selected"',
            'selected_0' => '',
        ];

        // edit
        if ($edit_view == 2 && !is_null($note_id)) {
            $noteRow = DB::selectOne(
                $this->prepareSql(
                    'SELECT n.*
                    FROM `' . NOTES . "` n
                    WHERE n.`note_id` = '" . $note_id . "'
                        AND n.`note_owner` = '" . $this->user['id'] . "';"
                )
            );
            $note = $noteRow !== null ? (array) $noteRow : [];
            $selected = array_fill_keys(array_keys($selected), null); // clear values keeping the keys

            if ($note) {
                $note_data = new Note(
                    [$note]
                );

                $selected['selected_' . $note_data->getNoteById($note_id)->getNotePriority()] = 'selected="selected"';

                return array_merge([
                    's' => 2,
                    'note_id' => '<input type="hidden" name="n" value=' . $note_data->getNoteById($note_id)->getNoteId() . '>',
                    'title' => __('game/notices.nt_edit_note'),
                    'subject' => $note_data->getNoteById($note_id)->getNoteTitle(),
                    'text' => $note_data->getNoteById($note_id)->getNoteText(),
                ], $selected);
            }
        }

        // add or default
        return array_merge([
            's' => 1,
            'note_id' => '',
            'title' => __('game/notices.nt_add_note'),
            'subject' => __('game/notices.nt_your_subject'),
            'text' => '',
        ], $selected);
    }

    private function createNewNote(array $data): void
    {
        $note_data = [
            'note_owner' => $this->user['id'],
            'note_time' => time(),
            'note_priority' => is_int($data['u']) ? $data['u'] : Importance::important,
            'note_title' => !empty($data['title']) ? $data['title'] : __('game/notices.nt_your_subject'),
            'note_text' => !empty($data['text']) ? $data['text'] : '',
        ];
        $sql = [];
        foreach ($note_data as $field => $value) {
            $sql[] = '`' . $field . "` = '" . $value . "'";
        }
        DB::statement($this->prepareSql('INSERT INTO `' . NOTES . '` SET ' . join(', ', $sql)));
    }

    private function editNote(array $data): void
    {
        $note_data = [
            'note_time' => time(),
            'note_priority' => is_int($data['u']) ? $data['u'] : Importance::important,
            'note_title' => !empty($data['title']) ? $data['title'] : __('game/notices.nt_your_subject'),
            'note_text' => !empty($data['text']) ? $data['text'] : '',
        ];
        $sql = [];
        foreach ($note_data as $field => $value) {
            $sql[] = 'n.`' . $field . "` = '" . $value . "'";
        }
        DB::statement(
            $this->prepareSql(
                'UPDATE `' . NOTES . '` n SET '
                . join(', ', $sql)
                . " WHERE n.`note_owner` = '" . $this->user['id'] . "'
                    AND n.`note_id` = '" . $data['n'] . "';"
            )
        );
    }

    private function deleteNote(array $data): void
    {
        $delete_string = [];

        foreach ($data as $note_id => $set) {
            if ($set == 'y') {
                $delete_string[] = $note_id;
            }
        }

        DB::statement(
            $this->prepareSql(
                'DELETE FROM `' . NOTES . "`
                WHERE `note_owner` = '" . $this->user['id'] . "'
                    AND `note_id` IN (" . join(',', $delete_string) . ');'
            )
        );
    }
}
