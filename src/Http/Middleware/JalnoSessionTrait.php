<?php

namespace Jalno\AAA\Http\Middleware;

use Illuminate\Support\Facades\Auth;

trait JalnoSessionTrait
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return bool if true, we find user within session, otherwise user not found
     */
    public function authenticateJalnoSession($request)
    {
        $cookieName = config('jalno-aaa.jalno-session.cookie.name', 'PHPSESSID');
        if (!$request->hasCookie($cookieName) or $request->user()) {
            return false;
        }
        $sessionId = $request->cookies->get($cookieName);

        /** @var \Illuminate\Contracts\Session\Session */
        $store = app('session.jalno-store');
        $sessionIdPrefix = match (config('jalno-aaa.jalno-session.driver')) {
            'db' => '',
            'php' => 'sess_',
            'cache' => 'session-',
            default => ''
        };
        $store->setId($sessionIdPrefix.$sessionId);
        $store->start();

        if ($userId = $store->has('userid')) {
            return boolval(
                Auth::loginUsingId($userId)
            );
        }

        return false;
    }
}
