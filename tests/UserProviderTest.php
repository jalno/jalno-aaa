<?php

namespace Jalno\AAA\Tests;

use Illuminate\Support\Facades\Auth;
use Jalno\AAA\Models\User;
use Jalno\AAA\UserProvider;

class UserProviderTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        config()->set('auth.providers.users.driver', 'jalno-aaa');
    }

    public function testSuccessLogin(): void
    {
        $user = User::factory()->createOne();

        $result = Auth::attempt([
            'username' => $user->email,
            'password' => 'password',
        ]);

        $this->assertTrue($result);
        $user = Auth::getUser();
        $this->assertInstanceOf(User::class, $user);
    }

    public function testWrongPassword(): void
    {
        $user = User::factory()->createOne();
        $result = Auth::attempt(['username' => $user->cellphone, 'password' => '1234']);
        $this->assertFalse($result);
    }

    public function testWrongUsername(): void
    {
        $result = Auth::attempt(['username' => 'wrong@username.net', 'password' => '1234']);
        $this->assertFalse($result);
    }

    public function testRetrieveById(): void
    {
        $userProvider = app(UserProvider::class);
        $this->assertNull($userProvider->retrieveById(-1));

        $user = User::factory()->createOne();
        $this->assertSame($user->id, $userProvider->retrieveById($user->id)->id);
    }

    public function testRememberToken(): void
    {
        $userProvider = app(UserProvider::class);
        $user = User::factory()->createOne();
        $userProvider->updateRememberToken($user, '123');
        $this->assertSame('123', $user->remember_token);
        $this->assertNull($userProvider->retrieveByToken($user->getId(), ''));
        $this->assertSame($user->id, $userProvider->retrieveByToken($user->getId(), '123')?->id);
    }

    public function testRetrieveByCredentials(): void
    {
        $userProvider = app(UserProvider::class);

        $this->assertNull($userProvider->retrieveByCredentials([]));
        $this->assertNull($userProvider->retrieveByCredentials(['username' => 'a@a.net']));

        $user = User::factory()->createOne();
        $this->assertSame($user->id, $userProvider->retrieveByCredentials([
            'username' => $user->email,
        ])->getId());
    }
}
