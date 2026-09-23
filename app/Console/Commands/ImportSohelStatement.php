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

class ImportSohelStatement extends Command
{
    protected $signature = 'import:sohel-statement {--email=mujawarsohel849@gmail.com} {--name=Sohel Imran Mujawar}';
    protected $description = 'Import 43-page FamApp statement transactions (22 Sep 2025 - 23 Sep 2026) for Sohel Imran Mujawar';

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
        $this->info("🎉 FAMAPP 43-PAGE STATEMENT IMPORT COMPLETE");
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
        $pages = [];

        // Page 1
        $pages[1] = [
            ['datetime' => '2026-09-08 16:11:00', 'type' => 'expense', 'party' => 'Mahesh Kumar Krishnakant Chaudhary', 'upi' => 'paytm.s2ez38k@pty', 'txn_id' => 'TOTud260908104139C9D0865C6E21489ABD', 'utr' => '625157850975', 'amount' => 60.00, 'category' => 'General & Services'],
            ['datetime' => '2026-09-07 22:49:00', 'type' => 'expense', 'party' => 'Ganesh Kondiba Shinalkar', 'upi' => 'paytmqrm786cgssfc@paytm', 'txn_id' => 'TOTud2609071719014CBB2116455A40ED9A', 'utr' => '625054003256', 'amount' => 10.00, 'category' => 'General & Services'],
            ['datetime' => '2026-09-07 22:45:00', 'type' => 'income', 'party' => 'Yasmeen Imran Mujawar', 'upi' => '9067210380-3@ybl', 'txn_id' => 'FMPIB6555576397', 'utr' => '383510556949', 'amount' => 20.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-09-07 17:57:00', 'type' => 'expense', 'party' => 'Sohanlal Binjaram Parmar', 'upi' => 'q403982861@ybl', 'txn_id' => 'TOTud2609071227419454B549F2CE483C9D', 'utr' => '625051116391', 'amount' => 10.00, 'category' => 'General & Services'],
            ['datetime' => '2026-09-07 08:34:00', 'type' => 'income', 'party' => 'Yasmeen Imran Mujawar', 'upi' => '9067210380-3@axl', 'txn_id' => 'FMPIB6550440999', 'utr' => '562238709447', 'amount' => 50.00, 'category' => 'Family Inflow'],
        ];

        // Page 2
        $pages[2] = [
            ['datetime' => '2026-09-06 22:15:00', 'type' => 'expense', 'party' => 'Krish', 'upi' => 'kvmm@ptyes', 'txn_id' => 'TOTud260906164522697759D3870640E5BF', 'utr' => '624946169519', 'amount' => 1.00, 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-09-02 20:40:00', 'type' => 'expense', 'party' => 'Mrs Babita Pradip Agrawal', 'upi' => 'q166444853@ybl', 'txn_id' => 'TOTud260902151012888F0330D1FA416998', 'utr' => '624514441861', 'amount' => 20.00, 'category' => 'General & Services'],
            ['datetime' => '2026-09-02 20:34:00', 'type' => 'expense', 'party' => 'Mahendra Parashar', 'upi' => 'q075138508@ybl', 'txn_id' => 'TOTud260902150418FC9E54BBA4394CA895', 'utr' => '624514379205', 'amount' => 25.00, 'category' => 'General & Services'],
            ['datetime' => '2026-09-02 19:10:00', 'type' => 'income', 'party' => 'Yasmeen Imran Mujawar', 'upi' => 'yasminmujawar0204@okaxis', 'txn_id' => 'FMPIB6521462689', 'utr' => '661135245636', 'amount' => 50.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-09-02 09:41:00', 'type' => 'expense', 'party' => 'Irctc', 'upi' => 'indian.railway.irctc@icici', 'txn_id' => 'TOTud260902041103DBEEB3685D7D432982', 'utr' => '624508921605', 'amount' => 112.25, 'category' => 'Transport & Fuel'],
            ['datetime' => '2026-09-02 09:39:00', 'type' => 'income', 'party' => 'Irfan Daud Muzawar', 'upi' => 'mujawarirfan609@okhdfcbank', 'txn_id' => 'FMPIB6518151349', 'utr' => '128886611670', 'amount' => 120.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-08-27 19:10:00', 'type' => 'expense', 'party' => 'Meesho', 'upi' => 'meeshoonlinepg@ybl', 'txn_id' => 'TOTud260827134001BC8A97B9F2D844329C', 'utr' => '660569535635', 'amount' => 92.00, 'category' => 'Shopping & Groceries'],
            ['datetime' => '2026-08-27 19:09:00', 'type' => 'income', 'party' => 'Yasmeen Imran Mujawar', 'upi' => '9067210380-3@axl', 'txn_id' => 'FMPIB6483907434', 'utr' => '098530439645', 'amount' => 92.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-07-16 18:10:00', 'type' => 'expense', 'party' => 'Master Sohel Imran Mujawar', 'upi' => 'mujawarsohel849@okhdfcbank', 'txn_id' => 'TOTud2607161240233ECE0625DFEE49429E', 'utr' => '619765750859', 'amount' => 10.00, 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-07-16 18:09:00', 'type' => 'income', 'party' => 'Azim Irfan Mujawar', 'upi' => null, 'txn_id' => 'FMPIB6183334090', 'utr' => null, 'amount' => 10.00, 'category' => 'Family Inflow'],
        ];

        // Page 3
        $pages[3] = [
            ['datetime' => '2026-07-13 16:09:00', 'type' => 'expense', 'party' => 'Pawan Ramsewak Gupta', 'upi' => 'paytmqr6v4n8t@ptys', 'txn_id' => 'FMPIB6150205120', 'utr' => '619443459483', 'amount' => 10.00, 'category' => 'General & Services'],
            ['datetime' => '2026-07-13 16:08:00', 'type' => 'income', 'party' => 'Master Sohel Imran Mujawar', 'upi' => 'mujawarsohel849@okhdfcbank', 'txn_id' => 'FMPIB6150192025', 'utr' => '126226005746', 'amount' => 2.00, 'category' => 'Transfers & Friends'],
            ['datetime' => '2026-07-11 12:29:00', 'type' => 'income', 'party' => 'Amazon', 'upi' => null, 'txn_id' => 'FMPIB6124899975', 'utr' => null, 'amount' => 2.00, 'category' => 'Cashback & Refunds'],
            ['datetime' => '2026-07-09 17:14:00', 'type' => 'expense', 'party' => 'Amazon', 'upi' => null, 'txn_id' => 'FMPIB6105627055', 'utr' => null, 'amount' => 2.00, 'category' => 'Shopping & Groceries'],
            ['datetime' => '2026-07-02 16:42:00', 'type' => 'expense', 'party' => 'Zepto', 'upi' => 'zepto.payu@hdfcbank', 'txn_id' => 'FMPIB6028189661', 'utr' => '654969300454', 'amount' => 158.00, 'category' => 'Shopping & Groceries'],
            ['datetime' => '2026-07-02 13:40:00', 'type' => 'expense', 'party' => 'Komal Verma', 'upi' => null, 'txn_id' => 'FMPIB6026263747', 'utr' => null, 'amount' => 750.00, 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-07-01 20:46:00', 'type' => 'expense', 'party' => 'Komal Verma', 'upi' => null, 'txn_id' => 'FMPIB6020829533', 'utr' => null, 'amount' => 700.00, 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-07-01 18:49:00', 'type' => 'income', 'party' => 'Uffra Afzal Hussain', 'upi' => 'hussainuffra88-1@okaxis', 'txn_id' => 'FMPIB6018760768', 'utr' => '618264336088', 'amount' => 1600.00, 'category' => 'Transfers & Friends'],
            ['datetime' => '2026-07-01 16:29:00', 'type' => 'expense', 'party' => 'Aarush Enterprise', 'upi' => 'gpay-11251531550@okbizaxis', 'txn_id' => 'FMPIB6016918203', 'utr' => '654862570427', 'amount' => 20.00, 'category' => 'Shopping & Groceries'],
            ['datetime' => '2026-07-01 10:53:00', 'type' => 'expense', 'party' => 'Irctc', 'upi' => 'indian.railway.irctc@icici', 'txn_id' => 'FMPIB6013491378', 'utr' => '654860477868', 'amount' => 112.25, 'category' => 'Transport & Fuel'],
            ['datetime' => '2026-06-30 22:28:00', 'type' => 'income', 'party' => 'Yasmeen Imran Mujawar', 'upi' => 'yasminmujawar0204@okaxis', 'txn_id' => 'FMPIB6010919971', 'utr' => '654760761186', 'amount' => 125.00, 'category' => 'Family Inflow'],
        ];

        // Page 4
        $pages[4] = [
            ['datetime' => '2026-06-30 16:16:00', 'type' => 'income', 'party' => 'Yasmeen Imran Mujawar', 'upi' => 'yasminmujawar0204@okhdfcbank', 'txn_id' => 'FMPIB6005756385', 'utr' => '125535129220', 'amount' => 20.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-06-30 13:44:00', 'type' => 'expense', 'party' => 'Irctc', 'upi' => 'indian.railway.irctc@icici', 'txn_id' => 'FMPIB6004168317', 'utr' => '654755033281', 'amount' => 122.25, 'category' => 'Transport & Fuel'],
            ['datetime' => '2026-06-30 13:42:00', 'type' => 'income', 'party' => 'Yasmeen Imran Mujawar', 'upi' => 'yasminmujawar0204@okhdfcbank', 'txn_id' => 'FMPIB6004150837', 'utr' => '125528085638', 'amount' => 15.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-06-30 13:41:00', 'type' => 'income', 'party' => 'Master Sohel Imran Mujawar', 'upi' => 'mujawarsohel849@okaxis', 'txn_id' => 'FMPIB6004144191', 'utr' => '618106596505', 'amount' => 110.00, 'category' => 'Transfers & Friends'],
            ['datetime' => '2026-06-17 17:36:00', 'type' => 'expense', 'party' => 'Yasmeen Imran Mujawar', 'upi' => 'yasminmujawar0204@okaxis', 'txn_id' => 'FMPIB5861682820', 'utr' => '616870082334', 'amount' => 100.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-06-17 16:05:00', 'type' => 'expense', 'party' => 'Pawan Ramsewak Gupta', 'upi' => 'paytmqr6v4n8t@ptys', 'txn_id' => 'FMPIB5860629832', 'utr' => '616869436442', 'amount' => 25.00, 'category' => 'General & Services'],
            ['datetime' => '2026-06-17 10:28:00', 'type' => 'income', 'party' => 'Yasmeen Imran Mujawar', 'upi' => 'yasminmujawar0204@okaxis', 'txn_id' => 'FMPIB5857179973', 'utr' => '653460629802', 'amount' => 125.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-06-13 00:41:00', 'type' => 'expense', 'party' => 'Master Sohel Imran Mujawar', 'upi' => 'mujawarsohel849@okhdfcbank', 'txn_id' => 'FMPIB5809870140', 'utr' => '616439153867', 'amount' => 50.00, 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-06-13 00:35:00', 'type' => 'income', 'party' => 'Yasmeen Imran Mujawar', 'upi' => 'yasminmujawar0204@okaxis', 'txn_id' => 'FMPIB5809854237', 'utr' => '653029532707', 'amount' => 50.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-06-12 12:34:00', 'type' => 'expense', 'party' => 'Amazon', 'upi' => 'amazon-pod@apl', 'txn_id' => 'FMPIB5801511097', 'utr' => '616334015920', 'amount' => 341.00, 'category' => 'Shopping & Groceries'],
        ];

        // Page 5
        $pages[5] = [
            ['datetime' => '2026-06-10 13:33:00', 'type' => 'income', 'party' => 'Yasmeen Imran Mujawar', 'upi' => 'yasminmujawar0204@okaxis', 'txn_id' => 'FMPIB5779783713', 'utr' => '616121855456', 'amount' => 10.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-06-10 13:32:00', 'type' => 'income', 'party' => 'Yasmeen Imran Mujawar', 'upi' => 'yasminmujawar0204@okhdfcbank', 'txn_id' => 'FMPIB5779763293', 'utr' => '124507229871', 'amount' => 70.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-06-10 12:52:00', 'type' => 'expense', 'party' => 'Google Play Gift Card', 'upi' => null, 'txn_id' => 'FMPIB019eb069-333a-7b87-9195-c26b8ff7ad2a', 'utr' => null, 'amount' => 30.00, 'category' => 'Entertainment & Leisure'],
            ['datetime' => '2026-06-10 12:14:00', 'type' => 'expense', 'party' => 'Zepto', 'upi' => 'zepto.payu@hdfcbank', 'txn_id' => 'FMPIB5778918785', 'utr' => '616120468340', 'amount' => 100.00, 'category' => 'Shopping & Groceries'],
            ['datetime' => '2026-06-09 19:08:00', 'type' => 'expense', 'party' => 'M J Khan', 'upi' => 'q550059478@ybl', 'txn_id' => 'FMPIB5772513021', 'utr' => '616016702566', 'amount' => 10.00, 'category' => 'General & Services'],
            ['datetime' => '2026-06-09 17:25:00', 'type' => 'income', 'party' => 'Baban Choudhury', 'upi' => null, 'txn_id' => 'FMPIB5771038034', 'utr' => null, 'amount' => 399.00, 'category' => 'Transfers & Friends'],
            ['datetime' => '2026-06-08 20:11:00', 'type' => 'expense', 'party' => 'Yasmeen Imran Mujawar', 'upi' => 'yasminmujawar0204@okaxis', 'txn_id' => 'FMPIB5762467938', 'utr' => '615910706797', 'amount' => 60.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-06-08 20:07:00', 'type' => 'expense', 'party' => 'Aarush Enterprise', 'upi' => 'gpay-11251531550@okbizaxis', 'txn_id' => 'FMPIB5762400373', 'utr' => '615910664273', 'amount' => 40.00, 'category' => 'Shopping & Groceries'],
            ['datetime' => '2026-06-08 20:03:00', 'type' => 'income', 'party' => 'Yasmeen Imran Mujawar', 'upi' => 'yasminmujawar0204@okhdfcbank', 'txn_id' => 'FMPIB5762317049', 'utr' => '124423158256', 'amount' => 100.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-06-07 15:23:00', 'type' => 'expense', 'party' => 'Zepto', 'upi' => 'zeptoonline@ybl', 'txn_id' => 'FMPIB5747315081', 'utr' => '615801510900', 'amount' => 87.00, 'category' => 'Shopping & Groceries'],
            ['datetime' => '2026-06-07 15:09:00', 'type' => 'income', 'party' => 'Azim Irfan Mujawar', 'upi' => null, 'txn_id' => 'FMPIB5747170808', 'utr' => null, 'amount' => 4.00, 'category' => 'Family Inflow'],
        ];

        // Page 6
        $pages[6] = [
            ['datetime' => '2026-06-07 15:07:00', 'type' => 'income', 'party' => 'Yasmeen Imran Mujawar', 'upi' => 'yasminmujawar0204@okaxis', 'txn_id' => 'FMPIB5747152135', 'utr' => '652452673486', 'amount' => 30.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-06-07 14:35:00', 'type' => 'expense', 'party' => 'Uffra Afzal Hussain', 'upi' => 'hussainuffra88@oksbi', 'txn_id' => 'FMPIB5746835710', 'utr' => '615801227050', 'amount' => 5000.00, 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-06-07 14:17:00', 'type' => 'expense', 'party' => 'Guljar Shah', 'upi' => 'shahgulzar87827@okaxis', 'txn_id' => 'FMPIB5746648250', 'utr' => '615801114798', 'amount' => 10.00, 'category' => 'General & Services'],
            ['datetime' => '2026-06-07 13:52:00', 'type' => 'expense', 'party' => 'Boost Up Fee', 'upi' => null, 'txn_id' => 'FMPIB5746385277', 'utr' => null, 'amount' => 39.00, 'category' => 'Fees & Charges'],
            ['datetime' => '2026-06-07 13:52:00', 'type' => 'income', 'party' => 'Sandip Kumar Singh', 'upi' => '7982182697@amazonpay', 'txn_id' => 'FMPIB5746385191', 'utr' => '652431684779', 'amount' => 5000.00, 'category' => 'Transfers & Friends'],
            ['datetime' => '2026-06-05 22:27:00', 'type' => 'expense', 'party' => 'Uffra Afzal Hussain', 'upi' => 'hussainuffra88@oksbi', 'txn_id' => 'FMPIB5730605170', 'utr' => '652291729952', 'amount' => 350.00, 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-06-05 19:42:00', 'type' => 'income', 'party' => 'Komal Verma', 'upi' => null, 'txn_id' => 'FMPIB5728230574', 'utr' => null, 'amount' => 400.00, 'category' => 'Transfers & Friends'],
            ['datetime' => '2026-06-05 13:12:00', 'type' => 'expense', 'party' => 'M. Jai Tanish', 'upi' => null, 'txn_id' => 'FMPIB5723442181', 'utr' => null, 'amount' => 1500.00, 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-06-05 13:11:00', 'type' => 'expense', 'party' => 'Zepto', 'upi' => 'zeptomarketplacepriva.payu@mairtel', 'txn_id' => 'FMPIB5723432955', 'utr' => '652287296516', 'amount' => 149.00, 'category' => 'Shopping & Groceries'],
            ['datetime' => '2026-06-04 21:45:00', 'type' => 'income', 'party' => 'Pinki Kumari', 'upi' => '8209509186@ybl', 'txn_id' => 'FMPIB5719118357', 'utr' => '334092828742', 'amount' => 1100.00, 'category' => 'Transfers & Friends'],
            ['datetime' => '2026-06-04 19:51:00', 'type' => 'income', 'party' => 'Pinki Kumari', 'upi' => '8209509186@axl', 'txn_id' => 'FMPIB5717376245', 'utr' => '338620374799', 'amount' => 400.00, 'category' => 'Transfers & Friends'],
        ];

        // Page 7
        $pages[7] = [
            ['datetime' => '2026-06-04 19:29:00', 'type' => 'income', 'party' => 'Pinki Kumari', 'upi' => '8209509186@ybl', 'txn_id' => 'FMPIB5716993580', 'utr' => '125985148308', 'amount' => 100.00, 'category' => 'Transfers & Friends'],
            ['datetime' => '2026-06-04 19:24:00', 'type' => 'income', 'party' => 'Amit', 'upi' => '9650453544-3@axl', 'txn_id' => 'FMPIB5716913714', 'utr' => '750956874438', 'amount' => 7.50, 'category' => 'Transfers & Friends'],
            ['datetime' => '2026-06-04 12:54:00', 'type' => 'expense', 'party' => 'Zepto', 'upi' => 'zepto.payu@axisbank', 'txn_id' => 'FMPIB5712338083', 'utr' => '652180631383', 'amount' => 100.00, 'category' => 'Shopping & Groceries'],
            ['datetime' => '2026-06-04 10:58:00', 'type' => 'income', 'party' => 'Imran Daud Mujawar', 'upi' => 'imranmujawar4444@okaxis', 'txn_id' => 'FMPIB5711178554', 'utr' => '615513677055', 'amount' => 125.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-06-03 21:42:00', 'type' => 'expense', 'party' => 'Mr Jala Ram', 'upi' => 'paytmqr6b7qmt@ptys', 'txn_id' => 'FMPIB5708095079', 'utr' => '652078236197', 'amount' => 70.00, 'category' => 'General & Services'],
            ['datetime' => '2026-06-03 21:34:00', 'type' => 'income', 'party' => 'Yasmeen Imran Mujawar', 'upi' => 'yasminmujawar0204@okhdfcbank', 'txn_id' => 'FMPIB5707996582', 'utr' => '124163315081', 'amount' => 100.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-06-03 17:30:00', 'type' => 'expense', 'party' => 'Indian Railways Uts', 'upi' => 'bdpg2.iruts@sbi', 'txn_id' => 'FMPIB5704192749', 'utr' => '652075788952', 'amount' => 9.70, 'category' => 'Transport & Fuel'],
            ['datetime' => '2026-06-03 15:39:00', 'type' => 'expense', 'party' => 'Irfan Daud Muzawar', 'upi' => 'mujawarirfan609@okhdfcbank', 'txn_id' => 'FMPIB5702965296', 'utr' => '652075048435', 'amount' => 50.00, 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-06-03 00:01:00', 'type' => 'income', 'party' => 'Imran Daud Mujawar', 'upi' => 'imranmujawar4444@okaxis', 'txn_id' => 'FMPIB5697873653', 'utr' => '652061376918', 'amount' => 100.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-06-01 21:24:00', 'type' => 'expense', 'party' => 'Zepto', 'upi' => 'zepto.payu@axisbank', 'txn_id' => 'FMPIB5685764337', 'utr' => '651864846369', 'amount' => 104.00, 'category' => 'Shopping & Groceries'],
        ];

        // Page 8
        $pages[8] = [
            ['datetime' => '2026-06-01 21:24:00', 'type' => 'income', 'party' => 'Imran Rajmohammad Momin', 'upi' => 'irmr8087@okaxis', 'txn_id' => 'FMPIB5685760687', 'utr' => '615242168757', 'amount' => 2.00, 'category' => 'Transfers & Friends'],
            ['datetime' => '2026-06-01 21:02:00', 'type' => 'income', 'party' => 'Mahek Irfan Mujawar', 'upi' => null, 'txn_id' => 'FMPIB5685464847', 'utr' => null, 'amount' => 50.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-06-01 15:48:00', 'type' => 'expense', 'party' => 'Jio Prepaid', 'upi' => null, 'txn_id' => 'FMPIB019e82b1-5a79-78df-befb-9030a17519f1', 'utr' => null, 'amount' => 29.99, 'category' => 'Utilities & Bills'],
            ['datetime' => '2026-06-01 11:41:00', 'type' => 'expense', 'party' => 'Sheshram Kanaram Choudhari', 'upi' => 'q219890328@ybl', 'txn_id' => 'FMPIB5678460890', 'utr' => '651860285915', 'amount' => 10.00, 'category' => 'General & Services'],
            ['datetime' => '2026-06-01 11:39:00', 'type' => 'expense', 'party' => 'Sheshram Kanaram Choudhari', 'upi' => 'q219890328@ybl', 'txn_id' => 'FMPIB5678435793', 'utr' => '651860270384', 'amount' => 40.00, 'category' => 'General & Services'],
            ['datetime' => '2026-06-01 09:40:00', 'type' => 'expense', 'party' => 'Jadhav Arvind Vitthal', 'upi' => 'q175639347@ybl', 'txn_id' => 'FMPIB5677401281', 'utr' => '651859637346', 'amount' => 25.00, 'category' => 'General & Services'],
            ['datetime' => '2026-05-31 18:21:00', 'type' => 'expense', 'party' => 'Shree Krushna Market', 'upi' => 'gpay-12200589305@okbizaxis', 'txn_id' => 'FMPIB5671792144', 'utr' => '651756238609', 'amount' => 10.00, 'category' => 'Shopping & Groceries'],
            ['datetime' => '2026-05-31 17:54:00', 'type' => 'expense', 'party' => 'Shree Krushna Market', 'upi' => 'gpay-12200589305@okbizaxis', 'txn_id' => 'FMPIB5671427329', 'utr' => '651756014615', 'amount' => 8.00, 'category' => 'Shopping & Groceries'],
            ['datetime' => '2026-05-31 11:30:00', 'type' => 'income', 'party' => 'Irctc', 'upi' => 'indian.railway.irctc@icici', 'txn_id' => 'FMPIB5667394875', 'utr' => '651646998477', 'amount' => 137.25, 'category' => 'Cashback & Refunds'],
            ['datetime' => '2026-05-30 11:14:00', 'type' => 'expense', 'party' => 'Imran Daud Mujawar', 'upi' => 'imranmujawar4444@oksbi', 'txn_id' => 'FMPIB5656352192', 'utr' => '651647041173', 'amount' => 150.00, 'category' => 'Family Inflow'],
        ];

        // Page 9
        $pages[9] = [
            ['datetime' => '2026-05-30 11:06:00', 'type' => 'expense', 'party' => 'Irctc', 'upi' => 'indian.railway.irctc@icici', 'txn_id' => 'FMPIB5656281653', 'utr' => '651646998477', 'amount' => 137.25, 'category' => 'Transport & Fuel'],
            ['datetime' => '2026-05-30 10:56:00', 'type' => 'income', 'party' => 'Imran Daud Mujawar', 'upi' => 'imranmujawar4444@oksbi', 'txn_id' => 'FMPIB5656189789', 'utr' => '615009382047', 'amount' => 300.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-05-28 23:09:00', 'type' => 'expense', 'party' => 'Azim Irfan Mujawar', 'upi' => null, 'txn_id' => 'FMPIB5643300068', 'utr' => null, 'amount' => 3.00, 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-05-28 23:07:00', 'type' => 'expense', 'party' => 'Mahek Irfan Mujawar', 'upi' => null, 'txn_id' => 'FMPIB5643291124', 'utr' => null, 'amount' => 10.00, 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-05-28 16:25:00', 'type' => 'expense', 'party' => 'Irctc', 'upi' => 'indian.railway.irctc@icici', 'txn_id' => 'FMPIB5638257064', 'utr' => '651436191603', 'amount' => 112.25, 'category' => 'Transport & Fuel'],
            ['datetime' => '2026-05-28 16:24:00', 'type' => 'income', 'party' => 'Imran Daud Mujawar', 'upi' => 'imranmujawar4444@okaxis', 'txn_id' => 'FMPIB5638242920', 'utr' => '614886295700', 'amount' => 150.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-05-24 15:06:00', 'type' => 'expense', 'party' => 'Nazir Zaheer Khan', 'upi' => null, 'txn_id' => 'FMPIB5593277618', 'utr' => null, 'amount' => 121.00, 'category' => 'General & Services'],
            ['datetime' => '2026-05-24 14:54:00', 'type' => 'income', 'party' => 'Muskan Imran Mujawar', 'upi' => '7350334459@axl', 'txn_id' => 'FMPIB5593169202', 'utr' => '332740824874', 'amount' => 21.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-05-24 12:41:00', 'type' => 'expense', 'party' => 'Uffra Afzal Hussain', 'upi' => 'hussainuffra88@oksbi', 'txn_id' => 'FMPIB5591812773', 'utr' => '651008287377', 'amount' => 150.00, 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-05-23 21:51:00', 'type' => 'income', 'party' => 'Vivek Sharma', 'upi' => null, 'txn_id' => 'FMPIB5587830133', 'utr' => null, 'amount' => 249.00, 'category' => 'Transfers & Friends'],
            ['datetime' => '2026-05-23 18:00:00', 'type' => 'expense', 'party' => 'Vishal Shivaji Sirsim', 'upi' => 'vishalsirsim193-1@okaxis', 'txn_id' => 'FMPIB5584226055', 'utr' => '650903750189', 'amount' => 122.00, 'category' => 'General & Services'],
        ];

        // Page 10
        $pages[10] = [
            ['datetime' => '2026-05-23 17:49:00', 'type' => 'income', 'party' => 'Yasmeen Imran Mujawar', 'upi' => 'yasminmujawar0204@okaxis', 'txn_id' => 'FMPIB5584078175', 'utr' => '614328949293', 'amount' => 122.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-05-23 17:08:00', 'type' => 'expense', 'party' => 'Jiohotstar', 'upi' => 'starindiatejcj.rzpap@axisbank', 'txn_id' => 'FMPIB5583590889', 'utr' => null, 'amount' => 1.00, 'category' => 'Entertainment & Leisure'],
            ['datetime' => '2026-05-22 23:02:00', 'type' => 'expense', 'party' => 'Blinkit', 'upi' => 'paytm-blinkit@ptybl', 'txn_id' => 'FMPIB5577537065', 'utr' => '614299831799', 'amount' => 163.00, 'category' => 'Shopping & Groceries'],
            ['datetime' => '2026-05-22 22:51:00', 'type' => 'income', 'party' => 'Yasmeen Imran Mujawar', 'upi' => 'yasminmujawar0204@okaxis', 'txn_id' => 'FMPIB5577464193', 'utr' => '650827896921', 'amount' => 163.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-05-19 20:08:00', 'type' => 'expense', 'party' => 'Zepto', 'upi' => 'zepto.payu@hdfcbank', 'txn_id' => 'FMPIB5542188344', 'utr' => '613978313336', 'amount' => 157.00, 'category' => 'Shopping & Groceries'],
            ['datetime' => '2026-05-19 19:54:00', 'type' => 'income', 'party' => 'Yasmeen Imran Mujawar', 'upi' => 'yasminmujawar0204@okaxis', 'txn_id' => 'FMPIB5541943648', 'utr' => '613990796939', 'amount' => 157.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-05-19 10:50:00', 'type' => 'expense', 'party' => 'Irctc', 'upi' => 'indian.railway.irctc@icici', 'txn_id' => 'FMPIB5535583109', 'utr' => '613974199226', 'amount' => 97.25, 'category' => 'Transport & Fuel'],
            ['datetime' => '2026-05-19 10:49:00', 'type' => 'income', 'party' => 'Yasmeen Imran Mujawar', 'upi' => 'yasminmujawar0204@okaxis', 'txn_id' => 'FMPIB5535571823', 'utr' => '613912957068', 'amount' => 100.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-05-17 11:01:00', 'type' => 'expense', 'party' => 'Zepto', 'upi' => 'zepto.payu@axisbank', 'txn_id' => 'FMPIB5513813473', 'utr' => '613760957931', 'amount' => 108.00, 'category' => 'Shopping & Groceries'],
            ['datetime' => '2026-05-17 10:49:00', 'type' => 'income', 'party' => 'Azim Irfan Mujawar', 'upi' => null, 'txn_id' => 'FMPIB5513698179', 'utr' => null, 'amount' => 6.00, 'category' => 'Family Inflow'],
        ];

        // Page 11
        $pages[11] = [
            ['datetime' => '2026-05-17 10:46:00', 'type' => 'income', 'party' => 'Yasmeen Imran Mujawar', 'upi' => 'yasminmujawar0204@okaxis', 'txn_id' => 'FMPIB5513668658', 'utr' => '613785164483', 'amount' => 30.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-05-16 19:36:00', 'type' => 'income', 'party' => 'Yasmeen Imran Mujawar', 'upi' => 'yasminmujawar0204@okhdfcbank', 'txn_id' => 'FMPIB5508500535', 'utr' => '123235955603', 'amount' => 12.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-05-16 19:36:00', 'type' => 'expense', 'party' => 'Mr Jala Ram', 'upi' => 'paytmqr6b7qmt@ptys', 'txn_id' => 'FMPIB5508499134', 'utr' => '613657731556', 'amount' => 41.00, 'category' => 'General & Services'],
            ['datetime' => '2026-05-16 19:32:00', 'type' => 'income', 'party' => 'Yasmeen Imran Mujawar', 'upi' => 'yasminmujawar0204@okaxis', 'txn_id' => 'FMPIB5508429968', 'utr' => '613691021888', 'amount' => 100.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-05-16 15:38:00', 'type' => 'expense', 'party' => 'Sandeep Subashchandra Gupta', 'upi' => 'sandeepsubhashgupta@okicici', 'txn_id' => 'FMPIB5505406831', 'utr' => '613655820195', 'amount' => 10.00, 'category' => 'General & Services'],
            ['datetime' => '2026-05-16 15:35:00', 'type' => 'expense', 'party' => 'Arjun Kallu Gupta', 'upi' => 'paytmqr6k2pqp@ptys', 'txn_id' => 'FMPIB5505382839', 'utr' => '613655805896', 'amount' => 40.00, 'category' => 'General & Services'],
            ['datetime' => '2026-05-16 15:28:00', 'type' => 'income', 'party' => 'Yasmeen Imran Mujawar', 'upi' => 'yasminmujawar0204@okaxis', 'txn_id' => 'FMPIB5505309774', 'utr' => '613612910802', 'amount' => 50.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-05-15 11:50:00', 'type' => 'expense', 'party' => 'Jio Prepaid', 'upi' => null, 'txn_id' => 'FMPIB019e2a4b-8910-74f7-ac1d-743b29a07d43', 'utr' => null, 'amount' => 320.99, 'category' => 'Utilities & Bills'],
            ['datetime' => '2026-05-15 11:50:00', 'type' => 'income', 'party' => 'Imran Daud Mujawar', 'upi' => 'imranmujawar4444@okaxis', 'txn_id' => 'FMPIB5491752447', 'utr' => '613594080912', 'amount' => 2.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-05-15 11:48:00', 'type' => 'income', 'party' => 'Yasmeenimranmujawar', 'upi' => '9067210380@upi', 'txn_id' => 'FMPIB5491737280', 'utr' => '114759898844', 'amount' => 319.00, 'category' => 'Family Inflow'],
        ];

        // Page 12
        $pages[12] = [
            ['datetime' => '2026-05-12 18:25:00', 'type' => 'expense', 'party' => 'Zepto', 'upi' => 'zeptomarketplacepriva.payu@mairtel', 'txn_id' => 'FMPIB5463263694', 'utr' => '613230339556', 'amount' => 112.00, 'category' => 'Shopping & Groceries'],
            ['datetime' => '2026-05-12 17:53:00', 'type' => 'income', 'party' => 'Yasmeenimranmujawar', 'upi' => '9067210380@upi', 'txn_id' => 'FMPIB5462820510', 'utr' => '175328542847', 'amount' => 5.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-05-12 17:42:00', 'type' => 'income', 'party' => 'Muskan Imran Mujawar', 'upi' => '7350334459@axl', 'txn_id' => 'FMPIB5462676102', 'utr' => '892188655101', 'amount' => 100.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-05-11 18:43:00', 'type' => 'income', 'party' => 'Google Play', 'upi' => 'playstore1.bd@axisbank', 'txn_id' => 'FMPIB5452564660', 'utr' => '218680551316', 'amount' => 2.00, 'category' => 'Cashback & Refunds'],
            ['datetime' => '2026-05-11 18:43:00', 'type' => 'expense', 'party' => 'Google Play', 'upi' => 'playstore1.bd@axisbank', 'txn_id' => 'FMPIB5452552098', 'utr' => null, 'amount' => 2.00, 'category' => 'Entertainment & Leisure'],
            ['datetime' => '2026-05-09 17:47:00', 'type' => 'expense', 'party' => 'Amarjeet Kishor Awale', 'upi' => '8652493034-1@nyes', 'txn_id' => 'FMPIB5429625162', 'utr' => '612909954602', 'amount' => 92.00, 'category' => 'General & Services'],
            ['datetime' => '2026-05-09 17:30:00', 'type' => 'income', 'party' => 'Hasn Preet Singh', 'upi' => 'hasanpreet.personal@oksbi', 'txn_id' => 'FMPIB5429411621', 'utr' => '612921209049', 'amount' => 1.00, 'category' => 'Transfers & Friends'],
            ['datetime' => '2026-05-09 17:29:00', 'type' => 'income', 'party' => 'Hasn Preet Singh', 'upi' => 'hasanpreet.personal@okaxis', 'txn_id' => 'FMPIB5429403384', 'utr' => '649590268199', 'amount' => 30.00, 'category' => 'Transfers & Friends'],
            ['datetime' => '2026-05-08 10:13:00', 'type' => 'expense', 'party' => 'Zepto', 'upi' => 'zeptoonline@ybl', 'txn_id' => 'FMPIB5414008003', 'utr' => '612800518256', 'amount' => 166.00, 'category' => 'Shopping & Groceries'],
            ['datetime' => '2026-05-08 09:57:00', 'type' => 'income', 'party' => 'Yasmeenimranmujawar', 'upi' => '9067210380@upi', 'txn_id' => 'FMPIB5413884891', 'utr' => '095737546701', 'amount' => 10.00, 'category' => 'Family Inflow'],
        ];

        // Page 13
        $pages[13] = [
            ['datetime' => '2026-05-08 09:55:00', 'type' => 'income', 'party' => 'Yasmeen Imran Mujawar', 'upi' => '9067210380-3@ybl', 'txn_id' => 'FMPIB5413872022', 'utr' => '025389822247', 'amount' => 8.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-05-08 09:54:00', 'type' => 'income', 'party' => 'Yasmeen Imran Mujawar', 'upi' => 'yasminmujawar0204@okaxis', 'txn_id' => 'FMPIB5413862780', 'utr' => '612862543097', 'amount' => 69.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-05-08 00:27:00', 'type' => 'expense', 'party' => 'Zepto', 'upi' => 'zepto.payu@axisbank', 'txn_id' => 'FMPIB5412430206', 'utr' => '649499714949', 'amount' => 59.00, 'category' => 'Shopping & Groceries'],
            ['datetime' => '2026-05-07 01:25:00', 'type' => 'expense', 'party' => 'Blinkit', 'upi' => 'blinkit.rzp@hdfcbank', 'txn_id' => 'FMPIB5401634740', 'utr' => '649393311759', 'amount' => 186.00, 'category' => 'Shopping & Groceries'],
            ['datetime' => '2026-05-07 01:25:00', 'type' => 'income', 'party' => 'Yasmeen Imran Mujawar', 'upi' => 'yasminmujawar0204@okhdfcbank', 'txn_id' => 'FMPIB5401634384', 'utr' => '122721370149', 'amount' => 186.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-05-05 17:36:00', 'type' => 'expense', 'party' => 'Zepto', 'upi' => 'zeptomarketplaceprivat-12682647.payu@indus', 'txn_id' => 'FMPIB5386153107', 'utr' => '649183906940', 'amount' => 197.00, 'category' => 'Shopping & Groceries'],
            ['datetime' => '2026-05-05 17:22:00', 'type' => 'income', 'party' => 'Yasmeen Imran Mujawar', 'upi' => 'yasminmujawar0204@okaxis', 'txn_id' => 'FMPIB5385988994', 'utr' => '649193688045', 'amount' => 100.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-05-05 16:21:00', 'type' => 'expense', 'party' => 'Darakshaan Afzal Hussain', 'upi' => null, 'txn_id' => 'FMPIB5385306436', 'utr' => null, 'amount' => 5.00, 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-05-05 15:12:00', 'type' => 'expense', 'party' => 'Krish', 'upi' => null, 'txn_id' => 'FMPIB5384600271', 'utr' => null, 'amount' => 100.00, 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-05-05 15:10:00', 'type' => 'income', 'party' => 'Karan Singh Rawat', 'upi' => null, 'txn_id' => 'FMPIB5384586028', 'utr' => null, 'amount' => 400.00, 'category' => 'Transfers & Friends'],
            ['datetime' => '2026-05-04 12:06:00', 'type' => 'expense', 'party' => 'Zepto', 'upi' => 'zeptoonline@ybl', 'txn_id' => 'FMPIB5372295678', 'utr' => '649075505420', 'amount' => 100.00, 'category' => 'Shopping & Groceries'],
        ];

        // Page 14
        $pages[14] = [
            ['datetime' => '2026-05-04 11:40:00', 'type' => 'income', 'party' => 'Yasmeen Imran Mujawar', 'upi' => 'yasminmujawar0204@okaxis', 'txn_id' => 'FMPIB5372060340', 'utr' => '612450482899', 'amount' => 9.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-05-04 11:32:00', 'type' => 'income', 'party' => 'Darakshaan Afzal Hussain', 'upi' => null, 'txn_id' => 'FMPIB5371986745', 'utr' => null, 'amount' => 100.00, 'category' => 'Transfers & Friends'],
            ['datetime' => '2026-05-03 15:46:00', 'type' => 'expense', 'party' => 'Shankar Awadhutrao Maraskolhe', 'upi' => 'maraskolheshankar51@okaxis', 'txn_id' => 'FMPIB5364071364', 'utr' => '648970589256', 'amount' => 300.00, 'category' => 'General & Services'],
            ['datetime' => '2026-05-03 15:39:00', 'type' => 'income', 'party' => 'Darakshaan Afzal Hussain', 'upi' => null, 'txn_id' => 'FMPIB5364002031', 'utr' => null, 'amount' => 100.00, 'category' => 'Transfers & Friends'],
            ['datetime' => '2026-05-03 15:28:00', 'type' => 'expense', 'party' => 'Darakshaan Afzal Hussain', 'upi' => null, 'txn_id' => 'FMPIB5363901144', 'utr' => null, 'amount' => 100.00, 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-05-03 15:10:00', 'type' => 'income', 'party' => 'Shankar Awadhutrao Maraskolhe', 'upi' => 'maraskolheshankar51@okaxis', 'txn_id' => 'FMPIB5363723823', 'utr' => '612315108431', 'amount' => 300.00, 'category' => 'Transfers & Friends'],
            ['datetime' => '2026-05-02 20:02:00', 'type' => 'expense', 'party' => 'Darakshaan Afzal Hussain', 'upi' => null, 'txn_id' => 'FMPIB5356834813', 'utr' => null, 'amount' => 300.00, 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-05-02 18:15:00', 'type' => 'income', 'party' => 'Darakshaan Afzal Hussain', 'upi' => null, 'txn_id' => 'FMPIB5355120521', 'utr' => null, 'amount' => 300.00, 'category' => 'Transfers & Friends'],
            ['datetime' => '2026-04-27 17:53:00', 'type' => 'expense', 'party' => 'Yasmeen Imran Mujawar', 'upi' => 'yasminmujawar0204@okaxis', 'txn_id' => 'FMPIB5303158890', 'utr' => '648334124347', 'amount' => 50.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-04-27 17:41:00', 'type' => 'income', 'party' => 'Yasmeen Imran Mujawar', 'upi' => 'yasminmujawar0204@okaxis', 'txn_id' => 'FMPIB5303015181', 'utr' => '648324308105', 'amount' => 50.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-04-27 13:05:00', 'type' => 'expense', 'party' => 'Rudra Roy', 'upi' => 'rudrabarman054-1@okicici', 'txn_id' => 'FMPIB5300206881', 'utr' => '648332308494', 'amount' => 110.00, 'category' => 'General & Services'],
        ];

        // Page 15
        $pages[15] = [
            ['datetime' => '2026-04-27 12:52:00', 'type' => 'income', 'party' => 'Darakshaan Afzal Hussain', 'upi' => null, 'txn_id' => 'FMPIB5300076872', 'utr' => null, 'amount' => 7.00, 'category' => 'Transfers & Friends'],
            ['datetime' => '2026-04-27 12:49:00', 'type' => 'income', 'party' => 'Irfan Daud Mujawar', 'upi' => '9730526998@ibl', 'txn_id' => 'FMPIB5300036226', 'utr' => '737401365460', 'amount' => 50.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-04-26 09:05:00', 'type' => 'expense', 'party' => 'Zepto', 'upi' => 'zepto.payu@axisbank', 'txn_id' => 'FMPIB5287897412', 'utr' => '648224924825', 'amount' => 112.00, 'category' => 'Shopping & Groceries'],
            ['datetime' => '2026-04-25 20:38:00', 'type' => 'expense', 'party' => 'Zepto', 'upi' => 'zeptomarketplaceprivat-12682647.payu@indus', 'txn_id' => 'FMPIB5284989742', 'utr' => '648123286097', 'amount' => 115.00, 'category' => 'Shopping & Groceries'],
            ['datetime' => '2026-04-25 16:25:00', 'type' => 'expense', 'party' => 'Mohammad Amin Khan', 'upi' => 'paytmqr70wqo6@ptys', 'txn_id' => 'FMPIB5281537688', 'utr' => '648121124677', 'amount' => 10.00, 'category' => 'General & Services'],
            ['datetime' => '2026-04-25 16:17:00', 'type' => 'expense', 'party' => 'Sandeep Subashchandra Gupta', 'upi' => 'sandeepsubhashgupta@okicici', 'txn_id' => 'FMPIB5281455684', 'utr' => '648121074754', 'amount' => 10.00, 'category' => 'General & Services'],
            ['datetime' => '2026-04-25 12:35:00', 'type' => 'expense', 'party' => 'Muskan Imran Mujawar', 'upi' => '7350334459@ibl', 'txn_id' => 'FMPIB5279234943', 'utr' => '648119726885', 'amount' => 100.00, 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-04-24 22:16:00', 'type' => 'expense', 'party' => 'Yasmeen Imran Mujawar', 'upi' => 'yasminmujawar0204@oksbi', 'txn_id' => 'FMPIB5275745987', 'utr' => '648017775752', 'amount' => 100.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-04-24 19:56:00', 'type' => 'income', 'party' => 'Darakshaan Afzal Hussain', 'upi' => null, 'txn_id' => 'FMPIB5273894670', 'utr' => null, 'amount' => 500.00, 'category' => 'Transfers & Friends'],
            ['datetime' => '2026-04-23 22:09:00', 'type' => 'expense', 'party' => 'Yasmeenimranmujawar', 'upi' => '9067210380@upi', 'txn_id' => 'FMPIB5265214053', 'utr' => '647911378791', 'amount' => 320.00, 'category' => 'Family Inflow'],
        ];

        // Page 16
        $pages[16] = [
            ['datetime' => '2026-04-23 22:08:00', 'type' => 'income', 'party' => 'Yasmeen Imran Mujawar', 'upi' => 'yasminmujawar0204@okaxis', 'txn_id' => 'FMPIB5265199985', 'utr' => '611379505075', 'amount' => 5.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-04-23 22:07:00', 'type' => 'income', 'party' => 'Imran Daud Mujawar', 'upi' => 'imranmujawar4444@oksbi', 'txn_id' => 'FMPIB5265194688', 'utr' => '611350305115', 'amount' => 50.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-04-23 22:06:00', 'type' => 'income', 'party' => 'Imran Daud Mujawar', 'upi' => 'imranmujawar4444@oksbi', 'txn_id' => 'FMPIB5265179605', 'utr' => '611350232341', 'amount' => 120.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-04-23 18:41:00', 'type' => 'income', 'party' => 'Birumal Sufer Yadav', 'upi' => 'ry1272056@oksbi', 'txn_id' => 'FMPIB5262223786', 'utr' => '611333147972', 'amount' => 90.00, 'category' => 'Transfers & Friends'],
            ['datetime' => '2026-04-23 14:15:00', 'type' => 'income', 'party' => 'Yasmeen Imran Mujawar', 'upi' => '9067210380-3@axl', 'txn_id' => 'FMPIB5259350874', 'utr' => '525039687322', 'amount' => 50.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-04-20 18:15:00', 'type' => 'expense', 'party' => 'Zepto', 'upi' => 'zepto.payu@axisbank', 'txn_id' => 'FMPIB5230906554', 'utr' => '611090577434', 'amount' => 205.00, 'category' => 'Shopping & Groceries'],
            ['datetime' => '2026-04-20 18:03:00', 'type' => 'expense', 'party' => 'Master Sohel Imran Mujawar', 'upi' => 'mujawarsohel849@okhdfcbank', 'txn_id' => 'FMPIB5230754928', 'utr' => '611090482249', 'amount' => 1.00, 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-04-20 17:53:00', 'type' => 'income', 'party' => 'Muskan Imran Mujawar', 'upi' => '7350334459@axl', 'txn_id' => 'FMPIB5230629846', 'utr' => '447939596425', 'amount' => 12.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-04-20 12:23:00', 'type' => 'expense', 'party' => 'Zepto', 'upi' => 'zepto.payu@axisbank', 'txn_id' => 'FMPIB5227195562', 'utr' => '611088288931', 'amount' => 103.00, 'category' => 'Shopping & Groceries'],
            ['datetime' => '2026-04-19 20:12:00', 'type' => 'income', 'party' => 'Darakshaan Afzal Hussain', 'upi' => null, 'txn_id' => 'FMPIB5222409283', 'utr' => null, 'amount' => 300.00, 'category' => 'Transfers & Friends'],
        ];

        // Page 17
        $pages[17] = [
            ['datetime' => '2026-04-19 14:46:00', 'type' => 'expense', 'party' => 'Master Sohel Imran Mujawar', 'upi' => 'mujawarsohel849@okhdfcbank', 'txn_id' => 'FMPIB5218311814', 'utr' => '610982963534', 'amount' => 1.00, 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-04-15 22:31:00', 'type' => 'expense', 'party' => 'Indian Railways Uts', 'upi' => 'bdpg2.iruts@sbi', 'txn_id' => 'FMPIB5181610082', 'utr' => '610560912889', 'amount' => 4.85, 'category' => 'Transport & Fuel'],
            ['datetime' => '2026-04-15 22:30:00', 'type' => 'income', 'party' => 'Yasmeen Imran Mujawar', 'upi' => 'yasminmujawar0204@okhdfcbank', 'txn_id' => 'FMPIB5181600029', 'utr' => '121663479060', 'amount' => 5.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-04-15 12:16:00', 'type' => 'expense', 'party' => 'Zepto', 'upi' => 'zeptomarketplaceprivat-12682647.payu@indus', 'txn_id' => 'FMPIB5174001582', 'utr' => '610556187119', 'amount' => 127.00, 'category' => 'Shopping & Groceries'],
            ['datetime' => '2026-04-15 11:59:00', 'type' => 'income', 'party' => 'Yasmeen Imran Mujawar', 'upi' => 'yasminmujawar0204@okhdfcbank', 'txn_id' => 'FMPIB5173826605', 'utr' => '121628210329', 'amount' => 30.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-04-15 11:57:00', 'type' => 'expense', 'party' => 'Irctc', 'upi' => 'indian.railway.irctc@icici', 'txn_id' => 'FMPIB5173803988', 'utr' => '610556064350', 'amount' => 192.25, 'category' => 'Transport & Fuel'],
            ['datetime' => '2026-04-15 11:55:00', 'type' => 'income', 'party' => 'Yasmeen Imran Mujawar', 'upi' => 'yasminmujawar0204@okhdfcbank', 'txn_id' => 'FMPIB5173781430', 'utr' => '121628002446', 'amount' => 191.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-04-15 11:54:00', 'type' => 'income', 'party' => 'Yasmeen Imran Mujawar', 'upi' => 'yasminmujawar0204@okhdfcbank', 'txn_id' => 'FMPIB5173772712', 'utr' => '121627974837', 'amount' => 1.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-04-15 11:40:00', 'type' => 'income', 'party' => 'Yasmeen Imran Mujawar', 'upi' => 'yasminmujawar0204@okaxis', 'txn_id' => 'FMPIB5173615176', 'utr' => '610577929463', 'amount' => 100.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-04-13 17:50:00', 'type' => 'expense', 'party' => 'Indian Railways Uts', 'upi' => 'bdpg2.iruts@sbi', 'txn_id' => 'FMPIB5156120113', 'utr' => '610345600155', 'amount' => 4.85, 'category' => 'Transport & Fuel'],
        ];

        // Page 18
        $pages[18] = [
            ['datetime' => '2026-04-13 17:50:00', 'type' => 'income', 'party' => 'Yasmeen Imran Mujawar', 'upi' => 'yasminmujawar0204@okaxis', 'txn_id' => 'FMPIB5156117571', 'utr' => '610388874285', 'amount' => 5.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-04-13 15:57:00', 'type' => 'expense', 'party' => 'Zepto', 'upi' => 'zeptomarketplaceprivat-12682647.payu@indus', 'txn_id' => 'FMPIB5154812928', 'utr' => '610344789712', 'amount' => 111.00, 'category' => 'Shopping & Groceries'],
            ['datetime' => '2026-04-13 15:31:00', 'type' => 'income', 'party' => 'Yasmeen Imran Mujawar', 'upi' => 'yasminmujawar0204@okaxis', 'txn_id' => 'FMPIB5154544066', 'utr' => '610346070578', 'amount' => 10.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-04-13 15:23:00', 'type' => 'income', 'party' => 'Yasmeen Imran Mujawar', 'upi' => 'yasminmujawar0204@okaxis', 'txn_id' => 'FMPIB5154462686', 'utr' => '610339862415', 'amount' => 70.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-04-13 12:57:00', 'type' => 'expense', 'party' => 'Irctc', 'upi' => 'indian.railway.irctc@icici', 'txn_id' => 'FMPIB5152886875', 'utr' => '610343600261', 'amount' => 192.25, 'category' => 'Transport & Fuel'],
            ['datetime' => '2026-04-13 12:54:00', 'type' => 'income', 'party' => 'Muskan Imran Mujawar', 'upi' => '7350334459@axl', 'txn_id' => 'FMPIB5152855781', 'utr' => '552720405370', 'amount' => 192.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-04-12 00:48:00', 'type' => 'expense', 'party' => 'Zepto', 'upi' => 'zeptomarketplaceprivat-12682647.payu@indus', 'txn_id' => 'FMPIB5139190821', 'utr' => '610235473386', 'amount' => 104.00, 'category' => 'Shopping & Groceries'],
            ['datetime' => '2026-04-12 00:29:00', 'type' => 'income', 'party' => 'Yasmeen Imran Mujawar', 'upi' => 'yasminmujawar0204@okhdfcbank', 'txn_id' => 'FMPIB5139146852', 'utr' => '121460055132', 'amount' => 110.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-04-10 12:10:00', 'type' => 'expense', 'party' => 'Zepto', 'upi' => 'zepto.payu@hdfcbank', 'txn_id' => 'FMPIB5120154245', 'utr' => '610023907950', 'amount' => 99.00, 'category' => 'Shopping & Groceries'],
            ['datetime' => '2026-04-10 11:50:00', 'type' => 'income', 'party' => 'Imran Daud Mujawar', 'upi' => 'imranmujawar4444@oksbi', 'txn_id' => 'FMPIB5119963751', 'utr' => '610053119307', 'amount' => 124.00, 'category' => 'Family Inflow'],
        ];

        // Page 19
        $pages[19] = [
            ['datetime' => '2026-04-09 15:00:00', 'type' => 'expense', 'party' => 'Zepto', 'upi' => 'zeptomarketplacepriva.payu@mairtel', 'txn_id' => 'FMPIB5111433679', 'utr' => '609918693022', 'amount' => 131.00, 'category' => 'Shopping & Groceries'],
            ['datetime' => '2026-04-09 14:48:00', 'type' => 'income', 'party' => 'Imran Daud Mujawar', 'upi' => 'imranmujawar4444@okaxis', 'txn_id' => 'FMPIB5111309539', 'utr' => '646518700907', 'amount' => 104.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-04-09 14:39:00', 'type' => 'income', 'party' => 'Digigold', 'upi' => null, 'txn_id' => 'FMPIB5111220935', 'utr' => null, 'amount' => 22.00, 'category' => 'Other Income'],
            ['datetime' => '2026-04-09 14:38:00', 'type' => 'income', 'party' => 'Sohel Imran Mujawar', 'upi' => null, 'txn_id' => 'FMPIB5111210266', 'utr' => null, 'amount' => 5.00, 'category' => 'Transfers & Friends'],
            ['datetime' => '2026-04-08 21:27:00', 'type' => 'expense', 'party' => 'Sohel Imran Mujawar', 'upi' => null, 'txn_id' => 'FMPIB5106105104', 'utr' => null, 'amount' => 2.00, 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-04-05 20:25:00', 'type' => 'expense', 'party' => 'Muskan Imran Mujawar', 'upi' => '7350334459@ibl', 'txn_id' => 'FMPIB5074616288', 'utr' => '646196709053', 'amount' => 60.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-04-05 11:43:00', 'type' => 'income', 'party' => 'Indian Railway Catering And Tourism Corporation Limited', 'upi' => 'indianrailwayca252644.rzp@rxaxis', 'txn_id' => 'FMPIB5068489889', 'utr' => '609411235181', 'amount' => 60.36, 'category' => 'Cashback & Refunds'],
            ['datetime' => '2026-04-04 19:30:00', 'type' => 'expense', 'party' => 'Shanno Dastagir Chaudhari', 'upi' => 'paytm.s1ynbwf@pty', 'txn_id' => 'FMPIB5063509200', 'utr' => '646090099324', 'amount' => 10.00, 'category' => 'General & Services'],
            ['datetime' => '2026-04-04 11:50:00', 'type' => 'expense', 'party' => 'Indian Railway Catering And Tourism Corporation Limited', 'upi' => 'indianrailwayca252644.rzp@rxaxis', 'txn_id' => 'FMPIB86227772', 'utr' => '609411235181', 'amount' => 192.25, 'category' => 'Transport & Fuel'],
            ['datetime' => '2026-04-04 11:47:00', 'type' => 'income', 'party' => 'Muskan Imran Mujawar', 'upi' => '7350334459@ybl', 'txn_id' => 'FMPIB5058279616', 'utr' => '124421079719', 'amount' => 200.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-04-03 20:14:00', 'type' => 'expense', 'party' => 'Jio', 'upi' => 'jiostarindiapri263667.rzprec@rxairtel', 'txn_id' => 'FMPIB5054115017', 'utr' => null, 'amount' => 1.00, 'category' => 'Utilities & Bills'],
        ];

        // Page 20
        $pages[20] = [
            ['datetime' => '2026-04-03 20:13:00', 'type' => 'income', 'party' => 'Azim Irfan Mujawar', 'upi' => null, 'txn_id' => 'FMPIB5054092951', 'utr' => null, 'amount' => 5.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-04-03 13:22:00', 'type' => 'expense', 'party' => 'Zepto', 'upi' => 'zeptomarketplaceprivat-12682647.payu@indus', 'txn_id' => 'FMPIB5049278183', 'utr' => '645981618565', 'amount' => 105.00, 'category' => 'Shopping & Groceries'],
            ['datetime' => '2026-04-03 12:57:00', 'type' => 'income', 'party' => 'Yasmeen Imran Mujawar', 'upi' => '9067210380-3@axl', 'txn_id' => 'FMPIB5049029838', 'utr' => '255922527070', 'amount' => 5.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-04-03 10:52:00', 'type' => 'income', 'party' => 'Imran Daud Mujawar', 'upi' => 'imranmujawar4444@oksbi', 'txn_id' => 'FMPIB5047891284', 'utr' => '609390619653', 'amount' => 100.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-03-27 14:15:00', 'type' => 'expense', 'party' => 'Zepto', 'upi' => 'zeptomarketplaceprivat-12682647.payu@indus', 'txn_id' => 'FMPIB4981699231', 'utr' => '645241525412', 'amount' => 91.00, 'category' => 'Shopping & Groceries'],
            ['datetime' => '2026-03-27 13:36:00', 'type' => 'income', 'party' => 'Yasmeen Imran Mujawar', 'upi' => '9067210380-3@ybl', 'txn_id' => 'FMPIB4981317258', 'utr' => '195299272510', 'amount' => 10.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-03-27 13:29:00', 'type' => 'income', 'party' => 'Imran Daud Mujawar', 'upi' => 'imranmujawar4444@oksbi', 'txn_id' => 'FMPIB4981247695', 'utr' => '608658468505', 'amount' => 60.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-03-26 18:13:00', 'type' => 'expense', 'party' => 'Darakshaan Afzal Hussain', 'upi' => null, 'txn_id' => 'FMPIB4974451385', 'utr' => null, 'amount' => 5.00, 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-03-26 14:50:00', 'type' => 'income', 'party' => 'Azim Irfan Mujawar', 'upi' => null, 'txn_id' => 'FMPIB4972390251', 'utr' => null, 'amount' => 20.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-03-26 09:39:00', 'type' => 'expense', 'party' => 'Zepto', 'upi' => 'zeptomarketplaceprivat-12682647.payu@indus', 'txn_id' => 'FMPIB4969666441', 'utr' => '645134373235', 'amount' => 78.00, 'category' => 'Shopping & Groceries'],
            ['datetime' => '2026-03-26 09:34:00', 'type' => 'income', 'party' => 'Imran Daud Mujawar', 'upi' => 'imranmujawar4444@oksbi', 'txn_id' => 'FMPIB4969632669', 'utr' => '608577919163', 'amount' => 80.00, 'category' => 'Family Inflow'],
        ];

        // Page 21
        $pages[21] = [
            ['datetime' => '2026-03-24 23:51:00', 'type' => 'expense', 'party' => 'Zepto', 'upi' => 'zeptonow.bdpg111@kotakpay', 'txn_id' => 'FMPIB4958678288', 'utr' => '644927917942', 'amount' => 56.00, 'category' => 'Shopping & Groceries'],
            ['datetime' => '2026-03-24 23:39:00', 'type' => 'income', 'party' => 'Yasmeen Imran Mujawar', 'upi' => '9067210380-3@axl', 'txn_id' => 'FMPIB4958644360', 'utr' => '748968609494', 'amount' => 56.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-03-24 13:08:00', 'type' => 'expense', 'party' => 'Zepto', 'upi' => 'zepto.payu@axisbank', 'txn_id' => 'FMPIB4951946876', 'utr' => '644923784132', 'amount' => 214.00, 'category' => 'Shopping & Groceries'],
            ['datetime' => '2026-03-24 13:00:00', 'type' => 'income', 'party' => 'Imran Daud Mujawar', 'upi' => 'imranmujawar4444@okaxis', 'txn_id' => 'FMPIB4951859663', 'utr' => '608337018451', 'amount' => 200.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-03-23 23:17:00', 'type' => 'expense', 'party' => 'Zepto', 'upi' => 'zeptoonline@ybl', 'txn_id' => 'FMPIB4948869740', 'utr' => '644822036539', 'amount' => 95.00, 'category' => 'Shopping & Groceries'],
            ['datetime' => '2026-03-23 23:15:00', 'type' => 'income', 'party' => 'Krish', 'upi' => null, 'txn_id' => 'FMPIB4948861668', 'utr' => null, 'amount' => 55.00, 'category' => 'Transfers & Friends'],
            ['datetime' => '2026-03-23 13:09:00', 'type' => 'expense', 'party' => 'Zepto', 'upi' => 'zeptomarketplaceprivat-12682647.payu@indus', 'txn_id' => 'FMPIB4942107979', 'utr' => '644817938448', 'amount' => 117.00, 'category' => 'Shopping & Groceries'],
            ['datetime' => '2026-03-23 12:47:00', 'type' => 'income', 'party' => 'Yasmeen Imran Mujawar', 'upi' => '9067210380-3@ybl', 'txn_id' => 'FMPIB4941882264', 'utr' => '526808298676', 'amount' => 58.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-03-23 12:41:00', 'type' => 'expense', 'party' => 'Irfan Daud Muzawar', 'upi' => 'mujawarirfan609@okaxis', 'txn_id' => 'FMPIB4941822013', 'utr' => '644817762643', 'amount' => 100.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-03-23 11:11:00', 'type' => 'income', 'party' => 'Irctc', 'upi' => 'indian.railway.irctc@icici', 'txn_id' => 'FMPIB4940986763', 'utr' => '607155481928', 'amount' => 101.44, 'category' => 'Cashback & Refunds'],
        ];

        // Page 22
        $pages[22] = [
            ['datetime' => '2026-03-22 13:58:00', 'type' => 'expense', 'party' => 'Azim Abdul Sattar Mujawar', 'upi' => 'mujawarazim9@okaxis', 'txn_id' => 'FMPIB4932965014', 'utr' => '644712559714', 'amount' => 123.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-03-22 13:43:00', 'type' => 'income', 'party' => 'Muskan Imran Mujawar', 'upi' => '7350334459@axl', 'txn_id' => 'FMPIB4932813454', 'utr' => '052244436758', 'amount' => 61.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-03-22 10:59:00', 'type' => 'income', 'party' => 'Azim Irfan Mujawar', 'upi' => null, 'txn_id' => 'FMPIB4931257685', 'utr' => null, 'amount' => 50.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-03-22 10:59:00', 'type' => 'income', 'party' => 'Azim Irfan Mujawar', 'upi' => null, 'txn_id' => 'FMPIB4931253929', 'utr' => null, 'amount' => 50.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-03-21 17:39:00', 'type' => 'expense', 'party' => 'Airtel Prepaid', 'upi' => null, 'txn_id' => 'FMPIB019d104d-3319-76d6-a5f1-1cb09c6b54cd', 'utr' => null, 'amount' => 22.99, 'category' => 'Utilities & Bills'],
            ['datetime' => '2026-03-21 14:55:00', 'type' => 'income', 'party' => 'Irfan Daud Muzawar', 'upi' => 'mujawarirfan609@okaxis', 'txn_id' => 'FMPIB4924186741', 'utr' => '644652297711', 'amount' => 100.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-03-18 17:03:00', 'type' => 'expense', 'party' => 'Zepto', 'upi' => 'zeptomarketplaceprivat-12682647.payu@indus', 'txn_id' => 'FMPIB4895977067', 'utr' => '607790959354', 'amount' => 117.00, 'category' => 'Shopping & Groceries'],
            ['datetime' => '2026-03-18 16:48:00', 'type' => 'income', 'party' => 'Yasmeen Imran Mujawar', 'upi' => '9067210380-3@ybl', 'txn_id' => 'FMPIB4895815410', 'utr' => '697725810182', 'amount' => 107.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-03-15 15:53:00', 'type' => 'expense', 'party' => 'Zepto', 'upi' => 'zepto.payu@axisbank', 'txn_id' => 'FMPIB4866454763', 'utr' => '607473464594', 'amount' => 100.00, 'category' => 'Shopping & Groceries'],
            ['datetime' => '2026-03-15 15:38:00', 'type' => 'expense', 'party' => 'Yasmeen Imran Mujawar', 'upi' => '9067210380-3@axl', 'txn_id' => 'FMPIB4866321906', 'utr' => '607473387071', 'amount' => 200.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-03-15 15:18:00', 'type' => 'income', 'party' => 'Yasmeen Imran Mujawar', 'upi' => '9067210380-3@axl', 'txn_id' => 'FMPIB4866145632', 'utr' => '549065343712', 'amount' => 300.00, 'category' => 'Family Inflow'],
        ];

        // Page 23
        $pages[23] = [
            ['datetime' => '2026-03-12 12:53:00', 'type' => 'expense', 'party' => 'Irctc', 'upi' => 'indian.railway.irctc@icici', 'txn_id' => 'FMPIB4835940745', 'utr' => '607155481928', 'amount' => 353.60, 'category' => 'Transport & Fuel'],
            ['datetime' => '2026-03-12 12:52:00', 'type' => 'income', 'party' => 'Irfan Daud Muzawar', 'upi' => 'mujawarirfan609@okhdfcbank', 'txn_id' => 'FMPIB4835929925', 'utr' => '119896792913', 'amount' => 350.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-03-12 12:09:00', 'type' => 'expense', 'party' => 'Yasmeen Imran Mujawar', 'upi' => '9067210380-3@axl', 'txn_id' => 'FMPIB4835550055', 'utr' => '607155241593', 'amount' => 45.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-03-11 20:30:00', 'type' => 'expense', 'party' => 'Irctc', 'upi' => 'indian.railway.irctc@icici', 'txn_id' => 'FMPIB4831687337', 'utr' => '607053004710', 'amount' => 353.60, 'category' => 'Transport & Fuel'],
            ['datetime' => '2026-03-11 20:25:00', 'type' => 'income', 'party' => 'Irfan Daud Muzawar', 'upi' => 'mujawarirfan609@okaxis', 'txn_id' => 'FMPIB4831610042', 'utr' => '607012834676', 'amount' => 400.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-03-11 13:39:00', 'type' => 'expense', 'party' => 'Zepto', 'upi' => 'zeptomarketplacepriva.payu@mairtel', 'txn_id' => 'FMPIB4827019011', 'utr' => '607050188811', 'amount' => 136.00, 'category' => 'Shopping & Groceries'],
            ['datetime' => '2026-03-11 13:16:00', 'type' => 'income', 'party' => 'Yasmeen Imran Mujawar', 'upi' => 'yasminmujawar0204@okaxis', 'txn_id' => 'FMPIB4826785164', 'utr' => '643648380921', 'amount' => 40.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-03-11 13:10:00', 'type' => 'income', 'party' => 'Imran Daud Mujawar', 'upi' => 'imranmujawar4444@okaxis', 'txn_id' => 'FMPIB4826719655', 'utr' => '643624994233', 'amount' => 100.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-03-10 17:24:00', 'type' => 'expense', 'party' => 'Aarush Enterprise', 'upi' => 'gpay-11183881768@okbizaxis', 'txn_id' => 'FMPIB4819593163', 'utr' => '606945896295', 'amount' => 65.00, 'category' => 'Shopping & Groceries'],
            ['datetime' => '2026-03-10 17:20:00', 'type' => 'income', 'party' => 'Yasmeen Imran Mujawar', 'upi' => '9067210380-3@axl', 'txn_id' => 'FMPIB4819552664', 'utr' => '036850018894', 'amount' => 20.00, 'category' => 'Family Inflow'],
        ];

        // Page 24
        $pages[24] = [
            ['datetime' => '2026-03-10 10:50:00', 'type' => 'expense', 'party' => 'Santu Bhagwant Gaikwad', 'upi' => 'gaikwadsantu2572@okicici', 'txn_id' => 'FMPIB4816024126', 'utr' => '606943742163', 'amount' => 50.00, 'category' => 'General & Services'],
            ['datetime' => '2026-03-10 07:20:00', 'type' => 'income', 'party' => 'Imran Daud Mujawar', 'upi' => 'imranmujawar4444@oksbi', 'txn_id' => 'FMPIB4814939236', 'utr' => '606900287294', 'amount' => 100.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-03-09 12:48:00', 'type' => 'expense', 'party' => 'Yasmeen Imran Mujawar', 'upi' => '9067210380-3@axl', 'txn_id' => 'FMPIB4807717609', 'utr' => '606838819271', 'amount' => 20.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-03-08 21:44:00', 'type' => 'expense', 'party' => 'Darakshaan Afzal Hussain', 'upi' => null, 'txn_id' => 'FMPIB4804418751', 'utr' => null, 'amount' => 10.00, 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-03-08 10:28:00', 'type' => 'expense', 'party' => 'Blinkit', 'upi' => 'paytm-blinkit@ptybl', 'txn_id' => 'FMPIB4797396011', 'utr' => '606732735306', 'amount' => 221.00, 'category' => 'Shopping & Groceries'],
            ['datetime' => '2026-03-08 10:18:00', 'type' => 'income', 'party' => 'Yasmeen Imran Mujawar', 'upi' => '9067210380-3@axl', 'txn_id' => 'FMPIB4797324983', 'utr' => '285472721967', 'amount' => 221.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-03-07 16:12:00', 'type' => 'income', 'party' => 'Yasmeen Imran Mujawar', 'upi' => 'yasminmujawar0204@okaxis', 'txn_id' => 'FMPIB4791106213', 'utr' => '606667605123', 'amount' => 30.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-02-27 21:16:00', 'type' => 'expense', 'party' => 'Aarush Enterprise', 'upi' => 'gpay-11251531550@okbizaxis', 'txn_id' => 'FMPIB4722754459', 'utr' => '642489017467', 'amount' => 49.00, 'category' => 'Shopping & Groceries'],
            ['datetime' => '2026-02-27 21:10:00', 'type' => 'income', 'party' => 'Yasmeen Imran Mujawar', 'upi' => '9067210380-3@ybl', 'txn_id' => 'FMPIB4722693299', 'utr' => '082993840950', 'amount' => 50.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-02-26 14:17:00', 'type' => 'expense', 'party' => 'Zepto', 'upi' => 'zepto.payu@axisbank', 'txn_id' => 'FMPIB4709017891', 'utr' => '642380830474', 'amount' => 235.00, 'category' => 'Shopping & Groceries'],
        ];

        // Page 25
        $pages[25] = [
            ['datetime' => '2026-02-26 14:09:00', 'type' => 'income', 'party' => 'Yasmeen Imran Mujawar', 'upi' => '9067210380-3@axl', 'txn_id' => 'FMPIB4708948264', 'utr' => '199663728552', 'amount' => 10.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-02-26 14:08:00', 'type' => 'expense', 'party' => 'Yasmeen Imran Mujawar', 'upi' => '9067210380-3@ibl', 'txn_id' => 'FMPIB4708943518', 'utr' => '642380785526', 'amount' => 100.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-02-26 14:06:00', 'type' => 'income', 'party' => 'Yasmeen Imran Mujawar', 'upi' => '9067210380-3@ybl', 'txn_id' => 'FMPIB4708923564', 'utr' => '417321337294', 'amount' => 235.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-02-26 12:50:00', 'type' => 'income', 'party' => 'Shivam Santosh Sharma', 'upi' => null, 'txn_id' => 'FMPIB4708221718', 'utr' => null, 'amount' => 50.00, 'category' => 'Transfers & Friends'],
            ['datetime' => '2026-02-26 12:38:00', 'type' => 'expense', 'party' => 'Govind Tiwari', 'upi' => 'paytmqr6mplhq@ptys', 'txn_id' => 'FMPIB4708111560', 'utr' => '642380279034', 'amount' => 50.00, 'category' => 'General & Services'],
            ['datetime' => '2026-02-23 22:30:00', 'type' => 'income', 'party' => 'Imran Daud Mujawar', 'upi' => 'imranmujawar4444@oksbi', 'txn_id' => 'FMPIB4687982829', 'utr' => '605419624235', 'amount' => 50.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-02-21 20:11:00', 'type' => 'expense', 'party' => 'Sikandar Mustafa Shaikh', 'upi' => 'paytm.s1q9ri6@pty', 'txn_id' => 'FMPIB4669210103', 'utr' => '641857170280', 'amount' => 70.00, 'category' => 'General & Services'],
            ['datetime' => '2026-02-21 19:22:00', 'type' => 'income', 'party' => 'Yasmeen Imran Mujawar', 'upi' => '9067210380-3@axl', 'txn_id' => 'FMPIB4668479540', 'utr' => '858829737761', 'amount' => 100.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-02-21 15:18:00', 'type' => 'expense', 'party' => 'Jio Prepaid', 'upi' => null, 'txn_id' => 'FMPIB019c7f99-ca16-70e6-87e4-131627cc866a', 'utr' => null, 'amount' => 320.99, 'category' => 'Utilities & Bills'],
            ['datetime' => '2026-02-21 15:16:00', 'type' => 'income', 'party' => 'Azim Irfan Mujawar', 'upi' => null, 'txn_id' => 'FMPIB4665686430', 'utr' => null, 'amount' => 320.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-02-21 13:58:00', 'type' => 'expense', 'party' => 'Shadowfax', 'upi' => 'bharatpe5022547940@yesbankltd', 'txn_id' => 'FMPIB4664972469', 'utr' => '641854545986', 'amount' => 89.00, 'category' => 'General & Services'],
        ];

        // Page 26
        $pages[26] = [
            ['datetime' => '2026-02-21 13:57:00', 'type' => 'income', 'party' => 'Yasmeen Imran Mujawar', 'upi' => '9067210380-3@axl', 'txn_id' => 'FMPIB4664970445', 'utr' => '726668839056', 'amount' => 100.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-02-21 12:40:00', 'type' => 'expense', 'party' => 'Aarush Enterprise', 'upi' => 'gpay-11251531550@okbizaxis', 'txn_id' => 'FMPIB4664269258', 'utr' => '641854118585', 'amount' => 15.00, 'category' => 'Shopping & Groceries'],
            ['datetime' => '2026-02-20 19:33:00', 'type' => 'expense', 'party' => 'Aarush Enterprise', 'upi' => 'gpay-11251531550@okbizaxis', 'txn_id' => 'FMPIB4659894274', 'utr' => '641751529864', 'amount' => 53.00, 'category' => 'Shopping & Groceries'],
            ['datetime' => '2026-02-20 19:28:00', 'type' => 'income', 'party' => 'Imran Daud Mujawar', 'upi' => 'imranmujawar4444@okaxis', 'txn_id' => 'FMPIB4659837645', 'utr' => '641785629633', 'amount' => 60.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-02-18 16:42:00', 'type' => 'expense', 'party' => 'Pyari Devi', 'upi' => 'paytm.s1qm5st@pty', 'txn_id' => 'FMPIB4640585687', 'utr' => '641539849239', 'amount' => 20.00, 'category' => 'General & Services'],
            ['datetime' => '2026-02-18 16:32:00', 'type' => 'expense', 'party' => 'Mehboob Khaja Hussai', 'upi' => 'q371753341@ybl', 'txn_id' => 'FMPIB4640489948', 'utr' => '641539790365', 'amount' => 100.00, 'category' => 'General & Services'],
            ['datetime' => '2026-02-18 16:19:00', 'type' => 'income', 'party' => 'Imran Daud Mujawar', 'upi' => 'imranmujawar4444@oksbi', 'txn_id' => 'FMPIB4640361411', 'utr' => '604985070149', 'amount' => 100.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-02-18 13:38:00', 'type' => 'expense', 'party' => 'Zepto', 'upi' => 'zepto.payu@hdfcbank', 'txn_id' => 'FMPIB4638850768', 'utr' => '641538775656', 'amount' => 151.00, 'category' => 'Shopping & Groceries'],
            ['datetime' => '2026-02-18 13:37:00', 'type' => 'income', 'party' => 'Yasmeen Imran Mujawar', 'upi' => 'yasminmujawar0204@okaxis', 'txn_id' => 'FMPIB4638847440', 'utr' => '604902593975', 'amount' => 151.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-02-18 13:31:00', 'type' => 'expense', 'party' => 'Yasmeen Imran Mujawar', 'upi' => 'yasminmujawar0204@oksbi', 'txn_id' => 'FMPIB4638784485', 'utr' => '641538734234', 'amount' => 30.00, 'category' => 'Family Inflow'],
        ];

        // Page 27
        $pages[27] = [
            ['datetime' => '2026-02-16 22:24:00', 'type' => 'expense', 'party' => 'Mrs. Pushpa Krishna Shahu', 'upi' => '7977230235@idfcfirst', 'txn_id' => 'FMPIB4626656915', 'utr' => '641331398697', 'amount' => 10.00, 'category' => 'General & Services'],
            ['datetime' => '2026-02-16 17:25:00', 'type' => 'expense', 'party' => 'Aarush Enterprise', 'upi' => 'gpay-11251531550@okbizaxis', 'txn_id' => 'FMPIB4623118925', 'utr' => '641329315737', 'amount' => 29.00, 'category' => 'Shopping & Groceries'],
            ['datetime' => '2026-02-16 17:23:00', 'type' => 'expense', 'party' => 'Aarush Enterprise', 'upi' => 'gpay-11251531550@okbizaxis', 'txn_id' => 'FMPIB4623098455', 'utr' => '641329302870', 'amount' => 10.00, 'category' => 'Shopping & Groceries'],
            ['datetime' => '2026-02-16 17:13:00', 'type' => 'income', 'party' => 'Imran Daud Mujawar', 'upi' => 'imranmujawar4444@oksbi', 'txn_id' => 'FMPIB4622984570', 'utr' => '604795518701', 'amount' => 50.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-02-14 12:29:00', 'type' => 'expense', 'party' => 'Aarush Enterprise', 'upi' => 'gpay-11251531550@okbizaxis', 'txn_id' => 'FMPIB4601344470', 'utr' => '641116132573', 'amount' => 58.00, 'category' => 'Shopping & Groceries'],
            ['datetime' => '2026-02-14 12:25:00', 'type' => 'income', 'party' => 'Imran Daud Mujawar', 'upi' => 'imranmujawar4444@oksbi', 'txn_id' => 'FMPIB4601302493', 'utr' => '604572995893', 'amount' => 100.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-02-11 20:22:00', 'type' => 'expense', 'party' => 'Yasmeen Imran Mujawar', 'upi' => '9067210380-3@ibl', 'txn_id' => 'FMPIB4578692235', 'utr' => '640802536507', 'amount' => 30.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-02-10 16:35:00', 'type' => 'expense', 'party' => 'Audiva F M Private Limited', 'upi' => 'paytm-83965507@ptybl', 'txn_id' => 'FMPIB4566218884', 'utr' => '604194894979', 'amount' => 100.00, 'category' => 'Entertainment & Leisure'],
            ['datetime' => '2026-02-09 20:05:00', 'type' => 'income', 'party' => 'Yasmeen Imran Mujawar', 'upi' => 'yasminmujawar0204@okaxis', 'txn_id' => 'FMPIB4559751249', 'utr' => '640660370651', 'amount' => 40.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-02-09 17:36:00', 'type' => 'expense', 'party' => 'Shekhar Singh', 'upi' => null, 'txn_id' => 'FMPIB4557634853', 'utr' => null, 'amount' => 10.00, 'category' => 'General & Services'],
        ];

        // Page 28
        $pages[28] = [
            ['datetime' => '2026-02-09 17:35:00', 'type' => 'expense', 'party' => 'Jio Prepaid', 'upi' => null, 'txn_id' => 'FMPIB019c424a-ac49-768a-88ef-d8f80277d568', 'utr' => null, 'amount' => 29.99, 'category' => 'Utilities & Bills'],
            ['datetime' => '2026-02-09 17:33:00', 'type' => 'income', 'party' => 'Shekhar Singh', 'upi' => null, 'txn_id' => 'FMPIB4557603494', 'utr' => null, 'amount' => 39.00, 'category' => 'Transfers & Friends'],
            ['datetime' => '2026-02-09 13:15:00', 'type' => 'expense', 'party' => 'Shaboddin Jahangir Shaikh', 'upi' => 'shabuddinsk307@okaxis', 'txn_id' => 'FMPIB4554951818', 'utr' => '604087997029', 'amount' => 40.00, 'category' => 'General & Services'],
            ['datetime' => '2026-02-09 12:48:00', 'type' => 'income', 'party' => 'Miss Muskan Imran Mujawar', 'upi' => 'muskanmujawar2005-1@okaxis', 'txn_id' => 'FMPIB4554677526', 'utr' => '640610615671', 'amount' => 50.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-02-07 22:48:00', 'type' => 'income', 'party' => 'Miss Muskan Imran Mujawar', 'upi' => 'muskanmujawar2005-1@okaxis', 'txn_id' => 'FMPIB4542462162', 'utr' => '640482962701', 'amount' => 50.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-02-07 13:24:00', 'type' => 'expense', 'party' => 'Yasmeen Imran Mujawar', 'upi' => 'yasminmujawar0204@oksbi', 'txn_id' => 'FMPIB4536121281', 'utr' => '603876852577', 'amount' => 30.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-02-06 18:35:00', 'type' => 'income', 'party' => 'Imran Daud Mujawar', 'upi' => 'imranmujawar4444@okaxis', 'txn_id' => 'FMPIB4530091814', 'utr' => '603771848701', 'amount' => 30.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-02-06 17:05:00', 'type' => 'income', 'party' => 'Aniket Kumar Singh', 'upi' => null, 'txn_id' => 'FMPIB4528935093', 'utr' => null, 'amount' => 30.00, 'category' => 'Transfers & Friends'],
            ['datetime' => '2026-02-04 20:37:00', 'type' => 'expense', 'party' => 'Aarush Enterprise', 'upi' => 'gpay-11251531550@okbizaxis', 'txn_id' => 'FMPIB4513531210', 'utr' => '603563326784', 'amount' => 35.00, 'category' => 'Shopping & Groceries'],
            ['datetime' => '2026-02-04 20:33:00', 'type' => 'income', 'party' => 'Yasmeen Imran Mujawar', 'upi' => 'yasminmujawar0204@okhdfcbank', 'txn_id' => 'FMPIB4513487288', 'utr' => '118172897698', 'amount' => 50.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-02-04 18:33:00', 'type' => 'income', 'party' => 'Ujjwal Choudhary', 'upi' => null, 'txn_id' => 'FMPIB4511825286', 'utr' => null, 'amount' => 1.00, 'category' => 'Transfers & Friends'],
        ];

        // Page 29
        $pages[29] = [
            ['datetime' => '2026-02-03 12:36:00', 'type' => 'expense', 'party' => 'Aarush Enterprise', 'upi' => 'gpay-11251531550@okbizaxis', 'txn_id' => 'FMPIB4498967379', 'utr' => '603454466484', 'amount' => 50.00, 'category' => 'Shopping & Groceries'],
            ['datetime' => '2026-02-03 12:31:00', 'type' => 'income', 'party' => 'Yasmeen Imran Mujawar', 'upi' => 'yasminmujawar0204@okaxis', 'txn_id' => 'FMPIB4498921096', 'utr' => '640000053068', 'amount' => 50.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-01-30 19:49:00', 'type' => 'expense', 'party' => 'Shivam', 'upi' => null, 'txn_id' => 'FMPIB4467406632', 'utr' => null, 'amount' => 55.00, 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-01-30 19:49:00', 'type' => 'expense', 'party' => 'Priyanshu Pandey', 'upi' => null, 'txn_id' => 'FMPIB4467402943', 'utr' => null, 'amount' => 50.00, 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-01-30 13:02:00', 'type' => 'income', 'party' => 'Priyanshu Pandey', 'upi' => null, 'txn_id' => 'FMPIB4462899315', 'utr' => null, 'amount' => 50.00, 'category' => 'Transfers & Friends'],
            ['datetime' => '2026-01-29 17:25:00', 'type' => 'income', 'party' => 'Shivam', 'upi' => null, 'txn_id' => 'FMPIB4456594136', 'utr' => null, 'amount' => 55.00, 'category' => 'Transfers & Friends'],
            ['datetime' => '2026-01-28 13:27:00', 'type' => 'expense', 'party' => 'Darakshaan Afzal Hussain', 'upi' => null, 'txn_id' => 'FMPIB4445458518', 'utr' => null, 'amount' => 17.00, 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-01-28 13:24:00', 'type' => 'income', 'party' => 'Imran Daud Mujawar', 'upi' => 'imranmujawar4444@oksbi', 'txn_id' => 'FMPIB4445428117', 'utr' => '602891827042', 'amount' => 20.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-01-26 21:47:00', 'type' => 'expense', 'party' => 'Imran Daud Mujawar', 'upi' => 'imranmujawar4444@oksbi', 'txn_id' => 'FMPIB4433738487', 'utr' => '602615660562', 'amount' => 20.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-01-26 18:06:00', 'type' => 'expense', 'party' => 'Aarush Enterprise', 'upi' => 'gpay-11251531550@okbizaxis', 'txn_id' => 'FMPIB4431034627', 'utr' => '602614062465', 'amount' => 10.00, 'category' => 'Shopping & Groceries'],
            ['datetime' => '2026-01-26 18:01:00', 'type' => 'income', 'party' => 'Imran Daud Mujawar', 'upi' => 'imranmujawar4444@oksbi', 'txn_id' => 'FMPIB4430975829', 'utr' => '602689821452', 'amount' => 30.00, 'category' => 'Family Inflow'],
        ];

        // Page 30
        $pages[30] = [
            ['datetime' => '2026-01-25 18:46:00', 'type' => 'expense', 'party' => 'Aarush Enterprise', 'upi' => 'gpay-11251531550@okbizaxis', 'txn_id' => 'FMPIB4422598596', 'utr' => '602509097339', 'amount' => 50.00, 'category' => 'Shopping & Groceries'],
            ['datetime' => '2026-01-25 18:40:00', 'type' => 'income', 'party' => 'Yasmeen Imran Mujawar', 'upi' => '9067210380-3@axl', 'txn_id' => 'FMPIB4422512261', 'utr' => '196570834939', 'amount' => 50.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-01-24 19:28:00', 'type' => 'expense', 'party' => 'Yasmeen Imran Mujawar', 'upi' => '9067210380-3@ibl', 'txn_id' => 'FMPIB4414073106', 'utr' => '602404114332', 'amount' => 10.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-01-24 08:21:00', 'type' => 'expense', 'party' => 'Irctc', 'upi' => 'indian.railway.irctc@icici', 'txn_id' => 'FMPIB81606800', 'utr' => '109048929511', 'amount' => 192.25, 'category' => 'Transport & Fuel'],
            ['datetime' => '2026-01-24 08:20:00', 'type' => 'income', 'party' => 'Yasmeen Imran Mujawar', 'upi' => '9067210380-3@axl', 'txn_id' => 'FMPIB4407809401', 'utr' => '741649209367', 'amount' => 100.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-01-23 17:24:00', 'type' => 'expense', 'party' => 'Google Play Gift Card', 'upi' => null, 'txn_id' => 'FMPIB019beab4-dae8-7089-8d39-965814fd96ac', 'utr' => null, 'amount' => 20.00, 'category' => 'Entertainment & Leisure'],
            ['datetime' => '2026-01-23 12:25:00', 'type' => 'expense', 'party' => 'Rashida Malang Shaikh', 'upi' => 'q485469945@ybl', 'txn_id' => 'FMPIB4400537748', 'utr' => '638996016517', 'amount' => 10.00, 'category' => 'General & Services'],
            ['datetime' => '2026-01-23 10:17:00', 'type' => 'expense', 'party' => 'Indian Railway Catering And Tourism Corporation Limited', 'upi' => 'indianrailwayca252644.rzp@rxaxis', 'txn_id' => 'FMPIB81522991', 'utr' => '602310294596', 'amount' => 372.70, 'category' => 'Transport & Fuel'],
            ['datetime' => '2026-01-23 07:17:00', 'type' => 'income', 'party' => 'Imran Daud Mujawar', 'upi' => 'imranmujawar4444@oksbi', 'txn_id' => 'FMPIB4398642020', 'utr' => '602387033497', 'amount' => 500.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-01-19 11:23:00', 'type' => 'expense', 'party' => 'Imrandautmujawar', 'upi' => '7020997570@upi', 'txn_id' => 'FMPIB4364228599', 'utr' => '638574156295', 'amount' => 300.00, 'category' => 'Family Inflow'],
        ];

        // Page 31
        $pages[31] = [
            ['datetime' => '2026-01-19 11:21:00', 'type' => 'income', 'party' => 'Imran Daud Mujawar', 'upi' => 'imranmujawar4444@oksbi', 'txn_id' => 'FMPIB4364208601', 'utr' => '601998956089', 'amount' => 300.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-01-10 15:31:00', 'type' => 'expense', 'party' => 'Shyama Devi Wo Praveen Kumar', 'upi' => 'mishra.pravin101-1@okhdfcbank', 'txn_id' => 'FMPIB4285417435', 'utr' => '637627303260', 'amount' => 30.00, 'category' => 'General & Services'],
            ['datetime' => '2026-01-10 15:30:00', 'type' => 'income', 'party' => 'Yasmeen Imran Mujawar', 'upi' => 'yasminmujawar0204@okaxis', 'txn_id' => 'FMPIB4285410048', 'utr' => '601067935575', 'amount' => 10.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-01-09 23:04:00', 'type' => 'expense', 'party' => 'Abhijay Shailesh Shetgaonkar', 'upi' => null, 'txn_id' => 'FMPIB4281184055', 'utr' => null, 'amount' => 80.00, 'category' => 'General & Services'],
            ['datetime' => '2026-01-09 22:49:00', 'type' => 'income', 'party' => 'Sidarth Amb', 'upi' => 'bm5082908@okaxis', 'txn_id' => 'FMPIB4281118368', 'utr' => '637597886383', 'amount' => 70.00, 'category' => 'Transfers & Friends'],
            ['datetime' => '2026-01-09 22:22:00', 'type' => 'expense', 'party' => 'Dfft', 'upi' => 'paytm-83864611@ptybl', 'txn_id' => 'FMPIB4280978652', 'utr' => '637524693295', 'amount' => 109.00, 'category' => 'Food & Dining'],
            ['datetime' => '2026-01-09 22:22:00', 'type' => 'income', 'party' => 'Imrandautmujawar', 'upi' => '7020997570@upi', 'txn_id' => 'FMPIB4280976544', 'utr' => '222233440241', 'amount' => 110.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-01-09 18:22:00', 'type' => 'expense', 'party' => 'Nilam Vilas Unde', 'upi' => 'paytm.s1ta86u@pty', 'txn_id' => 'FMPIB4278116428', 'utr' => '637522977782', 'amount' => 95.00, 'category' => 'General & Services'],
            ['datetime' => '2026-01-09 18:14:00', 'type' => 'income', 'party' => 'Imrandautmujawar', 'upi' => '7020997570@upi', 'txn_id' => 'FMPIB4278014159', 'utr' => '181444806295', 'amount' => 100.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-01-08 16:50:00', 'type' => 'income', 'party' => 'Shekhar Singh', 'upi' => null, 'txn_id' => 'FMPIB4267937890', 'utr' => null, 'amount' => 19.00, 'category' => 'Transfers & Friends'],
            ['datetime' => '2026-01-08 16:29:00', 'type' => 'expense', 'party' => 'Jio Prepaid', 'upi' => null, 'txn_id' => 'FMPIB019b9d43-6b37-794b-b1db-8d5089bcbcbe', 'utr' => null, 'amount' => 19.99, 'category' => 'Utilities & Bills'],
        ];

        // Page 32
        $pages[32] = [
            ['datetime' => '2026-01-08 15:07:00', 'type' => 'expense', 'party' => 'Tabassum Wasim Inamdar', 'upi' => null, 'txn_id' => 'FMPIB4266918531', 'utr' => null, 'amount' => 1.00, 'category' => 'Transfers & Payments'],
            ['datetime' => '2026-01-08 15:03:00', 'type' => 'income', 'party' => 'Tabassum Wasim Inamdar', 'upi' => null, 'txn_id' => 'FMPIB4266886647', 'utr' => null, 'amount' => 1.00, 'category' => 'Transfers & Friends'],
            ['datetime' => '2026-01-08 11:54:00', 'type' => 'income', 'party' => 'Google Play', 'upi' => 'playstore1.bd@axisbank', 'txn_id' => 'FMPIB4265139464', 'utr' => '591155800086', 'amount' => 2.00, 'category' => 'Cashback & Refunds'],
            ['datetime' => '2026-01-08 11:53:00', 'type' => 'expense', 'party' => 'Google Play', 'upi' => 'playstore1.bd@axisbank', 'txn_id' => 'FMPIB4265131115', 'utr' => null, 'amount' => 2.00, 'category' => 'Entertainment & Leisure'],
            ['datetime' => '2026-01-06 17:16:00', 'type' => 'income', 'party' => 'Yasmeen Imran Mujawar', 'upi' => 'yasminmujawar0204@okhdfcbank', 'txn_id' => 'FMPIB4250739774', 'utr' => '116774267245', 'amount' => 12.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-01-06 11:09:00', 'type' => 'expense', 'party' => 'Yasmeen Imran Mujawar', 'upi' => '9067210380-3@ibl', 'txn_id' => 'FMPIB4247338593', 'utr' => '637204536521', 'amount' => 2.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-01-05 14:59:00', 'type' => 'expense', 'party' => 'Indian Railways Uts', 'upi' => 'bdpg2.iruts@sbi', 'txn_id' => 'FMPIB4240725977', 'utr' => '637100591877', 'amount' => 10.00, 'category' => 'Transport & Fuel'],
            ['datetime' => '2026-01-03 21:02:00', 'type' => 'expense', 'party' => 'Umesh Prakash Gangurde', 'upi' => 'q910978815@ybl', 'txn_id' => 'FMPIB4227420581', 'utr' => '600392800059', 'amount' => 70.00, 'category' => 'General & Services'],
            ['datetime' => '2026-01-03 19:07:00', 'type' => 'income', 'party' => 'Yasmeen Imran Mujawar', 'upi' => 'yasminmujawar0204@okhdfcbank', 'txn_id' => 'FMPIB4225992727', 'utr' => '116628365201', 'amount' => 100.00, 'category' => 'Family Inflow'],
            ['datetime' => '2025-12-31 23:51:00', 'type' => 'expense', 'party' => 'Yasmeen Imran Mujawar', 'upi' => 'yasminmujawar0204@oksbi', 'txn_id' => 'FMPIB4201282779', 'utr' => '536577378182', 'amount' => 20.00, 'category' => 'Family Inflow'],
            ['datetime' => '2025-12-31 23:50:00', 'type' => 'income', 'party' => 'Azim Irfan Mujawar', 'upi' => null, 'txn_id' => 'FMPIB4201279501', 'utr' => null, 'amount' => 20.00, 'category' => 'Family Inflow'],
        ];

        // Page 33
        $pages[33] = [
            ['datetime' => '2025-12-30 20:32:00', 'type' => 'expense', 'party' => 'Tomar Enterprises', 'upi' => 'q051079524@ybl', 'txn_id' => 'FMPIB4189559144', 'utr' => '536470649540', 'amount' => 30.00, 'category' => 'Shopping & Groceries'],
            ['datetime' => '2025-12-30 17:55:00', 'type' => 'expense', 'party' => 'Indian Railways', 'upi' => 'railsbiupi11.812301349269480cr@sbi', 'txn_id' => 'FMPIB4187500385', 'utr' => '536469424321', 'amount' => 10.00, 'category' => 'Transport & Fuel'],
            ['datetime' => '2025-12-29 22:38:00', 'type' => 'expense', 'party' => 'Aditya Chauhan', 'upi' => null, 'txn_id' => 'FMPIB4181987838', 'utr' => null, 'amount' => 10.00, 'category' => 'Transfers & Payments'],
            ['datetime' => '2025-12-29 20:12:00', 'type' => 'income', 'party' => 'Shekhar Singh', 'upi' => null, 'txn_id' => 'FMPIB4180727615', 'utr' => null, 'amount' => 50.00, 'category' => 'Transfers & Friends'],
            ['datetime' => '2025-12-28 21:11:00', 'type' => 'expense', 'party' => 'Aarush Enterprise', 'upi' => 'gpay-11251531550@okbizaxis', 'txn_id' => 'FMPIB4172918328', 'utr' => '536260848029', 'amount' => 50.00, 'category' => 'Shopping & Groceries'],
            ['datetime' => '2025-12-28 21:07:00', 'type' => 'income', 'party' => 'Yasmeen Imran Mujawar', 'upi' => 'yasminmujawar0204@okaxis', 'txn_id' => 'FMPIB4172884297', 'utr' => '572834687842', 'amount' => 20.00, 'category' => 'Family Inflow'],
            ['datetime' => '2025-12-28 21:07:00', 'type' => 'income', 'party' => 'Yasmeen Imran Mujawar', 'upi' => 'yasminmujawar0204@okhdfcbank', 'txn_id' => 'FMPIB4172881485', 'utr' => '116326363787', 'amount' => 30.00, 'category' => 'Family Inflow'],
            ['datetime' => '2025-12-28 20:50:00', 'type' => 'expense', 'party' => 'Miss Muskan Imran Mujawar', 'upi' => 'muskanmujawar2005-1@okaxis', 'txn_id' => 'FMPIB4172713718', 'utr' => '536260727683', 'amount' => 20.00, 'category' => 'Family Inflow'],
            ['datetime' => '2025-12-28 20:48:00', 'type' => 'income', 'party' => 'Shekhar Singh', 'upi' => null, 'txn_id' => 'FMPIB4172692160', 'utr' => null, 'amount' => 2.00, 'category' => 'Transfers & Friends'],
            ['datetime' => '2025-12-28 20:06:00', 'type' => 'expense', 'party' => 'Bigfoot Retail Solutions Private Limited', 'upi' => 'bigfootretailso9.rzp@icici', 'txn_id' => 'FMPIB4172226806', 'utr' => '536260439644', 'amount' => 200.00, 'category' => 'Shopping & Groceries'],
            ['datetime' => '2025-12-28 20:06:00', 'type' => 'income', 'party' => 'Shekhar Singh', 'upi' => null, 'txn_id' => 'FMPIB4172225292', 'utr' => null, 'amount' => 200.00, 'category' => 'Transfers & Friends'],
        ];

        // Page 34
        $pages[34] = [
            ['datetime' => '2025-12-28 19:13:00', 'type' => 'expense', 'party' => 'Miss Muskan Imran Mujawar', 'upi' => 'muskanmujawar2005-1@okaxis', 'txn_id' => 'FMPIB4171544794', 'utr' => '536260035141', 'amount' => 22.00, 'category' => 'Family Inflow'],
            ['datetime' => '2025-12-28 14:47:00', 'type' => 'expense', 'party' => 'Vrikshit Foundation', 'upi' => '7827552596@okbizaxis', 'txn_id' => 'FMPIB4168565582', 'utr' => '536258271549', 'amount' => 10.00, 'category' => 'General & Services'],
            ['datetime' => '2025-12-27 14:07:00', 'type' => 'income', 'party' => 'Yasmeen Imran Mujawar', 'upi' => 'yasminmujawar0204@okaxis', 'txn_id' => 'FMPIB459595302', 'utr' => '536112577081', 'amount' => 50.00, 'category' => 'Family Inflow'],
            ['datetime' => '2025-12-27 13:52:00', 'type' => 'expense', 'party' => 'Azim Irfan Mujawar', 'upi' => null, 'txn_id' => 'FMPIB4159448483', 'utr' => null, 'amount' => 99.00, 'category' => 'Transfers & Payments'],
            ['datetime' => '2025-12-27 12:08:00', 'type' => 'expense', 'party' => 'Aarush Enterprise', 'upi' => 'gpay-11251531550@okbizaxis', 'txn_id' => 'FMPIB4158483170', 'utr' => '536152364767', 'amount' => 30.00, 'category' => 'Shopping & Groceries'],
            ['datetime' => '2025-12-27 12:05:00', 'type' => 'income', 'party' => 'Miss Muskan Imran Mujawar', 'upi' => 'muskanmujawar2005-1@okaxis', 'txn_id' => 'FMPIB4158455372', 'utr' => '536162264617', 'amount' => 30.00, 'category' => 'Family Inflow'],
            ['datetime' => '2025-12-26 16:24:00', 'type' => 'expense', 'party' => 'Mitkar Vada Pav', 'upi' => 'gpay-12190527839@okbizaxis', 'txn_id' => 'FMPIB4152245582', 'utr' => '536048704005', 'amount' => 20.00, 'category' => 'Food & Dining'],
            ['datetime' => '2025-12-26 16:23:00', 'type' => 'expense', 'party' => 'Mitkar Vada Pav', 'upi' => 'gpay-12190527839@okbizaxis', 'txn_id' => 'FMPIB4152233184', 'utr' => '536048696520', 'amount' => 20.00, 'category' => 'Food & Dining'],
            ['datetime' => '2025-12-26 15:11:00', 'type' => 'expense', 'party' => 'Indian Railways', 'upi' => 'railsbiupi11.812261342704047cr@sbi', 'txn_id' => 'FMPIB4151553095', 'utr' => '536048289615', 'amount' => 20.00, 'category' => 'Transport & Fuel'],
            ['datetime' => '2025-12-26 14:20:00', 'type' => 'expense', 'party' => 'R K Associates', 'upi' => 'q460858005@ybl', 'txn_id' => 'FMPIB4151085143', 'utr' => '536048010111', 'amount' => 15.00, 'category' => 'General & Services'],
        ];

        // Page 35
        $pages[35] = [
            ['datetime' => '2025-12-25 16:16:00', 'type' => 'expense', 'party' => 'Aarush Enterprise', 'upi' => 'gpay-11251531550@okbizaxis', 'txn_id' => 'FMPIB4143517485', 'utr' => '535943560626', 'amount' => 30.00, 'category' => 'Shopping & Groceries'],
            ['datetime' => '2025-12-25 13:12:00', 'type' => 'income', 'party' => 'Azim Irfan Mujawar', 'upi' => null, 'txn_id' => 'FMPIB4141734755', 'utr' => null, 'amount' => 200.00, 'category' => 'Family Inflow'],
            ['datetime' => '2025-12-23 20:00:00', 'type' => 'expense', 'party' => 'Deepak Mohanlal Chourisia', 'upi' => 'paytmqr6t14ra@ptys', 'txn_id' => 'FMPIB4128360421', 'utr' => '535734694658', 'amount' => 5.00, 'category' => 'General & Services'],
            ['datetime' => '2025-12-23 19:19:00', 'type' => 'expense', 'party' => 'Mr Suresh Lal Dang', 'upi' => 'q767405370@ybl', 'txn_id' => 'FMPIB4127818884', 'utr' => '535734371654', 'amount' => 10.00, 'category' => 'General & Services'],
            ['datetime' => '2025-12-23 19:17:00', 'type' => 'income', 'party' => 'Yasmeen Imran Mujawar', 'upi' => 'yasminmujawar0204@okhdfcbank', 'txn_id' => 'FMPIB4127784183', 'utr' => '116078962140', 'amount' => 10.00, 'category' => 'Family Inflow'],
            ['datetime' => '2025-12-23 18:30:00', 'type' => 'expense', 'party' => 'Aarush Enterprise', 'upi' => 'gpay-11251531550@okbizaxis', 'txn_id' => 'FMPIB4127134695', 'utr' => '535733960981', 'amount' => 10.00, 'category' => 'Shopping & Groceries'],
            ['datetime' => '2025-12-23 17:11:00', 'type' => 'expense', 'party' => 'Sambhaji Maruti Wagh', 'upi' => 'paytmqr6u7jlu@ptys', 'txn_id' => 'FMPIB4126081484', 'utr' => '535733312915', 'amount' => 20.00, 'category' => 'General & Services'],
            ['datetime' => '2025-12-22 16:24:00', 'type' => 'expense', 'party' => 'Niranjan Ganesh Rathod', 'upi' => 'niranjangrathod77-3@okaxis', 'txn_id' => 'FMPIB4116787736', 'utr' => '535627778275', 'amount' => 423.00, 'category' => 'General & Services'],
            ['datetime' => '2025-12-22 16:03:00', 'type' => 'income', 'party' => 'Imran Daud Mujawar', 'upi' => 'imranmujawar4444@oksbi', 'txn_id' => 'FMPIB4116572270', 'utr' => '535664438837', 'amount' => 200.00, 'category' => 'Family Inflow'],
            ['datetime' => '2025-12-22 16:02:00', 'type' => 'income', 'party' => 'Azim Irfan Mujawar', 'upi' => null, 'txn_id' => 'FMPIB4116561110', 'utr' => null, 'amount' => 40.00, 'category' => 'Family Inflow'],
        ];

        // Page 36
        $pages[36] = [
            ['datetime' => '2025-12-22 16:01:00', 'type' => 'income', 'party' => 'Miss Muskan Imran Mujawar', 'upi' => 'muskanmujawar2005-1@okaxis', 'txn_id' => 'FMPIB4116557844', 'utr' => '535606438685', 'amount' => 160.00, 'category' => 'Family Inflow'],
            ['datetime' => '2025-12-20 23:58:00', 'type' => 'income', 'party' => 'Azim Irfan Mujawar', 'upi' => null, 'txn_id' => 'FMPIB4103643355', 'utr' => null, 'amount' => 40.00, 'category' => 'Family Inflow'],
            ['datetime' => '2025-12-20 19:15:00', 'type' => 'expense', 'party' => 'Aarush Enterprise', 'upi' => 'gpay-11251531550@okbizaxis', 'txn_id' => 'FMPIB4101288378', 'utr' => '535418667252', 'amount' => 29.00, 'category' => 'Shopping & Groceries'],
            ['datetime' => '2025-12-19 22:01:00', 'type' => 'expense', 'party' => 'Aarush Enterprise', 'upi' => 'gpay-11251531550@okbizaxis', 'txn_id' => 'FMPIB4094242259', 'utr' => '535314489815', 'amount' => 50.00, 'category' => 'Shopping & Groceries'],
            ['datetime' => '2025-12-18 14:51:00', 'type' => 'expense', 'party' => 'Tikuji Snacks Counter 1', 'upi' => 'paytmqr5emveu@ptys', 'txn_id' => 'FMPIB4080546846', 'utr' => '535206305577', 'amount' => 10.00, 'category' => 'Food & Dining'],
            ['datetime' => '2025-12-18 06:34:00', 'type' => 'income', 'party' => 'Imran Daud Mujawar', 'upi' => 'imranmujawar4444@okaxis', 'txn_id' => 'FMPIB4077396703', 'utr' => '535227739329', 'amount' => 100.00, 'category' => 'Family Inflow'],
            ['datetime' => '2025-12-14 16:09:00', 'type' => 'expense', 'party' => 'Mohammed Imran', 'upi' => 'mohammedimrangadang-1@okaxis', 'txn_id' => 'FMPIB4046537000', 'utr' => '571485921388', 'amount' => 100.00, 'category' => 'Transfers & Payments'],
            ['datetime' => '2025-12-14 16:08:00', 'type' => 'income', 'party' => 'Yasmeen Imran Mujawar', 'upi' => 'yasminmujawar0204@okaxis', 'txn_id' => 'FMPIB4046532450', 'utr' => '534828290834', 'amount' => 50.00, 'category' => 'Family Inflow'],
            ['datetime' => '2025-12-14 11:36:00', 'type' => 'expense', 'party' => 'Dhanshri Santosh Kamble', 'upi' => 'dahnshrikamble@okhdfcbank', 'txn_id' => 'FMPIB4043881749', 'utr' => '571484360981', 'amount' => 50.00, 'category' => 'General & Services'],
            ['datetime' => '2025-12-14 11:35:00', 'type' => 'income', 'party' => 'Yasmeen Imran Mujawar', 'upi' => 'yasminmujawar0204@okhdfcbank', 'txn_id' => 'FMPIB4043867516', 'utr' => '115608535873', 'amount' => 100.00, 'category' => 'Family Inflow'],
        ];

        // Page 37
        $pages[37] = [
            ['datetime' => '2025-12-09 21:54:00', 'type' => 'expense', 'party' => 'Hungry Birds', 'upi' => 'paytmqr6l6c8c@ptys', 'txn_id' => 'FMPIB4006168238', 'utr' => '570961889131', 'amount' => 60.00, 'category' => 'Food & Dining'],
            ['datetime' => '2025-12-09 21:54:00', 'type' => 'income', 'party' => 'Muskan Imran Mujawar', 'upi' => '7350334459@axl', 'txn_id' => 'FMPIB4006165999', 'utr' => '423655015197', 'amount' => 70.00, 'category' => 'Family Inflow'],
            ['datetime' => '2025-12-05 17:52:00', 'type' => 'expense', 'party' => 'Aarush Enterprise', 'upi' => 'gpay-11251531550@okbizaxis', 'txn_id' => 'FMPIB3967930860', 'utr' => '570539127278', 'amount' => 31.00, 'category' => 'Shopping & Groceries'],
            ['datetime' => '2025-12-05 17:47:00', 'type' => 'income', 'party' => 'Imran Daud Mujawar', 'upi' => 'imranmujawar4444@oksbi', 'txn_id' => 'FMPIB3967865975', 'utr' => '533995338892', 'amount' => 30.00, 'category' => 'Family Inflow'],
            ['datetime' => '2025-12-03 21:12:00', 'type' => 'expense', 'party' => 'Aarush Enterprise', 'upi' => 'gpay-11251531550@okbizaxis', 'txn_id' => 'FMPIB3953535218', 'utr' => '570330517579', 'amount' => 40.00, 'category' => 'Shopping & Groceries'],
            ['datetime' => '2025-12-03 21:07:00', 'type' => 'income', 'party' => 'Imran Daud Mujawar', 'upi' => 'imranmujawar4444@oksbi', 'txn_id' => 'FMPIB3953485381', 'utr' => '533714430545', 'amount' => 20.00, 'category' => 'Family Inflow'],
            ['datetime' => '2025-12-03 15:39:00', 'type' => 'expense', 'party' => 'Indian Railways Uts', 'upi' => 'bdpg.iruts@sbi', 'txn_id' => 'FMPIB3949562106', 'utr' => '570328082699', 'amount' => 20.00, 'category' => 'Transport & Fuel'],
            ['datetime' => '2025-12-03 11:31:00', 'type' => 'income', 'party' => 'Yasmeen Imran Mujawar', 'upi' => 'yasminmujawar0204@okaxis', 'txn_id' => 'FMPIB3947452262', 'utr' => '570386489992', 'amount' => 20.00, 'category' => 'Family Inflow'],
            ['datetime' => '2025-12-01 19:53:00', 'type' => 'expense', 'party' => 'Jaisinh Banderao Ingle', 'upi' => 'q054214504@ybl', 'txn_id' => 'FMPIB3935809108', 'utr' => '570119850351', 'amount' => 5.00, 'category' => 'General & Services'],
            ['datetime' => '2025-12-01 09:48:00', 'type' => 'income', 'party' => 'Imran Daud Mujawar', 'upi' => 'imranmujawar4444@okicici', 'txn_id' => 'FMPIB3930118740', 'utr' => '570180316783', 'amount' => 15.00, 'category' => 'Family Inflow'],
        ];

        // Page 38
        $pages[38] = [
            ['datetime' => '2025-11-30 19:25:00', 'type' => 'expense', 'party' => 'Nabilal Dastagir Tamboli', 'upi' => 'paytm.s129w85@pty', 'txn_id' => 'FMPIB3927172460', 'utr' => '570014704379', 'amount' => 15.00, 'category' => 'General & Services'],
            ['datetime' => '2025-11-28 17:44:00', 'type' => 'expense', 'party' => 'Mr Dhondappa Shivsharan Kumbhar1', 'upi' => 'bharatpe.90069623153@fbpe', 'txn_id' => 'FMPIB3908514538', 'utr' => '569803898282', 'amount' => 5.00, 'category' => 'General & Services'],
            ['datetime' => '2025-11-28 15:05:00', 'type' => 'expense', 'party' => 'Nanda Bairva', 'upi' => '9664477370-2@ibl', 'txn_id' => 'FMPIB3906886589', 'utr' => '569802910841', 'amount' => 20.00, 'category' => 'General & Services'],
            ['datetime' => '2025-11-28 15:04:00', 'type' => 'income', 'party' => 'Imran Daud Mujawar', 'upi' => 'imranmujawar4444@okaxis', 'txn_id' => 'FMPIB3906880769', 'utr' => '569813024785', 'amount' => 50.00, 'category' => 'Family Inflow'],
            ['datetime' => '2025-11-28 13:29:00', 'type' => 'expense', 'party' => 'Mr Dhondappa Shivsharan Kumbhar1', 'upi' => 'bharatpe.90069623153@fbpe', 'txn_id' => 'FMPIB3906042574', 'utr' => '569802394869', 'amount' => 2.00, 'category' => 'General & Services'],
            ['datetime' => '2025-11-25 12:54:00', 'type' => 'expense', 'party' => 'Aarush Enterprise', 'upi' => '7045481757@okbizaxis', 'txn_id' => 'FMPIB3880679945', 'utr' => '532987203024', 'amount' => 45.00, 'category' => 'Shopping & Groceries'],
            ['datetime' => '2025-11-25 12:50:00', 'type' => 'income', 'party' => 'Yasmeen Imran Mujawar', 'upi' => 'yasminmujawar0204@okaxis', 'txn_id' => 'FMPIB3880645128', 'utr' => '532934025573', 'amount' => 45.00, 'category' => 'Family Inflow'],
            ['datetime' => '2025-11-24 17:19:00', 'type' => 'expense', 'party' => 'Sohanlal Binjaram Parmar', 'upi' => 'q781732771@ybl', 'txn_id' => 'FMPIB3874731123', 'utr' => '532883657525', 'amount' => 30.00, 'category' => 'General & Services'],
            ['datetime' => '2025-11-24 16:10:00', 'type' => 'expense', 'party' => 'Aarush Enterprise', 'upi' => 'gpay-11183881768@okbizaxis', 'txn_id' => 'FMPIB3874007479', 'utr' => '532883211963', 'amount' => 10.00, 'category' => 'Shopping & Groceries'],
            ['datetime' => '2025-11-24 16:03:00', 'type' => 'income', 'party' => 'Yasmeen Imran Mujawar', 'upi' => 'yasminmujawar0204@okaxis', 'txn_id' => 'FMPIB3873937558', 'utr' => '569408850742', 'amount' => 40.00, 'category' => 'Family Inflow'],
        ];

        // Page 39
        $pages[39] = [
            ['datetime' => '2025-11-20 17:21:00', 'type' => 'expense', 'party' => 'Yasmeen Imran Mujawar', 'upi' => 'yasminmujawar0204@okaxis', 'txn_id' => 'FMPIB3840170774', 'utr' => '532463068274', 'amount' => 40.00, 'category' => 'Family Inflow'],
            ['datetime' => '2025-11-18 19:51:00', 'type' => 'income', 'party' => 'Azim Irfan Mujawar', 'upi' => null, 'txn_id' => 'FMPIB3825292194', 'utr' => null, 'amount' => 40.00, 'category' => 'Family Inflow'],
            ['datetime' => '2025-11-09 21:28:00', 'type' => 'expense', 'party' => 'Mohammed Arif Abdul Gafoor Ansari', 'upi' => 'paytmqr6c72t3@ptys', 'txn_id' => 'FMPIB3749081929', 'utr' => '531308348606', 'amount' => 96.00, 'category' => 'General & Services'],
            ['datetime' => '2025-11-09 20:55:00', 'type' => 'income', 'party' => 'Yasmeen Imran Mujawar', 'upi' => 'yasminmujawar0204@okaxis', 'txn_id' => 'FMPIB3748768021', 'utr' => '567915742587', 'amount' => 20.00, 'category' => 'Family Inflow'],
            ['datetime' => '2025-11-09 20:51:00', 'type' => 'income', 'party' => 'Muskan Imran Mujawar', 'upi' => '7350334459@ybl', 'txn_id' => 'FMPIB3748730306', 'utr' => '269590340310', 'amount' => 80.00, 'category' => 'Family Inflow'],
            ['datetime' => '2025-11-08 12:09:00', 'type' => 'expense', 'party' => 'Nishi Kirana And General Store', 'upi' => 'kesharwanipremchand8-1@okicici', 'txn_id' => 'FMPIB3734287476', 'utr' => '567899567985', 'amount' => 30.00, 'category' => 'Shopping & Groceries'],
            ['datetime' => '2025-11-08 12:07:00', 'type' => 'income', 'party' => 'Yasmeen Imran Mujawar', 'upi' => 'yasminmujawar0204@okhdfcbank', 'txn_id' => 'FMPIB3734275663', 'utr' => '113831605699', 'amount' => 30.00, 'category' => 'Family Inflow'],
            ['datetime' => '2025-11-05 23:14:00', 'type' => 'expense', 'party' => 'Darakshaan Afzal Hussain', 'upi' => null, 'txn_id' => 'FMPIB3715455223', 'utr' => null, 'amount' => 50.00, 'category' => 'Transfers & Payments'],
            ['datetime' => '2025-11-05 17:46:00', 'type' => 'expense', 'party' => 'Mrs Suchita Dhiraj Jaiswal', 'upi' => 'q032046480@ybl', 'txn_id' => 'FMPIB3712108819', 'utr' => '567586318420', 'amount' => 10.00, 'category' => 'General & Services'],
            ['datetime' => '2025-11-05 17:41:00', 'type' => 'expense', 'party' => 'Premium Chicken', 'upi' => 'gpay-12191157695@okbizaxis', 'txn_id' => 'FMPIB3712044568', 'utr' => '567586278900', 'amount' => 50.00, 'category' => 'Food & Dining'],
        ];

        // Page 40
        $pages[40] = [
            ['datetime' => '2025-11-05 17:24:00', 'type' => 'income', 'party' => 'Muskan Imran Mujawar', 'upi' => '7350334459@axl', 'txn_id' => 'FMPIB3711842868', 'utr' => '659777523974', 'amount' => 60.00, 'category' => 'Family Inflow'],
            ['datetime' => '2025-11-05 17:00:00', 'type' => 'income', 'party' => 'Darakshaan Afzal Hussain', 'upi' => null, 'txn_id' => 'FMPIB3711580226', 'utr' => null, 'amount' => 50.00, 'category' => 'Transfers & Friends'],
            ['datetime' => '2025-11-05 12:26:00', 'type' => 'expense', 'party' => 'Kesarwanijitendarnar', 'upi' => 'q764414513@ybl', 'txn_id' => 'FMPIB3709155757', 'utr' => '567584549492', 'amount' => 100.00, 'category' => 'General & Services'],
            ['datetime' => '2025-11-05 12:11:00', 'type' => 'income', 'party' => 'Muskan Imran Mujawar', 'upi' => '7350334459@ybl', 'txn_id' => 'FMPIB3709027157', 'utr' => '664935168216', 'amount' => 35.00, 'category' => 'Family Inflow'],
            ['datetime' => '2025-11-05 12:10:00', 'type' => 'income', 'party' => 'Muskan Imran Mujawar', 'upi' => '7350334459@ibl', 'txn_id' => 'FMPIB3709019971', 'utr' => '489842970531', 'amount' => 65.00, 'category' => 'Family Inflow'],
            ['datetime' => '2025-11-02 12:48:00', 'type' => 'expense', 'party' => 'Nishi Kirana And General Store', 'upi' => 'kesharwanipremchand8-1@okicici', 'txn_id' => 'FMPIB3684749198', 'utr' => '567270002386', 'amount' => 45.00, 'category' => 'Shopping & Groceries'],
            ['datetime' => '2025-10-31 18:52:00', 'type' => 'expense', 'party' => 'Muskan Imran Mujawar', 'upi' => '7350334459@ibl', 'txn_id' => 'FMPIB3671714648', 'utr' => '567062388472', 'amount' => 15.00, 'category' => 'Family Inflow'],
            ['datetime' => '2025-10-29 21:49:00', 'type' => 'expense', 'party' => 'Sahebaz Dilavar Khan Tadvi', 'upi' => 'sahehazkhan6n-2@oksbi', 'txn_id' => 'FMPIB3658127378', 'utr' => '566854400743', 'amount' => 40.00, 'category' => 'General & Services'],
            ['datetime' => '2025-10-27 22:25:00', 'type' => 'income', 'party' => 'Irfan Daud Muzawar', 'upi' => 'mujawarirfan609@okaxis', 'txn_id' => 'FMPIB3643331461', 'utr' => '530016032264', 'amount' => 100.00, 'category' => 'Family Inflow'],
            ['datetime' => '2025-10-27 21:51:00', 'type' => 'expense', 'party' => 'Azim Irfan Mujawar', 'upi' => null, 'txn_id' => 'FMPIB3643140400', 'utr' => null, 'amount' => 10.00, 'category' => 'Transfers & Payments'],
        ];

        // Page 41
        $pages[41] = [
            ['datetime' => '2025-10-26 21:12:00', 'type' => 'expense', 'party' => 'Arebiyan Shorma', 'upi' => 'gpay-12190006943@okbizaxis', 'txn_id' => 'FMPIB3635369773', 'utr' => '566540983559', 'amount' => 20.00, 'category' => 'Food & Dining'],
            ['datetime' => '2025-10-26 21:09:00', 'type' => 'expense', 'party' => 'Chinese House', 'upi' => 'gpay-11259444729@okbizaxis', 'txn_id' => 'FMPIB3635344874', 'utr' => '566540970029', 'amount' => 70.00, 'category' => 'Food & Dining'],
            ['datetime' => '2025-10-20 07:06:00', 'type' => 'expense', 'party' => 'Muskan Imran Mujawar', 'upi' => '7350334459@axl', 'txn_id' => 'FMPIB3582246566', 'utr' => '565910044785', 'amount' => 100.00, 'category' => 'Family Inflow'],
            ['datetime' => '2025-10-20 06:27:00', 'type' => 'income', 'party' => 'Imran Daud Mujawar', 'upi' => 'imranmujawar4444@okaxis', 'txn_id' => 'FMPIB3582200473', 'utr' => '529378491532', 'amount' => 200.00, 'category' => 'Family Inflow'],
            ['datetime' => '2025-10-19 17:01:00', 'type' => 'expense', 'party' => 'Nishi Kirana And General Store', 'upi' => 'kesharwanipremchand8-1@okicici', 'txn_id' => 'FMPIB3577824953', 'utr' => '565807440993', 'amount' => 30.00, 'category' => 'Shopping & Groceries'],
            ['datetime' => '2025-10-19 16:58:00', 'type' => 'income', 'party' => 'Yasmeen Imran Mujawar', 'upi' => 'yasminmujawar0204@okhdfcbank', 'txn_id' => 'FMPIB3577792588', 'utr' => '112901533980', 'amount' => 10.00, 'category' => 'Family Inflow'],
            ['datetime' => '2025-10-19 16:57:00', 'type' => 'income', 'party' => 'Sohel Imran Mujawar', 'upi' => null, 'txn_id' => 'FMPIB3577784127', 'utr' => null, 'amount' => 20.00, 'category' => 'Transfers & Friends'],
            ['datetime' => '2025-10-18 20:33:00', 'type' => 'expense', 'party' => 'Sohel Imran Mujawar', 'upi' => null, 'txn_id' => 'FMPIB3571637437', 'utr' => null, 'amount' => 23.00, 'category' => 'Transfers & Payments'],
            ['datetime' => '2025-10-18 20:32:00', 'type' => 'income', 'party' => 'Sohel Imran Mujawar', 'upi' => null, 'txn_id' => 'FMPIB3571618463', 'utr' => null, 'amount' => 20.00, 'category' => 'Transfers & Friends'],
            ['datetime' => '2025-10-17 09:39:00', 'type' => 'expense', 'party' => 'Imran Daud Mujawar', 'upi' => 'imranmujawar4444@okhdfcbank', 'txn_id' => 'FMPIB3556222025', 'utr' => '529094725853', 'amount' => 1.00, 'category' => 'Family Inflow'],
            ['datetime' => '2025-10-16 10:16:00', 'type' => 'expense', 'party' => 'Digigold', 'upi' => null, 'txn_id' => 'FMPIB3547826954', 'utr' => null, 'amount' => 10.00, 'category' => 'General & Services'],
        ];

        // Page 42
        $pages[42] = [
            ['datetime' => '2025-10-16 10:15:00', 'type' => 'expense', 'party' => 'Sohel Imran Mujawar', 'upi' => null, 'txn_id' => 'FMPIB3547821373', 'utr' => null, 'amount' => 10.00, 'category' => 'Transfers & Payments'],
            ['datetime' => '2025-10-16 09:53:00', 'type' => 'expense', 'party' => 'Mr Vanumamalai Perumal Velshanmugam Nadar', 'upi' => 'paytmqr6jje6c@ptys', 'txn_id' => 'FMPIB3547692697', 'utr' => '528989639281', 'amount' => 155.00, 'category' => 'General & Services'],
            ['datetime' => '2025-10-16 09:50:00', 'type' => 'income', 'party' => 'Yasmeen Imran Mujawar', 'upi' => 'yasminmujawar0204@okhdfcbank', 'txn_id' => 'FMPIB3547673660', 'utr' => '112721361134', 'amount' => 170.00, 'category' => 'Family Inflow'],
            ['datetime' => '2026-10-16 09:48:00', 'type' => 'expense', 'party' => 'Shrikant Sunil Bhalekar', 'upi' => 'paytmqr6aj29x@ptys', 'txn_id' => 'FMPIB3547666753', 'utr' => '528989624276', 'amount' => 24.00, 'category' => 'General & Services'],
            ['datetime' => '2025-10-10 18:52:00', 'type' => 'expense', 'party' => 'Yasmeen Imran Mujawar', 'upi' => 'yasminmujawar0204@okaxis', 'txn_id' => 'FMPIB3501974661', 'utr' => '528362399633', 'amount' => 10.00, 'category' => 'Family Inflow'],
            ['datetime' => '2025-10-10 18:51:00', 'type' => 'expense', 'party' => 'Yasmeen Imran Mujawar', 'upi' => 'yasminmujawar0204@okaxis', 'txn_id' => 'FMPIB3501964778', 'utr' => '528362393611', 'amount' => 30.00, 'category' => 'Family Inflow'],
            ['datetime' => '2025-10-07 11:27:00', 'type' => 'expense', 'party' => 'Muskan Imran Mujawar', 'upi' => '7350334459@ybl', 'txn_id' => 'FMPIB3473324172', 'utr' => '528045183552', 'amount' => 40.00, 'category' => 'Family Inflow'],
            ['datetime' => '2025-10-07 10:46:00', 'type' => 'expense', 'party' => 'Javed Chicken Centre', 'upi' => 'gpay-11207874950@okbizaxis', 'txn_id' => 'FMPIB3473049143', 'utr' => '528045019819', 'amount' => 50.00, 'category' => 'Food & Dining'],
            ['datetime' => '2025-10-07 10:44:00', 'type' => 'expense', 'party' => 'Choudhary Bhimaram S', 'upi' => 'paytmqr67ynn8@ptys', 'txn_id' => 'FMPIB3473037528', 'utr' => '528045012937', 'amount' => 30.00, 'category' => 'General & Services'],
            ['datetime' => '2025-10-07 10:43:00', 'type' => 'income', 'party' => 'Imran Daud Mujawar', 'upi' => 'imranmujawar4444@okaxis', 'txn_id' => 'FMPIB3473033678', 'utr' => '564643244924', 'amount' => 100.00, 'category' => 'Family Inflow'],
        ];

        // Page 43
        $pages[43] = [
            ['datetime' => '2025-10-07 10:38:00', 'type' => 'expense', 'party' => 'Jay Shree Ram Wada Pav', 'upi' => 'bajajpay.6879729.eze7112937@indus', 'txn_id' => 'FMPIB3473002497', 'utr' => '528044992026', 'amount' => 12.00, 'category' => 'Food & Dining'],
            ['datetime' => '2025-10-07 10:36:00', 'type' => 'expense', 'party' => 'Jay Shree Ram Wada Pav', 'upi' => 'bajajpay.6879729.eze7112937@indus', 'txn_id' => 'FMPIB3472990532', 'utr' => '528044984993', 'amount' => 45.00, 'category' => 'Food & Dining'],
            ['datetime' => '2025-10-04 19:40:00', 'type' => 'expense', 'party' => 'Muskan Imran Mujawar', 'upi' => '7350334459@ybl', 'txn_id' => 'FMPIB3454215125', 'utr' => '527733899187', 'amount' => 30.00, 'category' => 'Family Inflow'],
            ['datetime' => '2025-10-04 13:44:00', 'type' => 'expense', 'party' => 'Muskan Imran Mujawar', 'upi' => '7350334459@ybl', 'txn_id' => 'FMPIB3450797061', 'utr' => '527731845414', 'amount' => 20.00, 'category' => 'Family Inflow'],
            ['datetime' => '2025-10-01 18:24:00', 'type' => 'income', 'party' => 'Mr Ibrahim Ismail Shaikh', 'upi' => 'ibrahimshaikhq1386@oksbi', 'txn_id' => 'FMPIB3429552876', 'utr' => '527459510271', 'amount' => 200.00, 'category' => 'Transfers & Friends'],
        ];

        $allTransactions = [];
        foreach ($pages as $pageList) {
            foreach ($pageList as $tx) {
                $allTransactions[] = $tx;
            }
        }

        return $allTransactions;
    }
}
