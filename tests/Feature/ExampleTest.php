<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_sees_landing_page_at_root(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Kharchify');
    }

    public function test_authenticated_user_with_plan_is_redirected_to_dashboard_from_root(): void
    {
        $user = User::factory()->create([
            'plan' => 'basic',
        ]);

        $response = $this->actingAs($user)->get('/');

        $response->assertRedirect('/dashboard');
    }

    public function test_authenticated_user_without_plan_is_redirected_to_choose_plan_from_root(): void
    {
        $user = User::factory()->create([
            'plan' => null,
        ]);

        $response = $this->actingAs($user)->get('/');

        $response->assertRedirect('/choose-plan');
    }
}
