<?php

namespace Jalno\AAA\Rules;

use Illuminate\Contracts\Validation\InvokableRule;

class ModelExists implements InvokableRule
{
    public function __construct(protected object $manager)
    {
    }

    public function __invoke($attribute, $value, $fail)
    {
        if (!is_numeric($value) and !is_string($value)) {
            $fail('The :attribute must be numeric or string');

            return;
        }

        $model = $this->manager->find($value);
        if (!$model) {
            $fail('The :attribute is invalid');
        }
    }
}
