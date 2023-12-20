<?php

namespace dnj\AAA\Tests\Unit;

use dnj\AAA\Models\User;
use dnj\AAA\Models\Username;
use dnj\AAA\Tests\TestCase;

class UserTest extends TestCase
{
    public function test()
    {
        /**
         * @var User
         */
        $user = User::factory()->create();

        $this->assertSame('id', $user->getAuthIdentifierName());

        $this->assertSame($user->getId(), $user->getAuthIdentifier());

        $this->assertEmpty($user->getAuthPassword());
        $username = Username::factory()
            ->withUser($user)
            ->withPassword('123')
            ->create();
        $user->refresh();
        $this->assertSame($username->password, $user->getAuthPassword());

        $username2 = Username::factory()
            ->withUser($user)
            ->withPassword('1234')
            ->create();
        $user->refresh();

        $this->assertSame($username->password, $user->getAuthPassword());
        $this->assertNotSame($username2->password, $user->getAuthPassword());

        $this->assertNull($user->getRememberToken());

        $user->setRememberToken('');
        $this->assertTrue(true);

        $this->assertEmpty($user->getRememberTokenName());
    }
}
