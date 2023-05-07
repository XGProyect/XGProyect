<?php

namespace Xgp\App\Libraries\Users;

use Xgp\App\Core\Entity\NotesEntity;

class Notes
{
    private array $notes = [];
    private int $notes_count = 0;

    public function __construct($notes)
    {
        if (is_array($notes)) {
            $this->setUp($notes);
        }
    }

    public function getNotes(): array
    {
        $list_of_notes = [];

        foreach ($this->notes as $notes) {
            if (($notes instanceof NotesEntity)) {
                $list_of_notes[] = $notes;
            }
        }

        return $list_of_notes;
    }

    public function getNoteById(int $note_id): NotesEntity
    {
        if ($note_id == $this->getNotes()[0]->getNoteId()) {
            return $this->getNotes()[0];
        }

        return null;
    }

    private function setUp(array $notes): void
    {
        foreach ($notes as $note) {
            $this->notes[] = $this->createNewNotesEntity($note);

            $this->setNotesCount();
        }
    }

    public function hasNotes(): bool
    {
        return ($this->getNotesCount() > 0);
    }

    private function setNotesCount(): void
    {
        ++$this->notes_count;
    }

    public function getNotesCount(): int
    {
        return $this->notes_count;
    }

    private function createNewNotesEntity(array $note): NotesEntity
    {
        return new NotesEntity($note);
    }
}
