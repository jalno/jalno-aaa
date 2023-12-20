<?php

namespace Jalno\AAA\Rules;

use dnj\AAA\Contracts\ITypeManager;

class TypeExists extends OwnerableModelExists
{
    public function __construct(ITypeManager $manager)
    {
        parent::__construct($manager);
    }

    public function __invoke($attribute, $value, $fail)
    {
        if (!is_numeric($value)) {
            $fail('The :attribute must be numeric');

            return;
        }

        $model = $this->manager->find($value);
        if (!$model) {
            $fail('The :attribute is invalid');
        }
    }
}
