<?php
namespace Jalno\AAA\Eloquent;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int                   $id
 * @property string                $name
 * @property int|string|array|null $value
 */
class Meta extends Model
{
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'name', 'value',
    ];

    protected function getValueAttribute(): int|string|array|null
    {
        $value = $this->getRawOriginal('value');
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (!is_null($decoded)) {
                return $decoded;
            }
        }

        return $value;
    }
}
