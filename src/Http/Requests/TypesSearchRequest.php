<?php

namespace Jalno\AAA\Http\Requests;

use dnj\AAA\Contracts\IType;
use Illuminate\Foundation\Http\FormRequest;
use Yeganemehr\LaravelSupport\Http\Requests\HasExtraRules;

/**
 * @property int|string|null      $id
 * @property string|null          $title
 * @property bool|string|int|null $has_full_access
 */
class TypesSearchRequest extends FormRequest
{
    use HasExtraRules;

    public function authorize(): bool
    {
        return $this->user()->canAny([
            'viewAny',
            'userpanel_settings_usertypes_list', // Jalno Permission
        ], IType::class);
    }

    public function defaultRules(): array
    {
        return [
            'id' => ['sometimes', 'required', 'numeric'],
            'title' => ['sometimes', 'required', 'string'],
            'has_full_access' => ['sometimes', 'required', 'boolean'],
        ];
    }
}
