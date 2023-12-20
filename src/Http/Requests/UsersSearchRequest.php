<?php

namespace Jalno\AAA\Http\Requests;

use dnj\AAA\Contracts\IUser;
use dnj\AAA\Contracts\UserStatus;
use dnj\AAA\Rules\TypeExists;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Yeganemehr\LaravelSupport\Http\Requests\HasExtraRules;

class UsersSearchRequest extends FormRequest
{
    use HasExtraRules;

    public function authorize(): bool
    {
        return $this->user()->canAny([
            'viewAny',
            'userpanel_users_list', // Jalno Permission
        ], IUser::class);
    }

    public function defaultRules(): array
    {
        return [
            'id' => ['sometimes', 'required', 'numeric'],
            'name' => ['sometimes', 'required', 'string'],
            'type_id' => ['sometimes', 'required', app(TypeExists::class)->userHasAccess($this->user())],
            'status' => ['sometimes', 'required', Rule::enum(UserStatus::class)],
            'username' => ['sometimes', 'required', 'string'],
            'online' => ['sometimes', 'required', 'boolean'],
        ];
    }
}
