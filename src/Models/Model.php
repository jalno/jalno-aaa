<?php

namespace Jalno\AAA\Models;

use Illuminate\Database\Eloquent\Model as EloquentModel;
use Jalno\AAA\Eloquent\Connectionable;

class Model extends EloquentModel
{
    use Connectionable;
}
