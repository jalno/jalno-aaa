<?php

namespace Jalno\AAA\Policies;

use dnj\AAA\Policies\UserPolicy as BaseUserPolicy;

class UserPolicy extends BaseUserPolicy
{
    public function getAbilityName(string $method): string
    {
        return match (strtolower($method)) {
            'view' => 'userpanel_users_view',
            'destroy' => 'userpanel_users_delete',
            'update' => 'userpanel_users_edit',
        };
    }
}
