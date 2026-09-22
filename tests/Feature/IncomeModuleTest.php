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

class IncomeModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_from_incomes(): void
    {
        $response = $this->get('/incomes');
        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_income_index(): void
    {
        $user = User::factory()->create(['plan' => 'basic']);

        // Create some sample income
        Income::create([
            'user_id' => $user->id,
            'source' => 'Sold Old Phone on OLX',
            'amount' => 5500.00,
            'payment_method' => 'UPI / GPay / PhonePe / Paytm',
            'income_date' => Carbon::today(),
            'note' => 'Transferred via UPI',
        ]);

        $response = $this->actingAs($user)->get('/incomes');

        $response->assertStatus(200);
        $response->assertSee('Income & Earnings', false);
        $response->assertSee('Sold Old Phone on OLX', false);
        $response->assertSee('5,500.00', false);
        $response->assertSee('+ Add Income', false);
    }

    public function test_user_can_create_new_income_entry(): void
    {
        $user = User::factory()->create(['plan' => 'pro']);
        $category = IncomeCategory::create([
            'name' => 'Sales & Products',
            'icon' => '📦',
            'color' => '#10b981',
        ]);

        $response = $this->actingAs($user)->post('/incomes', [
            'amount' => 650.00,
            'source' => 'Sold Handmade Craft item',
            'income_category_id' => $category->id,
            'payment_method' => 'Cash',
            'income_date' => '2026-09-22',
            'note' => 'Cash received on delivery',
            'transaction_id' => 'CASH-991',
        ]);

        $response->assertRedirect('/incomes');
        $response->assertSessionHas('success');

        $income = Income::where('source', 'Sold Handmade Craft item')->first();
        $this->assertNotNull($income);
        $this->assertEquals(650.00, $income->amount);
        $this->assertEquals($user->id, $income->user_id);
        $this->assertEquals('Cash', $income->payment_method);
        $this->assertEquals('CASH-991', $income->transaction_id);
    }

    public function test_user_can_edit_and_update_income_entry(): void
    {
        $user = User::factory()->create(['plan' => 'pro']);
        $income = Income::create([
            'user_id' => $user->id,
            'source' => 'Freelance Web Design',
            'amount' => 12000.00,
            'payment_method' => 'Bank Transfer / IMPS / NEFT',
            'income_date' => Carbon::today(),
            'note' => 'Milestone 1',
        ]);

        $response = $this->actingAs($user)->get('/incomes/' . $income->id . '/edit');
        $response->assertStatus(200);
        $response->assertSee('Edit Earning / Income');
        $response->assertSee('Freelance Web Design');

        $updateResponse = $this->actingAs($user)->put('/incomes/' . $income->id, [
            'amount' => 15000.00,
            'source' => 'Freelance Web Design (Full Pay)',
            'payment_method' => 'Bank Transfer / IMPS / NEFT',
            'income_date' => Carbon::today()->format('Y-m-d'),
            'note' => 'Milestone 1 + 2 completed',
            'transaction_id' => 'HDFC-882190',
        ]);

        $updateResponse->assertRedirect('/incomes');

        $income->refresh();
        $this->assertEquals(15000.00, $income->amount);
        $this->assertEquals('Freelance Web Design (Full Pay)', $income->source);
        $this->assertEquals('HDFC-882190', $income->transaction_id);
    }

    public function test_user_cannot_edit_or_delete_another_users_income(): void
    {
        $user1 = User::factory()->create(['plan' => 'pro']);
        $user2 = User::factory()->create(['plan' => 'pro']);

        $income = Income::create([
            'user_id' => $user1->id,
            'source' => 'Secret Freelance Gig',
            'amount' => 45000.00,
            'payment_method' => 'Cash',
            'income_date' => Carbon::today(),
        ]);

        $response = $this->actingAs($user2)->get('/incomes/' . $income->id . '/edit');
        $response->assertStatus(404);

        $deleteResponse = $this->actingAs($user2)->delete('/incomes/' . $income->id);
        $deleteResponse->assertStatus(404);

        $this->assertDatabaseHas('incomes', ['id' => $income->id]);
    }

    public function test_user_can_delete_their_income(): void
    {
        $user = User::factory()->create(['plan' => 'pro']);
        $income = Income::create([
            'user_id' => $user->id,
            'source' => 'Mistaken Entry',
            'amount' => 500.00,
            'payment_method' => 'Cash',
            'income_date' => Carbon::today(),
        ]);

        $response = $this->actingAs($user)->delete('/incomes/' . $income->id);
        $response->assertRedirect('/incomes');

        $this->assertDatabaseMissing('incomes', ['id' => $income->id]);
    }

    public function test_user_model_cash_flow_methods_compute_net_savings_accurately(): void
    {
        $user = User::factory()->create(['plan' => 'pro']);
        $cat = Category::create(['name' => 'Bills', 'user_id' => $user->id]);

        // Incomes
        Income::create([
            'user_id' => $user->id,
            'source' => 'Monthly Salary',
            'amount' => 50000.00,
            'payment_method' => 'Bank Transfer / IMPS / NEFT',
            'income_date' => Carbon::now(),
        ]);
        Income::create([
            'user_id' => $user->id,
            'source' => 'Sold Old Cycle',
            'amount' => 3000.00,
            'payment_method' => 'Cash',
            'income_date' => Carbon::now(),
        ]);

        // Expenses
        Expense::create([
            'user_id' => $user->id,
            'category_id' => $cat->id,
            'paid_to' => 'Apartment Landlord',
            'amount' => 15000.00,
            'payment_method' => 'UPI',
            'expense_date' => Carbon::now(),
        ]);
        Expense::create([
            'user_id' => $user->id,
            'category_id' => $cat->id,
            'paid_to' => 'Grocery Store',
            'amount' => 5000.00,
            'payment_method' => 'Card',
            'expense_date' => Carbon::now(),
        ]);

        $this->assertEquals(53000.00, $user->totalIncome());
        $this->assertEquals(20000.00, $user->totalExpense());
        $this->assertEquals(53000.00, $user->thisMonthIncome());
        $this->assertEquals(20000.00, $user->thisMonthExpense());
        $this->assertEquals(33000.00, $user->thisMonthNetSavings());
        $this->assertEquals(33000.00, $user->allTimeNetSavings());
    }

    public function test_dashboard_renders_income_and_net_savings_widgets(): void
    {
        $user = User::factory()->create(['plan' => 'medium']);
        $cat = Category::create(['name' => 'Utilities', 'user_id' => $user->id]);

        Income::create([
            'user_id' => $user->id,
            'source' => 'Sold Laptop',
            'amount' => 25000.00,
            'payment_method' => 'UPI',
            'income_date' => Carbon::now(),
        ]);

        Expense::create([
            'user_id' => $user->id,
            'category_id' => $cat->id,
            'paid_to' => 'Electricity Bill',
            'amount' => 2500.00,
            'payment_method' => 'UPI',
            'expense_date' => Carbon::now(),
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Total Earnings', false);
        $response->assertSee('Total Spent', false);
        $response->assertSee('Net Cash Flow', false);
        $response->assertSee('25,000', false);
        $response->assertSee('2,500', false);
        $response->assertSee('Recent Earnings', false);
        $response->assertSee('Sold Laptop', false);
    }
}
