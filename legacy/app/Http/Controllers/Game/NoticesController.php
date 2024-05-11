<?php

namespace Xgp\App\Http\Controllers\Game;

use Illuminate\Routing\Controller as BaseController;
use Xgp\App\Core\Entity\NotesEntity;
use Xgp\App\Core\Enumerators\ImportanceEnumerator as Importance;
use Xgp\App\Core\Template;
use Xgp\App\Libraries\FormatLib;
use Xgp\App\Libraries\Functions;
use Xgp\App\Libraries\TimingLibrary as Timing;
use Xgp\App\Libraries\Users;
use Xgp\App\Libraries\Users\Notes as Note;
use Xgp\App\Models\Game\Notes;

class NoticesController extends BaseController
{
    public const MODULE_ID = 19;
    public const REDIRECT_TARGET = 'game.php?page=notices';

    private array $user = [];
    private ?Note $notes = null;
    private Notes $notesModel;

    public function __invoke(): void
    {
        Functions::moduleMessage(Functions::isModuleAccesible(self::MODULE_ID));

        $this->user = Users::getInstance()->getUserData();
        $this->notesModel = new Notes();

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
            $this->notesModel->getAllNotesByUserId($this->user['id'])
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
                        'note_time' => Timing::formatExtendedDate($note->getNoteTime()),
                        'note_color' => FormatLib::getImportanceColor($note->getNotePriority()),
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
            $note = $this->notesModel->getNoteById($this->user['id'], $note_id);
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
        $this->notesModel->createNewNote(
            [
                'note_owner' => $this->user['id'],
                'note_time' => time(),
                'note_priority' => is_int($data['u']) ? $data['u'] : Importance::important,
                'note_title' => !empty($data['title']) ? $data['title'] : __('game/notices.nt_your_subject'),
                'note_text' => !empty($data['text']) ? $data['text'] : '',
            ]
        );
    }

    private function editNote(array $data): void
    {
        $this->notesModel->updateNoteById(
            $this->user['id'],
            $data['n'],
            [
                'note_time' => time(),
                'note_priority' => is_int($data['u']) ? $data['u'] : Importance::important,
                'note_title' => !empty($data['title']) ? $data['title'] : __('game/notices.nt_your_subject'),
                'note_text' => !empty($data['text']) ? $data['text'] : '',
            ]
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

        $this->notesModel->deleteNoteById(
            $this->user['id'],
            join(',', $delete_string)
        );
    }
}
