<?php

namespace Jalno\AAA\Session;

use Illuminate\Session\DatabaseSessionHandler;
use Illuminate\Support\Carbon;

class JalnoDatabaseSessionHandler extends DatabaseSessionHandler
{
    public function read($sessionId): string|false
    {
        $session = (object) $this->getQuery()->find($sessionId);

        if ($this->expired($session)) {
            $this->exists = true;

            return '';
        }

        if (isset($session->data)) {
            $this->exists = true;

            return strval($session->data);
        }

        return '';
    }

    protected function expired($session)
    {
        return isset($session->lastuse_at)
            && $session->lastuse_at < Carbon::now()->subMinutes($this->minutes)->getTimestamp();
    }
}
