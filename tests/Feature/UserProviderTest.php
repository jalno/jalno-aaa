<?php

namespace dnj\AAA\Tests\Feature;

use dnj\AAA\Contracts\IUser;
use dnj\AAA\Models\User;
use dnj\AAA\Models\Username;
use dnj\AAA\Tests\TestCase;
use dnj\AAA\UserProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;

class UserProviderTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        config()->set('auth.providers.users.driver', 'aaa');
    }

    public function testSuccessLogin(): void
    {
        $password = '123';
        $username = Username::factory()
            ->withPassword($password)
            ->create();

        $result = Auth::attempt([
            'username' => $username->username,
            'password' => $password,
        ]);

        $this->assertTrue($result);
        $user = Auth::getUser();
        $this->assertInstanceOf(IUser::class, $user);
        if ($user instanceof IUser) {
            $this->assertSame($username->getUserID(), $user->getId());
        }
    }

    public function testWrongPassword(): void
    {
        $username = Username::factory()->create();
        $result = Auth::attempt(['username' => $username->username, 'password' => '1234']);
        $this->assertFalse($result);
    }

    public function testWrongUsername(): void
    {
        $result = Auth::attempt(['username' => 'wrong@username.net', 'password' => '1234']);
        $this->assertFalse($result);
    }

    public function testRetrieveById(): void
    {
        $this->assertNull($this->getUserProvider()->retrieveById(-1));

        $user = User::factory()->create();
        $this->assertSame($user->getId(), $this->getUserProvider()->retrieveById($user->getId())->getId());
    }

    public function testUpdateRememberToken(): void
    {
        $mockUser = \Mockery::mock(Authenticatable::class);
        $this->getUserProvider()->updateRememberToken($mockUser, '123');
        $this->assertTrue(true);
    }

    public function testRetrieveByCredentials(): void
    {
        $this->assertNull($this->getUserProvider()->retrieveByCredentials([]));
        $this->assertNull($this->getUserProvider()->retrieveByCredentials(['username' => 'a@a.net']));

        $username = Username::factory()->create();
        $this->assertSame($username->getUserID(), $this->getUserProvider()->retrieveByCredentials([
            'username' => $username->getUsername(),
        ])->getId());
    }

    public function testValidateCredentials(): void
    {
        $mockUser = \Mockery::mock(Authenticatable::class);
        $this->assertFalse($this->getUserProvider()->validateCredentials($mockUser, []));

        /**
         * @var Username
         */
        $username = Username::factory()->withPassword('123')->create();

        $this->assertFalse($this->getUserProvider()->validateCredentials($mockUser, [
            'username' => $username->getUsername(),
            'password' => '123',
        ]));

        $this->assertFalse($this->getUserProvider()->validateCredentials($username->getUser(), [
            'username' => 'a@a.net',
            'password' => '123',
        ]));

        $this->assertTrue($this->getUserProvider()->validateCredentials($username->getUser(), [
            'username' => $username->getUsername(),
            'password' => '123',
        ]));
    }

    public function testRetrieveByToken(): void
    {
        $user = User::factory()->create();
        $this->assertNull($this->getUserProvider()->retrieveByToken($user->getId(), ''));
    }

    protected function getUserProvider(): UserProvider
    {
        return $this->app->make(UserProvider::class);
    }
}
