<?php

namespace dnj\AAA\Tests\Doubles;

class DummyManager
{
    public function find(int $id): ?DummyModel
    {
        return DummyModel::query()->find($id);
    }
}
