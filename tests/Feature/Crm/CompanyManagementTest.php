<?php

namespace Tests\Feature\Crm;

use App\Models\Company;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CompanyManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $this->actingAs(User::factory()->create());
    }

    public function test_index_lists_companies_ten_per_page_with_employee_counts(): void
    {
        Company::factory()->count(15)->create();
        Employee::factory()->count(3)->for(Company::factory()->create(['name' => 'Zeta Corp']))->create();

        $this->get(route('companies.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('companies/Index')
                ->has('companies.data', 10)
                ->where('companies.total', 16)
                ->where('companies.last_page', 2)
                ->has('companies.data.0.employees_count'));

        $this->get(route('companies.index', ['page' => 2]))
            ->assertInertia(fn (Assert $page) => $page->has('companies.data', 6));
    }

    public function test_index_can_be_searched(): void
    {
        Company::factory()->create(['name' => 'FNXperts Sdn Bhd']);
        Company::factory()->count(5)->create();

        $this->get(route('companies.index', ['search' => 'fnxperts']))
            ->assertInertia(fn (Assert $page) => $page
                ->has('companies.data', 1)
                ->where('companies.data.0.name', 'FNXperts Sdn Bhd')
                ->where('filters.search', 'fnxperts'));
    }

    public function test_company_can_be_created_with_a_logo_stored_on_the_public_disk(): void
    {
        $response = $this->post(route('companies.store'), [
            'name' => 'FNXperts Sdn Bhd',
            'email' => 'HR@Xperts.my',
            'website' => 'xperts.my',
            'logo' => UploadedFile::fake()->image('logo.png', 150, 150),
        ]);

        $company = Company::firstOrFail();

        $response->assertRedirect(route('companies.show', $company));
        $this->assertSame('hr@xperts.my', $company->email);
        $this->assertSame('https://xperts.my', $company->website);
        $this->assertStringStartsWith('logos/', $company->logo);
        Storage::disk('public')->assertExists($company->logo);
        $this->assertStringContainsString('/storage/logos/', $company->logo_url);
    }

    public function test_only_the_name_is_required(): void
    {
        $this->post(route('companies.store'), ['name' => 'Minimal Co'])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('companies', ['name' => 'Minimal Co', 'email' => null, 'logo' => null]);
    }

    public function test_store_validates_input(): void
    {
        $this->post(route('companies.store'), [
            'name' => '',
            'email' => 'not-an-email',
            'website' => 'not a url',
        ])->assertSessionHasErrors(['name', 'email', 'website']);

        $this->assertDatabaseCount('companies', 0);
    }

    public function test_logo_must_be_at_least_100_by_100(): void
    {
        $this->post(route('companies.store'), [
            'name' => 'Tiny Logo Inc',
            'logo' => UploadedFile::fake()->image('logo.png', 99, 120),
        ])->assertSessionHasErrors(['logo' => 'The logo must be at least 100×100 pixels.']);

        $this->assertDatabaseCount('companies', 0);
    }

    public function test_logo_must_be_an_image(): void
    {
        $this->post(route('companies.store'), [
            'name' => 'Pdf Logo Inc',
            'logo' => UploadedFile::fake()->create('logo.pdf', 10, 'application/pdf'),
        ])->assertSessionHasErrors('logo');
    }

    public function test_show_displays_the_company_with_paginated_employees(): void
    {
        $company = Company::factory()->create();
        Employee::factory()->count(12)->for($company)->create();

        $this->get(route('companies.show', $company))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('companies/Show')
                ->where('company.id', $company->id)
                ->where('company.employees_count', 12)
                ->has('employees.data', 10)
                ->where('employees.total', 12));
    }

    public function test_company_can_be_updated_and_the_old_logo_is_replaced(): void
    {
        $company = Company::factory()->create([
            'logo' => UploadedFile::fake()->image('old.png', 120, 120)->store('logos', 'public'),
        ]);
        $oldLogo = $company->logo;

        $this->put(route('companies.update', $company), [
            'name' => 'Renamed Co',
            'logo' => UploadedFile::fake()->image('new.png', 200, 200),
        ])->assertRedirect(route('companies.show', $company));

        $company->refresh();
        $this->assertSame('Renamed Co', $company->name);
        $this->assertNotSame($oldLogo, $company->logo);
        Storage::disk('public')->assertMissing($oldLogo);
        Storage::disk('public')->assertExists($company->logo);
    }

    public function test_logo_can_be_removed_on_update(): void
    {
        $company = Company::factory()->create([
            'logo' => UploadedFile::fake()->image('old.png', 120, 120)->store('logos', 'public'),
        ]);
        $oldLogo = $company->logo;

        $this->put(route('companies.update', $company), [
            'name' => $company->name,
            'remove_logo' => '1',
        ])->assertSessionHasNoErrors();

        $this->assertNull($company->refresh()->logo);
        Storage::disk('public')->assertMissing($oldLogo);
    }

    public function test_deleting_a_company_removes_its_logo_and_unassigns_employees(): void
    {
        $company = Company::factory()->create([
            'logo' => UploadedFile::fake()->image('logo.png', 120, 120)->store('logos', 'public'),
        ]);
        $employee = Employee::factory()->for($company)->create();

        $this->delete(route('companies.destroy', $company))
            ->assertRedirect(route('companies.index'));

        $this->assertModelMissing($company);
        Storage::disk('public')->assertMissing($company->logo);
        $this->assertNull($employee->refresh()->company_id);
    }

    public function test_create_and_edit_pages_render(): void
    {
        $company = Company::factory()->create();

        $this->get(route('companies.create'))
            ->assertInertia(fn (Assert $page) => $page->component('companies/Create'));

        $this->get(route('companies.edit', $company))
            ->assertInertia(fn (Assert $page) => $page
                ->component('companies/Edit')
                ->where('company.name', $company->name));
    }
}
