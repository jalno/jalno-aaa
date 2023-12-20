<?php

namespace Jalno\AAA\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * @property \Illuminate\Support\Collection<TypeResource> $resource
 */
class TypeCollection extends ResourceCollection
{
    public $collects = TypeResource::class;

    public function __construct($resource, bool $localized = true, bool $summary = true)
    {
        parent::__construct($resource);

        $this->resource
            ->each(fn ($i) => $i->localized($localized))
            ->each(fn ($i) => $i->summarize($summary));
    }
}
