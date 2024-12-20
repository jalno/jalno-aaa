<?php

namespace Jalno\AAA;

use dnj\AAA\Contracts\IType;
use dnj\AAA\Contracts\IUser;
use dnj\AAA\Contracts\IUserManager;
use dnj\UserLogger\Contracts\ILogger;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Jalno\AAA\Models\User;

class UserManager implements IUserManager
{
    public static function getUserId(int|IUser $user): int
    {
        if ($user instanceof IUser) {
            return $user->getId();
        }

        return $user;
    }

    public function __construct(protected ILogger $userLogger)
    {
    }

    public function find(int $userId): ?User
    {
        return User::query()->find($userId);
    }

    public function findOrFail(int $userId): User
    {
        return User::query()->findOrFail($userId);
    }

    public function findByUsername(string $username): ?User
    {
        return User::query()
            ->where('email', $username)
            ->orWhere('cellphone', $username)
            ->first();
    }

    public function findByUsernameOrFail(string $username): User
    {
        return User::query()
            ->where('email', $username)
            ->orWhere('cellphone', $username)
            ->firstOrFail();
    }

    /**
     * @return Collection<User>
     */
    public function search(array $filters = []): Collection
    {
        return User::query()->filter($filters)->get();
    }

    public function store(string $name, string $username, string $password, int|IType $type, array $meta = [], bool $userActivityLog = false): User
    {
        throw new \LogicException('We Are Not Support Store At This Moment!');
    }

    public function update(int|IUser $user, array $changes, bool $userActivityLog = false): User
    {
        throw new \LogicException('We Are Not Support Update At This Moment!');
    }

    public function destroy(int|IUser $user, bool $userActivityLog = false): void
    {
        throw new \LogicException('We Are Not Support Destroy At This Moment!');
    }

    public function isParentOf(int|IUser $user, int|IUser $other): bool
    {
        if (is_int($user)) {
            $user = User::query()->findOrFail($user);
        }
        if (is_int($other)) {
            $other = User::query()->findOrFail($other);
        }

        /*
         * @var User $user
         * @var User $other
         */

        return $user->type->isParentOf($other->type_id);
    }

    public function isChildOf(int|IUser $user, int|IUser $other): bool
    {
        if (is_int($user)) {
            $user = User::query()->findOrFail($user);
        }
        if (is_int($other)) {
            $other = User::query()->findOrFail($other);
        }

        /*
         * @var User $user
         * @var User $other
         */

        return $user->type->isChildOf($other->type_id);
    }

    public function count(array $filters = []): int
    {
        return User::query()
            ->filter($filters)
            ->count();
    }

    public function ping(int|IUser $user): void
    {
        $user = User::ensureId($user);
        DB::transaction(function () use ($user) {
            $user = User::query()
                ->lockForUpdate()
                ->findOrFail($user);
            $user->update(['lastonline' => now()]);
        });
    }
}
