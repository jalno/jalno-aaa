<?php

namespace dnj\AAA;

use dnj\AAA\Contracts\ITypeManager;
use dnj\AAA\Contracts\IUser;
use dnj\AAA\Contracts\IUserManager;
use dnj\AAA\Models\User;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait HasOwner
{
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getOwnerUserId(): ?int
    {
        return $this->owner_id;
    }

    public function getOwnerUserColumn(): string
    {
        return 'owner_id';
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
        if (!$user instanceof Model) {
            throw new \Exception('This method just work with '.Model::class);
        }
        /**
         * @var ITypeManager
         */
        $typeManager = app(ITypeManager::class);
        $type = $typeManager->findOrFail($user->getTypeId());
        $childIds = $type->getChildIds();

        if ($childIds) {
            $accessToAnonymous = app(Gate::class)->forUser($user)->check('viewAnonymous', $this);
            $query->where(function ($query) use ($user, $childIds, $accessToAnonymous) {
                $userTable = $user->getTable();
                $userKeyName = $user->getKeyName();
                $query->whereIn($this->getOwnerUserColumn(), function ($query) use ($userKeyName, $userTable, $childIds) {
                    $query->select($userKeyName)
                        ->from($userTable)
                        ->whereIn("{$userTable}.type_id", $childIds);
                });
                $query->orWhere($this->getOwnerUserColumn(), $user->getId());
                if ($accessToAnonymous) {
                    $query->orWhereNull($this->getOwnerUserColumn());
                }
            });
        } else {
            $query->where($this->getOwnerUserColumn(), $user->getId());
        }
    }

    public function hasUserAccess(int|IUser $user): bool
    {
        if (null === $this->getOwnerUserId()) {
            if (is_int($user)) {
                /**
                 * @var IUserManager
                 */
                $userManager = app(IUserManager::class);
                $user = $userManager->findOrFail($user);
            }

            $accessToAnonymous = app(Gate::class)->forUser($user)->check('viewAnonymous', $this);

            return $accessToAnonymous;
        }
        if (User::ensureId($user) == $this->getOwnerUserId()) {
            return true;
        }
        /**
         * @var IUserManager
         */
        $userManager = app(IUserManager::class);

        return $userManager->isParentOf($user, $this->getOwnerUserId());
    }
}
