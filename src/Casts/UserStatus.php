<?php

namespace Jalno\AAA\Casts;

use dnj\AAA\Contracts\UserStatus as AAAUserStatus;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class UserStatus implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param array<string, mixed> $attributes
     */
    public function get($model, string $key, $value, array $attributes): mixed
    {
        return match ($value) {
            1 => AAAUserStatus::ACTIVE, // UserpanelUsers::ACTIVE
            2 => AAAUserStatus::SUSPEND, // UserpanelUsers::SUSPEND
            3 => AAAUserStatus::SUSPEND, // UserpanelUsers::DEACTIVE
        };
    }

    /**
     * Prepare the given value for storage.
     *
     * @param array<string, mixed> $attributes
     */
    public function set($model, string $key, $value, array $attributes): mixed
    {
        return match ($value) {
            AAAUserStatus::ACTIVE => 1, // UserpanelUsers::ACTIVE
            AAAUserStatus::SUSPEND => 2, // UserpanelUsers::SUSPEND
            AAAUserStatus::SUSPEND => 3, // UserpanelUsers::DEACTIVE
        };
    }
}
