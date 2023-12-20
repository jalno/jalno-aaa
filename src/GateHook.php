<?php

namespace dnj\AAA;

use dnj\AAA\Contracts\IHasAbilities;
use dnj\AAA\Contracts\ITypeManager;
use Illuminate\Auth\Access\Response;
use Illuminate\Contracts\Auth\Access\Authorizable;
use Illuminate\Support\Facades\Gate;

class GateHook
{
    public function __construct(protected ITypeManager $typeManager)
    {
    }

    public function __invoke(?Authorizable $user, string $ability, Response|bool|null $response): Response|bool|null
    {
        if (!is_null($response)) {
            return $response;
        }

        if (Gate::has($ability)) {
            return null;
        }
        if ($user) {
            if (!$user instanceof IHasAbilities) {
                return null;
            }

            return $user->can($ability);
        }

        $guestType = $this->typeManager->getGuestType();
        if (null !== $guestType) {
            return $guestType->can($ability);
        }

        return null;
    }
}
