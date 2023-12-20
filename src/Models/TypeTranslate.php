<?php

namespace Jalno\AAA\Models;

use dnj\AAA\Contracts\ITypeTranslate;
use dnj\AAA\Database\Factories\TypeTranslateFactory;
use dnj\Localization\Eloquent\IsTranslate;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int    $id
 * @property string $locale
 * @property int    $type_id
 * @property string $title
 * @property Type   $type
 */
class TypeTranslate extends Model implements ITypeTranslate
{
    use HasFactory;
    use IsTranslate;

    public static function newFactory(): TypeTranslateFactory
    {
        return TypeTranslateFactory::new();
    }

    /**
     * @var string
     */
    protected $table = 'aaa_types_translates';

    protected $fillable = ['locale', 'title'];

    public function type(): BelongsTo
    {
        return $this->belongsTo(Type::class);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function getTypeID(): int
    {
        return $this->type_id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }
}
