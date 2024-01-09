<?php

namespace Jalno\AAA\Models;

use dnj\AAA\Contracts\ITypeManager;
use dnj\AAA\Contracts\IUser;
use dnj\AAA\Contracts\IUserManager;
use dnj\AAA\Contracts\UserStatus;
use dnj\UserLogger\Concerns\Loggable;
use Illuminate\Contracts\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Jalno\AAA\Casts\UserStatus as UserStatusCast;
use Jalno\AAA\Models\Concerns\HasAbilities;
use Jalno\AAA\Models\Concerns\HasDynamicFields;

/**
 * @property string               $name
 * @property string               $lastname
 * @property string               $email
 * @property string|null          $avatar
 * @property int                  $type_id
 * @property Collection<Meta>     $meta
 * @property UserStatus           $status
 * @property Type                 $type
 * @property Collection<Username> $usernames
 */
class User extends Model implements IUser, Authenticatable, Authorizable
{
    use HasAbilities;
    use HasDynamicFields;
    use Loggable;
    use HasFactory;

    public static function ensureId(int|IUser $value): int
    {
        return $value instanceof IUser ? $value->getId() : $value;
    }

    public static function getOnlineTimeWindow(): int
    {
        return intval(config('jalno-aaa.online-users-time-window'));
    }

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    protected ?Username $activeUsername = null;

    protected $casts = [
        'status' => UserStatusCast::class,
        'lastonline' => 'datetime',
        'registered_at' => 'datetime',
        'has_custom_permissions' => 'boolean',
    ];

    protected $fillable = [
        'name',
        'lastname',
        'type', // Note: you should use type instead of type_id for query builder!
        'status',
    ];

    protected $guarded = [
        'remember_token',
        'credit',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $appends = [
        'usernames',
    ];

    /**
     * @var string
     */
    protected $table = 'userpanel_users';

    public function scopeFilter(Builder $query, array $filters): void
    {
        if (isset($filters['id'])) {
            if (is_array($filters['id'])) {
                $query->whereIn('id', $filters['id']);
            } else {
                $query->where('id', $filters['id']);
            }
        }
        if (isset($filters['name'])) {
            $query->where('name', 'LIKE', $filters['name']);
        }
        if (isset($filters['lastname'])) {
            $query->where('lastname', 'LIKE', $filters['lastname']);
        }
        $typeId = $filters['type_id'] ?? $filters['type'] ?? null;
        if (!is_null($typeId)) {
            $query->where('type', $typeId);
        }
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (isset($filters['userHasAccess'])) {
            $this->scopeUserHasAccess($query, $filters['userHasAccess']);
        }
    }

    public function scopeUserHasAccess(Builder $query, int|IUser $user): void
    {
        if (is_int($user)) {
            /**
             * @var IUserManager
             */
            $userManager = app(IUserManager::class);
            $user = $userManager->findOrFail($user);
        }
        if (!$user instanceof self) {
            throw new \Exception('This method just work with '.self::class);
        }
        /**
         * @var ITypeManager
         */
        $typeManager = app(ITypeManager::class);
        $type = $typeManager->findOrFail($user->getTypeId());
        $childIds = $type->getChildIds();

        if ($childIds) {
            $query->where(function ($query) use ($user, $childIds) {
                $query->whereIn('type', $childIds);
                $query->orWhere('id', $user->getId());
            });
        } else {
            $query->where('id', $user->getId());
        }
    }

    public function scopeAreOnline(Builder $query): void
    {
        $query->where('lastonline', '>=', now()->subSeconds($this->getOnlineTimeWindow()));
    }

    public function scopeAreNotOnline(Builder $query): void
    {
        $query->where(function (Builder $q) {
            $q->where('lastonline', '<', now()->subSeconds($this->getOnlineTimeWindow()));
            $q->orWhereNull('lastonline');
        });
    }

    public function isOnline(): bool
    {
        return null !== $this->lastonline and $this->lastonline->isAfter(now()->subSeconds($this->getOnlineTimeWindow()));
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(Type::class, 'type_id', 'id');
    }

    public function meta(): HasMany
    {
        return $this->hasMany(UserMeta::class, 'user');
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getTypeId(): int
    {
        return $this->type->id;
    }

    public function getStatus(): UserStatus
    {
        return $this->status;
    }

    public function getMeta(): array
    {
        return $this->meta->pluck('value', 'name')->toArray();
    }

    /**
     * @return string[]
     */
    public function getAbilities(): array
    {
        return $this->type->getAbilities();
    }

    public function getAuthIdentifierName(): string
    {
        return $this->getKeyName();
    }

    public function getAuthIdentifier(): int
    {
        return $this->getKey();
    }

    public function getAuthPassword(): string
    {
        return $this->usernames->first()?->password ?? '';
    }

    public function getRememberToken(): ?string
    {
        return $this->remember_token;
    }

    public function setRememberToken($value): void
    {
        $this->remember_token = $value;
    }

    public function getRememberTokenName(): string
    {
        return '';
    }

    public function getCreatedAt(): Carbon
    {
        return $this->registered_at;
    }

    public function getUpdatedAt(): ?Carbon
    {
        return null;
    }

    public function getPingAt(): ?Carbon
    {
        return $this->lastonline;
    }

    public function verifyPassword(string $password): bool
    {
        return Hash::check($password, $this->password);
    }

    /**
     * Get an attribute from the model.
     * In Jalno's UserPanel we use type key to store id of the type, and now, we try to convert type to type_id.
     *
     * @param string $key
     */
    public function getAttribute($key)
    {
        if ('type' == $key or 'type_id' == $key) {
            $this->attributes['type_id'] = $this->attributes['type_id'] ?? $this->attributes['type'];
            unset($this->attributes['type']);
        }

        return parent::getAttribute($key);
    }

    protected function getFullnameAttribute(): string
    {
        return $this->name.' '.$this->lastname;
    }

    protected function getUsernamesAttribute(): Collection
    {
        return new Collection([
            new Username([
                'id' => null,
                'user_id' => $this->id,
                'username' => $this->email,
                'password' => $this->password,
            ]),
            new Username([
                'id' => null,
                'user_id' => $this->id,
                'username' => $this->cellphone,
                'password' => $this->password,
            ]),
        ]);
    }
}
