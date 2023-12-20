<?php

namespace Jalno\AAA\Rules;

use dnj\AAA\Contracts\IUserManager;

class UserExists extends OwnerableModelExists
{
    public function __construct(IUserManager $manager)
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
        if (
            !$model
            or (
                $this->user
                and $this->user->getId() != $model->getId()
                and !$this->manager->isParentOf($this->user, $model)
            )
        ) {
            $fail('The :attribute is invalid');
        }
    }
}
