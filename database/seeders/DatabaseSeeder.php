<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Jalno\AAA\Database\Seeders\TypeSeeder;
use Jalno\AAA\Database\Seeders\UserSeeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(TypeSeeder::class);
        $this->call(UserSeeder::class);
    }
}
