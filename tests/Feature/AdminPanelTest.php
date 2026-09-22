<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Category;
use App\Models\Expense;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_from_admin_panel(): void
    {
        $response = $this->get(route('admin.dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_non_admin_user_cannot_access_admin_panel(): void
    {
        $user = User::factory()->create([
            'is_admin' => false,
            'plan' => User::PLAN_FREE,
        ]);

        $response = $this->actingAs($user)->get(route('admin.dashboard'));
        $response->assertRedirect('/dashboard');
        $response->assertSessionHas('warning');
    }

    public function test_admin_user_can_access_admin_dashboard(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
            'plan' => User::PLAN_PRO,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Admin Dashboard');
        $response->assertSee('Kharchify');
    }

    public function test_admin_can_view_users_list(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $regularUser = User::factory()->create(['name' => 'Alice Test', 'email' => 'alice@test.com', 'is_admin' => false]);

        $response = $this->actingAs($admin)->get(route('admin.users.index'));
        $response->assertStatus(200);
        $response->assertSee('Alice Test');
        $response->assertSee('alice@test.com');
    }

    public function test_admin_can_view_user_details(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create(['name' => 'Bob Test', 'plan' => User::PLAN_FREE]);

        $category = Category::create([
            'user_id' => $user->id,
            'name' => 'Tech Gadgets',
            'color' => '#06b6d4',
        ]);

        Expense::create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'amount' => 4999.00,
            'paid_to' => 'Amazon India',
            'payment_method' => 'Credit Card',
            'expense_date' => now(),
            'note' => 'Wireless Headphones',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.users.show', $user->id));
        $response->assertStatus(200);
        $response->assertSee('Bob Test');
        $response->assertSee('Amazon India');
        $response->assertSee('Tech Gadgets');
    }

    public function test_admin_can_change_user_plan(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create(['plan' => User::PLAN_FREE]);

        $response = $this->actingAs($admin)->post(route('admin.users.update-plan', $user->id), [
            'plan' => User::PLAN_PRO,
        ]);

        $response->assertRedirect();
        $this->assertEquals(User::PLAN_PRO, $user->fresh()->plan);
    }

    public function test_admin_can_toggle_user_admin_privileges(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create(['is_admin' => false]);

        $response = $this->actingAs($admin)->post(route('admin.users.toggle-admin', $user->id));
        $response->assertRedirect();
        $this->assertTrue($user->fresh()->is_admin);

        // Toggle back
        $response = $this->actingAs($admin)->post(route('admin.users.toggle-admin', $user->id));
        $response->assertRedirect();
        $this->assertFalse($user->fresh()->is_admin);
    }

    public function test_admin_can_view_analytics_and_system(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $responseAnalytics = $this->actingAs($admin)->get(route('admin.analytics'));
        $responseAnalytics->assertStatus(200);
        $responseAnalytics->assertSee('Platform Financials');

        $responseSystem = $this->actingAs($admin)->get(route('admin.system'));
        $responseSystem->assertStatus(200);
        $responseSystem->assertSee('System Health');
    }

    public function test_make_admin_artisan_command(): void
    {
        $user = User::factory()->create(['email' => 'promote.me@example.com', 'is_admin' => false]);

        $this->artisan('make:admin', ['email' => 'promote.me@example.com'])
            ->assertSuccessful();

        $this->assertTrue($user->fresh()->is_admin);
    }
}
