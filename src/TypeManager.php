<?php

namespace Jalno\AAA;

use dnj\AAA\Contracts\IType;
use dnj\AAA\Contracts\ITypeManager;
use dnj\UserLogger\Contracts\ILogger;
use Illuminate\Database\Eloquent\Collection;
use Jalno\AAA\Models\Type;
use Jalno\AAA\Models\TypeAbility;

class TypeManager implements ITypeManager
{
    public static function getTypeId(int|IType $type): int
    {
        if ($type instanceof IType) {
            return $type->getId();
        }

        return $type;
    }

    public function __construct(protected ILogger $userLogger)
    {
    }

    public function getGuestTypeID(): ?int
    {
        return config('aaa.guestType');
    }

    public function getGuestType(): ?Type
    {
        $id = $this->getGuestTypeID();
        if (null === $id) {
            return $id;
        }

        return $this->findOrFail($id);
    }

    public function find(int $id): ?Type
    {
        return Type::query()->find($id);
    }

    public function findOrFail(int $id): Type
    {
        return Type::query()->findOrFail($id);
    }

    /**
     * @return Collection<Type>
     */
    public function search(array $filters = []): Collection
    {
        return Type::query()->filter($filters)->get();
    }

    public function store(
        array $translates,
        array $abilities = [],
        array $childIds = [],
        array $meta = [],
        bool $childToItself = false,
        bool $userActivityLog = false,
    ): Type {
        throw new \LogicException('Currently We Are Not Support Store At This Moment!');
    }

    public function update(int|IType $type, array $changes, bool $userActivityLog = false): Type
    {
        throw new \LogicException('Currently We Are Not Support Update At This Moment!');
    }

    public function destroy(int|IType $type, bool $userActivityLog = false): void
    {
        throw new \LogicException('Currently We Are Not Support Destroy At This Moment!');
    }

    public function isParentOf(int|IType $type, int|IType $other): bool
    {
        if (is_int($type)) {
            $type = Type::query()->findOrFail($type);
        }

        return $type->isParentOf($other);
    }

    public function isChildOf(int|IType $type, int|IType $other): bool
    {
        if (is_int($type)) {
            $type = Type::query()->findOrFail($type);
        }

        return $type->isChildOf($other);
    }

    /**
     * @return string[]
     */
    public function getAllAbilities(): array
    {
        return TypeAbility::query()->toBase()->distinct()->pluck('name')->all();
    }
}
