<?php

namespace Jalno\AAA;

use dnj\AAA\UserProvider as AAAUserProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use Jalno\AAA\Models\User;

class UserProvider extends AAAUserProvider
{
    public function updateRememberToken(Authenticatable $user, $token): void
    {
        if (!$user instanceof User) {
            throw new \InvalidArgumentException('user must be an instance of '.User::class);
        }
        $user->remember_token = $token;
        $user->save();
    }

    public function retrieveByToken($identifier, $token): ?User
    {
        $user = $this->userManager->find($identifier);

        return $user->remember_token === $token ? $user : null;
    }

    public function validateCredentials(Authenticatable $user, array $credentials): bool
    {
        if (!isset($credentials['username'], $credentials['password'])) {
            return false;
        }
        if (!$user instanceof User) {
            return false;
        }

        return $user->verifyPassword($credentials['password']);
    }

    public function rehashPasswordIfRequired(Authenticatable $user, array $credentials, bool $force = false)
    {
        if (!$user instanceof User) {
            throw new \InvalidArgumentException('user must be an instance of '.User::class);
        }

        if (!$this->hasher->needsRehash($user->password) && !$force) {
            return;
        }

        $user->forceFill([
            'password' => $this->hasher->make($credentials['password']),
        ])->save();
    }
}
