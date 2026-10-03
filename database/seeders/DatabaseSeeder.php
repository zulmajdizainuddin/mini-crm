<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(AdminUserSeeder::class);

        // Demo data is only seeded outside production.
        if (! app()->isProduction()) {
            $this->call(DemoDataSeeder::class);
        }
    }
}
