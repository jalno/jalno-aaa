<?php

namespace Jalno\AAA;

use dnj\AAA\Contracts\IType;
use dnj\AAA\Contracts\ITypeManager;
use dnj\UserLogger\Contracts\ILogger;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
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
        throw new \Exception('Currently We Are Not Support Store At This Moment!');

        return DB::transaction(function () use ($translates, $abilities, $childIds, $meta, $childToItself, $userActivityLog) {
            /**
             * @var Type
             */
            $type = Type::query()->create([
                'meta' => $meta,
            ]);
            foreach ($translates as $locale => $fields) {
                $type->addTranslate($locale, $fields);
            }
            $type->abilities()->createMany(array_map(fn ($name) => ['name' => $name], $abilities));
            foreach ($childIds as $childId) {
                $type->children()->attach($childId, [], false);
            }
            if ($childToItself) {
                $type->children()->attach($type->id, [], false);
            }

            if ($userActivityLog) {
                $this->userLogger
                    ->on($type)
                    ->withRequest(request())
                    ->log('created');
            }

            return $type;
        });
    }

    public function update(int|IType $type, array $changes, bool $userActivityLog = false): Type
    {
        throw new \Exception('Currently We Are Not Support Update At This Moment!');

        return DB::transaction(function () use ($type, $changes, $userActivityLog) {
            $needToRefresh = false;
            /**
             * @var Type
             */
            $type = Type::query()
                ->lockForUpdate()
                ->findOrFail(self::getTypeId($type));
            if (isset($changes['translates'])) {
                $type->updateTranslates($changes['translates']);
                unset($changes['translates']);
                $needToRefresh = true;
            }
            if (isset($changes['abilities'])) {
                $type->updateAbilities($changes['abilities']);
                unset($changes['abilities']);
                $needToRefresh = true;
            }
            if (isset($changes['childIds'])) {
                $type->updateChildIds($changes['childIds']);
                unset($changes['childIds']);
                $needToRefresh = true;
            }
            $type->fill($changes);
            $changes = $type->changesForLog();
            $type->save();

            if ($userActivityLog) {
                $this->userLogger
                    ->on($type)
                    ->withRequest(request())
                    ->withProperties($changes)
                    ->log('updated');
            }

            if ($needToRefresh) {
                $type->refresh();
            }

            return $type;
        });
    }

    public function destroy(int|IType $type, bool $userActivityLog = false): void
    {
        throw new \Exception('Currently We Are Not Support Destroy At This Moment!');
        DB::transaction(function () use ($type, $userActivityLog) {
            /**
             * @var Type
             */
            $type = Type::query()
                ->lockForUpdate()
                ->findOrFail(self::getTypeId($type));
            $type->delete();
            if ($userActivityLog) {
                $this->userLogger
                    ->on($type)
                    ->withRequest(request())
                    ->withProperties($type->toArray())
                    ->log('deleted');
            }
        });
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
