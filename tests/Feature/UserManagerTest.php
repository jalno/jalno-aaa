<?php

namespace dnj\AAA\Tests\Feature;

use dnj\AAA\Contracts\UserStatus;
use dnj\AAA\Models\Type;
use dnj\AAA\Models\TypeAbility;
use dnj\AAA\Models\User;
use dnj\AAA\Models\Username;
use dnj\AAA\Tests\TestCase;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\MultipleRecordsFoundException;
use Illuminate\Support\Facades\Hash;

class UserManagerTest extends TestCase
{
    public function testStore(): void
    {
        $type = Type::factory()
            ->has(TypeAbility::factory(5), 'abilities')
            ->create();
        $user = $this->getUserManager()->store(
            'Alex',
            'alex@gmail.com',
            Hash::make('123'),
            $type,
            ['k1' => 'v1'],
            true,
        );
        $this->assertDatabaseHas($user->getTable(), ['id' => $user->getId()]);
        $this->assertSame('Alex', $user->getName());
        $this->assertSame(['k1' => 'v1'], $user->getMeta());
        $this->assertSame($type->getId(), $user->getTypeId());
        $this->assertSame(UserStatus::ACTIVE, $user->getStatus());
        $this->assertCount(1, $user->usernames);
        $this->assertSame('alex@gmail.com', $user->usernames[0]->getUsername());
        $this->assertTrue($user->usernames[0]->verifyPassword('123'));
        $this->assertFalse($user->usernames[0]->verifyPassword('1234'));
        $this->assertEqualsCanonicalizing($type->getAbilities(), $user->getAbilities());
        $this->assertNotNull($user->getCreatedAt());
        $this->assertNotNull($user->getUpdatedAt());
        $this->assertNull($user->getPingAt());
    }

    public function testStoreDuplicateUsername(): void
    {
        $username = Username::factory()->create();
        $type = Type::factory()->create();

        $this->expectException(MultipleRecordsFoundException::class);
        $this->getUserManager()->store(
            'Alex',
            $username->getUsername(),
            Hash::make('123'),
            $type,
        );
    }

    public function testUpdate(): void
    {
        $user = User::factory()
            ->has(Username::factory(2), 'usernames')
            ->create();
        $newType = Type::factory()->create();

        $changes = [
            'name' => 'John Doe',
            'type' => $newType,
            'status' => UserStatus::SUSPEND,
            'usernames' => [
                $user->usernames[1]->username => [],
                'john@example.com' => [
                    'password' => Hash::make('321'),
                ],
            ],
        ];
        $user = $this->getUserManager()->update($user->getId(), $changes, true);
        $this->assertSame($changes['name'], $user->getName());
        $this->assertSame($newType->getId(), $user->getTypeId());
        $this->assertSame($changes['status'], $user->getStatus());
        $this->assertCount(2, $user->usernames);
        $this->assertDatabaseHas(Username::class, ['username' => 'john@example.com', 'user_id' => $user->getId()]);
    }

    public function testDestroy(): void
    {
        $user = User::factory()->create();

        $this->getUserManager()->destroy($user, true);
        $this->assertModelMissing($user);
    }

    public function testFind(): void
    {
        $this->assertNull($this->getUserManager()->find(-1));

        $user = User::factory()->create();
        $this->assertSame($user->getId(), $this->getUserManager()->find($user->getId())->getId());
        $this->assertSame($user->getId(), $this->getUserManager()->findOrFail($user->getId())->getId());

        $this->expectException(ModelNotFoundException::class);
        $this->getUserManager()->findOrFail(-1);

        $username = Username::factory()->withUser($user)->create();
        $this->assertSame($user->getId(), $this->getUserManager()->findByUsername($username->getUsername())->getId());
    }

    public function testFindUsername(): void
    {
        $this->assertNull($this->getUserManager()->findByUsername('@@'));

        $username = Username::factory()->create();
        $this->assertSame($username->getUserID(), $this->getUserManager()->findByUsername($username->getUsername())->getId());
        $this->assertSame($username->getUserID(), $this->getUserManager()->findByUsernameOrFail($username->getUsername())->getId());

        $this->expectException(ModelNotFoundException::class);
        $this->getUserManager()->findByUsernameOrFail('@@');
    }

    public function testPing(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $this->assertFalse($user1->isOnline());
        $this->assertFalse($user2->isOnline());
        $this->getUserManager()->ping($user1);
        $user1->refresh();
        $user2->refresh();
        $this->assertTrue($user1->isOnline());
        $this->assertFalse($user2->isOnline());

        $count = $this->getUserManager()->count();
        $this->assertSame(2, $count);

        $users = $this->getUserManager()->search(['online' => true]);
        $this->assertCount(1, $users);
        $this->assertSame($user1->getId(), $users[0]->getId());
        $count = $this->getUserManager()->count(['online' => true]);
        $this->assertSame(1, $count);

        $users = $this->getUserManager()->search(['online' => false]);
        $this->assertCount(1, $users);
        $this->assertSame($user2->getId(), $users[0]->getId());
        $count = $this->getUserManager()->count(['online' => false]);
        $this->assertSame(1, $count);
    }

    public function testPingInvalidUser(): void
    {
        $this->expectException(ModelNotFoundException::class);
        $this->getUserManager()->ping(0);
    }
}
