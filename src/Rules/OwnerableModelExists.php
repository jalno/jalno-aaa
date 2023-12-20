<?php

namespace Jalno\AAA\Rules;

use dnj\AAA\Contracts\IUser;

class OwnerableModelExists extends ModelExists
{
    protected int|IUser|null $user = null;

    public function __invoke($attribute, $value, $fail)
    {
        if (!is_numeric($value) and !is_string($value)) {
            $fail('The :attribute must be numeric or string');

            return;
        }

        $model = $this->manager->find($value);
        if (!$model or ($this->user and !$model->hasUserAccess($this->user))) {
            $fail('The :attribute is invalid');
        }
    }

    /**
     * @return $this
     */
    public function userHasAccess(int|IUser|null $user): static
    {
        $this->user = $user;

        return $this;
    }
}
