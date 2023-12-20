<?php

namespace Jalno\AAA\Http\Resources;

use dnj\AAA\Http\Resources\Concerns\HasMeta;
use dnj\AAA\Http\Resources\Concerns\HasSummary;
use dnj\Localization\Http\Resources\HasTranslate;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Type $resource
 */
class TypeResource extends JsonResource
{
    use HasMeta;
    use HasTranslate;
    use HasSummary;

    public function toArray($request)
    {
        $data = array_merge(
            parent::toArray($request),
            $this->localize(),
            $this->exportMeta(),
            $this->exportChildren(),
            ['abilities' => $this->resource->getAbilities()],
        );

        return $data;
    }

    public function localized(bool $localized = true): static
    {
        $this->localized = $localized;

        return $this;
    }

    protected function exportChildren(): array
    {
        if ($this->summary) {
            return ['children' => $this->resource->getChildIds()];
        }

        return [
            'children' => TypeCollection::make($this->resource->children, true, true),
        ];
    }
}
