<?php

namespace Jalno\AAA\Database\Seeders;

use dnj\AAA\Contracts\IType;
use dnj\AAA\Contracts\ITypeManager;
use Illuminate\Database\Seeder;

class TypeSeeder extends Seeder
{
    public function __construct(protected ITypeManager $typeManager)
    {
    }

    public function run(): void
    {
        $adminTypes = iterator_count($this->typeManager->search(['hasFullAccess' => true]));
        if ($adminTypes) {
            return;
        }
        $abilities = $this->typeManager->getAllAbilities();
        $children = collect($this->typeManager->search())->map(fn (IType $t) => $t->getId())->all();
        $this->typeManager->store(
            translates: ['en' => ['title' => 'Admin']],
            abilities: $abilities,
            childIds: $children,
            childToItself: true,
        );
    }
}
