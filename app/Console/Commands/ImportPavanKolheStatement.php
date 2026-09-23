<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Category;
use App\Models\IncomeCategory;
use App\Models\Expense;
use App\Models\Income;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class ImportPavanKolheStatement extends Command
{
    protected $signature = 'import:pavan-kolhe-statement {--email=kolhepavan52@gmail.com} {--name=Pavan Ramdas Kolhe}';
    protected $description = 'Import 29-page FamApp statement transactions (22 Sep 2025 - 23 Sep 2026) for Pavan Ramdas Kolhe';

    public function handle()
    {
        $email = $this->option('email');
        $name = $this->option('name');

        $this->info("🔍 Finding or creating user account for: {$email} ({$name})");

        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make('Kharchify@2026'),
                'plan' => 'pro',
                'monthly_budget' => 25000.00,
                'email_verified_at' => Carbon::now(),
            ]
        );

        $this->info("✅ Target User ID: {$user->id} ({$user->name})");

        // Ensure default expense categories exist for this user
        $expenseCategories = [
            'Food & Dining' => ['icon' => '🍔', 'color' => 'amber'],
            'Shopping & Groceries' => ['icon' => '🛒', 'color' => 'emerald'],
            'Utilities & Bills' => ['icon' => '⚡', 'color' => 'blue'],
            'Transport & Fuel' => ['icon' => '🚗', 'color' => 'indigo'],
            'Entertainment & Leisure' => ['icon' => '🎮', 'color' => 'purple'],
            'Health & Medical' => ['icon' => '💊', 'color' => 'rose'],
            'Transfers & Payments' => ['icon' => '💸', 'color' => 'cyan'],
            'Fees & Charges' => ['icon' => '🏷️', 'color' => 'slate'],
            'General & Services' => ['icon' => '📦', 'color' => 'slate'],
        ];

        $expCatMap = [];
        foreach ($expenseCategories as $catName => $meta) {
            $cat = Category::firstOrCreate(
                ['user_id' => $user->id, 'name' => $catName]
            );
            $expCatMap[$catName] = $cat->id;
        }

        // Ensure income categories exist
        $incomeCategories = [
            'Transfers & Friends' => ['icon' => '👥', 'color' => 'blue'],
            'Family Inflow' => ['icon' => '🏠', 'color' => 'emerald'],
            'Cashback & Refunds' => ['icon' => '🎁', 'color' => 'purple'],
            'Salary & Earnings' => ['icon' => '💰', 'color' => 'amber'],
            'Other Income' => ['icon' => '💵', 'color' => 'slate'],
        ];

        $incCatMap = [];
        foreach ($incomeCategories as $catName => $meta) {
            $cat = IncomeCategory::firstOrCreate(
                ['user_id' => $user->id, 'name' => $catName],
                ['icon' => $meta['icon'], 'color' => $meta['color']]
            );
            $incCatMap[$catName] = $cat->id;
        }

        $transactions = $this->getTransactionsData();

        $importedExpenses = 0;
        $importedIncomes = 0;
        $totalExpenseSum = 0.0;
        $totalIncomeSum = 0.0;

        foreach ($transactions as $t) {
            $type = $t['type'];
            $date = Carbon::parse($t['datetime']);
            $amount = (float) $t['amount'];
            $party = $t['party'];
            $txnId = $t['txn_id'] ?? null;
            $utr = $t['utr'] ?? null;
            $upi = $t['upi'] ?? null;
            $categoryName = $t['category'];

            $noteParts = [];
            if ($upi) $noteParts[] = "UPI: {$upi}";
            if ($utr) $noteParts[] = "UTR: {$utr}";
            if ($txnId) $noteParts[] = "Txn ID: {$txnId}";
            $note = implode(' | ', $noteParts);

            if ($type === 'expense') {
                $categoryId = $expCatMap[$categoryName] ?? $expCatMap['General & Services'];

                if ($txnId) {
                    Expense::firstOrCreate(
                        [
                            'user_id' => $user->id,
                            'transaction_id' => $txnId,
                        ],
                        [
                            'category_id' => $categoryId,
                            'amount' => $amount,
                            'paid_to' => $party,
                            'payment_method' => $upi ? 'UPI / FamApp' : 'FamApp Wallet',
                            'note' => $note,
                            'expense_date' => $date,
                        ]
                    );
                } else {
                    Expense::firstOrCreate(
                        [
                            'user_id' => $user->id,
                            'paid_to' => $party,
                            'amount' => $amount,
                            'expense_date' => $date,
                        ],
                        [
                            'category_id' => $categoryId,
                            'payment_method' => 'FamApp Wallet',
                            'note' => $note,
                        ]
                    );
                }

                $importedExpenses++;
                $totalExpenseSum += $amount;
            } else {
                $categoryId = $incCatMap[$categoryName] ?? $incCatMap['Other Income'];

                if ($txnId) {
                    Income::firstOrCreate(
                        [
                            'user_id' => $user->id,
                            'transaction_id' => $txnId,
                        ],
                        [
                            'income_category_id' => $categoryId,
                            'amount' => $amount,
                            'source' => $party,
                            'payment_method' => $upi ? 'UPI / FamApp' : 'FamApp Wallet',
                            'note' => $note,
                            'income_date' => $date,
                        ]
                    );
                } else {
                    Income::firstOrCreate(
                        [
                            'user_id' => $user->id,
                            'source' => $party,
                            'amount' => $amount,
                            'income_date' => $date,
                        ],
                        [
                            'income_category_id' => $categoryId,
                            'payment_method' => 'FamApp Wallet',
                            'note' => $note,
                        ]
                    );
                }

                $importedIncomes++;
                $totalIncomeSum += $amount;
            }
        }

        $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->info("🎉 FAMAPP 29-PAGE STATEMENT IMPORT COMPLETE");
        $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->info("👤 User:            {$user->name} ({$user->email})");
        $this->info("💳 Total Expenses:  {$importedExpenses} entries (₹" . number_format($totalExpenseSum, 2) . ")");
        $this->info("💰 Total Incomes:   {$importedIncomes} entries (₹" . number_format($totalIncomeSum, 2) . ")");
        $this->info("📊 Net Cash Flow:   ₹" . number_format($totalIncomeSum - $totalExpenseSum, 2));
        $this->info("📋 Total Logged:    " . ($importedExpenses + $importedIncomes) . " transactions");
        $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");

        return Command::SUCCESS;
    }

    public function getTransactionsData(): array
    {
        return [
            // Page 1
            ['datetime' => '2026-09-22 21:38:00', 'type' => 'expense', 'party' => 'Google Play', 'amount' => 100.00, 'upi' => 'playstore1.bd@axisbank', 'txn_id' => 'Gtxn7JpkiAWzLfaxM5L9qBgizyeHWgE51H4', 'utr' => '663170214855', 'category' => 'Entertainment & Leisure'],
            ['datetime' => '2026-09-22 21:37:00', 'type' => 'income', 'party' => 'Pavan Ramdas Kolhe', 'amount' => 100.00, 'txn_id' => 'FMPIB6652008059', 'category' => 'Transfers & Friends'],
            ['datetime' => '2026-09-22 21:31:00', 'type' => 'expense', 'party' => 'Google Play', 'amount' => 100.00, 'upi' => 'playstore1.bd@axisbank', 'txn_id' => 'Gtxny5KrU8WKx838mE3KbBNUjGbsBRB7KbD', 'utr' => '663170156577', 'category' => 'Entertainment & Leisure'],
            ['datetime' => '2026-09-22 21:27:00', 'type' => 'income', 'party' => 'Pavan Ramdas Kolhe', 'amount' => 100.00, 'txn_id' => 'FMPIB6651950538', 'category' => 'Transfers & Friends'],
            ['datetime' => '2026-09-22 12:16:00', 'type' => 'expense', 'party' => 'Pavan Ramdas Kolhe', 'amount' => 184.00, 'txn_id' => 'FMPIB6648220449', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-09-21 20:35:00', 'type' => 'expense', 'party' => 'Varad Shivaji Kadam', 'amount' => 20.00, 'upi' => '7709591519@axl', 'txn_id' => 'TOTud260921150546C0961BD2FCD140039F', 'utr' => '663061957248', 'category' => 'Transfers & Payments'],

            // Page 2
            ['datetime' => '2026-09-20 22:14:00', 'type' => 'income', 'party' => 'Pavan Ramdas Kolhe', 'amount' => 200.00, 'txn_id' => 'FMPIB6639737506', 'category' => 'Transfers & Friends'],
            ['datetime' => '2026-09-20 17:06:00', 'type' => 'expense', 'party' => 'Google Play Gift Card', 'amount' => 29.00, 'txn_id' => 'FMPIB01a0be9a-e2eb-75b1-bfde-47d4f1cf2ee2', 'category' => 'Entertainment & Leisure'],
            ['datetime' => '2026-09-20 17:06:00', 'type' => 'income', 'party' => 'Pavan Ramdas Kolhe', 'amount' => 29.00, 'txn_id' => 'FMPIB6637415763', 'category' => 'Transfers & Friends'],
            ['datetime' => '2026-09-20 15:58:00', 'type' => 'expense', 'party' => 'Tanisha Afzal Husain', 'amount' => 5.35, 'txn_id' => 'FMPIB6636958867', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-09-20 15:00:00', 'type' => 'expense', 'party' => 'Tanisha Afzal Husain', 'amount' => 25.00, 'txn_id' => 'FMPIB6636605956', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-09-20 14:59:00', 'type' => 'expense', 'party' => 'Tanisha Afzal Husain', 'amount' => 25.00, 'txn_id' => 'FMPIB6636600099', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-09-20 14:59:00', 'type' => 'expense', 'party' => 'Tanisha Afzal Husain', 'amount' => 25.00, 'txn_id' => 'FMPIB6636598384', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-09-20 14:59:00', 'type' => 'expense', 'party' => 'Tanisha Afzal Husain', 'amount' => 25.00, 'txn_id' => 'FMPIB6636596383', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-09-20 14:58:00', 'type' => 'income', 'party' => 'Pavan Ramdas Kolhe', 'amount' => 100.00, 'txn_id' => 'FMPIB6636592934', 'category' => 'Transfers & Friends'],
            ['datetime' => '2026-09-19 21:52:00', 'type' => 'expense', 'party' => 'Swiggy Instamart Private Limited', 'amount' => 192.00, 'upi' => 'swiggyinstamart@axb', 'txn_id' => 'AXBf922f8a196fd4b40a6d3f9d9fa8f8c2e', 'utr' => '662847329101', 'category' => 'Shopping & Groceries'],
            ['datetime' => '2026-09-19 21:43:00', 'type' => 'income', 'party' => 'Nivkumar Kalpeshbhai Panchal', 'amount' => 1.00, 'txn_id' => 'FMPIB6632984783', 'category' => 'Transfers & Friends'],
            ['datetime' => '2026-09-19 21:39:00', 'type' => 'income', 'party' => 'Pavan Ramdas Kolhe', 'amount' => 100.00, 'txn_id' => 'FMPIB6632957831', 'category' => 'Transfers & Friends'],
            ['datetime' => '2026-09-19 16:28:00', 'type' => 'income', 'party' => 'Varad Shivaji Kadam', 'amount' => 50.00, 'upi' => '7709591519@axl', 'txn_id' => 'FMPIB6630619423', 'utr' => '983954577433', 'category' => 'Transfers & Friends'],

            // Page 3
            ['datetime' => '2026-09-19 16:17:00', 'type' => 'expense', 'party' => 'Varad Shivaji Kadam', 'amount' => 50.00, 'upi' => '7709591519@ybl', 'txn_id' => 'TOTud260919104757766E392C91D94AE994', 'utr' => '662843918600', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-09-18 16:30:00', 'type' => 'expense', 'party' => 'Tanisha Afzal Husain', 'amount' => 10.00, 'txn_id' => 'FMPIB6624245202', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-09-18 16:30:00', 'type' => 'expense', 'party' => 'Tanisha Afzal Husain', 'amount' => 10.00, 'txn_id' => 'FMPIB6624241290', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-09-18 16:29:00', 'type' => 'expense', 'party' => 'Tanisha Afzal Husain', 'amount' => 5.00, 'txn_id' => 'FMPIB6624239686', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-09-18 16:29:00', 'type' => 'expense', 'party' => 'Tanisha Afzal Husain', 'amount' => 5.00, 'txn_id' => 'FMPIB6624237796', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-09-18 16:29:00', 'type' => 'expense', 'party' => 'Tanisha Afzal Husain', 'amount' => 10.00, 'txn_id' => 'FMPIB6624234037', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-09-18 11:31:00', 'type' => 'expense', 'party' => 'Varad Shivaji Kadam', 'amount' => 50.00, 'upi' => '7709591519@ybl', 'txn_id' => 'TOTud260918060115C8689AF4D0704C38A7', 'utr' => '662733989462', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-09-18 01:48:00', 'type' => 'expense', 'party' => 'Pavan Ramdas Kolhe', 'amount' => 200.00, 'txn_id' => 'FMPIB6620918705', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-09-18 01:48:00', 'type' => 'income', 'party' => 'Jagdish Ananda Bhagwat', 'amount' => 105.00, 'upi' => '7767032701@nyes', 'txn_id' => 'FMPIB6620917350', 'utr' => '005084688091', 'category' => 'Transfers & Friends'],
            ['datetime' => '2026-09-18 01:47:00', 'type' => 'expense', 'party' => 'Swiggy', 'amount' => 215.00, 'upi' => 'swiggy@yespay', 'txn_id' => 'YJPd1025fb2d977427fa018f3c24bbe93ad', 'utr' => '662732611613', 'category' => 'Food & Dining'],
            ['datetime' => '2026-09-18 01:34:00', 'type' => 'expense', 'party' => 'Pavan Ramdas Kolhe', 'amount' => 500.00, 'txn_id' => 'FMPIB6620890525', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-09-17 20:12:00', 'type' => 'income', 'party' => 'Ramdas Dashrath Kolhe', 'amount' => 1000.00, 'upi' => 'kolheramdas2@axl', 'txn_id' => 'FMPIB6619551447', 'utr' => '409184650559', 'category' => 'Family Inflow'],

            // Page 4
            ['datetime' => '2026-09-17 11:13:00', 'type' => 'expense', 'party' => 'Tanisha Afzal Husain', 'amount' => 8.00, 'txn_id' => 'FMPIB6616050509', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-09-16 19:30:00', 'type' => 'expense', 'party' => 'Mr Ningappa Shrimant Pujari', 'amount' => 145.00, 'upi' => 'q972829620@ybl', 'txn_id' => 'TOTud26091614001831B0977AD4624A27A8', 'utr' => '662522790157', 'category' => 'Food & Dining'],
            ['datetime' => '2026-09-16 18:20:00', 'type' => 'expense', 'party' => 'Rafik Jamaluddin Shaikh', 'amount' => 138.00, 'upi' => 'mohammadali18823@okhdfcbank', 'txn_id' => 'TOTud260916125016B5A7505767734BF4B0', 'utr' => '662521934629', 'category' => 'Food & Dining'],
            ['datetime' => '2026-09-16 17:25:00', 'type' => 'expense', 'party' => 'Swiggy Instamart', 'amount' => 539.00, 'upi' => 'swiggyinstamartecom@icici', 'txn_id' => 'TOTud26091611552330F693C9C31E414E87', 'utr' => '662521352078', 'category' => 'Shopping & Groceries'],
            ['datetime' => '2026-09-16 16:52:00', 'type' => 'expense', 'party' => 'Maruti', 'amount' => 150.00, 'upi' => '9834354600@kotak', 'txn_id' => 'TOTud2609161122483EFE6CD17C6D463998', 'utr' => '662521044309', 'category' => 'Food & Dining'],
            ['datetime' => '2026-09-16 14:17:00', 'type' => 'expense', 'party' => 'Msrtc', 'amount' => 140.00, 'upi' => 'shev.063.29683@mairtel', 'txn_id' => 'TOTud260916084707B15A3C0857EE453EAD', 'utr' => '662519817648', 'category' => 'Transport & Fuel'],
            ['datetime' => '2026-09-16 13:10:00', 'type' => 'income', 'party' => 'Ramdas Dashrath Kolhe', 'amount' => 1000.00, 'upi' => 'kolheramdas2@axl', 'txn_id' => 'FMPIB6610308387', 'utr' => '816434681317', 'category' => 'Family Inflow'],
            ['datetime' => '2026-09-15 14:36:00', 'type' => 'expense', 'party' => 'Tanisha Afzal Husain', 'amount' => 10.00, 'txn_id' => 'FMPIB6604545259', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-09-15 14:35:00', 'type' => 'expense', 'party' => 'Tanisha Afzal Husain', 'amount' => 70.00, 'txn_id' => 'FMPIB6604542507', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-09-15 14:35:00', 'type' => 'income', 'party' => 'Ramdas Dashrath Kolhe', 'amount' => 200.00, 'upi' => 'kolheramdas2@axl', 'txn_id' => 'FMPIB6604537089', 'utr' => '718933299125', 'category' => 'Family Inflow'],
            ['datetime' => '2026-09-15 13:29:00', 'type' => 'expense', 'party' => 'Vivek Umakant Kawad', 'amount' => 188.00, 'upi' => '9699680569@ybl', 'txn_id' => 'TOTud260915075954847922D7BC8A4270AE', 'utr' => '662411764728', 'category' => 'Transfers & Payments'],

            // Page 5
            ['datetime' => '2026-09-14 18:01:00', 'type' => 'expense', 'party' => 'Switee Sachin Lodhe', 'amount' => 50.00, 'txn_id' => 'FMPIB6599654707', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-09-14 17:46:00', 'type' => 'expense', 'party' => 'Ashok Laxman Kuhrade', 'amount' => 130.00, 'upi' => 'bharatpe.9p0v0n1j2f813325@unitype', 'txn_id' => 'TOTud2609141216064B63F4D632664812AD', 'utr' => '662306299789', 'category' => 'Food & Dining'],
            ['datetime' => '2026-09-14 17:23:00', 'type' => 'expense', 'party' => 'Datta Gulab Kambale', 'amount' => 137.00, 'upi' => 'dattak90146@okhdfcbank', 'txn_id' => 'FMPIB6599396004', 'utr' => '662306079420', 'category' => 'Food & Dining'],
            ['datetime' => '2026-09-14 17:03:00', 'type' => 'income', 'party' => 'Ramdas Dashrath Kolhe', 'amount' => 500.00, 'upi' => 'kolheramdas2@axl', 'txn_id' => 'FMPIB6599272870', 'utr' => '656159874665', 'category' => 'Family Inflow'],
            ['datetime' => '2026-09-10 10:43:00', 'type' => 'expense', 'party' => 'Darakshaan Afzal Hussain', 'amount' => 250.00, 'txn_id' => 'FMPIB6570346780', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-09-10 01:44:00', 'type' => 'expense', 'party' => 'Rakesh Digambar Mhat', 'amount' => 20.00, 'upi' => 'rakesh80.mhatre02-1@okaxis', 'txn_id' => 'TOTud26090920142455F0D530FFB84289A3', 'utr' => '625369926457', 'category' => 'Food & Dining'],
            ['datetime' => '2026-09-10 01:43:00', 'type' => 'expense', 'party' => 'Rakesh Digambar Mhat', 'amount' => 20.00, 'upi' => 'rakesh80.mhatre02-1@okaxis', 'txn_id' => 'TOTud260909201300C512DDAE0EDE4457B2', 'utr' => '625369925651', 'category' => 'Food & Dining'],
            ['datetime' => '2026-09-10 01:41:00', 'type' => 'expense', 'party' => 'Rakesh Digambar Mhat', 'amount' => 20.00, 'upi' => 'rakesh80.mhatre02-1@okaxis', 'txn_id' => 'TOTud260909201113F7CF29A44DC547ADA1', 'utr' => '625369924656', 'category' => 'Food & Dining'],
            ['datetime' => '2026-09-10 01:16:00', 'type' => 'expense', 'party' => 'Swiggy Instamart Private Limited', 'amount' => 171.00, 'upi' => 'swiggyinstamart@axb', 'txn_id' => 'AXB641eec32248c4bee8d7387600d1b809e', 'utr' => '625369907952', 'category' => 'Shopping & Groceries'],
            ['datetime' => '2026-09-09 14:39:00', 'type' => 'expense', 'party' => 'Switee Sachin Lodhe', 'amount' => 200.00, 'txn_id' => 'FMPIB6565028967', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-09-09 10:01:00', 'type' => 'income', 'party' => 'Ved Somnath Khandekar', 'amount' => 20.00, 'txn_id' => 'FMPIB6563522247', 'category' => 'Transfers & Friends'],

            // Page 6
            ['datetime' => '2026-09-09 08:13:00', 'type' => 'expense', 'party' => 'Ved Somnath Khandekar', 'amount' => 40.00, 'txn_id' => 'FMPIB6563045534', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-09-07 20:38:00', 'type' => 'income', 'party' => 'Samarth Nanasaheb Gore', 'amount' => 700.00, 'txn_id' => 'FMPIB6554778513', 'category' => 'Transfers & Friends'],
            ['datetime' => '2026-09-03 15:35:00', 'type' => 'expense', 'party' => 'Jagdish Ananda Bhagwat', 'amount' => 5.00, 'upi' => '7767032701@ptyes', 'txn_id' => 'TOTud260903100501A7C049B0B57744CBA5', 'utr' => '624618916968', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-09-02 13:50:00', 'type' => 'expense', 'party' => 'Darakshaan Afzal Hussain', 'amount' => 300.00, 'txn_id' => 'FMPIB6519355645', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-09-02 13:50:00', 'type' => 'income', 'party' => 'Jagdish Ananda Bhagwat', 'amount' => 300.00, 'upi' => '7767032701@ptyes', 'txn_id' => 'FMPIB6519352735', 'utr' => '313763432766', 'category' => 'Transfers & Friends'],
            ['datetime' => '2026-08-29 14:02:00', 'type' => 'expense', 'party' => 'Darakshaan Afzal Hussain', 'amount' => 500.00, 'txn_id' => 'FMPIB6494885151', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-08-29 13:59:00', 'type' => 'income', 'party' => 'Amit Baban Kolhe', 'amount' => 500.00, 'upi' => 'amitkolhe30-2@okaxis', 'txn_id' => 'FMPIB6494864663', 'utr' => '660703413707', 'category' => 'Transfers & Friends'],
            ['datetime' => '2026-08-24 15:25:00', 'type' => 'expense', 'party' => 'Darakshaan Afzal Hussain', 'amount' => 50.00, 'txn_id' => 'FMPIB6463300753', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-08-22 02:07:00', 'type' => 'expense', 'party' => 'Jagdish Ananda Bhagwat', 'amount' => 15.00, 'upi' => '7767032701@axl', 'txn_id' => 'TOTud2608212037172BC65BBFD90F4D7CB8', 'utr' => '660026864354', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-08-21 18:26:00', 'type' => 'expense', 'party' => 'Vivek Umakant Kawad', 'amount' => 824.00, 'upi' => '9699680569@ybl', 'txn_id' => 'TOTud2608211256391C875C6C6E3A4D96A3', 'utr' => '659923993223', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-08-21 18:08:00', 'type' => 'expense', 'party' => 'Vivek Umakant Kawad', 'amount' => 100.00, 'upi' => '9699680569@ybl', 'txn_id' => 'TOTud2608211238155DBE1107D9CC4144BA', 'utr' => '659923798921', 'category' => 'Transfers & Payments'],

            // Page 7
            ['datetime' => '2026-08-21 17:04:00', 'type' => 'income', 'party' => 'Ramdas Dashrath Kolhe', 'amount' => 1000.00, 'upi' => 'kolheramdas2@ybl', 'txn_id' => 'FMPIB6444726009', 'utr' => '531558636519', 'category' => 'Family Inflow'],
            ['datetime' => '2026-08-07 16:00:00', 'type' => 'expense', 'party' => 'Airtel Prepaid', 'amount' => 49.99, 'txn_id' => 'FMPIB019fdbc6-478b-73ea-b695-134ffa59f57e', 'category' => 'Utilities & Bills'],
            ['datetime' => '2026-08-07 15:59:00', 'type' => 'income', 'party' => 'Pavan Ramdas Kolhe', 'amount' => 6.00, 'txn_id' => 'FMPIB6354782737', 'category' => 'Transfers & Friends'],
            ['datetime' => '2026-08-07 15:58:00', 'type' => 'income', 'party' => 'Vivek Umakant Kawad', 'amount' => 44.00, 'upi' => '9699680569@axl', 'txn_id' => 'FMPIB6354781044', 'utr' => '656327077240', 'category' => 'Transfers & Friends'],
            ['datetime' => '2026-08-05 07:40:00', 'type' => 'expense', 'party' => 'Aditya Suhas Tambe', 'amount' => 80.00, 'upi' => '9403467011@pthdfc', 'txn_id' => 'TOTud2608050210122437982A4B094C64AB', 'utr' => '621700905139', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-08-05 07:39:00', 'type' => 'income', 'party' => 'Pavan Ramdas Kolhe', 'amount' => 80.00, 'txn_id' => 'FMPIB6339938840', 'category' => 'Transfers & Friends'],
            ['datetime' => '2026-08-04 22:01:00', 'type' => 'expense', 'party' => 'Switee Sachin Lodhe', 'amount' => 100.00, 'txn_id' => 'FMPIB6338887672', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-08-04 22:01:00', 'type' => 'income', 'party' => 'Pavan Ramdas Kolhe', 'amount' => 100.00, 'txn_id' => 'FMPIB6338885895', 'category' => 'Transfers & Friends'],
            ['datetime' => '2026-08-04 19:18:00', 'type' => 'expense', 'party' => 'Vivek Umakant Kawad', 'amount' => 200.00, 'upi' => '9699680569@ybl', 'txn_id' => 'TOTud260804134837964A5086C9594F7CB3', 'utr' => '658298571133', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-08-04 18:09:00', 'type' => 'expense', 'party' => 'Pavan Ramdas Kolhe', 'amount' => 36.00, 'txn_id' => 'FMPIB6337070135', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-08-04 18:09:00', 'type' => 'expense', 'party' => 'Pavan Ramdas Kolhe', 'amount' => 150.00, 'txn_id' => 'FMPIB6337067680', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-08-04 18:03:00', 'type' => 'income', 'party' => 'Pratik Raghuveer Ujgare', 'amount' => 260.00, 'upi' => '9921199878@upi', 'txn_id' => 'FMPIB6337028107', 'utr' => '180349910927', 'category' => 'Transfers & Friends'],

            // Page 8
            ['datetime' => '2026-08-04 17:50:00', 'type' => 'expense', 'party' => 'Pratik Raghuveer Ujgare', 'amount' => 230.00, 'upi' => '9921199878@upi', 'txn_id' => 'TOTud260804122027BF0E0F91388E48BBBA', 'utr' => '658297653367', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-08-04 17:12:00', 'type' => 'expense', 'party' => 'Vivek Umakant Kawad', 'amount' => 150.00, 'upi' => '9699680569@ybl', 'txn_id' => 'TOTud2608041142446372CCAB71EE417299', 'utr' => '658297318068', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-08-04 16:32:00', 'type' => 'income', 'party' => 'Ramdas Dashrath Kolhe', 'amount' => 500.00, 'upi' => '9689012123@ptyes', 'txn_id' => 'FMPIB6336416072', 'utr' => '311750977963', 'category' => 'Family Inflow'],
            ['datetime' => '2026-08-02 18:36:00', 'type' => 'expense', 'party' => 'Vivek Umakant Kawad', 'amount' => 20.00, 'upi' => '9699680569@ybl', 'txn_id' => 'TOTud26080213060550E6D1BB2C064E48A2', 'utr' => '658083763195', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-08-02 16:04:00', 'type' => 'expense', 'party' => 'Google Play Gift Card', 'amount' => 10.00, 'txn_id' => 'FMPIB019fc209-cc7c-7e5b-b28a-89b7b8ca8ab2', 'category' => 'Entertainment & Leisure'],
            ['datetime' => '2026-08-01 03:09:00', 'type' => 'expense', 'party' => 'Google Play', 'amount' => 20.00, 'upi' => 'playstore1.bd@axisbank', 'txn_id' => 'GtxnXcRcx6GcmJFighfuF7mxRw5yy4SHJfC', 'utr' => '657971927187', 'category' => 'Entertainment & Leisure'],
            ['datetime' => '2026-07-31 13:11:00', 'type' => 'expense', 'party' => 'Nirde Sumit Chandrakant', 'amount' => 30.00, 'upi' => 'sumitnired@okhdfcbank', 'txn_id' => 'TOTud2607310741270CE32337FDDB439BAA', 'utr' => '657866751651', 'category' => 'Food & Dining'],
            ['datetime' => '2026-07-31 11:58:00', 'type' => 'expense', 'party' => 'Innovative Retail Concepts Private Limited', 'amount' => 115.00, 'upi' => 'cf.innovativeretailconcepts@cashfreensdlpb', 'txn_id' => 'TOTud260731062817C0E7D08D03C34A4A90', 'utr' => '657866267734', 'category' => 'Shopping & Groceries'],
            ['datetime' => '2026-07-30 23:07:00', 'type' => 'income', 'party' => 'Varad Shivaji Kadam', 'amount' => 150.00, 'upi' => '7709591519@ybl', 'txn_id' => 'FMPIB6304892065', 'utr' => '305102077195', 'category' => 'Transfers & Friends'],
            ['datetime' => '2026-07-30 23:04:00', 'type' => 'expense', 'party' => 'Varad Shivaji Kadam', 'amount' => 150.00, 'upi' => '7709591519@ibl', 'txn_id' => 'TOTud26073017343650B0A24EB7D24D01B6', 'utr' => '657764686932', 'category' => 'Transfers & Payments'],

            // Page 9
            ['datetime' => '2026-07-30 23:03:00', 'type' => 'expense', 'party' => 'Varad Shivaji Kadam', 'amount' => 1.00, 'upi' => '7709591519@ibl', 'txn_id' => 'TOTud260730173333A9E5DCAFE6A74D5F9E', 'utr' => '657764683683', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-07-30 22:40:00', 'type' => 'expense', 'party' => 'Varad Shivaji Kadam', 'amount' => 5.00, 'upi' => '7709591519@ybl', 'txn_id' => 'TOTud260730171038F53E5B6DD8FB4DB385', 'utr' => '657764600243', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-07-30 22:39:00', 'type' => 'expense', 'party' => 'Varad Shivaji Kadam', 'amount' => 35.00, 'upi' => '7709591519@ybl', 'txn_id' => 'TOTud26073017095242D56DD1C6934A1CB5', 'utr' => '657764597011', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-07-30 22:03:00', 'type' => 'expense', 'party' => 'Vivek Umakant Kawad', 'amount' => 30.00, 'upi' => '9699680569@ybl', 'txn_id' => 'TOTud2607301633487195394CF05C4EA2A3', 'utr' => '657764416266', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-07-30 22:03:00', 'type' => 'expense', 'party' => 'Ved Somnath Khandekar', 'amount' => 86.00, 'txn_id' => 'FMPIB6304616287', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-07-30 22:03:00', 'type' => 'expense', 'party' => 'Switee Sachin Lodhe', 'amount' => 150.00, 'txn_id' => 'FMPIB6304614544', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-07-30 20:34:00', 'type' => 'income', 'party' => 'Monika Venkateshwarlu Manimolu', 'amount' => 500.00, 'upi' => '8431480575@axl', 'txn_id' => 'FMPIB6303977479', 'utr' => '237073099491', 'category' => 'Transfers & Friends'],
            ['datetime' => '2026-07-30 14:49:00', 'type' => 'expense', 'party' => 'Vivek Umakant Kawad', 'amount' => 70.00, 'upi' => '9699680569@ybl', 'txn_id' => 'TOTud260730091912ADDA2287027741C1B9', 'utr' => '657760603044', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-07-30 14:48:00', 'type' => 'expense', 'party' => 'Ashish Mundu', 'amount' => 22.00, 'txn_id' => 'FMPIB6301446355', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-07-30 10:42:00', 'type' => 'income', 'party' => 'Google Play', 'amount' => 2.00, 'upi' => 'playstore1.bd@axisbank', 'txn_id' => 'FMPIB6300123993', 'utr' => '794723662116', 'category' => 'Cashback & Refunds'],
            ['datetime' => '2026-07-30 10:41:00', 'type' => 'income', 'party' => 'Google Play', 'amount' => 2.00, 'upi' => 'playstore1.bd@axisbank', 'txn_id' => 'FMPIB6300121667', 'utr' => '794659502116', 'category' => 'Cashback & Refunds'],

            // Page 10
            ['datetime' => '2026-07-30 10:41:00', 'type' => 'expense', 'party' => 'Google Play', 'amount' => 2.00, 'upi' => 'playstore1.bd@axisbank', 'txn_id' => 'FMPIB6300118300', 'category' => 'Entertainment & Leisure'],
            ['datetime' => '2026-07-30 10:39:00', 'type' => 'expense', 'party' => 'Google Play', 'amount' => 2.00, 'upi' => 'playstore1.bd@axisbank', 'txn_id' => 'FMPIB6300111356', 'category' => 'Entertainment & Leisure'],
            ['datetime' => '2026-07-30 09:40:00', 'type' => 'expense', 'party' => 'Aditya Suhas Tambe', 'amount' => 500.00, 'upi' => '9403467011@pthdfc', 'txn_id' => 'TOTud2607300410481528F83B5297444DBF', 'utr' => '657758743669', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-07-30 09:40:00', 'type' => 'expense', 'party' => 'Ashish Mundu', 'amount' => 5.00, 'txn_id' => 'FMPIB6299877457', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-07-30 09:29:00', 'type' => 'income', 'party' => 'Ramdas Dashrath Kolhe', 'amount' => 600.00, 'upi' => 'kolheramdas2@axl', 'txn_id' => 'FMPIB6299835388', 'utr' => '457068530475', 'category' => 'Family Inflow'],
            ['datetime' => '2026-07-30 09:27:00', 'type' => 'expense', 'party' => 'Ashish Mundu', 'amount' => 5.00, 'txn_id' => 'FMPIB6299827704', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-07-27 12:26:00', 'type' => 'expense', 'party' => 'Vivek Umakant Kawad', 'amount' => 120.00, 'upi' => '9699680569@ybl', 'txn_id' => 'TOTud2607270656493E27E7D5878F4C03B1', 'utr' => '657439175056', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-07-26 20:06:00', 'type' => 'expense', 'party' => 'Switee Sachin Lodhe', 'amount' => 100.00, 'txn_id' => 'FMPIB6278773077', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-07-26 10:54:00', 'type' => 'expense', 'party' => 'Vivek Umakant Kawad', 'amount' => 130.00, 'upi' => '9699680569@ybl', 'txn_id' => 'TOTud260726052454233A13DEC0A248159F', 'utr' => '657331620458', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-07-25 20:55:00', 'type' => 'expense', 'party' => 'Vivek Umakant Kawad', 'amount' => 10.00, 'upi' => '9699680569@ybl', 'txn_id' => 'TOTud260725152535191FF9FA4578447991', 'utr' => '657229514576', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-07-25 20:55:00', 'type' => 'expense', 'party' => 'Ved Somnath Khandekar', 'amount' => 30.00, 'txn_id' => 'FMPIB6271772444', 'category' => 'Transfers & Payments'],

            // Page 11
            ['datetime' => '2026-07-25 20:54:00', 'type' => 'expense', 'party' => 'Pratik Raghuveer Ujgare', 'amount' => 100.00, 'txn_id' => 'FMPIB6271769086', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-07-25 20:53:00', 'type' => 'income', 'party' => 'Ramdas Dashrath Kolhe', 'amount' => 500.00, 'upi' => 'kolheramdas2@axl', 'txn_id' => 'FMPIB6271754360', 'utr' => '744050492828', 'category' => 'Family Inflow'],
            ['datetime' => '2026-07-25 19:59:00', 'type' => 'expense', 'party' => 'Switee Sachin Lodhe', 'amount' => 10.00, 'txn_id' => 'FMPIB6271245626', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-07-25 19:59:00', 'type' => 'income', 'party' => 'Ved Somnath Khandekar', 'amount' => 10.00, 'txn_id' => 'FMPIB6271240366', 'category' => 'Transfers & Friends'],
            ['datetime' => '2026-07-24 13:28:00', 'type' => 'expense', 'party' => 'Switee Sachin Lodhe', 'amount' => 66.00, 'txn_id' => 'FMPIB6258721476', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-07-24 13:27:00', 'type' => 'income', 'party' => 'Pavan Ramdas Kolhe', 'amount' => 60.00, 'txn_id' => 'FMPIB6258716751', 'category' => 'Transfers & Friends'],
            ['datetime' => '2026-07-23 10:19:00', 'type' => 'expense', 'party' => 'Swiggy Instamart', 'amount' => 231.00, 'upi' => 'swiggyinstamart@icici', 'txn_id' => 'FMPIB6247948740', 'utr' => '657010904882', 'category' => 'Shopping & Groceries'],
            ['datetime' => '2026-07-22 13:30:00', 'type' => 'expense', 'party' => 'Vivek Umakant Kawad', 'amount' => 110.00, 'upi' => '9699680569@ybl', 'txn_id' => 'FMPIB6240074781', 'utr' => '656905342123', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-07-22 13:16:00', 'type' => 'expense', 'party' => 'Switee Sachin Lodhe', 'amount' => 60.00, 'txn_id' => 'FMPIB6239937372', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-07-22 13:14:00', 'type' => 'expense', 'party' => 'Switee Sachin Lodhe', 'amount' => 100.00, 'txn_id' => 'FMPIB6239916448', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-07-22 11:34:00', 'type' => 'income', 'party' => 'Mrs Jyoti Ramdas Kolhe', 'amount' => 500.00, 'upi' => '919322677010@wahdfcbank', 'txn_id' => 'FMPIB6239089519', 'utr' => '126676759439', 'category' => 'Family Inflow'],
            ['datetime' => '2026-07-22 09:19:00', 'type' => 'expense', 'party' => 'Pavan Ramdas Kolhe', 'amount' => 60.00, 'txn_id' => 'FMPIB6238205150', 'category' => 'Transfers & Payments'],

            // Page 12
            ['datetime' => '2026-07-22 09:15:00', 'type' => 'expense', 'party' => 'Vivek Umakant Kawad', 'amount' => 130.00, 'upi' => '9699680569@ybl', 'txn_id' => 'FMPIB6238183400', 'utr' => '656903943411', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-07-22 09:02:00', 'type' => 'expense', 'party' => 'Google Play Gift Card', 'amount' => 320.00, 'txn_id' => 'FMPIB019f87e2-0545-7c70-a4b1-9e074584a473', 'category' => 'Entertainment & Leisure'],
            ['datetime' => '2026-07-22 08:44:00', 'type' => 'income', 'party' => 'Ramdas Dashrath Kolhe', 'amount' => 500.00, 'upi' => '9689012123@ptyes', 'txn_id' => 'FMPIB6238013048', 'utr' => '310878646522', 'category' => 'Family Inflow'],
            ['datetime' => '2026-07-21 21:32:00', 'type' => 'expense', 'party' => 'Varad Shivaji Kadam', 'amount' => 20.00, 'upi' => '7709591519@ibl', 'txn_id' => 'FMPIB6236103051', 'utr' => '656802759529', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-07-21 21:13:00', 'type' => 'income', 'party' => 'Vivek Umakant Kawad', 'amount' => 30.00, 'upi' => '9699680569@ybl', 'txn_id' => 'FMPIB6235907185', 'utr' => '891537504375', 'category' => 'Transfers & Friends'],
            ['datetime' => '2026-07-18 14:33:00', 'type' => 'expense', 'party' => 'Google Play Gift Card', 'amount' => 160.00, 'txn_id' => 'FMPIB019f7477-cb53-7d1f-a119-8f5eaecd47e4', 'category' => 'Entertainment & Leisure'],
            ['datetime' => '2026-07-18 14:32:00', 'type' => 'income', 'party' => 'Switee Sachin Lodhe', 'amount' => 160.00, 'txn_id' => 'FMPIB6201355216', 'category' => 'Transfers & Friends'],
            ['datetime' => '2026-07-17 06:49:00', 'type' => 'expense', 'party' => 'Aditya Suhas Tambe', 'amount' => 150.00, 'upi' => '9403467011@pthdfc', 'txn_id' => 'FMPIB6187852863', 'utr' => '619868843031', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-07-14 11:08:00', 'type' => 'expense', 'party' => 'Switee Sachin Lodhe', 'amount' => 100.00, 'txn_id' => 'FMPIB6157984859', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-07-13 23:11:00', 'type' => 'expense', 'party' => 'Google Play Gift Card', 'amount' => 30.00, 'txn_id' => 'FMPIB019f5c91-ca90-7872-b585-54f5d5f48f6b', 'category' => 'Entertainment & Leisure'],
            ['datetime' => '2026-07-13 21:45:00', 'type' => 'expense', 'party' => 'Google Play Gift Card', 'amount' => 50.00, 'txn_id' => 'FMPIB019f5c43-534a-743b-aec0-45a286913feb', 'category' => 'Entertainment & Leisure'],
            ['datetime' => '2026-07-13 21:43:00', 'type' => 'expense', 'party' => 'Google Play Gift Card', 'amount' => 50.00, 'txn_id' => 'FMPIB019f5c41-60a8-732e-9cbe-cd7c0cb053dc', 'category' => 'Entertainment & Leisure'],

            // Page 13
            ['datetime' => '2026-07-13 21:43:00', 'type' => 'expense', 'party' => 'Vivek Umakant Kawad', 'amount' => 33.00, 'upi' => '9699680569@ybl', 'txn_id' => 'FMPIB6154919260', 'utr' => '619446742150', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-07-13 21:40:00', 'type' => 'expense', 'party' => 'Google Play Gift Card', 'amount' => 80.00, 'txn_id' => 'FMPIB019f5c3e-1421-79a2-9c24-5864cccdd156', 'category' => 'Entertainment & Leisure'],
            ['datetime' => '2026-07-13 21:37:00', 'type' => 'income', 'party' => 'Ramdas Dashrath Kolhe', 'amount' => 500.00, 'upi' => 'kolheramdas2@axl', 'txn_id' => 'FMPIB6154849216', 'utr' => '211076195111', 'category' => 'Family Inflow'],
            ['datetime' => '2026-07-12 21:59:00', 'type' => 'expense', 'party' => 'Vivek Umakant Kawad', 'amount' => 43.00, 'upi' => '9699680569@ybl', 'txn_id' => 'FMPIB6144311596', 'utr' => '619339765968', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-07-12 21:57:00', 'type' => 'expense', 'party' => 'Google Play Gift Card', 'amount' => 33.00, 'txn_id' => 'FMPIB019f5727-a396-7eb2-91b3-337147c87ee0', 'category' => 'Entertainment & Leisure'],
            ['datetime' => '2026-07-12 21:57:00', 'type' => 'income', 'party' => 'Vivek Umakant Kawad', 'amount' => 33.00, 'upi' => '9699680569@axl', 'txn_id' => 'FMPIB6144290951', 'utr' => '109761749942', 'category' => 'Transfers & Friends'],
            ['datetime' => '2026-07-12 21:52:00', 'type' => 'expense', 'party' => 'Google Play Gift Card', 'amount' => 12.00, 'txn_id' => 'FMPIB019f5723-4db1-7eef-8695-e13430f49cf8', 'category' => 'Entertainment & Leisure'],
            ['datetime' => '2026-07-12 21:35:00', 'type' => 'expense', 'party' => 'Vivek Umakant Kawad', 'amount' => 110.00, 'upi' => '9699680569@ybl', 'txn_id' => 'FMPIB6144034208', 'utr' => '619339587796', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-07-12 21:35:00', 'type' => 'expense', 'party' => 'Varad Shivaji Kadam', 'amount' => 50.00, 'upi' => '7709591519@ibl', 'txn_id' => 'FMPIB6144028558', 'utr' => '619339584036', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-07-12 21:28:00', 'type' => 'income', 'party' => 'Switee Sachin Lodhe', 'amount' => 100.00, 'txn_id' => 'FMPIB6143948068', 'category' => 'Transfers & Friends'],
            ['datetime' => '2026-07-11 21:33:00', 'type' => 'income', 'party' => 'Vivek Umakant Kawad', 'amount' => 114.49, 'upi' => '9699680569@ybl', 'txn_id' => 'FMPIB6132273789', 'utr' => '589165094954', 'category' => 'Transfers & Friends'],

            // Page 14
            ['datetime' => '2026-07-09 21:27:00', 'type' => 'expense', 'party' => 'Jagdish Ananda Bhagwat', 'amount' => 80.00, 'upi' => '7767032701@ibl', 'txn_id' => 'FMPIB6109483315', 'utr' => '619018039427', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-07-09 21:22:00', 'type' => 'expense', 'party' => 'Google Play Gift Card', 'amount' => 120.00, 'txn_id' => 'FMPIB019f4794-fa84-7351-b16c-2097c3f39c64', 'category' => 'Entertainment & Leisure'],
            ['datetime' => '2026-07-09 17:44:00', 'type' => 'income', 'party' => 'Ved Somnath Khandekar', 'amount' => 100.00, 'txn_id' => 'FMPIB6106014384', 'category' => 'Transfers & Friends'],
            ['datetime' => '2026-07-09 17:44:00', 'type' => 'expense', 'party' => 'Ved Somnath Khandekar', 'amount' => 100.00, 'txn_id' => 'FMPIB6106005837', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-07-08 19:32:00', 'type' => 'expense', 'party' => 'Switee Sachin Lodhe', 'amount' => 150.00, 'txn_id' => 'FMPIB6096794775', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-07-07 20:57:00', 'type' => 'income', 'party' => 'Switee Sachin Lodhe', 'amount' => 50.00, 'txn_id' => 'FMPIB6087450222', 'category' => 'Transfers & Friends'],
            ['datetime' => '2026-07-07 20:49:00', 'type' => 'expense', 'party' => 'Switee Sachin Lodhe', 'amount' => 300.00, 'txn_id' => 'FMPIB6087334553', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-07-07 20:46:00', 'type' => 'income', 'party' => 'Monika Venkateshwarlu Manimolu', 'amount' => 600.00, 'upi' => '8431480575@axl', 'txn_id' => 'FMPIB6087293783', 'utr' => '190612950825', 'category' => 'Transfers & Friends'],
            ['datetime' => '2026-07-05 14:41:00', 'type' => 'expense', 'party' => 'Switee Sachin Lodhe', 'amount' => 45.00, 'txn_id' => 'FMPIB6060673082', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-07-05 01:46:00', 'type' => 'income', 'party' => 'Switee Sachin Lodhe', 'amount' => 45.00, 'txn_id' => 'FMPIB6056276345', 'category' => 'Transfers & Friends'],
            ['datetime' => '2026-07-05 01:45:00', 'type' => 'expense', 'party' => 'Switee Sachin Lodhe', 'amount' => 45.00, 'txn_id' => 'FMPIB6056273089', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-07-04 18:23:00', 'type' => 'income', 'party' => 'Mr Ajay Raghuveer Prajapati', 'amount' => 45.00, 'upi' => '6353925477@axl', 'txn_id' => 'FMPIB6051822770', 'utr' => '844766587197', 'category' => 'Transfers & Friends'],

            // Page 15
            ['datetime' => '2026-06-29 20:52:00', 'type' => 'expense', 'party' => 'Google Play Gift Card', 'amount' => 30.00, 'txn_id' => 'FMPIB019f13f9-1a5f-7689-bfb5-607389bc952a', 'category' => 'Entertainment & Leisure'],
            ['datetime' => '2026-06-29 20:51:00', 'type' => 'income', 'party' => 'Aditya Suhas Tambe', 'amount' => 30.00, 'upi' => '9403467011@axl', 'txn_id' => 'FMPIB5998755319', 'utr' => '325003974955', 'category' => 'Transfers & Friends'],
            ['datetime' => '2026-06-28 16:31:00', 'type' => 'expense', 'party' => 'Switee Sachin Lodhe', 'amount' => 3.00, 'txn_id' => 'FMPIB5983904891', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-06-27 14:57:00', 'type' => 'expense', 'party' => 'Ved Somnath Khandekar', 'amount' => 70.00, 'txn_id' => 'FMPIB5971664166', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-06-27 14:57:00', 'type' => 'income', 'party' => 'Ved Somnath Khandekar', 'amount' => 70.00, 'txn_id' => 'FMPIB5971655052', 'category' => 'Transfers & Friends'],
            ['datetime' => '2026-06-27 14:24:00', 'type' => 'expense', 'party' => 'Ved Somnath Khandekar', 'amount' => 70.00, 'txn_id' => 'FMPIB5971317226', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-06-25 19:41:00', 'type' => 'expense', 'party' => 'Switee Sachin Lodhe', 'amount' => 70.00, 'txn_id' => 'FMPIB5953065634', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-06-24 12:44:00', 'type' => 'expense', 'party' => 'Samarth Nanasaheb Gore', 'amount' => 70.00, 'txn_id' => 'FMPIB5936979391', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-06-24 11:00:00', 'type' => 'expense', 'party' => 'Swiggy Instamart Private Limited', 'amount' => 300.00, 'upi' => 'swiggyinstamart232985.rzp@rxairtel', 'txn_id' => 'FMPIB5935982811', 'utr' => '654114623291', 'category' => 'Shopping & Groceries'],
            ['datetime' => '2026-06-24 10:57:00', 'type' => 'income', 'party' => 'Ramdas Dashrath Kolhe', 'amount' => 500.00, 'upi' => 'kolheramdas2@axl', 'txn_id' => 'FMPIB5935962718', 'utr' => '131946179559', 'category' => 'Family Inflow'],
            ['datetime' => '2026-06-23 16:29:00', 'type' => 'expense', 'party' => 'Vivek Umakant Kawad', 'amount' => 45.00, 'upi' => '9699680569@ybl', 'txn_id' => 'FMPIB5928482099', 'utr' => '654010148653', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-06-23 12:05:00', 'type' => 'expense', 'party' => 'Ved Somnath Khandekar', 'amount' => 200.00, 'txn_id' => 'FMPIB5925740149', 'category' => 'Transfers & Payments'],

            // Page 16
            ['datetime' => '2026-06-23 12:00:00', 'type' => 'expense', 'party' => 'Aditya Suhas Tambe', 'amount' => 100.00, 'upi' => '9403467011@pthdfc', 'txn_id' => 'FMPIB5925686091', 'utr' => '654008437550', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-06-23 07:32:00', 'type' => 'income', 'party' => 'Ved Somnath Khandekar', 'amount' => 200.00, 'txn_id' => 'FMPIB5923707147', 'category' => 'Transfers & Friends'],
            ['datetime' => '2026-06-23 07:30:00', 'type' => 'income', 'party' => 'Vivek Umakant Kawad', 'amount' => 100.00, 'upi' => '9699680569@ybl', 'txn_id' => 'FMPIB5923700177', 'utr' => '906240693218', 'category' => 'Transfers & Friends'],
            ['datetime' => '2026-06-18 08:15:00', 'type' => 'income', 'party' => 'Samarth Nanasaheb Gore', 'amount' => 20.00, 'txn_id' => 'FMPIB5867513107', 'category' => 'Transfers & Friends'],
            ['datetime' => '2026-06-18 07:21:00', 'type' => 'expense', 'party' => 'Vivek Umakant Kawad', 'amount' => 100.00, 'upi' => '9699680569@ybl', 'txn_id' => 'FMPIB5867275093', 'utr' => '616973412934', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-06-18 07:20:00', 'type' => 'income', 'party' => 'Pavan Ramdas Kolhe', 'amount' => 100.00, 'txn_id' => 'FMPIB5867273749', 'category' => 'Transfers & Friends'],
            ['datetime' => '2026-06-17 23:47:00', 'type' => 'expense', 'party' => 'Jagdish Ananda Bhagwat', 'amount' => 100.00, 'upi' => '7767032701@axl', 'txn_id' => 'FMPIB5866544316', 'utr' => '616873110878', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-06-17 23:44:00', 'type' => 'income', 'party' => 'Pavan Ramdas Kolhe', 'amount' => 100.00, 'txn_id' => 'FMPIB5866533251', 'category' => 'Transfers & Friends'],
            ['datetime' => '2026-06-16 13:38:00', 'type' => 'expense', 'party' => 'Jagdish Ananda Bhagwat', 'amount' => 120.00, 'upi' => '7767032701@ibl', 'txn_id' => 'FMPIB5847777431', 'utr' => '616761694654', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-06-16 13:36:00', 'type' => 'income', 'party' => 'Pavan Ramdas Kolhe', 'amount' => 100.00, 'txn_id' => 'FMPIB5847763080', 'category' => 'Transfers & Friends'],
            ['datetime' => '2026-06-14 23:34:00', 'type' => 'expense', 'party' => 'Pavan Ramdas Kolhe', 'amount' => 300.00, 'txn_id' => 'FMPIB5832540867', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-06-14 23:07:00', 'type' => 'expense', 'party' => 'Switee Sachin Lodhe', 'amount' => 200.00, 'txn_id' => 'FMPIB5832404390', 'category' => 'Transfers & Payments'],

            // Page 17
            ['datetime' => '2026-06-14 10:06:00', 'type' => 'expense', 'party' => 'Samarth Nanasaheb Gore', 'amount' => 45.00, 'txn_id' => 'FMPIB5822903647', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-06-13 21:49:00', 'type' => 'expense', 'party' => 'Vivek Umakant Kawad', 'amount' => 155.00, 'upi' => '9699680569@ybl', 'txn_id' => 'FMPIB5820262903', 'utr' => '616445334281', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-06-13 21:48:00', 'type' => 'expense', 'party' => 'Swiggy Instamart Private Limited', 'amount' => 243.00, 'upi' => 'swiggyinstamart@axb', 'txn_id' => 'FMPIB5820251139', 'utr' => '616445327000', 'category' => 'Shopping & Groceries'],
            ['datetime' => '2026-06-13 09:18:00', 'type' => 'income', 'party' => 'Ramdas Dashrath Kolhe', 'amount' => 1000.00, 'upi' => 'kolheramdas2@ybl', 'txn_id' => 'FMPIB5811073117', 'utr' => '475770766007', 'category' => 'Family Inflow'],
            ['datetime' => '2026-06-08 21:46:00', 'type' => 'expense', 'party' => 'Switee Sachin Lodhe', 'amount' => 75.00, 'txn_id' => 'FMPIB5763936253', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-06-08 21:17:00', 'type' => 'expense', 'party' => 'Samarth Nanasaheb Gore', 'amount' => 25.00, 'txn_id' => 'FMPIB5763556800', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-06-08 18:47:00', 'type' => 'income', 'party' => 'Vivek Umakant Kawad', 'amount' => 100.00, 'upi' => '9699680569@axl', 'txn_id' => 'FMPIB5760979935', 'utr' => '385089404517', 'category' => 'Transfers & Friends'],
            ['datetime' => '2026-06-02 18:32:00', 'type' => 'expense', 'party' => 'Switee Sachin Lodhe', 'amount' => 150.00, 'txn_id' => 'FMPIB5693895854', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-06-02 18:32:00', 'type' => 'income', 'party' => 'Pavan Ramdas Kolhe', 'amount' => 150.00, 'txn_id' => 'FMPIB5693891253', 'category' => 'Transfers & Friends'],
            ['datetime' => '2026-05-31 17:27:00', 'type' => 'expense', 'party' => 'Pavan Ramdas Kolhe', 'amount' => 50.00, 'txn_id' => 'FMPIB5671090508', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-05-31 17:26:00', 'type' => 'income', 'party' => 'Ved Somnath Khandekar', 'amount' => 49.79, 'txn_id' => 'FMPIB5671078813', 'category' => 'Transfers & Friends'],
            ['datetime' => '2026-05-28 13:15:00', 'type' => 'expense', 'party' => 'Vivek Umakant Kawad', 'amount' => 20.00, 'upi' => '9699680569@ybl', 'txn_id' => 'FMPIB5636446602', 'utr' => '651435118551', 'category' => 'Transfers & Payments'],

            // Page 18
            ['datetime' => '2026-05-28 01:09:00', 'type' => 'expense', 'party' => 'Vivek Umakant Kawad', 'amount' => 10.00, 'upi' => '9699680569@ybl', 'txn_id' => 'FMPIB5632975066', 'utr' => '651433144737', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-05-28 01:08:00', 'type' => 'expense', 'party' => 'Vivek Umakant Kawad', 'amount' => 100.00, 'upi' => '9699680569@ybl', 'txn_id' => 'FMPIB5632973918', 'utr' => '651433144224', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-05-25 19:41:00', 'type' => 'expense', 'party' => 'Google Play Gift Card', 'amount' => 160.00, 'txn_id' => 'FMPIB019e5f79-dbde-7615-ae3e-af175b5ead1d', 'category' => 'Entertainment & Leisure'],
            ['datetime' => '2026-05-25 18:21:00', 'type' => 'expense', 'party' => 'Vivek Umakant Kawad', 'amount' => 223.00, 'upi' => '9699680569@ybl', 'txn_id' => 'FMPIB5606418646', 'utr' => '651117066763', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-05-24 17:01:00', 'type' => 'expense', 'party' => 'Vivek Umakant Kawad', 'amount' => 311.00, 'upi' => '9699680569@ybl', 'txn_id' => 'FMPIB5594428598', 'utr' => '651009845563', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-05-24 16:22:00', 'type' => 'expense', 'party' => 'Vivek Umakant Kawad', 'amount' => 200.00, 'upi' => '9699680569@ybl', 'txn_id' => 'FMPIB5594011579', 'utr' => '651009598941', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-05-24 09:51:00', 'type' => 'income', 'party' => 'Ramdas Dashrath Kolhe', 'amount' => 1000.00, 'upi' => 'kolheramdas2@axl', 'txn_id' => 'FMPIB5590205575', 'utr' => '755488599211', 'category' => 'Family Inflow'],
            ['datetime' => '2026-05-22 17:55:00', 'type' => 'expense', 'party' => 'Gitangali Kirana Store', 'amount' => 20.00, 'upi' => '9307333848@okbizaxis', 'txn_id' => 'FMPIB5573222024', 'utr' => '614297107605', 'category' => 'Shopping & Groceries'],
            ['datetime' => '2026-05-22 10:35:00', 'type' => 'expense', 'party' => 'Switee Sachin Lodhe', 'amount' => 500.00, 'txn_id' => 'FMPIB5568624507', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-05-22 10:33:00', 'type' => 'income', 'party' => 'Mrs Jyoti Ramdas Kolhe', 'amount' => 500.00, 'upi' => '9322677010@axl', 'txn_id' => 'FMPIB5568606283', 'utr' => '646694482689', 'category' => 'Family Inflow'],

            // Page 19
            ['datetime' => '2026-05-22 10:14:00', 'type' => 'expense', 'party' => 'Mr Mukund Ramkrushna Bhaik', 'amount' => 15.00, 'upi' => 'q039940540@ybl', 'txn_id' => 'FMPIB5568444429', 'utr' => '614294184326', 'category' => 'Food & Dining'],
            ['datetime' => '2026-05-20 20:43:00', 'type' => 'expense', 'party' => 'Santosh Laxman Rasal', 'amount' => 70.00, 'upi' => 'q621318636@ybl', 'txn_id' => 'FMPIB5553828213', 'utr' => '614085388957', 'category' => 'Food & Dining'],
            ['datetime' => '2026-05-19 22:25:00', 'type' => 'expense', 'party' => 'Airtel Prepaid', 'amount' => 49.99, 'txn_id' => 'FMPIB019e4129-c96e-7170-ba53-35b522178b94', 'category' => 'Utilities & Bills'],
            ['datetime' => '2026-05-18 19:18:00', 'type' => 'expense', 'party' => 'Airtel Prepaid', 'amount' => 49.99, 'txn_id' => 'FMPIB019e3b58-d41d-7c95-ad2c-316e093df7f8', 'category' => 'Utilities & Bills'],
            ['datetime' => '2026-05-18 16:35:00', 'type' => 'expense', 'party' => 'Airtel Prepaid', 'amount' => 49.99, 'txn_id' => 'FMPIB019e3ac3-3e90-7380-b20a-2dd5ce99cb7f', 'category' => 'Utilities & Bills'],
            ['datetime' => '2026-05-18 12:00:00', 'type' => 'expense', 'party' => 'Pavan Ramdas Kolhe', 'amount' => 100.00, 'txn_id' => 'FMPIB5525305526', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-05-15 22:16:00', 'type' => 'expense', 'party' => 'Mr Ajay Raghuveer Prajapati', 'amount' => 92.00, 'upi' => '6353925477@ptyes', 'txn_id' => 'FMPIB5499856169', 'utr' => '613552557269', 'category' => 'Food & Dining'],
            ['datetime' => '2026-05-15 18:36:00', 'type' => 'expense', 'party' => 'Vivek Umakant Kawad', 'amount' => 27.00, 'upi' => '9699680569@ybl', 'txn_id' => 'FMPIB5496297113', 'utr' => '613550240305', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-05-15 17:56:00', 'type' => 'income', 'party' => 'Ramdas Dashrath Kolhe', 'amount' => 500.00, 'upi' => 'kolheramdas2@axl', 'txn_id' => 'FMPIB5495725036', 'utr' => '384117703429', 'category' => 'Family Inflow'],
            ['datetime' => '2026-05-15 16:22:00', 'type' => 'expense', 'party' => 'Airtel Prepaid', 'amount' => 26.99, 'txn_id' => 'FMPIB019e2b44-6180-7209-8fa8-181f33974a1b', 'category' => 'Utilities & Bills'],
            ['datetime' => '2026-05-15 16:22:00', 'type' => 'income', 'party' => 'Vivek Umakant Kawad', 'amount' => 1.01, 'upi' => '9699680569@ybl', 'txn_id' => 'FMPIB5494609289', 'utr' => '963110642410', 'category' => 'Transfers & Friends'],

            // Page 20
            ['datetime' => '2026-05-15 16:21:00', 'type' => 'income', 'party' => 'Vivek Umakant Kawad', 'amount' => 26.00, 'upi' => '9699680569@axl', 'txn_id' => 'FMPIB5494596925', 'utr' => '014324658139', 'category' => 'Transfers & Friends'],
            ['datetime' => '2026-05-14 17:58:00', 'type' => 'expense', 'party' => 'Vivek Umakant Kawad', 'amount' => 47.00, 'upi' => '9699680569@ybl', 'txn_id' => 'FMPIB5484736380', 'utr' => '613443356432', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-05-14 17:57:00', 'type' => 'income', 'party' => 'Switee Sachin Lodhe', 'amount' => 40.00, 'txn_id' => 'FMPIB5484732018', 'category' => 'Transfers & Friends'],
            ['datetime' => '2026-05-14 17:52:00', 'type' => 'expense', 'party' => 'Vivek Umakant Kawad', 'amount' => 40.00, 'upi' => '9699680569@ybl', 'txn_id' => 'FMPIB5484658716', 'utr' => '613443307774', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-05-14 14:37:00', 'type' => 'expense', 'party' => 'Aditya Suhas Tambe', 'amount' => 80.00, 'upi' => '9403467011@pthdfc', 'txn_id' => 'FMPIB582574423', 'utr' => '613442036301', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-05-14 11:58:00', 'type' => 'income', 'party' => 'Vivek Umakant Kawad', 'amount' => 89.99, 'upi' => '9699680569@axl', 'txn_id' => 'FMPIB5480868154', 'utr' => '215149557349', 'category' => 'Transfers & Friends'],
            ['datetime' => '2026-05-14 10:03:00', 'type' => 'income', 'party' => 'Vivek Umakant Kawad', 'amount' => 10.00, 'upi' => '9699680569@axl', 'txn_id' => 'FMPIB5479855098', 'utr' => '676414112129', 'category' => 'Transfers & Friends'],
            ['datetime' => '2026-05-13 18:27:00', 'type' => 'expense', 'party' => 'Aditya Suhas Tambe', 'amount' => 50.00, 'upi' => '9403467011@pthdfc', 'txn_id' => 'FMPIB5474260873', 'utr' => '613336999162', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-05-13 18:07:00', 'type' => 'expense', 'party' => 'Aditya Suhas Tambe', 'amount' => 50.00, 'upi' => '9403467011@pthdfc', 'txn_id' => 'FMPIB5473974104', 'utr' => '613336819483', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-05-13 18:03:00', 'type' => 'expense', 'party' => 'Aditya Suhas Tambe', 'amount' => 50.00, 'upi' => '9403467011@pthdfc', 'txn_id' => 'FMPIB5473926681', 'utr' => '613336789949', 'category' => 'Transfers & Payments'],

            // Page 21
            ['datetime' => '2026-05-13 09:42:00', 'type' => 'expense', 'party' => 'Switee Sachin Lodhe', 'amount' => 150.00, 'txn_id' => 'FMPIB5468766069', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-05-11 18:14:00', 'type' => 'expense', 'party' => 'Swiggy Instamart', 'amount' => 173.00, 'upi' => 'swiggyinstamart@icici', 'txn_id' => 'FMPIB5452134698', 'utr' => '613123592407', 'category' => 'Shopping & Groceries'],
            ['datetime' => '2026-05-08 17:00:00', 'type' => 'income', 'party' => 'Ramdas Dashrath Kolhe', 'amount' => 500.00, 'upi' => 'kolheramdas2@ybl', 'txn_id' => 'FMPIB5418165610', 'utr' => '332517848110', 'category' => 'Family Inflow'],
            ['datetime' => '2026-05-07 17:40:00', 'type' => 'expense', 'party' => 'Blinkit', 'amount' => 279.00, 'upi' => 'blinkit.payu@hdfcbank', 'txn_id' => 'FMPIB5407690027', 'utr' => '649396831186', 'category' => 'Shopping & Groceries'],
            ['datetime' => '2026-05-07 17:39:00', 'type' => 'income', 'party' => 'Vivek Umakant Kawad', 'amount' => 4.00, 'upi' => '9699680569@ybl', 'txn_id' => 'FMPIB5407679424', 'utr' => '175119078919', 'category' => 'Transfers & Friends'],
            ['datetime' => '2026-05-07 17:38:00', 'type' => 'income', 'party' => 'Yash Jagadish Bhagwat', 'amount' => 70.00, 'txn_id' => 'FMPIB5407658005', 'category' => 'Transfers & Friends'],
            ['datetime' => '2026-05-07 17:34:00', 'type' => 'income', 'party' => 'Yash Jagadish Bhagwat', 'amount' => 50.00, 'txn_id' => 'FMPIB5407613595', 'category' => 'Transfers & Friends'],
            ['datetime' => '2026-05-07 16:28:00', 'type' => 'income', 'party' => 'Aditya Suhas Tambe', 'amount' => 15.00, 'upi' => '9403467011@axl', 'txn_id' => 'FMPIB5406841716', 'utr' => '163316536655', 'category' => 'Transfers & Friends'],
            ['datetime' => '2026-05-07 16:26:00', 'type' => 'income', 'party' => 'Samarth Nanasaheb Gore', 'amount' => 39.99, 'txn_id' => 'FMPIB5406828656', 'category' => 'Transfers & Friends'],
            ['datetime' => '2026-05-06 16:46:00', 'type' => 'expense', 'party' => 'Vivek Umakant Kawad', 'amount' => 100.00, 'upi' => '9699680569@ybl', 'txn_id' => 'FMPIB5396255750', 'utr' => '649289979906', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-05-05 21:41:00', 'type' => 'expense', 'party' => 'Vivek Umakant Kawad', 'amount' => 300.00, 'upi' => '9699680569@axl', 'txn_id' => 'FMPIB5389814228', 'utr' => '649186224209', 'category' => 'Transfers & Payments'],

            // Page 22
            ['datetime' => '2026-05-05 21:34:00', 'type' => 'income', 'party' => 'Ramdas Dashrath Kolhe', 'amount' => 500.00, 'upi' => 'kolheramdas2@ybl', 'txn_id' => 'FMPIB5389731237', 'utr' => '448935939555', 'category' => 'Family Inflow'],
            ['datetime' => '2026-05-01 22:17:00', 'type' => 'expense', 'party' => 'Vivek Umakant Kawad', 'amount' => 9.00, 'upi' => '9699680569@ybl', 'txn_id' => 'FMPIB5348049247', 'utr' => '648761128043', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-04-30 23:27:00', 'type' => 'expense', 'party' => 'Blinkit', 'amount' => 165.00, 'upi' => 'paytm-blinkit@ptybl', 'txn_id' => 'FMPIB5338053665', 'utr' => '648655167712', 'category' => 'Shopping & Groceries'],
            ['datetime' => '2026-04-30 22:35:00', 'type' => 'expense', 'party' => 'Google Play Gift Card', 'amount' => 29.00, 'txn_id' => 'FMPIB019ddf5a-4364-7868-a7bf-66f9cba5d4b7', 'category' => 'Entertainment & Leisure'],
            ['datetime' => '2026-04-30 21:31:00', 'type' => 'expense', 'party' => 'Ved Somnath Khandekar', 'amount' => 200.00, 'txn_id' => 'FMPIB5337145423', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-04-30 21:27:00', 'type' => 'income', 'party' => 'Ramdas Dashrath Kolhe', 'amount' => 400.00, 'upi' => 'kolheramdas2@ybl', 'txn_id' => 'FMPIB5337093275', 'utr' => '501128062691', 'category' => 'Family Inflow'],
            ['datetime' => '2026-04-30 13:30:00', 'type' => 'expense', 'party' => 'Switee Sachin Lodhe', 'amount' => 60.00, 'txn_id' => 'FMPIB5331184443', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-04-30 10:35:00', 'type' => 'expense', 'party' => 'Samarth Nanasaheb Gore', 'amount' => 140.00, 'txn_id' => 'FMPIB5329494651', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-04-30 10:27:00', 'type' => 'income', 'party' => 'Ved Somnath Khandekar', 'amount' => 200.00, 'txn_id' => 'FMPIB5329428807', 'category' => 'Transfers & Friends'],
            ['datetime' => '2026-04-28 14:05:00', 'type' => 'expense', 'party' => 'Mansi Milind Satpute', 'amount' => 7.00, 'upi' => 'q767828986@ybl', 'txn_id' => 'FMPIB5311059865', 'utr' => '648438892054', 'category' => 'Food & Dining'],
            ['datetime' => '2026-04-27 11:40:00', 'type' => 'expense', 'party' => 'Samarth Nanasaheb Gore', 'amount' => 45.00, 'txn_id' => 'FMPIB5299381213', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-04-27 08:11:00', 'type' => 'income', 'party' => 'Switee Sachin Lodhe', 'amount' => 20.00, 'txn_id' => 'FMPIB5297859417', 'category' => 'Transfers & Friends'],

            // Page 23
            ['datetime' => '2026-04-26 00:14:00', 'type' => 'expense', 'party' => 'Blinkit', 'amount' => 122.00, 'upi' => 'blinkit.payu@hdfcbank', 'txn_id' => 'FMPIB5286840224', 'utr' => '648224411517', 'category' => 'Shopping & Groceries'],
            ['datetime' => '2026-04-26 00:08:00', 'type' => 'income', 'party' => 'Vivek Umakant Kawad', 'amount' => 40.00, 'upi' => '9699680569@ybl', 'txn_id' => 'FMPIB5286819951', 'utr' => '751944234267', 'category' => 'Transfers & Friends'],
            ['datetime' => '2026-04-26 00:00:00', 'type' => 'expense', 'party' => 'Vivek Umakant Kawad', 'amount' => 60.00, 'upi' => '9699680569@ybl', 'txn_id' => 'FMPIB5286793674', 'utr' => '648224386127', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-04-25 15:04:00', 'type' => 'expense', 'party' => 'Switee Sachin Lodhe', 'amount' => 100.00, 'txn_id' => 'FMPIB5280758788', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-04-24 20:57:00', 'type' => 'expense', 'party' => 'Vivek Umakant Kawad', 'amount' => 40.00, 'upi' => '9699680569@axl', 'txn_id' => 'FMPIB5274828420', 'utr' => '648017196558', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-04-24 18:05:00', 'type' => 'expense', 'party' => 'Airtel Prepaid', 'amount' => 620.99, 'txn_id' => 'FMPIB019dbf7d-1631-7ec8-bb23-bc52e3b40378', 'category' => 'Utilities & Bills'],
            ['datetime' => '2026-04-24 17:54:00', 'type' => 'income', 'party' => 'Ramdas Dashrath Kolhe', 'amount' => 800.00, 'upi' => 'kolheramdas2@ybl', 'txn_id' => 'FMPIB5271995020', 'utr' => '187315542022', 'category' => 'Family Inflow'],
            ['datetime' => '2026-04-23 21:10:00', 'type' => 'expense', 'party' => 'Swiggy Instamart', 'amount' => 127.00, 'upi' => 'swiggyinstamart@icici', 'txn_id' => 'FMPIB5264552553', 'utr' => '647910968589', 'category' => 'Shopping & Groceries'],
            ['datetime' => '2026-04-23 19:22:00', 'type' => 'expense', 'party' => 'Samarth Nanasaheb Gore', 'amount' => 1.00, 'txn_id' => 'FMPIB5262874207', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-04-22 18:42:00', 'type' => 'expense', 'party' => 'Samarth Nanasaheb Gore', 'amount' => 14.00, 'txn_id' => 'FMPIB5251862523', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-04-22 18:40:00', 'type' => 'expense', 'party' => 'Samarth Nanasaheb Gore', 'amount' => 80.00, 'txn_id' => 'FMPIB5251840320', 'category' => 'Transfers & Payments'],

            // Page 24
            ['datetime' => '2026-04-22 18:22:00', 'type' => 'expense', 'party' => 'Samarth Nanasaheb Gore', 'amount' => 20.00, 'txn_id' => 'FMPIB5251574653', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-04-22 17:42:00', 'type' => 'income', 'party' => 'Ved Somnath Khandekar', 'amount' => 80.00, 'txn_id' => 'FMPIB5251067085', 'category' => 'Transfers & Friends'],
            ['datetime' => '2026-04-22 17:41:00', 'type' => 'income', 'party' => 'Ved Somnath Khandekar', 'amount' => 40.00, 'txn_id' => 'FMPIB5251053075', 'category' => 'Transfers & Friends'],
            ['datetime' => '2026-04-22 17:41:00', 'type' => 'expense', 'party' => 'Samarth Nanasaheb Gore', 'amount' => 120.00, 'txn_id' => 'FMPIB5251052874', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-04-22 17:27:00', 'type' => 'expense', 'party' => 'Ved Somnath Khandekar', 'amount' => 120.00, 'txn_id' => 'FMPIB5250892626', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-04-22 17:02:00', 'type' => 'income', 'party' => 'Ramdas Dashrath Kolhe', 'amount' => 500.00, 'upi' => 'kolheramdas2@ibl', 'txn_id' => 'FMPIB5250612935', 'utr' => '171681406252', 'category' => 'Family Inflow'],
            ['datetime' => '2026-04-17 18:46:00', 'type' => 'expense', 'party' => 'Samarth Nanasaheb Gore', 'amount' => 80.00, 'txn_id' => 'FMPIB5199814185', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-04-17 04:06:00', 'type' => 'expense', 'party' => 'Airtel Prepaid', 'amount' => 49.99, 'txn_id' => 'FMPIB019d9870-b28b-7d04-a53d-300af9bd9288', 'category' => 'Utilities & Bills'],
            ['datetime' => '2026-04-16 21:00:00', 'type' => 'expense', 'party' => 'Ved Somnath Khandekar', 'amount' => 20.00, 'txn_id' => 'FMPIB5191243981', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-04-16 12:47:00', 'type' => 'expense', 'party' => 'Airtel Prepaid', 'amount' => 49.99, 'txn_id' => 'FMPIB019d9527-459e-7797-9559-1d84c6d8dea8', 'category' => 'Utilities & Bills'],
            ['datetime' => '2026-04-16 12:46:00', 'type' => 'income', 'party' => 'Ramdas Dashrath Kolhe', 'amount' => 200.00, 'upi' => 'kolheramdas2@ibl', 'txn_id' => 'FMPIB5185032457', 'utr' => '755087161253', 'category' => 'Family Inflow'],
            ['datetime' => '2026-04-14 17:34:00', 'type' => 'expense', 'party' => 'Switee Sachin Lodhe', 'amount' => 7.01, 'txn_id' => 'FMPIB5166489348', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-04-14 17:27:00', 'type' => 'income', 'party' => 'Samarth Nanasaheb Gore', 'amount' => 3.00, 'txn_id' => 'FMPIB5166402679', 'category' => 'Transfers & Friends'],

            // Page 25
            ['datetime' => '2026-04-14 17:25:00', 'type' => 'income', 'party' => 'Pavan Ramdas Kolhe', 'amount' => 4.00, 'txn_id' => 'FMPIB5166382656', 'category' => 'Transfers & Friends'],
            ['datetime' => '2026-04-13 21:27:00', 'type' => 'expense', 'party' => 'Pavan Ramdas Kolhe', 'amount' => 4.00, 'txn_id' => 'FMPIB5159421823', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-04-13 14:53:00', 'type' => 'expense', 'party' => 'Airtel Prepaid', 'amount' => 39.99, 'txn_id' => 'FMPIB019d8627-5896-7797-9d69-c9da1ec9203b', 'category' => 'Utilities & Bills'],
            ['datetime' => '2026-04-12 11:54:00', 'type' => 'expense', 'party' => 'Aditya Suhas Tambe', 'amount' => 500.00, 'upi' => '9403467011@pthdfc', 'txn_id' => 'FMPIB5141550211', 'utr' => '610236785801', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-04-12 11:20:00', 'type' => 'expense', 'party' => 'Aditya Suhas Tambe', 'amount' => 500.00, 'upi' => '9403467011@pthdfc', 'txn_id' => 'FMPIB5141207145', 'utr' => '610236576914', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-04-12 11:16:00', 'type' => 'expense', 'party' => 'Aditya Suhas Tambe', 'amount' => 500.00, 'upi' => '9403467011@pthdfc', 'txn_id' => 'FMPIB5141169799', 'utr' => '610236554135', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-04-12 08:25:00', 'type' => 'income', 'party' => 'Ramdas Dashrath Kolhe', 'amount' => 1500.00, 'upi' => 'kolheramdas2@ybl', 'txn_id' => 'FMPIB5139895081', 'utr' => '656668429837', 'category' => 'Family Inflow'],
            ['datetime' => '2026-04-12 08:12:00', 'type' => 'expense', 'party' => 'Shahzad Atik Ansari', 'amount' => 156.00, 'upi' => 'bharatpe09908803355@yesbankltd', 'txn_id' => 'FMPIB5139834987', 'utr' => '610235774619', 'category' => 'Food & Dining'],
            ['datetime' => '2026-04-09 19:01:00', 'type' => 'income', 'party' => 'Ramdas Dashrath Kolhe', 'amount' => 200.00, 'upi' => 'kolheramdas2@axl', 'txn_id' => 'FMPIB5114300465', 'utr' => '793966493814', 'category' => 'Family Inflow'],
            ['datetime' => '2026-04-08 13:04:00', 'type' => 'expense', 'party' => 'Google Play Gift Card', 'amount' => 26.00, 'txn_id' => 'FMPIB019d6c03-f80e-7485-a554-9a7558a5e2f1', 'category' => 'Entertainment & Leisure'],
            ['datetime' => '2026-04-08 13:04:00', 'type' => 'income', 'party' => 'Samarth Nanasaheb Gore', 'amount' => 26.00, 'txn_id' => 'FMPIB5099751202', 'category' => 'Transfers & Friends'],

            // Page 26
            ['datetime' => '2026-04-06 17:08:00', 'type' => 'expense', 'party' => 'Samarth Nanasaheb Gore', 'amount' => 20.00, 'txn_id' => 'FMPIB5081943164', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-04-04 19:11:00', 'type' => 'expense', 'party' => 'Samarth Nanasaheb Gore', 'amount' => 40.00, 'txn_id' => 'FMPIB5063193334', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-04-04 12:35:00', 'type' => 'expense', 'party' => 'Tikha Meetha Sweets And Namkin', 'amount' => 40.00, 'upi' => 'paytmqr6nvjpy@ptys', 'txn_id' => 'FMPIB5058738681', 'utr' => '646087167562', 'category' => 'Food & Dining'],
            ['datetime' => '2026-04-04 12:26:00', 'type' => 'income', 'party' => 'Samarth Nanasaheb Gore', 'amount' => 99.00, 'txn_id' => 'FMPIB5058656084', 'category' => 'Transfers & Friends'],
            ['datetime' => '2026-04-04 12:26:00', 'type' => 'income', 'party' => 'Samarth Nanasaheb Gore', 'amount' => 1.00, 'txn_id' => 'FMPIB5058651536', 'category' => 'Transfers & Friends'],
            ['datetime' => '2026-04-01 21:18:00', 'type' => 'expense', 'party' => 'Aditya Suhas Tambe', 'amount' => 15.67, 'upi' => '9403467011@axl', 'txn_id' => 'FMPIB5034771885', 'utr' => '645773135658', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-03-29 23:22:00', 'type' => 'expense', 'party' => 'Miss Anushka Prakash Kambale', 'amount' => 1.00, 'upi' => 'anushkakamble266@okaxis', 'txn_id' => 'FMPIB5006884463', 'utr' => '645456613080', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-03-29 18:31:00', 'type' => 'expense', 'party' => 'Samarth Nanasaheb Gore', 'amount' => 33.33, 'txn_id' => 'FMPIB5003567198', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-03-29 18:16:00', 'type' => 'income', 'party' => 'Samarth Nanasaheb Gore', 'amount' => 50.00, 'txn_id' => 'FMPIB5003374544', 'category' => 'Transfers & Friends'],
            ['datetime' => '2026-03-27 22:51:00', 'type' => 'expense', 'party' => 'Google Play Gift Card', 'amount' => 42.00, 'txn_id' => 'FMPIB019d3051-0b96-7413-9e77-b443281d3efd', 'category' => 'Entertainment & Leisure'],
            ['datetime' => '2026-03-27 22:40:00', 'type' => 'expense', 'party' => 'Google Play Gift Card', 'amount' => 180.00, 'txn_id' => 'FMPIB019d3046-966a-7a75-8aab-d747ab16a071', 'category' => 'Entertainment & Leisure'],
            ['datetime' => '2026-03-27 22:37:00', 'type' => 'expense', 'party' => 'Google Play Gift Card', 'amount' => 50.00, 'txn_id' => 'FMPIB019d3044-47f7-7e7d-b6f4-59e6a728a93e', 'category' => 'Entertainment & Leisure'],

            // Page 27
            ['datetime' => '2026-03-27 22:28:00', 'type' => 'expense', 'party' => 'Google Play Gift Card', 'amount' => 30.00, 'txn_id' => 'FMPIB019d303c-1611-726a-8e49-c4742e591bd0', 'category' => 'Entertainment & Leisure'],
            ['datetime' => '2026-03-27 22:25:00', 'type' => 'income', 'party' => 'Swapnil Ganesh Kolhe', 'amount' => 300.00, 'upi' => '918308861778@wahdfcbank', 'txn_id' => 'FMPIB4987264881', 'utr' => '120678198539', 'category' => 'Transfers & Friends'],
            ['datetime' => '2026-03-27 22:21:00', 'type' => 'expense', 'party' => 'Swapnil Ganesh Kolhe', 'amount' => 1.00, 'upi' => '8308861778@ibl', 'txn_id' => 'FMPIB4987239811', 'utr' => '645244914050', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-03-27 22:20:00', 'type' => 'income', 'party' => 'Swapnil Ganesh Kolhe', 'amount' => 1.00, 'upi' => '918308861778@wahdfcbank', 'txn_id' => 'FMPIB4987232754', 'utr' => '120678011649', 'category' => 'Transfers & Friends'],
            ['datetime' => '2026-03-22 09:46:00', 'type' => 'expense', 'party' => 'Ramdas Dashrath Kolhe', 'amount' => 1.00, 'upi' => 'q985099094@ybl', 'txn_id' => 'FMPIB4930711315', 'utr' => '644711212014', 'category' => 'Family Inflow'],
            ['datetime' => '2026-03-22 09:45:00', 'type' => 'income', 'party' => 'Ramdas Dashrath Kolhe', 'amount' => 1.00, 'upi' => 'kolheramdas2@axl', 'txn_id' => 'FMPIB4930705957', 'utr' => '139204588177', 'category' => 'Family Inflow'],
            ['datetime' => '2026-03-18 20:00:00', 'type' => 'expense', 'party' => 'Mrs Jyoti Ramdas Kolhe', 'amount' => 30.00, 'upi' => 'pavankolhe89@oksbi', 'txn_id' => 'FMPIB4898410051', 'utr' => '607792459770', 'category' => 'Family Inflow'],
            ['datetime' => '2026-03-18 17:07:00', 'type' => 'income', 'party' => 'Sai Balasaheb Pandharkar', 'amount' => 10.00, 'upi' => 'pandharkarsai8@okicici', 'txn_id' => 'FMPIB4896028124', 'utr' => '607716580078', 'category' => 'Transfers & Friends'],
            ['datetime' => '2026-03-18 12:47:00', 'type' => 'income', 'party' => 'Rahi Santosh Chikhale', 'amount' => 20.00, 'txn_id' => 'FMPIB4893351508', 'category' => 'Transfers & Friends'],
            ['datetime' => '2026-02-12 15:56:00', 'type' => 'expense', 'party' => 'Master Akshay Sudam Padwal', 'amount' => 60.00, 'upi' => '8766978059@axl', 'txn_id' => 'FMPIB4584537285', 'utr' => '640906016799', 'category' => 'Transfers & Payments'],

            // Page 28
            ['datetime' => '2026-02-12 14:29:00', 'type' => 'expense', 'party' => 'Rasal Rushikesh Balu', 'amount' => 490.00, 'upi' => 'q593438035@ybl', 'txn_id' => 'FMPIB4583730609', 'utr' => '640905521730', 'category' => 'Food & Dining'],
            ['datetime' => '2026-02-12 11:47:00', 'type' => 'income', 'party' => 'Mrs Jyoti Ramdas Kolhe', 'amount' => 500.00, 'upi' => 'pavankolhe89@okaxis', 'txn_id' => 'FMPIB4582198164', 'utr' => '604344751207', 'category' => 'Family Inflow'],
            ['datetime' => '2026-02-11 16:24:00', 'type' => 'expense', 'party' => 'Ashish Tukaram Kolhe', 'amount' => 70.00, 'upi' => 'q303516202@ybl', 'txn_id' => 'FMPIB4575539252', 'utr' => '640800591141', 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-02-11 15:51:00', 'type' => 'expense', 'party' => 'Shri Tulsai Udhyog', 'amount' => 880.00, 'upi' => 'gpay-11203574229@okbizaxis', 'txn_id' => 'FMPIB4575216161', 'utr' => '640800392676', 'category' => 'Shopping & Groceries'],
            ['datetime' => '2026-02-11 15:37:00', 'type' => 'income', 'party' => 'Vivek Umakant Kawad', 'amount' => 1000.00, 'upi' => '9699680569@ybl', 'txn_id' => 'FMPIB4575081805', 'utr' => '797101936642', 'category' => 'Transfers & Friends'],
            ['datetime' => '2025-12-26 11:34:00', 'type' => 'expense', 'party' => 'Swapnil Ganesh Kolhe', 'amount' => 1.00, 'upi' => '8308861778@ibl', 'txn_id' => 'FMPIB4149613032', 'utr' => '536047125655', 'category' => 'Transfers & Payments'],
            ['datetime' => '2025-12-23 18:41:00', 'type' => 'income', 'party' => 'Daksh Sachin Chopada', 'amount' => 1.00, 'txn_id' => 'FMPIB4127276629', 'category' => 'Transfers & Friends'],
            ['datetime' => '2025-10-27 09:17:00', 'type' => 'expense', 'party' => 'Swapnil Ganesh Kolhe', 'amount' => 10.01, 'upi' => '8308861778@ibl', 'txn_id' => 'FMPIB3636892971', 'utr' => '566641805139', 'category' => 'Transfers & Payments'],
            ['datetime' => '2025-10-21 10:23:00', 'type' => 'income', 'party' => 'Shelke Vishal Tanaji', 'amount' => 10.00, 'upi' => 'v2912353@okicici', 'txn_id' => 'FMPIB3591817982', 'utr' => '566001022817', 'category' => 'Transfers & Friends'],
            ['datetime' => '2025-10-17 22:07:00', 'type' => 'expense', 'party' => 'Airtel Prepaid', 'amount' => 320.99, 'txn_id' => 'FMPIB0199f308-44f5-7322-a949-a0e8d5b167f4', 'category' => 'Utilities & Bills'],

            // Page 29
            ['datetime' => '2025-10-17 22:06:00', 'type' => 'income', 'party' => 'Mrs Jyoti Ramdas Kolhe', 'amount' => 21.00, 'upi' => 'pavankolhe89@okaxis', 'txn_id' => 'FMPIB3563609204', 'utr' => '529034338575', 'category' => 'Family Inflow'],
            ['datetime' => '2025-10-17 21:56:00', 'type' => 'income', 'party' => 'Mrs Jyoti Ramdas Kolhe', 'amount' => 300.00, 'upi' => 'pavankolhe89@okaxis', 'txn_id' => 'FMPIB3563532625', 'utr' => '529024645761', 'category' => 'Family Inflow'],
            ['datetime' => '2025-10-16 19:55:00', 'type' => 'expense', 'party' => 'Ramdas Dashrath Kolhe', 'amount' => 250.00, 'upi' => 'kolheramdas2@ibl', 'txn_id' => 'FMPIB3553416985', 'utr' => '528993123145', 'category' => 'Family Inflow'],
            ['datetime' => '2025-10-16 19:42:00', 'type' => 'expense', 'party' => 'Shri Tulsai Udhyog', 'amount' => 750.00, 'upi' => 'gpay-11203574229@okbizaxis', 'txn_id' => 'FMPIB3553248096', 'utr' => '528993021301', 'category' => 'Shopping & Groceries'],
            ['datetime' => '2025-10-16 19:11:00', 'type' => 'income', 'party' => 'Ramdas Dashrath Kolhe', 'amount' => 999.00, 'upi' => 'kolheramdas2@axl', 'txn_id' => 'FMPIB3552839465', 'utr' => '720068358588', 'category' => 'Family Inflow'],
            ['datetime' => '2025-10-16 19:11:00', 'type' => 'income', 'party' => 'Ramdas Dashrath Kolhe', 'amount' => 1.00, 'upi' => 'kolheramdas2@ybl', 'txn_id' => 'FMPIB3552831852', 'utr' => '329348683511', 'category' => 'Family Inflow'],
            ['datetime' => '2025-10-15 20:18:00', 'type' => 'expense', 'party' => 'Mrs Jyoti Ramdas Kolhe', 'amount' => 1.00, 'upi' => 'pavankolhe89@oksbi', 'txn_id' => 'FMPIB3545119604', 'utr' => '528888168991', 'category' => 'Family Inflow'],
            ['datetime' => '2025-10-15 20:17:00', 'type' => 'income', 'party' => 'Mrs Jyoti Ramdas Kolhe', 'amount' => 1.00, 'upi' => 'pavankolhe89@okaxis', 'txn_id' => 'FMPIB3545107337', 'utr' => '565478496180', 'category' => 'Family Inflow'],
        ];
    }
}
