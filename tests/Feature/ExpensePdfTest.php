<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Expense;
use App\Models\Income;
use App\Models\IncomeCategory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpensePdfTest extends TestCase
{
    use RefreshDatabase;

    public function test_free_plan_user_can_download_pdf(): void
    {
        $user = User::factory()->create([
            'plan' => User::PLAN_FREE,
            'is_admin' => false,
        ]);

        $response = $this->actingAs($user)->get(route('expenses.pdf'));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
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

    public function test_user_can_download_pdf_for_single_month(): void
    {
        $user = User::factory()->create(['plan' => User::PLAN_BASIC]);
        $category = Category::create(['user_id' => $user->id, 'name' => 'Food', 'color' => '#10b981']);

        Expense::create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'amount' => 120.00,
            'paid_to' => 'Cafe',
            'payment_method' => 'UPI',
            'expense_date' => Carbon::parse('2026-08-15 14:00:00'),
        ]);

        $response = $this->actingAs($user)->get(route('expenses.pdf', ['month' => '2026-08']));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
        $this->assertStringContainsString('kharchify-statement-2026-08.pdf', $response->headers->get('content-disposition'));
    }

    public function test_user_can_download_pdf_with_presets(): void
    {
        $user = User::factory()->create(['plan' => User::PLAN_BASIC]);

        $response = $this->actingAs($user)->get(route('expenses.pdf', ['preset' => 'this_month']));
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');

        $responseLastMonth = $this->actingAs($user)->get(route('expenses.pdf', ['preset' => 'last_month']));
        $responseLastMonth->assertStatus(200);

        $responseQuarter = $this->actingAs($user)->get(route('expenses.pdf', ['preset' => 'last_3_months']));
        $responseQuarter->assertStatus(200);

        $responseYear = $this->actingAs($user)->get(route('expenses.pdf', ['preset' => 'this_year']));
        $responseYear->assertStatus(200);
    }

    public function test_user_can_download_pdf_for_custom_date_range(): void
    {
        $user = User::factory()->create(['plan' => User::PLAN_BASIC]);

        $response = $this->actingAs($user)->get(route('expenses.pdf', [
            'start_date' => '2026-08-01',
            'end_date' => '2026-08-25',
        ]));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
        $this->assertStringContainsString('kharchify-statement-20260801-to-20260825.pdf', $response->headers->get('content-disposition'));
    }

    public function test_user_can_download_pdf_with_statement_types(): void
    {
        $user = User::factory()->create(['plan' => User::PLAN_BASIC]);
        $incCat = IncomeCategory::create(['user_id' => $user->id, 'name' => 'Salary', 'icon' => '💼', 'color' => '#10b981']);

        Income::create([
            'user_id' => $user->id,
            'income_category_id' => $incCat->id,
            'source' => 'Client Payment',
            'amount' => 5000.00,
            'payment_method' => 'Bank Transfer',
            'income_date' => now(),
        ]);

        // 1. All statement
        $responseAll = $this->actingAs($user)->get(route('expenses.pdf', ['statement_type' => 'all']));
        $responseAll->assertStatus(200);

        // 2. Expenses only
        $responseExp = $this->actingAs($user)->get(route('expenses.pdf', ['statement_type' => 'expenses']));
        $responseExp->assertStatus(200);

        // 3. Incomes only
        $responseInc = $this->actingAs($user)->get(route('expenses.pdf', ['statement_type' => 'incomes']));
        $responseInc->assertStatus(200);
    }
}
