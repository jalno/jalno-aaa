<?php

namespace Jalno\AAA\Tests;

use dnj\AAA\Contracts\IUserManager;
use Jalno\AAA\Models\Type;
use Jalno\AAA\Models\User;
use Jalno\AAA\UserManager;

class UserManagerTest extends TestCase
{
    public function testSearch()
    {
        $user1 = User::factory()->createOne();
        $user2 = User::factory()->createOne();
        $user3 = User::factory()->createOne();
        $userManager = app(IUserManager::class);
        $result = $userManager->search([
            'id' => $user2->id,
        ]);
        $this->assertEquals([$user2->id], array_column(iterator_to_array($result), 'id'));
        $result = $userManager->search([
            'type_id' => $user1->type_id,
        ]);
        $this->assertEquals([$user1->id], array_column(iterator_to_array($result), 'id'));

        $this->assertSame(3, $userManager->count());

        $this->assertSame($user3->id, UserManager::getUserId($user3));
        $this->assertSame($user3->id, UserManager::getUserId($user3->id));
    }

    public function testFindOrFail()
    {
        $user = User::factory()->createOne();
        $userManager = app(IUserManager::class);

        $result = $userManager->find($user->id);
        $this->assertSame($user->id, $result->id);

        $result = $userManager->findOrFail($user->id);
        $this->assertSame($user->id, $result->id);
    }

    public function testFindByUsername()
    {
        $user = User::factory()->createOne();
        $userManager = app(IUserManager::class);
        $result = $userManager->findByUsername($user->cellphone);
        $this->assertSame($user->id, $result->id);

        $result = $userManager->findByUsernameOrFail($user->email);
        $this->assertSame($user->id, $result->id);
    }

    public function testParenting()
    {
        $typeParent = Type::factory()->createOne();
        $typeChild = Type::factory()->createOne();
        $typeParent->children()->attach($typeChild);

        $userParent = User::factory()->withType($typeParent)->createOne();
        $userChild = User::factory()->withType($typeChild)->createOne();
        $otherUser = User::factory()->createOne();

        $userManager = app(IUserManager::class);
        $this->assertFalse($userManager->isChildOf($userParent->id, $userParent->id));
        $this->assertFalse($userManager->isChildOf($otherUser, $userParent));
        $this->assertTrue($userManager->isChildOf($userChild, $userParent));
        $this->assertFalse($userManager->isChildOf($userParent, $userChild));
        $this->assertTrue($userManager->isParentOf($userParent, $userChild));
        $this->assertFalse($userManager->isParentOf($userChild, $userParent));
        $this->assertFalse($userManager->isParentOf($otherUser->id, $userParent->id));
        $this->assertFalse($userManager->isParentOf($userParent->id, $otherUser->id));
    }

    public function testPing()
    {
        $user = User::factory()->create();
        $oldPing = $user->lastonline;

        $userManager = app(IUserManager::class);
        $userManager->ping($user);
        $user->refresh();

        $this->assertGreaterThan($oldPing, $user->lastonline);
    }

    public function testStore()
    {
        $userManager = app(IUserManager::class);

        $this->expectException(\LogicException::class);
        $userManager->store(fake()->name(), fake()->email(), '', Type::factory()->createOne(), []);
    }

    public function testUpdate()
    {
        $user = User::factory()->createOne();
        $userManager = app(IUserManager::class);

        $this->expectException(\LogicException::class);
        $userManager->update($user, []);
    }

    public function testDestroy()
    {
        $user = User::factory()->createOne();
        $userManager = app(IUserManager::class);

        $this->expectException(\LogicException::class);
        $userManager->destroy($user);
    }
}
