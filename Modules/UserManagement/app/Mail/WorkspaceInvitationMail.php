<?php

namespace Modules\UserManagement\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WorkspaceInvitationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $workspaceName,
        public string $roleName,
        public string $invitationUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'You have been invited to Artmes',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'usermanagement::emails.workspace-invitation',
            with: [
                'workspaceName' => $this->workspaceName,
                'roleName' => $this->roleName,
                'invitationUrl' => $this->invitationUrl,
            ],
        );
    }
}
