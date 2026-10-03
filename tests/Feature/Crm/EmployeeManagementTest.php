<?php

namespace Tests\Feature\Crm;

use App\Models\Company;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class EmployeeManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    public function test_index_lists_employees_ten_per_page_with_their_company(): void
    {
        Employee::factory()->count(23)->create();

        $this->get(route('employees.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('employees/Index')
                ->has('employees.data', 10)
                ->where('employees.total', 23)
                ->where('employees.last_page', 3)
                ->has('employees.data.0.company.name')
                ->has('companies', 23));

        $this->get(route('employees.index', ['page' => 3]))
            ->assertInertia(fn (Assert $page) => $page->has('employees.data', 3));
    }

    public function test_index_can_be_filtered_by_company_and_search(): void
    {
        $acme = Company::factory()->create();
        Employee::factory()->for($acme)->create(['first_name' => 'Aisyah', 'last_name' => 'Rahman']);
        Employee::factory()->for($acme)->create(['first_name' => 'Daniel', 'last_name' => 'Lim']);
        Employee::factory()->count(4)->create();

        $this->get(route('employees.index', ['company' => $acme->id]))
            ->assertInertia(fn (Assert $page) => $page->has('employees.data', 2));

        $this->get(route('employees.index', ['company' => $acme->id, 'search' => 'aisyah']))
            ->assertInertia(fn (Assert $page) => $page
                ->has('employees.data', 1)
                ->where('employees.data.0.full_name', 'Aisyah Rahman'));
    }

    public function test_employee_can_be_created(): void
    {
        $company = Company::factory()->create();

        $response = $this->post(route('employees.store'), [
            'first_name' => ' Siti ',
            'last_name' => 'Aminah',
            'company_id' => $company->id,
            'email' => 'Siti@Example.com',
            'phone' => '+60 12-345 6789',
        ]);

        $employee = Employee::firstOrFail();
        $response->assertRedirect(route('employees.show', $employee));

        $this->assertSame('Siti', $employee->first_name);
        $this->assertSame('siti@example.com', $employee->email);
        $this->assertTrue($employee->company->is($company));
        $this->assertSame(1, $company->employees()->count());
    }

    public function test_first_and_last_name_are_required(): void
    {
        $this->post(route('employees.store'), [
            'first_name' => '',
            'last_name' => '',
        ])->assertSessionHasErrors(['first_name', 'last_name']);

        $this->assertDatabaseCount('employees', 0);
    }

    public function test_company_email_and_phone_are_validated(): void
    {
        $this->post(route('employees.store'), [
            'first_name' => 'Ali',
            'last_name' => 'Abu',
            'company_id' => 999,
            'email' => 'nope',
            'phone' => 'call me maybe',
        ])->assertSessionHasErrors(['company_id', 'email', 'phone']);
    }

    public function test_employee_without_company_is_allowed(): void
    {
        $this->post(route('employees.store'), [
            'first_name' => 'Free',
            'last_name' => 'Lancer',
            'company_id' => '',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('employees', ['first_name' => 'Free', 'company_id' => null]);
    }

    public function test_employee_can_be_updated(): void
    {
        $employee = Employee::factory()->create();
        $newCompany = Company::factory()->create();

        $this->put(route('employees.update', $employee), [
            'first_name' => 'Updated',
            'last_name' => 'Name',
            'company_id' => $newCompany->id,
        ])->assertRedirect(route('employees.show', $employee));

        $employee->refresh();
        $this->assertSame('Updated Name', $employee->full_name);
        $this->assertSame($newCompany->id, $employee->company_id);
    }

    public function test_employee_can_be_deleted(): void
    {
        $employee = Employee::factory()->create();

        $this->delete(route('employees.destroy', $employee))
            ->assertRedirect(route('employees.index'));

        $this->assertModelMissing($employee);
    }

    public function test_create_page_preselects_a_company_from_the_query_string(): void
    {
        $company = Company::factory()->create();

        $this->get(route('employees.create', ['company' => $company->id]))
            ->assertInertia(fn (Assert $page) => $page
                ->component('employees/Create')
                ->where('defaultCompanyId', $company->id));
    }

    public function test_show_and_edit_pages_render(): void
    {
        $employee = Employee::factory()->create();

        $this->get(route('employees.show', $employee))
            ->assertInertia(fn (Assert $page) => $page
                ->component('employees/Show')
                ->where('employee.company.id', $employee->company_id));

        $this->get(route('employees.edit', $employee))
            ->assertInertia(fn (Assert $page) => $page->component('employees/Edit'));
    }
}
