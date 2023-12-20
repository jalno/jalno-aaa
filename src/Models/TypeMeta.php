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
class TypeMeta extends Meta
{
    /**
     * @var string
     */
    protected $table = 'userpanel_usertypes_options';

    public function type(): HasOne
    {
        return $this->hasOne(Type::class);
    }
}
