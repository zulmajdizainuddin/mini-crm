<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Employee>
 */
class EmployeeFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $first = fake()->firstName();
        $last = fake()->lastName();

        return [
            'first_name' => $first,
            'last_name' => $last,
            'company_id' => Company::factory(),
            'email' => strtolower("{$first}.{$last}").fake()->unique()->numberBetween(1, 9999).'@example.com',
            'phone' => '+60 1'.fake()->numberBetween(2, 9).'-'.fake()->numerify('### ####'),
        ];
    }

    public function unassigned(): static
    {
        return $this->state(fn () => ['company_id' => null]);
    }
}
