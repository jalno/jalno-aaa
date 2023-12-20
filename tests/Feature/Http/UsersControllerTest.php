<?php

namespace dnj\AAA\Tests\Feature\Http;

use dnj\AAA\Contracts\IUser;
use dnj\AAA\Contracts\UserStatus;
use dnj\AAA\Models\Type;
use dnj\AAA\Models\User;
use dnj\AAA\Tests\TestCase;

class UsersControllerTest extends TestCase
{
    public function testUnauthenticated(): void
    {
        $this->getJson(route('users.index'))->assertUnauthorized();
    }

    public function testUnauthorized(): void
    {
        $this->actingAs(User::factory()->create());
        $this->getJson(route('users.index'))->assertForbidden();
    }

    public function testSearch(): void
    {
        $me = $this->createUserWithModelAbility(IUser::class, 'viewAny');

        $myChildType = Type::factory()->create();
        $myChildType->parents()->attach($me->getTypeId());

        /**
         * @var User $myChild
         */
        $myChild = User::factory()->withType($myChildType)->create();

        // Unknown User with Unknown Type
        $unknownUser = User::factory()->create();

        $this->actingAs($me);
        $response = $this->getJson(route('users.index'))->assertOk();
        $this->assertIsArray($response['data']);
        $this->assertCount(2, $response['data']);
        $this->assertEqualsCanonicalizing([$me->id, $myChild->id], array_column($response['data'], 'id'));
        $this->assertNotContains($unknownUser->id, array_column($response['data'], 'id'));
    }

    public function testSearchOnline(): void
    {
        $me = $this->createUserWithModelAbility(IUser::class, 'viewAny');

        $myChildType = Type::factory()->create();
        $myChildType->parents()->attach($me->getTypeId());

        /**
         * @var User $myChild
         */
        $myFirstChild = User::factory()->withType($myChildType)->create();
        $mySecondChild = User::factory()->withType($myChildType)->create();
        $this->getUserManager()->ping($myFirstChild);

        $this->actingAs($me);
        $response = $this->getJson(route('users.index').'?'.http_build_query(['online' => true]))->assertOk();
        $this->assertIsArray($response['data']);
        $this->assertCount(2, $response['data']);
        $this->assertEqualsCanonicalizing([$me->id, $myFirstChild->id], array_column($response['data'], 'id'));

        $response = $this->getJson(route('users.index').'?'.http_build_query(['online' => false]))->assertOk();
        $this->assertIsArray($response['data']);
        $this->assertCount(1, $response['data']);
        $this->assertSame($mySecondChild->id, $response['data'][0]['id']);
    }

    public function testShow(): void
    {
        $me = $this->createUserWithModelAbility(IUser::class, 'view');
        $this->actingAs($me);

        $this->getJson(route('users.show', ['user' => $me->id]))->assertOk();

        $myChildType = Type::factory()->create();
        $myChildType->parents()->attach($me->getTypeId());

        /**
         * @var User $myChild
         */
        $myChild = User::factory()->withType($myChildType)->create();
        $this->getJson(route('users.show', ['user' => $myChild->id]))->assertOk();

        // Unknown User with Unknown Type
        $unknownUser = User::factory()->create();
        $this->getJson(route('users.show', ['user' => $unknownUser->id]))->assertNotFound();
    }

    public function testStore(): void
    {
        $me = $this->createUserWithModelAbility(IUser::class, 'store');
        $this->actingAs($me);

        $data = [
            'name' => fake()->name(),
            'usernames' => [
                fake()->userName() => fake()->password(),
            ],
            'type_id' => $me->getTypeId(),
            'status' => UserStatus::SUSPEND->value,
        ];
        $this->postJson(route('users.store'), $data)
            ->assertCreated()
            ->assertJson([
                'data' => [
                    'name' => $data['name'],
                    'type_id' => $data['type_id'],
                    'status' => $data['status'],
                ],
            ]);
    }

    public function testUpdate(): void
    {
        $me = $this->createUserWithModelAbility(IUser::class, 'update');
        $this->actingAs($me);

        $myChildType = Type::factory()->create();
        $myChildType->parents()->attach($me->getTypeId());

        $data = [
            'name' => fake()->name(),
            'type_id' => $myChildType->id,
            'status' => UserStatus::SUSPEND->value,
        ];
        $this->putJson(route('users.update', ['user' => $me->getId()]), $data)
            ->assertOk()
            ->assertJson([
                'data' => [
                    'id' => $me->getId(),
                    'name' => $data['name'],
                    'type_id' => $data['type_id'],
                    'status' => $data['status'],
                ],
            ]);
    }

    public function testDestroy(): void
    {
        $me = $this->createUserWithModelAbility(IUser::class, 'destroy');
        $this->actingAs($me);

        $myChildType = Type::factory()->create();
        $myChildType->parents()->attach($me->getTypeId());
        $myChild = User::factory()->withType($myChildType)->create();

        $this->deleteJson(route('users.destroy', ['user' => $myChild->id]))->assertNoContent();
    }
}
