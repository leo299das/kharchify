<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpensePdfTest extends TestCase
{
    use RefreshDatabase;

    public function test_free_plan_user_is_redirected_when_downloading_pdf(): void
    {
        $user = User::factory()->create([
            'plan' => User::PLAN_FREE,
            'is_admin' => false,
        ]);

        $response = $this->actingAs($user)->get(route('expenses.pdf'));

        $response->assertRedirect(route('plans.show'));
        $response->assertSessionHas('error');
    }

    public function test_basic_plan_user_can_download_pdf(): void
    {
        $user = User::factory()->create([
            'plan' => User::PLAN_BASIC,
        ]);

        $category = Category::create([
            'user_id' => $user->id,
            'name' => 'Food & Dining',
            'color' => '#10b981',
        ]);

        Expense::create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'amount' => 250.00,
            'paid_to' => 'Swiggy',
            'payment_method' => 'UPI / GPay / PhonePe',
            'expense_date' => now(),
            'note' => 'Lunch order',
        ]);

        $response = $this->actingAs($user)->get(route('expenses.pdf'));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }
}
