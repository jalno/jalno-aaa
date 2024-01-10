<?php

namespace Jalno\AAA\Facades;

use Illuminate\Support\Facades\Facade;

class JalnoStore extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'session.jalno-store';
    }
}
