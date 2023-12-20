<?php

namespace Jalno\AAA;

use dnj\AAA\Contracts\IUserManager;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider as AuthUserProvider;
use Jalno\AAA\Models\User;

class UserProvider implements AuthUserProvider
{
    public function __construct(protected IUserManager $userManager)
    {
        //
    }

    public function retrieveById($identifier): ?User
    {
        return $this->userManager->find($identifier);
    }

    public function retrieveByToken($identifier, $token): ?User
    {
        //
        return null;
    }

    public function updateRememberToken(Authenticatable $user, $token): void
    {
        //
    }

    public function retrieveByCredentials(array $credentials): ?User
    {
        if (!isset($credentials['username'])) {
            return null;
        }

        return $this->userManager->findByUsername($credentials['username']);
    }

    public function validateCredentials(Authenticatable $user, array $credentials): bool
    {
        if (!isset($credentials['username'], $credentials['password'])) {
            return false;
        }
        if (!$user instanceof User) {
            return false;
        }
        /** @var User $user */
        $user = $this->userManager->findByUsername($credentials['username']);
        if ($user->verifyPassword($credentials['password'])) {
            return true;
        }
        

        return false;
    }
}
