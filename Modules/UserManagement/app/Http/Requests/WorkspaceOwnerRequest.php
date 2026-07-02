<?php

namespace Modules\UserManagement\Http\Requests;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Modules\UserManagement\Repositories\WorkspaceMemberRepository;

class WorkspaceOwnerRequest extends FormRequest
{
    public function authorize(WorkspaceMemberRepository $workspaceMemberRepository): bool
    {
        $workspaceId = $this->workspaceId();

        return $workspaceId !== null
            && $workspaceMemberRepository->isOwner($workspaceId, $this->user()->id);
    }

    /**
     * @return array<string, array<int, ValidationRule|string>>
     */
    public function rules(): array
    {
        return [];
    }

    private ?int $cachedWorkspaceId = null;

    protected function workspaceId(): ?int
    {
        return $this->cachedWorkspaceId ??= $this->user()?->workspaces()->value('workspaces.id');
    }

    protected function failedAuthorization(): void
    {
        throw new AuthorizationException('Only workspace owners can perform this action.');
    }
}
