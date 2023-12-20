<?php

namespace Database\Seeders;

use Jalno\AAA\Database\Seeders\TypeSeeder;
use Jalno\AAA\Database\Seeders\UserSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(TypeSeeder::class);
        $this->call(UserSeeder::class);
    }
}
