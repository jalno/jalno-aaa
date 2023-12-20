<?php

namespace Jalno\AAA\Http\Resources;

use dnj\AAA\Contracts\IType;
use dnj\AAA\Contracts\ITypeManager;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class UserCollection extends ResourceCollection
{
    public $collects = UserResource::class;

    public function __construct($resource, bool $summary = false)
    {
        if ($summary) {
            $this->collects = UserSummaryResource::class;
        }
        parent::__construct($resource);
    }

    public function paginationInformation(Request $request, array $paginated, array $default): array
    {
        $types = array_map(fn (UserResource $r) => $r->resource->getTypeId(), $paginated['data']);
        $types = array_unique($types);

        /**
         * @var ITypeManager
         */
        $typeManager = app(ITypeManager::class);
        $types = iterator_to_array($typeManager->search(['id' => $types]));
        $types = array_map(fn (IType $t) => TypeResource::make($t)->localized()->summarize(), $types);
        $default['types'] = $types;

        return $default;
    }
}
