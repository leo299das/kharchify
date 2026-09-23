<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Expense;
use App\Models\Income;

class PavanKolheImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_pavan_kolhe_famapp_statement_imports_accurately_with_exact_sums(): void
    {
        // Run artisan command
        $this->artisan('import:pavan-kolhe-statement', [
            '--email' => 'kolhepavan52@gmail.com',
            '--name' => 'Pavan Ramdas Kolhe',
        ])->assertSuccessful();

        $user = User::where('email', 'kolhepavan52@gmail.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('Pavan Ramdas Kolhe', $user->name);
        $this->assertEquals('pro', $user->plan);

        // Check Incomes: 104 records, total exactly ₹24,790.27 (page 1: Total Deposit)
        $incomes = Income::where('user_id', $user->id)->get();
        $this->assertCount(104, $incomes);
        $this->assertEquals(24790.27, round((float) $incomes->sum('amount'), 2));

        // Check Expenses: 216 records, total exactly ₹24,792.27 (page 1: Total Withdrawn)
        $expenses = Expense::where('user_id', $user->id)->get();
        $this->assertCount(216, $expenses);
        $this->assertEquals(24792.27, round((float) $expenses->sum('amount'), 2));

        // Check Net Savings: 24790.27 - 24792.27 = -2.00
        $this->assertEquals(-2.00, $user->allTimeNetSavings());

        // Test Idempotence: Running again does not duplicate records
        $this->artisan('import:pavan-kolhe-statement', [
            '--email' => 'kolhepavan52@gmail.com',
        ])->assertSuccessful();

        $this->assertCount(104, Income::where('user_id', $user->id)->get());
        $this->assertCount(216, Expense::where('user_id', $user->id)->get());
    }
}
