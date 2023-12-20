<?php

namespace Jalno\AAA\Http\Requests;

use dnj\AAA\Contracts\IUser;
use dnj\AAA\Contracts\IUserManager;
use dnj\AAA\Contracts\UserStatus;
use dnj\AAA\Rules\TypeExists;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Yeganemehr\LaravelSupport\Http\Requests\HasExtraRules;

/**
 * @property string|null     $name
 * @property int|null        $type_id
 * @property UserStatus|null $status
 */
class UserUpdateRequest extends FormRequest
{
    use HasExtraRules;

    public function getTheUser(): IUser
    {
        return app(IUserManager::class)->findOrFail($this->route('user'));
    }

    public function authorize(): bool
    {
        return $this->user()->can('update', $this->getTheUser());
    }

    public function defaultRules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string'],
            'type_id' => ['sometimes', 'required', app(TypeExists::class)->userHasAccess($this->user())],
            'status' => ['sometimes', 'required', Rule::enum(UserStatus::class)],
        ];
    }
}
