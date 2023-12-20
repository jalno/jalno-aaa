<?php

namespace Jalno\AAA\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Contracts\Session\Middleware\AuthenticatesSessions;

class AuthenticateSession extends Authenticate implements AuthenticatesSessions
{
    use JalnoSessionTrait;
    /**
     * {@inhertdoc}
     */
    protected function unauthenticated($request, array $guards)
    {
        $authenticated = $this->authenticateJalnoSession($request);
        if (!$authenticated) {
            return parent::unauthenticated($request, $guards);
        }
    }
}
