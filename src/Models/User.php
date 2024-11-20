<?php

namespace Jalno\AAA\Models;

use dnj\AAA\Contracts\ITypeManager;
use dnj\AAA\Contracts\IUser;
use dnj\AAA\Contracts\IUserManager;
use dnj\AAA\Contracts\UserStatus;
use dnj\AAA\Models\Username;
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
use Jalno\AAA\Contracts\Comparison;
use Jalno\AAA\Database\Factories\UserFactory;
use Jalno\AAA\Models\Concerns\HasAbilities;
use Jalno\AAA\Models\Concerns\HasDynamicFields;

/**
 * @property string               $name
 * @property string               $lastname
 * @property string               $email
 * @property string|null          $avatar
 * @property string|null          $remember_token
 * @property int                  $type_id
 * @property Collection<Meta>     $meta
 * @property UserStatus           $status
 * @property Type                 $type
 * @property Country              $country
 * @property Collection<Username> $usernames
 */
class User extends Model implements IUser, Authenticatable, Authorizable
{
    use HasAbilities;
    use HasDynamicFields;
    use Loggable;

    /**
     * @use HasFactory<UserFactory>
     */
    use HasFactory;

    public static function ensureId(int|IUser $value): int
    {
        return $value instanceof IUser ? $value->getId() : $value;
    }

    public static function getOnlineTimeWindow(): int
    {
        return intval(config('jalno-aaa.online-users-time-window'));
    }

    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    protected $casts = [
        'status' => UserStatusCast::class,
        'lastonline' => 'timestamp',
        'registered_at' => 'datetime',
        'has_custom_permissions' => 'boolean',
    ];

    protected $fillable = [
        'name',
        'lastname',
        'type', // Note: you should use type instead of type_id for query builder!
        'status',
        'lastonline',
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
        $comparison = $filters['comparison'] ?? null;

        if (isset($filters['id'])) {
            if (is_array($filters['id'])) {
                $query->whereIn('id', $filters['id']);
            } else {
                $query->where('id', $filters['id']);
            }
        }

        $word = $filters['word'] ?? null;
        if ($word) {
            $query->where(function (Builder $parenthesis) use ($filters, $word, $comparison): void {
                foreach (['name', 'lastname', 'email', 'cellphone'] as $field) {
                    if (isset($filters[$field])) {
                        continue;
                    }
                    Comparison::forQueryBuilder(
                        fn (?string $operator, string $value) => $parenthesis->orWhere($field, $operator, $value),
                        $word,
                        $comparison
                    );
                }
                Comparison::forQueryBuilder(
                    fn (?string $operator, string $value) => $parenthesis->orWhereRaw(
                        "CONCAT(`name`, ' ', `lastname`)".($operator ?: '=').'?',
                        $value
                    ),
                    $word,
                    $comparison
                );
            });
        }

        if (isset($filters['name'])) {
            Comparison::forQueryBuilder(
                fn (?string $operator, string $value) => $query->where('name', $operator, $filters['name']),
                $filters['name'],
                $comparison
            );
        }
        if (isset($filters['lastname'])) {
            Comparison::forQueryBuilder(
                fn (?string $operator, string $value) => $query->where('lastname', $operator, $filters['lastname']),
                $filters['lastname'],
                $comparison
            );
        }
        if (isset($filters['email'])) {
            Comparison::forQueryBuilder(
                fn (?string $operator, string $value) => $query->where('email', $operator, $filters['email']),
                $filters['email'],
                $comparison
            );
        }
        if (isset($filters['cellphone'])) {
            Comparison::forQueryBuilder(
                fn (?string $operator, string $value) => $query->where('cellphone', $operator, $filters['cellphone']),
                $filters['cellphone'],
                $comparison
            );
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

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country');
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
        return $this->password;
    }

    public function getAuthPasswordName(): string
    {
        return 'password';
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
        return 'remember_token';
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
