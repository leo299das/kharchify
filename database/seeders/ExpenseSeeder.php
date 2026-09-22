<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Expense;
use App\Models\Category;
use App\Models\User;
use Carbon\Carbon;

class ExpenseSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();
        if (!$user) {
            return;
        }

        $categories = Category::where('user_id', $user->id)->get()->keyBy('name');

        if ($categories->isEmpty()) {
            return;
        }

        $sampleExpenses = [
            // Current month expenses
            [
                'paid_to' => 'Swiggy Gourmet',
                'category' => 'Food & Dining',
                'amount' => 640.00,
                'payment_method' => 'UPI / GPay / PhonePe',
                'transaction_id' => 'UPI9823412',
                'note' => 'Dinner with team',
                'expense_date' => Carbon::now()->subHours(4),
            ],
            [
                'paid_to' => 'Uber Premier',
                'category' => 'Transport & Fuel',
                'amount' => 420.00,
                'payment_method' => 'Credit Card',
                'transaction_id' => 'TXN771239',
                'note' => 'Cab to office meeting',
                'expense_date' => Carbon::now()->subDays(1)->setTime(10, 30),
            ],
            [
                'paid_to' => 'Amazon India',
                'category' => 'Shopping & Groceries',
                'amount' => 2850.00,
                'payment_method' => 'Credit Card',
                'transaction_id' => 'AMZ992812',
                'note' => 'Desk organizer & books',
                'expense_date' => Carbon::now()->subDays(3)->setTime(15, 0),
            ],
            [
                'paid_to' => 'Apartment Rent',
                'category' => 'Rent & Housing',
                'amount' => 12500.00,
                'payment_method' => 'Net Banking',
                'transaction_id' => 'NEFT8839123',
                'note' => 'Monthly flat rent',
                'expense_date' => Carbon::now()->startOfMonth()->addDays(2),
            ],
            [
                'paid_to' => 'Jio Fiber Broadband',
                'category' => 'Utilities & Bills',
                'amount' => 999.00,
                'payment_method' => 'UPI / GPay / PhonePe',
                'transaction_id' => 'UPI552199',
                'note' => 'High speed internet bill',
                'expense_date' => Carbon::now()->startOfMonth()->addDays(4),
            ],
            [
                'paid_to' => 'Netflix & Spotify',
                'category' => 'Entertainment & Leisure',
                'amount' => 799.00,
                'payment_method' => 'Credit Card',
                'transaction_id' => 'SUB441928',
                'note' => 'Monthly digital entertainment subscriptions',
                'expense_date' => Carbon::now()->startOfMonth()->addDays(5),
            ],
            [
                'paid_to' => 'Apollo Pharmacy',
                'category' => 'Health & Medical',
                'amount' => 850.00,
                'payment_method' => 'Cash',
                'transaction_id' => null,
                'note' => 'Vitamins and routine checkup medicines',
                'expense_date' => Carbon::now()->subDays(6)->setTime(18, 20),
            ],

            // Previous Month (Month - 1)
            [
                'paid_to' => 'Supermarket Groceries',
                'category' => 'Shopping & Groceries',
                'amount' => 4500.00,
                'payment_method' => 'Debit Card',
                'transaction_id' => 'POS11293',
                'note' => 'Monthly pantry & household restock',
                'expense_date' => Carbon::now()->subMonth()->setDay(10),
            ],
            [
                'paid_to' => 'Apartment Rent',
                'category' => 'Rent & Housing',
                'amount' => 12500.00,
                'payment_method' => 'Net Banking',
                'transaction_id' => 'NEFT77123',
                'note' => 'Previous month rent',
                'expense_date' => Carbon::now()->subMonth()->setDay(3),
            ],
            [
                'paid_to' => 'Electricity Board',
                'category' => 'Utilities & Bills',
                'amount' => 1840.00,
                'payment_method' => 'UPI / GPay / PhonePe',
                'transaction_id' => 'UPI99120',
                'note' => 'Power utility bill',
                'expense_date' => Carbon::now()->subMonth()->setDay(12),
            ],
            [
                'paid_to' => 'Weekend Dining & Cafe',
                'category' => 'Food & Dining',
                'amount' => 2100.00,
                'payment_method' => 'UPI / GPay / PhonePe',
                'transaction_id' => 'UPI33918',
                'note' => 'Brunch with family',
                'expense_date' => Carbon::now()->subMonth()->setDay(18),
            ],

            // Two Months Ago (Month - 2)
            [
                'paid_to' => 'Apartment Rent',
                'category' => 'Rent & Housing',
                'amount' => 12500.00,
                'payment_method' => 'Net Banking',
                'transaction_id' => 'NEFT66192',
                'note' => 'Rent',
                'expense_date' => Carbon::now()->subMonths(2)->setDay(2),
            ],
            [
                'paid_to' => 'Vehicle Maintenance & Fuel',
                'category' => 'Transport & Fuel',
                'amount' => 3200.00,
                'payment_method' => 'Credit Card',
                'transaction_id' => 'TXN99182',
                'note' => 'Full tank petrol & car wash',
                'expense_date' => Carbon::now()->subMonths(2)->setDay(14),
            ],
            [
                'paid_to' => 'Dining Out',
                'category' => 'Food & Dining',
                'amount' => 1950.00,
                'payment_method' => 'UPI / GPay / PhonePe',
                'transaction_id' => 'UPI00291',
                'note' => 'Dinner with friends',
                'expense_date' => Carbon::now()->subMonths(2)->setDay(22),
            ],

            // Three Months Ago (Month - 3)
            [
                'paid_to' => 'Apartment Rent',
                'category' => 'Rent & Housing',
                'amount' => 12500.00,
                'payment_method' => 'Net Banking',
                'transaction_id' => 'NEFT55182',
                'note' => 'Rent',
                'expense_date' => Carbon::now()->subMonths(3)->setDay(3),
            ],
            [
                'paid_to' => 'Electronics & Gadgets',
                'category' => 'Shopping & Groceries',
                'amount' => 5400.00,
                'payment_method' => 'Credit Card',
                'transaction_id' => 'TXN44910',
                'note' => 'New mechanical keyboard & mouse',
                'expense_date' => Carbon::now()->subMonths(3)->setDay(15),
            ],
        ];

        foreach ($sampleExpenses as $item) {
            $cat = $categories->get($item['category']) ?? $categories->first();
            Expense::create([
                'user_id' => $user->id,
                'category_id' => $cat->id,
                'amount' => $item['amount'],
                'paid_to' => $item['paid_to'],
                'payment_method' => $item['payment_method'],
                'transaction_id' => $item['transaction_id'],
                'note' => $item['note'],
                'expense_date' => $item['expense_date'],
            ]);
        }
    }
}
