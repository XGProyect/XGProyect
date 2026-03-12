<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Services\SettingsService;
use Illuminate\Support\Facades\URL;

class Announcement extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        private string $emailSubject,
        private string $emailContent
    ) {
        //
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: !empty($this->emailSubject) ? $this->emailSubject : __('admin/announcement.an_none'),
            from: new Address(
                app(SettingsService::class)->getString('admin_email'),
                app(SettingsService::class)->getString('game_name')
            ),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.admin.announcement',
            with: [
                'content' => $this->emailContent,
                'gameName' => app(SettingsService::class)->getString('game_name'),
                'gameUrl' => URL::to('/'),
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
