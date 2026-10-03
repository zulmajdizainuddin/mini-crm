<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Employee;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    /**
     * Sample companies and employees so pagination (10 per page) is visible
     * straight away. Run with: php artisan db:seed --class=DemoDataSeeder
     */
    public function run(): void
    {
        Company::factory()
            ->count(24)
            ->withLogo()
            ->create()
            ->each(function (Company $company): void {
                Employee::factory()
                    ->count(fake()->numberBetween(0, 14))
                    ->for($company)
                    ->create();
            });

        Employee::factory()->count(3)->unassigned()->create();
    }
}
