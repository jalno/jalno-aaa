<?php

namespace Jalno\AAA\Http\Requests;

use dnj\AAA\Contracts\IType;
use Jalno\AAA\Rules\AbilityRule;
use Jalno\AAA\Rules\TypeExists;
use Illuminate\Foundation\Http\FormRequest;
use Yeganemehr\LaravelSupport\Http\Requests\HasExtraRules;

/**
 * @property array<array{title:string}> $translates
 * @property string[]                   $abilities
 * @property array<string|int>|null     $children
 * @property string|int|bool            $child_to_itself
 */
class TypeStoreRequest extends FormRequest
{
    use HasExtraRules;

    public function authorize(): bool
    {
        return $this->user()->can('store', IType::class);
    }

    public function defaultRules(): array
    {
        return [
            'translates.*.title' => ['required', 'string'],
            'abilities' => ['sometimes', 'required', 'array'],
            'abilities.*' => ['required', new AbilityRule()],
            'children' => ['sometimes', 'required', 'array'],
            'children.*' => ['required', app(TypeExists::class)->userHasAccess($this->user())],
            'child_to_itself' => ['required', 'boolean'],
        ];
    }
}
