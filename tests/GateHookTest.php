<?php

namespace Jalno\AAA\Tests;

use Illuminate\Support\Facades\Gate;
use Jalno\AAA\Models\Type;
use Jalno\AAA\Models\TypeAbility;
use Jalno\AAA\Models\User;

class GateHookTest extends TestCase
{
    public function testGates(): void
    {
        $adminType = Type::factory()
            ->has(TypeAbility::factory()->withName('blog_read'), 'abilities')
            ->has(TypeAbility::factory()->withName('blog_write'), 'abilities')
            ->createOne();

        $admin = User::factory()
            ->withType($adminType)
            ->createOne();

        $this->assertTrue(Gate::forUser($admin)->inspect('blog_read')->allowed());
        $this->assertFalse(Gate::forUser($admin)->inspect('blog_delete')->allowed());
    }
}
