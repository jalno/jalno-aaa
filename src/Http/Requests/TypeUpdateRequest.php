<?php

namespace Jalno\AAA\Http\Requests;

use dnj\AAA\Contracts\IType;
use dnj\AAA\Contracts\ITypeManager;
use dnj\AAA\Rules\AbilityRule;
use dnj\AAA\Rules\TypeExists;
use Illuminate\Foundation\Http\FormRequest;
use Yeganemehr\LaravelSupport\Http\Requests\HasExtraRules;

/**
 * @property array<array{title?:string}>|null $translates
 * @property string[]|null                    $abilities
 * @property array<string|int>|null           $children
 */
class TypeUpdateRequest extends FormRequest
{
    use HasExtraRules;

    public function getType(): IType
    {
        return app(ITypeManager::class)->findOrFail($this->route('type'));
    }

    public function authorize(): bool
    {
        return $this->user()->can('update', $this->getType());
    }

    public function defaultRules(): array
    {
        return [
            'translates.*.title' => ['sometimes', 'required', 'string'],
            'abilities' => ['sometimes', 'required', 'array'],
            'abilities.*' => ['required', new AbilityRule()],
            'children' => ['sometimes', 'required', 'array'],
            'children.*' => ['required', app(TypeExists::class)->userHasAccess($this->user())],
        ];
    }
}
