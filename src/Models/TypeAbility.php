<?php

namespace Jalno\AAA\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int    $id
 * @property string $name
 * @property int    $type_id
 * @property Type   $type
 */
class TypeAbility extends Model
{
    public $timestamps = false;

    /**
     * @var string
     */
    protected $table = 'userpanel_usertypes_permissions';

    protected $fillable = ['type', 'name'];

    protected $appends = [
        'type_id',
    ];

    public function type(): BelongsTo
    {
        return $this->belongsTo(Type::class);
    }

    public function getTypeIdAttribute()
    {
        return $this->getRawOriginal('type');
    }
}
