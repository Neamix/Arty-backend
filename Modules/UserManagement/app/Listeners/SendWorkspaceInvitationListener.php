<?php

namespace Modules\UserManagement\Listeners;

use App\Services\MailService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Modules\UserManagement\Events\WorkspaceInvitationCreated;
use Modules\UserManagement\Mail\WorkspaceInvitationMail;

class SendWorkspaceInvitationListener implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;

    public function __construct(private MailService $mailService) {}

    public function handle(WorkspaceInvitationCreated $event): void
    {
        $invitation = $event->invitation->loadMissing(['workspace', 'role']);

        $this->mailService->send($invitation->email, new WorkspaceInvitationMail(
            $invitation->workspace->name,
            $invitation->role->name,
            $event->invitationUrl,
        ));
    }
}
