<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Expense;
use App\Models\Income;

class SohelImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_sohel_famapp_statement_imports_accurately_with_exact_sums(): void
    {
        // Run artisan command
        $this->artisan('import:sohel-statement', [
            '--email' => 'mujawarsohel849@gmail.com',
            '--name' => 'Sohel Imran Mujawar',
        ])->assertSuccessful();

        $user = User::where('email', 'mujawarsohel849@gmail.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('Sohel Imran Mujawar', $user->name);
        $this->assertEquals('pro', $user->plan);

        // Check Incomes: 197 records, total exactly ₹25,206.55 (page 1: Total Deposit)
        $incomes = Income::where('user_id', $user->id)->get();
        $this->assertCount(197, $incomes);
        $this->assertEquals(25206.55, round((float) $incomes->sum('amount'), 2));

        // Check Expenses: 239 records, total exactly ₹25,204.74 (page 1: Total Withdrawn)
        $expenses = Expense::where('user_id', $user->id)->get();
        $this->assertCount(239, $expenses);
        $this->assertEquals(25204.74, round((float) $expenses->sum('amount'), 2));

        // Check Net Savings: 25206.55 - 25204.74 = 1.81
        $this->assertEquals(1.81, $user->allTimeNetSavings());

        // Test Idempotence: Running again does not duplicate records
        $this->artisan('import:sohel-statement', [
            '--email' => 'mujawarsohel849@gmail.com',
        ])->assertSuccessful();

        $this->assertCount(197, Income::where('user_id', $user->id)->get());
        $this->assertCount(239, Expense::where('user_id', $user->id)->get());
    }
}
