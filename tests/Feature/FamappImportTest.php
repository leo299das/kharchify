<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Expense;
use App\Models\Income;

class FamappImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_famapp_statement_imports_accurately_with_exact_sums(): void
    {
        // Run artisan command
        $this->artisan('import:famapp-transactions', [
            '--email' => 'darakshaan475@gmail.com',
            '--name' => 'Darakshaan Afzal Hussain',
        ])->assertSuccessful();

        $user = User::where('email', 'darakshaan475@gmail.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('Darakshaan Afzal Hussain', $user->name);
        $this->assertEquals('pro', $user->plan);

        // Check Incomes: 70 records, total exactly ₹16,036.00
        $incomes = Income::where('user_id', $user->id)->get();
        $this->assertCount(70, $incomes);
        $this->assertEquals(16036.00, round((float) $incomes->sum('amount'), 2));

        // Check Expenses: 82 records, total exactly ₹15,648.99
        $expenses = Expense::where('user_id', $user->id)->get();
        $this->assertCount(82, $expenses);
        $this->assertEquals(15648.99, round((float) $expenses->sum('amount'), 2));

        // Check Net Savings: 16036.00 - 15648.99 = 387.01
        $this->assertEquals(387.01, $user->allTimeNetSavings());

        // Test Idempotence: Running again does not duplicate records
        $this->artisan('import:famapp-transactions', [
            '--email' => 'darakshaan475@gmail.com',
        ])->assertSuccessful();

        $this->assertCount(70, Income::where('user_id', $user->id)->get());
        $this->assertCount(82, Expense::where('user_id', $user->id)->get());
    }
}
