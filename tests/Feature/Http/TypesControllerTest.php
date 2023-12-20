<?php

namespace dnj\AAA\Tests\Feature\Http;

use dnj\AAA\Contracts\IType;
use dnj\AAA\Contracts\IUser;
use dnj\AAA\Models\Type;
use dnj\AAA\Models\User;
use dnj\AAA\Tests\TestCase;

class TypesControllerTest extends TestCase
{
    public function testUnauthenticated(): void
    {
        $this->getJson(route('types.index'))->assertUnauthorized();
    }

    public function testUnauthorized(): void
    {
        $this->actingAs(User::factory()->create());
        $this->getJson(route('types.index'))->assertForbidden();
    }

    public function testSearch(): void
    {
        $me = $this->createUserWithModelAbility(IType::class, 'viewAny');

        $myChild = Type::factory()->create();
        $myChild->parents()->attach($me->getTypeId());

        $unknownType = Type::factory()->create();

        $this->actingAs($me);
        $response = $this->getJson(route('types.index'))->assertOk();
        $this->assertIsArray($response['data']);
        $this->assertCount(1, $response['data']);
        $this->assertEqualsCanonicalizing([$myChild->id], array_column($response['data'], 'id'));
        $this->assertNotContains($unknownType->id, array_column($response['data'], 'id'));
    }

    public function testShow(): void
    {
        $me = $this->createUserWithModelAbility(IType::class, 'view');
        $this->actingAs($me);

        $this->getJson(route('types.show', ['type' => $me->getTypeId()]))->assertNotFound();

        $me->type->updateChildIds([$me->getTypeId()]);
        $me->type->refresh();

        $this->getJson(route('types.show', ['type' => $me->getTypeId()]))->assertOk();

        $myChild = Type::factory()->create();
        $myChild->parents()->attach($me->getTypeId());
        $me->type->refresh();

        $this->getJson(route('types.show', ['type' => $myChild->id]))->assertOk();

        // Unknown Type
        $unknown = Type::factory()->create();
        $this->getJson(route('types.show', ['type' => $unknown->id]))->assertNotFound();
    }

    public function testStore(): void
    {
        $me = $this->createUserWithModelAbility(IType::class, 'store');
        $me->type->updateChildIds([$me->getTypeId()]);
        $me->type->refresh();
        $this->actingAs($me);

        $data = [
            'translates' => [
                'en' => ['title' => fake('en_US')->jobTitle()],
                'fa' => ['title' => fake('fa_IR')->jobTitle()],
                'ar' => ['title' => fake('ar_SA')->jobTitle()],
            ],
            'abilities' => [
                IType::class.'@viewAny',
                IType::class.'@view',
                IType::class.'@store',
                IType::class.'@update',
            ],
            'child_to_itself' => true,
            'children' => [$me->getTypeId()],
        ];
        $this->postJson(route('types.store'), $data)
            ->assertCreated()
            ->assertJsonStructure([
                'data' => [
                    'meta',
                    'updated_at',
                    'created_at',
                    'id',
                    'translates' => [
                        '*' => [
                            'type_id',
                            'locale',
                            'title',
                        ],
                    ],
                    'children' => [
                        '*' => [
                            'id',
                        ],
                    ],
                    'abilities',
                ],
            ]);
    }

    public function testUpdate(): void
    {
        $me = $this->createUserWithModelAbility(IType::class, 'update');
        $this->actingAs($me);

        $myChild = Type::factory()->create();
        $myChild->parents()->attach($me->getTypeId());
        $me->type->refresh();

        $data = [
            'translates' => [
                'it' => ['title' => fake('it_IT')->jobTitle()],
                'fa' => ['title' => fake('fa_IR')->jobTitle()],
            ],
            'abilities' => [
                IUser::class.'@viewAny',
                IUser::class.'@view',
                IUser::class.'@store',
                IUser::class.'@update',
            ],
            'children' => [$me->getTypeId(), $myChild->getId()],
        ];
        $this->putJson(route('types.update', ['type' => $myChild->getId()]), $data)
            ->assertOk()
            ->assertJson([
                'data' => array_merge(['id' => $myChild->getId()], $data, [
                    'children' => [
                        [
                            'id' => $me->getTypeId(),
                        ],
                        [
                            'id' => $myChild->getId(),
                        ],
                    ],
                ]),
            ]);
    }

    public function testDestroy(): void
    {
        $me = $this->createUserWithModelAbility(IType::class, 'destroy');
        $this->actingAs($me);

        $myChild = Type::factory()->create();
        $myChild->parents()->attach($me->getTypeId());
        $me->type->refresh();

        $this->deleteJson(route('types.destroy', ['type' => $myChild->id]))->assertNoContent();
    }
}
