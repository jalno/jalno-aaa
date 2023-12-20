<?php

namespace dnj\AAA\Tests\Doubles;

use dnj\AAA\Policy;

class DummyPolicy extends Policy
{
    public function getModel(): string
    {
        return DummyModel::class;
    }
}
