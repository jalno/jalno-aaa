<?php

namespace dnj\AAA\Tests\Feature;

use dnj\AAA\GateHook;
use dnj\AAA\Models\Type;
use dnj\AAA\Models\TypeAbility;
use dnj\AAA\Models\User;
use dnj\AAA\Tests\TestCase;
use Illuminate\Auth\Access\Response;
use Illuminate\Contracts\Auth\Access\Authorizable;
use Illuminate\Support\Facades\Gate;

class GateHookTest extends TestCase
{
    public function testGates(): void
    {
        $adminType = Type::factory()
            ->has(TypeAbility::factory()->withName('blog_read'), 'abilities')
            ->has(TypeAbility::factory()->withName('blog_write'), 'abilities')
            ->create();

        $admin = User::factory()
            ->withType($adminType)
            ->create();

        $this->assertTrue(Gate::forUser($admin)->inspect('blog_read')->allowed());
        $this->assertFalse(Gate::forUser($admin)->inspect('blog_delete')->allowed());
    }

    public function testInvoke(): void
    {
        $adminType = Type::factory()
            ->has(TypeAbility::factory()->withName('blog_read'), 'abilities')
            ->has(TypeAbility::factory()->withName('blog_write'), 'abilities')
            ->create();

        $admin = User::factory()
            ->withType($adminType)
            ->create();

        $hook = $this->app->make(GateHook::class);

        $response = Response::deny();
        $response2 = $hook->__invoke(\Mockery::mock(Authorizable::class), 'ability', $response);
        $this->assertInstanceOf(Response::class, $response2);
        $this->assertSame($response->allowed(), $response2->allowed());

        $response = $hook->__invoke(\Mockery::mock(Authorizable::class), 'ability', false);
        $this->assertFalse($response);

        $response = $hook->__invoke(\Mockery::mock(Authorizable::class), 'ability', true);
        $this->assertTrue($response);

        $response = $hook->__invoke(\Mockery::mock(Authorizable::class), 'ability', null);
        $this->assertNull($response);
    }

    public function testGuest(): void
    {
        $guestType = Type::factory()
            ->has(TypeAbility::factory()->withName('blog_read'), 'abilities')
            ->create();
        config()->set('aaa.guestType', $guestType->getId());

        $this->assertTrue(Gate::inspect('blog_read')->allowed());
        $this->assertFalse(Gate::inspect('blog_write')->allowed());

        config()->set('aaa.guestType', null);
    }
}
