<?php

namespace Jalno\AAA\Models;

use dnj\AAA\Contracts\IType;
use dnj\AAA\Contracts\IUser;
use dnj\AAA\Models\Concerns\HasAbilities;
use dnj\AAA\Models\TypeTranslate;
use dnj\UserLogger\Concerns\Loggable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Jalno\AAA\Eloquent\HasNotTranslate;

/**
 * @property int                     $id
 * @property Collection<TypeAbility> $abilities
 * @property Collection<User>        $users
 * @property Collection<self>        $children
 * @property Collection<self>        $parents
 * @property Collection<Meta>        $meta
 *
 * @method ?TypeTranslate          getTranslate(string $locale)
 * @method iterable<TypeTranslate> getTranslates()
 */
class Type extends Model implements IType
{
    use HasAbilities;
    use Loggable;
    use HasNotTranslate;

    public static function ensureId(int|IType $value): int
    {
        return $value instanceof IType ? $value->getId() : $value;
    }

    /**
     * @var string
     */
    protected $table = 'userpanel_usertypes';

    public $timestamps = false;

    protected $fillable = [
        'title',
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'type');
    }

    public function meta(): HasMany
    {
        return $this->hasMany(TypeMeta::class, 'usertype');
    }

    public function abilities()
    {
        return $this->hasMany(TypeAbility::class, 'type');
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function scopeFilter(Builder $query, array $filters): void
    {
        if (isset($filters['id'])) {
            if (is_array($filters['id'])) {
                $query->whereIn('id', $filters['id']);
            } else {
                $query->where('id', $filters['id']);
            }
        }
        if (isset($filters['hasFullAccess']) and $filters['hasFullAccess']) {
            $this->scopeHasFullAccess($query);
        }
    }

    public function scopeHasFullAccess(Builder $query): void
    {
        $typesCount = self::query()->count();
        $abilitiesCount = TypeAbility::query()->toBase()->distinct()->count('name');
        $query->has('abilities', $abilitiesCount);
        $query->has('children', $typesCount);
    }

    public function scopeUserHasAccess(Builder $query, IUser $user): void
    {
        if ($user instanceof User) {
            /**
             * @var IType
             */
            $type = $user->type;
        } else {
            /**
             * @var ITypeManager
             */
            $typeManager = app(ITypeManager::class);
            $type = $typeManager->findOrFail($user->getTypeId());
        }
        $query->whereIn('id', $type->getChildIds());
    }

    /**
     * @return string[]
     */
    public function getAbilities(): array
    {
        return $this->abilities->pluck('name')->all();
    }

    /**
     * @return int[]
     */
    public function getChildIds(): array
    {
        return $this->getChildren()->pluck('id')->all();
    }

    /**
     * @return int[]
     */
    public function getParentIds(): array
    {
        return $this->getParents()->pluck('id')->all();
    }

    /**
     * @return Collection<Type>
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    /**
     * @return Collection<Type>
     */
    public function getParents(): Collection
    {
        return $this->parents;
    }

    public function children(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'userpanel_usertypes_priorities', 'parent', 'child');
    }

    public function parents(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'userpanel_usertypes_priorities', 'child', 'parent');
    }

    public function getMeta(): array
    {
        return $this->meta->pluck('value', 'name')->toArray();
    }

    public function isParentOf(int|IType $other): bool
    {
        return $this->children->pluck('id')->contains(self::ensureId($other));
    }

    public function isChildOf(int|IType $other): bool
    {
        return $this->parents->pluck('id')->contains(self::ensureId($other));
    }
}
