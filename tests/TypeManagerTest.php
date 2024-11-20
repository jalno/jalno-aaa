<?php

namespace Jalno\AAA\Tests;

use dnj\AAA\Contracts\ITypeManager;
use Jalno\AAA\Models\Type;
use Jalno\AAA\TypeManager;

class TypeManagerTest extends TestCase
{
    public function testSearch()
    {
        $type1 = Type::factory()->createOne();
        $type2 = Type::factory()->createOne();
        $type3 = Type::factory()->createOne();
        $typeManager = app(ITypeManager::class);
        $result = $typeManager->search([
            'id' => $type2->id,
        ]);
        $this->assertEquals([$type2->id], array_column(iterator_to_array($result), 'id'));
        $result = $typeManager->search([
            'title' => $type1->title,
        ]);
        $this->assertEquals([$type1->id], array_column(iterator_to_array($result), 'id'));

        $this->assertSame($type3->id, TypeManager::getTypeId($type3));
        $this->assertSame($type3->id, TypeManager::getTypeId($type3->id));
    }

    public function testFindOrFail()
    {
        $type = Type::factory()->createOne();
        $typeManager = app(ITypeManager::class);

        $result = $typeManager->find($type->id);
        $this->assertSame($type->id, $result->id);

        $result = $typeManager->findOrFail($type->id);
        $this->assertSame($type->id, $result->id);
    }

    public function testGetAllAbilities()
    {
        $type1 = Type::factory()->createOne();
        $type1->abilities()->createMany([
            ['name' => 'ability-1'],
            ['name' => 'ability-2'],
        ]);
        $type2 = Type::factory()->createOne();
        $type2->abilities()->createMany([
            ['name' => 'ability-1'],
            ['name' => 'ability-3'],
        ]);

        $typeManager = app(ITypeManager::class);
        $this->assertEqualsCanonicalizing(['ability-1', 'ability-2', 'ability-3'], $typeManager->getAllAbilities());
    }

    public function testParenting()
    {
        $typeParent = Type::factory()->createOne();
        $typeChild = Type::factory()->createOne();
        $typeParent->children()->attach($typeChild);
        $otherType = Type::factory()->createOne();

        $typeManager = app(ITypeManager::class);
        $this->assertFalse($typeManager->isChildOf($typeParent->id, $typeParent->id));
        $this->assertFalse($typeManager->isChildOf($otherType, $typeParent));
        $this->assertTrue($typeManager->isChildOf($typeChild, $typeParent));
        $this->assertFalse($typeManager->isChildOf($typeParent, $typeChild));
        $this->assertTrue($typeManager->isParentOf($typeParent, $typeChild));
        $this->assertFalse($typeManager->isParentOf($typeChild, $typeParent));
        $this->assertFalse($typeManager->isParentOf($otherType->id, $typeParent->id));
        $this->assertFalse($typeManager->isParentOf($typeParent->id, $otherType->id));
    }

    public function testStore()
    {
        $typeManager = app(ITypeManager::class);

        $this->expectException(\LogicException::class);
        $typeManager->store([]);
    }

    public function testUpdate()
    {
        $type = Type::factory()->createOne();
        $typeManager = app(ITypeManager::class);

        $this->expectException(\LogicException::class);
        $typeManager->update($type, []);
    }

    public function testDestroy()
    {
        $type = Type::factory()->createOne();
        $typeManager = app(ITypeManager::class);

        $this->expectException(\LogicException::class);
        $typeManager->destroy($type);
    }
}
