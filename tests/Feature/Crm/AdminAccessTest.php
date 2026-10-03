<?php

namespace Tests\Feature\Crm;

use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_creates_the_default_admin_who_can_log_in(): void
    {
        $this->seed(AdminUserSeeder::class);

        $this->post(route('login.store'), [
            'email' => 'admin@admin.com',
            'password' => 'password',
        ])->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticatedAs(User::where('email', 'admin@admin.com')->first());
    }

    public function test_admin_seeder_is_idempotent(): void
    {
        $this->seed(AdminUserSeeder::class);
        $this->seed(AdminUserSeeder::class);

        $this->assertSame(1, User::where('email', 'admin@admin.com')->count());
    }

    public function test_registration_is_disabled(): void
    {
        $this->get('/register')->assertNotFound();
        $this->post('/register', [
            'name' => 'Intruder',
            'email' => 'intruder@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertNotFound();

        $this->assertDatabaseMissing('users', ['email' => 'intruder@example.com']);
    }

    public function test_home_redirects_to_the_dashboard(): void
    {
        $this->get('/')->assertRedirect('/dashboard');
    }

    public function test_guests_cannot_access_crm_pages(): void
    {
        foreach (['/companies', '/companies/create', '/employees', '/employees/create'] as $url) {
            $this->get($url)->assertRedirect(route('login'));
        }
    }
}
