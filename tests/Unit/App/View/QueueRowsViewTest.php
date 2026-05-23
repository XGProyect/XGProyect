<?php

declare(strict_types=1);

namespace Tests\Unit\App\View;

use Tests\TestCase;

class QueueRowsViewTest extends TestCase
{
    public function testItRendersAnActiveQueueRow(): void
    {
        $html = view('game.queue_rows', [
            'rows' => [[
                'label' => '1.: Computer Technology 7',
                'is_active' => true,
                'time_left' => 120,
                'cancel_url' => 'game.php?page=research&cmd=cancel',
                'cancel_label' => 'Cancel',
                'timer_variables' => ['pp' => 120],
                'finish_at' => '2026-05-23 12:00:00',
            ]],
        ])->render();

        $this->assertStringContainsString('1.: Computer Technology 7', $html);
        $this->assertStringContainsString('game.php?page=research&amp;cmd=cancel', $html);
        $this->assertStringContainsString('pp = "120";', $html);
        $this->assertStringContainsString('2026-05-23 12:00:00', $html);
    }

    public function testItRendersAnInactiveQueueRow(): void
    {
        $html = view('game.queue_rows', [
            'rows' => [[
                'label' => '2.: Metal Mine 11',
                'is_active' => false,
                'remove_url' => 'game.php?page=buildings&cmd=remove&listid=2',
                'remove_label' => 'Cancel',
            ]],
        ])->render();

        $this->assertStringContainsString('2.: Metal Mine 11', $html);
        $this->assertStringContainsString('game.php?page=buildings&amp;cmd=remove&amp;listid=2', $html);
        $this->assertStringContainsString('Cancel', $html);
    }
}
