<?php

namespace Jalno\AAA\Http\Controllers;

use dnj\AAA\Contracts\ITypeManager;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Jalno\AAA\Http\Requests\TypesSearchRequest;
use Jalno\AAA\Http\Requests\TypeStoreRequest;
use Jalno\AAA\Http\Requests\TypeUpdateRequest;
use Jalno\AAA\Http\Resources\TypeCollection;
use Jalno\AAA\Http\Resources\TypeResource;
use Jalno\AAA\Models\Type;

class TypesController extends Controller
{
    use AuthorizesRequests;
    use ValidatesRequests;

    public function __construct(protected ITypeManager $typeManager)
    {
    }

    public function index(TypesSearchRequest $request): TypeCollection
    {
        $data = $request->validated();
        if (isset($data['has_full_access'])) {
            $data['hasFullAccess'] = $data['has_full_access'];
            unset($data['has_full_access']);
        }
        $types = Type::query()
            ->filter($data)
            ->userHasAccess(Auth::user())
            ->cursorPaginate();

        return TypeCollection::make($types);
    }

    public function store(TypeStoreRequest $request): TypeResource
    {
        $type = $this->typeManager->store(
            translates: $request->translates,
            abilities: $request->abilities,
            childIds: $request->children ?: [],
            childToItself: in_array($request->child_to_itself, [true, 'true', '1'], true),
            userActivityLog: true
        );

        return TypeResource::make($type);
    }

    public function show(int $type): TypeResource
    {
        $type = $this->typeManager->findOrFail($type);
        $this->authorize('view', $type);

        return TypeResource::make($type);
    }

    public function update(int $type, TypeUpdateRequest $request): TypeResource
    {
        $data = $request->validated();
        $type = $request->getType();
        $this->authorize('update', $type);

        $changes = $data;
        if (isset($changes['children'])) {
            $changes['childIds'] = $changes['children'];
            unset($changes['children']);
        }
        $user = $this->typeManager->update(
            type: $type,
            changes: $changes,
            userActivityLog: true,
        );

        return TypeResource::make($user);
    }

    public function destroy(int $type)
    {
        $type = $this->typeManager->findOrFail($type);
        $this->authorize('destroy', $type);
        $this->typeManager->destroy(
            type: $type,
            userActivityLog: true
        );

        return response()->noContent();
    }
}
