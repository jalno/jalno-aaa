<?php

namespace Jalno\AAA\Http\Requests;

use dnj\AAA\Contracts\IUser;
use dnj\AAA\Contracts\UserStatus;
use dnj\AAA\Rules\TypeExists;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Yeganemehr\LaravelSupport\Http\Requests\HasExtraRules;

/**
 * @property string               $name
 * @property int                  $type_id
 * @property UserStatus           $status
 * @property array<string,string> $usernames
 */
class UserStoreRequest extends FormRequest
{
    use HasExtraRules;

    public function authorize(): bool
    {
        return $this->user()->can('store', IUser::class);
    }

    public function defaultRules(): array
    {
        return [
            'name' => ['required', 'string'],
            'type_id' => ['required', app(TypeExists::class)->userHasAccess($this->user())],
            'status' => ['sometimes', 'required', Rule::enum(UserStatus::class)],
            'usernames' => ['required', 'array', 'min:1'],
            'usernames.*' => ['required', 'string'],
        ];
    }
}
