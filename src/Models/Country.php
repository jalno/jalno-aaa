<?php

namespace Jalno\AAA\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Jalno\AAA\Database\Factories\CountryFactory;

/**
 * @property int              $id
 * @property string           $code
 * @property string           $name
 * @property string           $dialing_code
 * @property Collection<User> $users
 */
class Country extends Model
{
    /**
     * @use HasFactory<CountryFactory>
     */
    use HasFactory;

    public static function ensureId(int|self $value): int
    {
        return $value instanceof self ? $value->id : $value;
    }

    protected static function newFactory(): CountryFactory
    {
        return CountryFactory::new();
    }

    public $timestamps = false;

    /**
     * @var string
     */
    protected $table = 'userpanel_countries';

    protected $fillable = [
        'code',
        'name',
        'dialing_code',
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'country');
    }
}
