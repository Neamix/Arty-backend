<?php

namespace Modules\UserManagement\Support;

class PermissionRegistry
{
    public static function grouped(): array
    {
        return [
            'workspace' => [
                'workspace.view',
                'workspace.update',
                'workspace.invite_members',
                'workspace.manage_members',
                'workspace.manage_roles',
            ],
            'projects' => [
                'projects.view',
                'projects.write',
                'projects.delete',
            ],
            'boards' => [
                'boards.view',
                'boards.write',
            ],
            'stages' => [
                'stages.view',
                'stages.write',
                'stages.delete',
            ],
            'leads' => [
                'leads.view',
                'leads.write',
                'leads.move',
                'leads.delete',
            ],
            'forms' => [
                'forms.view',
                'forms.write',
            ],
            'fields' => [
                'fields.write',
                'fields.delete',
            ],
            'activity' => [
                'activity.view',
            ],
        ];
    }

    public static function all(): array
    {
        return collect(self::grouped())->flatten()->values()->all();
    }
}
