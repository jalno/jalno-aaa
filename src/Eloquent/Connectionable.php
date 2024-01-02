<?php

namespace Jalno\AAA\Eloquent;

trait Connectionable
{
    /**
     * Get the current connection name for the model.
     *
     * @return string|null
     */
    public function getConnectionName()
    {
        return config(
            sprintf('jalno-aaa.database.models-connection.%s', static::class),
            config('jalno-aaa.database.models-connection-default', $this->connection)
        );
    }
}
