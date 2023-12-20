<?php

namespace Jalno\AAA\Models;

use Illuminate\Database\Eloquent\Relations\HasOne;
use Jalno\AAA\Eloquent\Meta;

/**
 * @property int                   $id
 * @property string                $name
 * @property User                  $user
 * @property int|string|array|null $value
 */
class UserMeta extends Meta
{
    /**
     * @var string
     */
    protected $table = 'userpanel_users_options';

    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }
}
