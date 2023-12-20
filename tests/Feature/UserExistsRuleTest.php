<?php

namespace dnj\AAA\Tests\Feature;

use dnj\AAA\Models\Type;
use dnj\AAA\Models\User;
use dnj\AAA\Rules\UserExists;
use dnj\AAA\Tests\TestCase;

class UserExistsRuleTest extends TestCase
{
    public function testExisting(): void
    {
        $myType = Type::factory()
            ->has(Type::factory(1), 'children')
            ->create();
        $me = User::factory()->withType($myType)->create();
        $child = User::factory()->withType($myType->children->first())->create();

        $rule = new UserExists($this->getUserManager());
        $rule->userHasAccess($me);

        $called = false;
        $rule('user', $child->getId(), function ($message) use (&$called) {
            $called = true;
        });
        $this->assertFalse($called);
    }

    public function testNonExisting(): void
    {
        $called = false;
        $rule = new UserExists($this->getUserManager());
        $rule('user', -1, function ($message) use (&$called) {
            $called = true;
        });
        $this->assertTrue($called);
    }

    public function testInvalidId(): void
    {
        $called = false;
        $rule = new UserExists($this->getUserManager());
        $rule('user', [-1], function ($message) use (&$called) {
            $called = true;
            $this->assertStringContainsString('numeric', $message);
        });
        $this->assertTrue($called);
    }
}
