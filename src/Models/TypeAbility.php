<?php

namespace Jalno\AAA\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Jalno\AAA\Database\Factories\TypeAbilityFactory;

/**
 * @property int    $id
 * @property string $name
 * @property int    $type_id
 * @property Type   $type
 */
class TypeAbility extends Model
{
    /**
     * @use HasFactory<TypeAbilityFactory>
     */
    use HasFactory;

    protected static function newFactory(): TypeAbilityFactory
    {
        return TypeAbilityFactory::new();
    }

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
