<?php

namespace dnj\AAA\Tests\Doubles;

use dnj\AAA\Contracts\IOwnerableModel;
use dnj\AAA\HasOwner;
use Illuminate\Database\Eloquent\Model;

class DummyModel extends Model implements IOwnerableModel
{
    use HasOwner;

    protected $guarded = [];
    protected $table = 'aaa_dummy';
}
