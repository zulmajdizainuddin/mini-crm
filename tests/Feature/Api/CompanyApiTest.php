<?php

namespace Tests\Feature\Api;

use App\Models\Company;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CompanyApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_token_can_be_issued_with_valid_credentials(): void
    {
        User::factory()->create(['email' => 'admin@admin.com', 'password' => 'password']);

        $response = $this->postJson('/api/v1/auth/token', [
            'email' => 'admin@admin.com',
            'password' => 'password',
            'device_name' => 'postman',
        ]);

        $response->assertCreated()
            ->assertJsonStructure(['token_type', 'access_token', 'user' => ['id', 'name', 'email']]);

        $this->getJson('/api/v1/companies', [
            'Authorization' => 'Bearer '.$response->json('access_token'),
        ])->assertOk();
    }

    public function test_invalid_credentials_are_rejected(): void
    {
        User::factory()->create(['email' => 'admin@admin.com', 'password' => 'password']);

        $this->postJson('/api/v1/auth/token', [
            'email' => 'admin@admin.com',
            'password' => 'wrong',
        ])->assertUnprocessable()->assertJsonValidationErrors('email');
    }

    public function test_token_can_be_revoked(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $this->deleteJson('/api/v1/auth/token', [], ['Authorization' => "Bearer {$token}"])
            ->assertNoContent();

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_api_requires_authentication(): void
    {
        $company = Company::factory()->create();

        $this->getJson("/api/v1/companies/{$company->id}")->assertUnauthorized();
        $this->getJson('/api/v1/companies')->assertUnauthorized();
    }

    public function test_show_returns_a_company_with_its_employees_and_employee_count(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $company = Company::factory()->create(['name' => 'FNXperts Sdn Bhd']);
        Employee::factory()->count(3)->for($company)->create();
        Employee::factory()->count(2)->create(); // belong to other companies

        $this->getJson("/api/v1/companies/{$company->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $company->id)
            ->assertJsonPath('data.name', 'FNXperts Sdn Bhd')
            ->assertJsonPath('data.employee_count', 3)
            ->assertJsonCount(3, 'data.employees')
            ->assertJsonStructure([
                'data' => [
                    'id', 'name', 'email', 'website', 'logo_url', 'employee_count',
                    'employees' => [['id', 'first_name', 'last_name', 'full_name', 'email', 'phone', 'company_id']],
                    'created_at', 'updated_at',
                ],
            ]);
    }

    public function test_show_returns_zero_employee_count_for_an_empty_company(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $company = Company::factory()->create();

        $this->getJson("/api/v1/companies/{$company->id}")
            ->assertOk()
            ->assertJsonPath('data.employee_count', 0)
            ->assertJsonCount(0, 'data.employees');
    }

    public function test_unknown_company_returns_a_clean_404(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/v1/companies/999')
            ->assertNotFound()
            ->assertExactJson(['message' => 'Resource not found.']);
    }

    public function test_index_is_paginated_and_includes_employee_count(): void
    {
        Sanctum::actingAs(User::factory()->create());
        Company::factory()->count(12)->create()->first()->employees()->createMany([
            ['first_name' => 'A', 'last_name' => 'One'],
            ['first_name' => 'B', 'last_name' => 'Two'],
        ]);

        $this->getJson('/api/v1/companies')
            ->assertOk()
            ->assertJsonCount(10, 'data')
            ->assertJsonPath('meta.total', 12)
            ->assertJsonPath('meta.per_page', 10)
            ->assertJsonMissingPath('data.0.employees')
            ->assertJsonStructure(['data' => [['id', 'name', 'employee_count']], 'links', 'meta']);
    }
}
