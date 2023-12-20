<?php

namespace Jalno\AAA\Database\Seeders;

use dnj\AAA\Contracts\IType;
use dnj\AAA\Contracts\ITypeManager;
use dnj\AAA\Contracts\IUserManager;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function __construct(
        protected IUserManager $userManager,
        protected ITypeManager $typeManager,
    ) {
    }

    public function run(): void
    {
        if ($this->userManager->findByUsername('admin')) {
            return;
        }
        $this->userManager->store(
            name: 'Admin',
            username: 'admin',
            password: Hash::make('12345678'),
            type: $this->getAdminType(),
        );
    }

    protected function getAdminType(): IType
    {
        $adminTypes = collect($this->typeManager->search(['hasFullAccess' => true]));
        if ($adminTypes->isEmpty()) {
            throw new \Exception('Cannot find admin type. Please run TypeSeeder first');
        }

        return $adminTypes->first();
    }
}
