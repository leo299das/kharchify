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

class ImportFamappStatement extends Command
{
    protected $signature = 'import:famapp-transactions {--email=darakshaan475@gmail.com} {--name=Darakshaan Afzal Hussain}';
    protected $description = 'Import 15-page FamApp statement transactions (31 Jul 2026 - 23 Sep 2026) for specified user';

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
                'monthly_budget' => 20000.00,
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

        // All 152 Transactions extracted from the 15-page statement (31 Jul 2026 - 23 Sep 2026)
        $transactions = $this->getTransactionsData();

        $importedExpenses = 0;
        $importedIncomes = 0;
        $totalExpenseSum = 0.0;
        $totalIncomeSum = 0.0;

        foreach ($transactions as $t) {
            $type = $t['type']; // 'expense' or 'income'
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
                $catId = $expCatMap[$categoryName] ?? $expCatMap['General & Services'];

                // Check if already exists to prevent duplicate insertion
                $existing = Expense::where('user_id', $user->id)
                    ->where('expense_date', $date)
                    ->where('amount', $amount)
                    ->where('paid_to', $party)
                    ->first();

                if (!$existing && $txnId) {
                    $existing = Expense::where('user_id', $user->id)
                        ->where('transaction_id', $txnId)
                        ->first();
                }

                if (!$existing) {
                    Expense::create([
                        'user_id' => $user->id,
                        'category_id' => $catId,
                        'amount' => $amount,
                        'paid_to' => $party,
                        'payment_method' => 'UPI / FamApp',
                        'transaction_id' => $txnId,
                        'note' => $note,
                        'expense_date' => $date,
                        'created_at' => $date,
                        'updated_at' => $date,
                    ]);
                    $importedExpenses++;
                }
                $totalExpenseSum += $amount;

            } elseif ($type === 'income') {
                $catId = $incCatMap[$categoryName] ?? $incCatMap['Other Income'];

                // Check if already exists to prevent duplicate insertion
                $existing = Income::where('user_id', $user->id)
                    ->where('income_date', $date)
                    ->where('amount', $amount)
                    ->where('source', $party)
                    ->first();

                if (!$existing && $txnId) {
                    $existing = Income::where('user_id', $user->id)
                        ->where('transaction_id', $txnId)
                        ->first();
                }

                if (!$existing) {
                    Income::create([
                        'user_id' => $user->id,
                        'income_category_id' => $catId,
                        'source' => $party,
                        'amount' => $amount,
                        'payment_method' => 'UPI / FamApp',
                        'transaction_id' => $txnId,
                        'note' => $note,
                        'income_date' => $date,
                        'created_at' => $date,
                        'updated_at' => $date,
                    ]);
                    $importedIncomes++;
                }
                $totalIncomeSum += $amount;
            }
        }

        $this->info("==================================================");
        $this->info("🎉 FamApp Statement Import Completed Successfully!");
        $this->info("==================================================");
        $this->info("👤 User: {$user->name} ({$user->email})");
        $this->info("📈 Incomes Processed: {$importedIncomes} records (+₹" . number_format($totalIncomeSum, 2) . ")");
        $this->info("📉 Expenses Processed: {$importedExpenses} records (-₹" . number_format($totalExpenseSum, 2) . ")");
        $this->info("💰 Net Inflow/Outflow: ₹" . number_format($totalIncomeSum - $totalExpenseSum, 2));
        $this->info("==================================================");

        return 0;
    }

    private function getTransactionsData(): array
    {
        return [
            // Page 1
            ['type' => 'expense', 'datetime' => '2026-09-23 08:28:00', 'party' => 'Bharati Arjun Jadhav', 'amount' => 125.00, 'category' => 'Food & Dining', 'upi' => 'paytm.s1qrtae@pty', 'txn_id' => 'TOTud260923025810A8065779DC5344A29D', 'utr' => '663271368328'],
            ['type' => 'expense', 'datetime' => '2026-09-23 08:21:00', 'party' => 'Nandkumar And Brothers', 'amount' => 122.00, 'category' => 'Shopping & Groceries', 'upi' => 'paytmqrobev2amja6@paytm', 'txn_id' => 'TOTud260923025152EF67866F5ADF4E42A1', 'utr' => '663271344276'],
            ['type' => 'expense', 'datetime' => '2026-09-23 08:16:00', 'party' => 'Tuljabhavani Amrutulya', 'amount' => 27.00, 'category' => 'Food & Dining', 'upi' => '7219763536@okbizaxis', 'txn_id' => 'TOTud26092302463354A8379CA3C14142B2', 'utr' => '663271324777'],
            ['type' => 'income', 'datetime' => '2026-09-23 07:30:00', 'party' => 'Aswad Ur Rahman', 'amount' => 150.00, 'category' => 'Transfers & Friends', 'txn_id' => 'FMPIB6653327340'],
            ['type' => 'expense', 'datetime' => '2026-09-22 22:29:00', 'party' => 'Google Play Gift Card', 'amount' => 40.00, 'category' => 'Entertainment & Leisure', 'txn_id' => 'FMPIB01a0ca0e-949e-75f0-ae8a-d514493ec27a'],
            ['type' => 'income', 'datetime' => '2026-09-22 20:48:00', 'party' => 'Harshit', 'amount' => 50.00, 'category' => 'Transfers & Friends', 'txn_id' => 'FMPIB6651692599'],

            // Page 2
            ['type' => 'expense', 'datetime' => '2026-09-22 16:35:00', 'party' => 'Bharati Arjun Jadhav', 'amount' => 731.00, 'category' => 'Food & Dining', 'upi' => 'paytm.s1qrtae@pty', 'txn_id' => 'TOTud260922110527076AEA2106B1425389', 'utr' => '663167014493'],
            ['type' => 'expense', 'datetime' => '2026-09-22 16:24:00', 'party' => 'Bhairu Narayan Dange', 'amount' => 50.00, 'category' => 'Transfers & Payments', 'upi' => 'q161083474@ybl', 'txn_id' => 'TOTud2609221054134FAF6390505A412EA8', 'utr' => '663166918641'],
            ['type' => 'income', 'datetime' => '2026-09-22 15:57:00', 'party' => 'Maaz Imtiyaz Shaikh', 'amount' => 1000.00, 'category' => 'Transfers & Friends', 'upi' => 'maaz.1738@waaxis', 'txn_id' => 'FMPIB6649526162', 'utr' => '663100731375'],
            ['type' => 'expense', 'datetime' => '2026-09-22 15:51:00', 'party' => 'Juber Valimohammad Fazlani', 'amount' => 592.00, 'category' => 'Transfers & Payments', 'upi' => 'q330284186@ybl', 'txn_id' => 'TOTud2609221021308263AA93C51D4298B3', 'utr' => '663166652450'],
            ['type' => 'expense', 'datetime' => '2026-09-22 10:10:00', 'party' => 'Cloudonfire Gbnag Upin', 'amount' => 175.82, 'category' => 'Utilities & Bills', 'txn_id' => 'FMPIB6647657550'],
            ['type' => 'income', 'datetime' => '2026-09-22 06:22:00', 'party' => 'Bindu .', 'amount' => 288.00, 'category' => 'Transfers & Friends', 'upi' => '9821118255@ptyes', 'txn_id' => 'FMPIB6646881787', 'utr' => '315237851852'],
            ['type' => 'income', 'datetime' => '2026-09-21 18:16:00', 'party' => 'Amir Khan', 'amount' => 150.00, 'category' => 'Transfers & Friends', 'upi' => '9630967520@ybl', 'txn_id' => 'FMPIB6644249336', 'utr' => '294920388744'],
            ['type' => 'expense', 'datetime' => '2026-09-21 15:24:00', 'party' => 'Jio Prepaid', 'amount' => 199.99, 'category' => 'Utilities & Bills', 'txn_id' => 'FMPIB01a0c363-6be3-7b19-bd9a-c89dc5855446'],
            ['type' => 'income', 'datetime' => '2026-09-21 15:23:00', 'party' => 'Tanisha Afzal Husain', 'amount' => 62.00, 'category' => 'Family Inflow', 'txn_id' => 'FMPIB6643089352'],
            ['type' => 'income', 'datetime' => '2026-09-21 12:57:00', 'party' => 'Mr Parth Jayant Lohakare', 'amount' => 36.00, 'category' => 'Transfers & Friends', 'upi' => 'parthlohakare8@oksbi', 'txn_id' => 'FMPIB6642210572', 'utr' => '626426873831'],
            ['type' => 'income', 'datetime' => '2026-09-21 08:02:00', 'party' => 'Tanisha Afzal Husain', 'amount' => 292.00, 'category' => 'Family Inflow', 'txn_id' => 'FMPIB6640928166'],

            // Page 3
            ['type' => 'income', 'datetime' => '2026-09-21 08:00:00', 'party' => 'Afzal Hussain Kader', 'amount' => 450.00, 'category' => 'Family Inflow', 'upi' => 'afzal.9056@waaxis', 'txn_id' => 'FMPIB6640924215', 'utr' => '663001107948'],
            ['type' => 'expense', 'datetime' => '2026-09-20 16:57:00', 'party' => 'Tanisha Afzal Husain', 'amount' => 30.00, 'category' => 'Transfers & Payments', 'txn_id' => 'FMPIB6637354613'],
            ['type' => 'expense', 'datetime' => '2026-09-20 08:30:00', 'party' => 'Blinkit', 'amount' => 208.00, 'category' => 'Shopping & Groceries', 'upi' => 'blinkit1.paytm@hdfcbank', 'txn_id' => 'TOTud260920030054963E394E0C66421E8B', 'utr' => '662948434006'],
            ['type' => 'income', 'datetime' => '2026-09-20 08:21:00', 'party' => 'Tanisha Afzal Husain', 'amount' => 50.00, 'category' => 'Family Inflow', 'txn_id' => 'FMPIB6634469067'],
            ['type' => 'expense', 'datetime' => '2026-09-19 17:20:00', 'party' => 'Sta', 'amount' => 30.00, 'category' => 'Food & Dining', 'upi' => 'gpay-11240186479@okbizaxis', 'txn_id' => 'TOTud260919115036C690E88FD0D74EF7BD', 'utr' => '662844453104'],
            ['type' => 'expense', 'datetime' => '2026-09-19 17:19:00', 'party' => 'Bharati Arjun Jadhav', 'amount' => 86.00, 'category' => 'Food & Dining', 'upi' => 'paytm.s1qrtae@pty', 'txn_id' => 'TOTud26091911492877E2C97A518C4F18B0', 'utr' => '662844442678'],
            ['type' => 'expense', 'datetime' => '2026-09-19 17:14:00', 'party' => 'Bharati Arjun Jadhav', 'amount' => 10.00, 'category' => 'Food & Dining', 'upi' => 'paytm.s1qrtae@pty', 'txn_id' => 'TOTud260919114436D9DD8CA256724C6D81', 'utr' => '662844398174'],
            ['type' => 'expense', 'datetime' => '2026-09-19 11:45:00', 'party' => 'Rajendra Maruti Bobade', 'amount' => 30.00, 'category' => 'Food & Dining', 'upi' => 'paytm.s2ddwfv@pty', 'txn_id' => 'TOTud26091906152880FE608B503144809D', 'utr' => '662841791368'],
            ['type' => 'income', 'datetime' => '2026-09-19 11:05:00', 'party' => 'Tanisha Afzal Husain', 'amount' => 250.00, 'category' => 'Family Inflow', 'txn_id' => 'FMPIB6628662314'],
            ['type' => 'expense', 'datetime' => '2026-09-18 18:37:00', 'party' => 'Bharati Arjun Jadhav', 'amount' => 376.00, 'category' => 'Food & Dining', 'upi' => 'paytm.s1qrtae@pty', 'txn_id' => 'TOTud260918130735D0C26506B8CE4B57B0', 'utr' => '662737596041'],
            ['type' => 'income', 'datetime' => '2026-09-18 17:15:00', 'party' => 'Neel Kalpeshbhai Patel', 'amount' => 10.00, 'category' => 'Transfers & Friends', 'txn_id' => 'FMPIB6624546126'],

            // Page 4
            ['type' => 'expense', 'datetime' => '2026-09-17 15:48:00', 'party' => 'Santosh Keraba Telang', 'amount' => 60.00, 'category' => 'Transfers & Payments', 'upi' => 'paytm.s1op84l@pty', 'txn_id' => 'TOTud260917101821D0AA11F725C24C7B8E', 'utr' => '662628296929'],
            ['type' => 'expense', 'datetime' => '2026-09-17 14:08:00', 'party' => 'Msrtc', 'amount' => 440.00, 'category' => 'Transport & Fuel', 'upi' => 'dvsa.268.2932@mairtel', 'txn_id' => 'TOTud260917083814EF06D2BA71674F9296', 'utr' => '662627546255'],
            ['type' => 'income', 'datetime' => '2026-09-17 14:07:00', 'party' => 'Afzal Hussain Kader', 'amount' => 450.00, 'category' => 'Family Inflow', 'upi' => 'afzal.9056@waaxis', 'txn_id' => 'FMPIB6617018432', 'utr' => '662602196220'],
            ['type' => 'expense', 'datetime' => '2026-09-17 14:04:00', 'party' => 'Wahid Abdulrajak Kothimbire', 'amount' => 130.00, 'category' => 'Transfers & Payments', 'upi' => 'pqr.vn2jr9q@ptyes', 'txn_id' => 'TOTud260917083417884CE91BBB8A425785', 'utr' => '662627515412'],
            ['type' => 'expense', 'datetime' => '2026-09-17 13:27:00', 'party' => 'Israr Baig', 'amount' => 70.00, 'category' => 'Transfers & Payments', 'upi' => 'q361109192@ybl', 'txn_id' => 'TOTud260917075713E679603BA5BC4501BB', 'utr' => '662627212591'],
            ['type' => 'income', 'datetime' => '2026-09-17 13:08:00', 'party' => 'Maaz Imtiyaz Shaikh', 'amount' => 700.00, 'category' => 'Transfers & Friends', 'upi' => 'maaz.1738@waaxis', 'txn_id' => 'FMPIB6616643667', 'utr' => '662600994324'],
            ['type' => 'expense', 'datetime' => '2026-09-16 18:20:00', 'party' => 'Medico Point', 'amount' => 20.00, 'category' => 'Health & Medical', 'upi' => 'paytm.s15oqgc@pty', 'txn_id' => 'TOTud260916125056814833CF63E54A1D9B', 'utr' => '662521942381'],
            ['type' => 'income', 'datetime' => '2026-09-16 18:20:00', 'party' => 'Master Sohel Imran Mujawar', 'amount' => 30.00, 'category' => 'Transfers & Friends', 'upi' => 'mujawarsohel849@okaxis', 'txn_id' => 'FMPIB6612298082', 'utr' => '662593797599'],
            ['type' => 'expense', 'datetime' => '2026-09-15 16:53:00', 'party' => 'Nandkumar And Brothers', 'amount' => 121.00, 'category' => 'Shopping & Groceries', 'upi' => 'paytmqr6ratib@ptys', 'txn_id' => 'TOTud260915112326898B393DFCC1435493', 'utr' => '662413363637'],
            ['type' => 'income', 'datetime' => '2026-09-15 16:44:00', 'party' => 'Tanisha Afzal Husain', 'amount' => 121.00, 'category' => 'Family Inflow', 'txn_id' => 'FMPIB6605325198'],

            // Page 5
            ['type' => 'expense', 'datetime' => '2026-09-12 09:19:00', 'party' => 'Tanisha Afzal Husain', 'amount' => 600.00, 'category' => 'Transfers & Payments', 'txn_id' => 'FMPIB6583129755'],
            ['type' => 'income', 'datetime' => '2026-09-12 09:17:00', 'party' => 'Sandip Kumar Singh', 'amount' => 600.00, 'category' => 'Transfers & Friends', 'upi' => '7982182697@amazonpay', 'txn_id' => 'FMPIB6583121736', 'utr' => '662159864573'],
            ['type' => 'expense', 'datetime' => '2026-09-10 16:47:00', 'party' => 'Tanisha Afzal Husain', 'amount' => 20.00, 'category' => 'Transfers & Payments', 'txn_id' => 'FMPIB6572440487'],
            ['type' => 'income', 'datetime' => '2026-09-10 14:51:00', 'party' => 'Suryoday Shukla', 'amount' => 20.00, 'category' => 'Transfers & Friends', 'txn_id' => 'FMPIB6571707661'],
            ['type' => 'expense', 'datetime' => '2026-09-10 10:46:00', 'party' => 'Tanisha Afzal Husain', 'amount' => 211.00, 'category' => 'Transfers & Payments', 'txn_id' => 'FMPIB6570356445'],
            ['type' => 'expense', 'datetime' => '2026-09-10 10:43:00', 'party' => 'Boost Up Fee', 'amount' => 39.00, 'category' => 'Fees & Charges', 'txn_id' => 'FMPIB6570346852'],
            ['type' => 'income', 'datetime' => '2026-09-10 10:43:00', 'party' => 'Pavan Ramdas Kolhe', 'amount' => 250.00, 'category' => 'Transfers & Friends', 'txn_id' => 'FMPIB6570346780'],
            ['type' => 'expense', 'datetime' => '2026-09-09 10:40:00', 'party' => 'Nandkumar And Brothers', 'amount' => 200.00, 'category' => 'Shopping & Groceries', 'upi' => 'paytmqr6ratib@ptys', 'txn_id' => 'TOTud2609090510598758AC1B2FE04011A4', 'utr' => '625263144110'],
            ['type' => 'income', 'datetime' => '2026-09-09 10:36:00', 'party' => 'Tanisha Afzal Husain', 'amount' => 200.00, 'category' => 'Family Inflow', 'txn_id' => 'FMPIB6563667440'],
            ['type' => 'expense', 'datetime' => '2026-09-08 21:09:00', 'party' => 'Tanisha Afzal Husain', 'amount' => 1000.23, 'category' => 'Transfers & Payments', 'txn_id' => 'FMPIB6561356441'],
            ['type' => 'expense', 'datetime' => '2026-09-08 17:36:00', 'party' => 'Bharati Arjun Jadhav', 'amount' => 10.00, 'category' => 'Food & Dining', 'upi' => 'paytm.s1qrtae@pty', 'txn_id' => 'TOTud260908120610152572807FE54C9FB7', 'utr' => '625158618897'],
            ['type' => 'expense', 'datetime' => '2026-09-08 17:35:00', 'party' => 'Bharati Arjun Jadhav', 'amount' => 76.00, 'category' => 'Food & Dining', 'upi' => 'paytm.s1qrtae@pty', 'txn_id' => 'TOTud26090812050996CDFB7ACE63413DB8', 'utr' => '625158608554'],

            // Page 6
            ['type' => 'income', 'datetime' => '2026-09-08 17:07:00', 'party' => 'Ratan Gupta', 'amount' => 700.00, 'category' => 'Transfers & Friends', 'upi' => '9354676195@ptyes', 'txn_id' => 'FMPIB6559452649', 'utr' => '215170635590'],
            ['type' => 'income', 'datetime' => '2026-09-08 16:09:00', 'party' => 'Kataria Kushal Kaushik', 'amount' => 300.00, 'category' => 'Transfers & Friends', 'upi' => 'kushal4955c@okicici', 'txn_id' => 'FMPIB6559066878', 'utr' => '625152692685'],
            ['type' => 'expense', 'datetime' => '2026-09-08 13:40:00', 'party' => 'Miss Sanika Tulashidas Deshmukh', 'amount' => 500.00, 'category' => 'Transfers & Payments', 'upi' => 'bharatpe.9o0w0i0q7i626062@unitype', 'txn_id' => 'TOTud2609080810543D8FCA1AB5A241D09E', 'utr' => '625156679604'],
            ['type' => 'income', 'datetime' => '2026-09-08 13:20:00', 'party' => 'Afzal Hussain Kader', 'amount' => 140.00, 'category' => 'Family Inflow', 'upi' => 'afzal.9056@waaxis', 'txn_id' => 'FMPIB6558070151', 'utr' => '661701287052'],
            ['type' => 'expense', 'datetime' => '2026-09-07 13:16:00', 'party' => 'Jio Prepaid', 'amount' => 199.99, 'category' => 'Utilities & Bills', 'txn_id' => 'FMPIB01a07ad5-1ca1-7e6a-b22f-330e80dbfb4e'],
            ['type' => 'income', 'datetime' => '2026-09-07 13:16:00', 'party' => 'Afzal Hussain Kader', 'amount' => 200.00, 'category' => 'Family Inflow', 'upi' => 'afzal.9056@waaxis', 'txn_id' => 'FMPIB6551698700', 'utr' => '661603193214'],
            ['type' => 'expense', 'datetime' => '2026-09-07 10:12:00', 'party' => 'Jio Prepaid', 'amount' => 29.99, 'category' => 'Utilities & Bills', 'txn_id' => 'FMPIB01a07a2c-c088-7b73-a550-e55056d7b833'],
            ['type' => 'expense', 'datetime' => '2026-09-06 16:27:00', 'party' => 'Bharati Arjun Jadhav', 'amount' => 76.00, 'category' => 'Food & Dining', 'upi' => 'paytm.s1qrtae@pty', 'txn_id' => 'TOTud2609061057341CDE57E2DE2B4F7087', 'utr' => '624942633396'],
            ['type' => 'expense', 'datetime' => '2026-09-05 16:48:00', 'party' => 'Sandip Kumar Singh', 'amount' => 350.00, 'category' => 'Transfers & Payments', 'upi' => '7982182697@amazonpay', 'txn_id' => 'TOTud260905111855EF061671066E4B7ABA', 'utr' => '624835262470'],
            ['type' => 'income', 'datetime' => '2026-09-05 13:41:00', 'party' => 'J Nilamadhab Behera', 'amount' => 900.00, 'category' => 'Transfers & Friends', 'upi' => 'tushar9090tus@ybl', 'txn_id' => 'FMPIB6538860279', 'utr' => '667230130494'],

            // Page 7
            ['type' => 'expense', 'datetime' => '2026-09-05 10:27:00', 'party' => 'Nibha Mandal', 'amount' => 45.00, 'category' => 'Food & Dining', 'upi' => 'paytmqr16au9ljzay@paytm', 'txn_id' => 'TOTud260905045733CB7E57045BDC4D0F9E', 'utr' => '624832310915'],
            ['type' => 'expense', 'datetime' => '2026-09-05 08:28:00', 'party' => 'Bharati Arjun Jadhav', 'amount' => 8.00, 'category' => 'Food & Dining', 'upi' => 'paytm.s1qrtae@pty', 'txn_id' => 'TOTud2609050258388566B518287D43DDB4', 'utr' => '624831725446'],
            ['type' => 'income', 'datetime' => '2026-09-05 07:57:00', 'party' => 'Mukesh Kumar', 'amount' => 50.00, 'category' => 'Transfers & Friends', 'upi' => '9431429888@ybl', 'txn_id' => 'FMPIB6537141954', 'utr' => '362836541795'],
            ['type' => 'expense', 'datetime' => '2026-09-03 21:49:00', 'party' => 'Google Play Gift Card', 'amount' => 100.00, 'category' => 'Entertainment & Leisure', 'txn_id' => 'FMPIB01a06811-ba2c-71bf-a05c-ca1fe2efde42'],
            ['type' => 'expense', 'datetime' => '2026-09-03 21:44:00', 'party' => 'Google Play Gift Card', 'amount' => 50.00, 'category' => 'Entertainment & Leisure', 'txn_id' => 'FMPIB01a0680c-e9c5-75a9-86d8-53fbc5139e7f'],
            ['type' => 'income', 'datetime' => '2026-09-03 20:01:00', 'party' => 'Nivkumar Kalpeshbhai Panchal', 'amount' => 100.00, 'category' => 'Transfers & Friends', 'txn_id' => 'FMPIB6528206255'],
            ['type' => 'expense', 'datetime' => '2026-09-03 15:08:00', 'party' => 'Komal Verma', 'amount' => 700.00, 'category' => 'Transfers & Payments', 'txn_id' => 'FMPIB6526059629'],
            ['type' => 'income', 'datetime' => '2026-09-03 07:06:00', 'party' => 'Sandip Kumar Singh', 'amount' => 750.00, 'category' => 'Transfers & Friends', 'upi' => '7982182697@amazonpay', 'txn_id' => 'FMPIB6523791530', 'utr' => '661257009900'],
            ['type' => 'expense', 'datetime' => '2026-09-02 22:19:00', 'party' => 'Komal Verma', 'amount' => 700.00, 'category' => 'Transfers & Payments', 'txn_id' => 'FMPIB6522867280'],
            ['type' => 'income', 'datetime' => '2026-09-02 22:16:00', 'party' => 'Krishan Murari So Sarau Prasad', 'amount' => 700.00, 'category' => 'Transfers & Friends', 'upi' => '9815850205@axl', 'txn_id' => 'FMPIB6522850024', 'utr' => '223386030019'],
            ['type' => 'expense', 'datetime' => '2026-09-02 13:51:00', 'party' => 'Afzal Hussain Kader', 'amount' => 300.00, 'category' => 'Transfers & Payments', 'upi' => 'afzal.9056@waaxis', 'txn_id' => 'TOTud26090208215940D385F9F8B149D3AF', 'utr' => '624510569128'],

            // Page 8
            ['type' => 'income', 'datetime' => '2026-09-02 13:50:00', 'party' => 'Pavan Ramdas Kolhe', 'amount' => 300.00, 'category' => 'Transfers & Friends', 'txn_id' => 'FMPIB6519355645'],
            ['type' => 'expense', 'datetime' => '2026-09-01 09:06:00', 'party' => 'Afzal Hussain Kader', 'amount' => 500.00, 'category' => 'Transfers & Payments', 'upi' => 'afzal.9056@waaxis', 'txn_id' => 'TOTud260901033627CF39FA3C6B1742D98E', 'utr' => '624401450849'],
            ['type' => 'income', 'datetime' => '2026-09-01 09:03:00', 'party' => 'Vishal Arjun Jadhav', 'amount' => 500.00, 'category' => 'Transfers & Friends', 'upi' => 'vishaljadhav2015@axl', 'txn_id' => 'FMPIB6511875574', 'utr' => '250137538719'],
            ['type' => 'income', 'datetime' => '2026-08-31 11:03:00', 'party' => 'Amazon', 'amount' => 2.00, 'category' => 'Cashback & Refunds', 'upi' => 'amazonaws@rapl', 'txn_id' => 'FMPIB6506294436', 'utr' => '624249055692'],
            ['type' => 'expense', 'datetime' => '2026-08-30 17:36:00', 'party' => 'Afzal Hussain Kader', 'amount' => 113.00, 'category' => 'Transfers & Payments', 'upi' => 'afzal.9056@waaxis', 'txn_id' => 'TOTud2608301206582B3873CBBF4D4768AF', 'utr' => '660890560651'],
            ['type' => 'expense', 'datetime' => '2026-08-30 17:36:00', 'party' => 'Bharati Arjun Jadhav', 'amount' => 151.00, 'category' => 'Food & Dining', 'upi' => 'paytm.s1qrtae@pty', 'txn_id' => 'TOTud260830120617A057FFA8D6BC46138D', 'utr' => '660890554420'],
            ['type' => 'expense', 'datetime' => '2026-08-30 17:24:00', 'party' => 'Sattyappa Ramappa Madar', 'amount' => 75.00, 'category' => 'Transfers & Payments', 'upi' => 'paytm.s1m1qly@pty', 'txn_id' => 'TOTud2608301154008FE97335C3984F3A87', 'utr' => '660890445137'],
            ['type' => 'expense', 'datetime' => '2026-08-30 17:18:00', 'party' => 'Miss Vidya Hanmant Vibhute', 'amount' => 160.00, 'category' => 'Transfers & Payments', 'upi' => 'q957779056@ybl', 'txn_id' => 'TOTud2608301148336F072BAE8A5B4B0B94', 'utr' => '660890397929'],
            ['type' => 'income', 'datetime' => '2026-08-30 17:09:00', 'party' => 'Afzal Hussain Kader', 'amount' => 500.00, 'category' => 'Family Inflow', 'upi' => 'afzal.9056@waaxis', 'txn_id' => 'FMPIB6502237996', 'utr' => '660800942427'],
            ['type' => 'expense', 'datetime' => '2026-08-30 09:04:00', 'party' => 'Nandkumar And Brothers', 'amount' => 121.00, 'category' => 'Shopping & Groceries', 'upi' => 'paytmqr6ratib@ptys', 'txn_id' => 'TOTud260830033431BB179B9CCEFB4A6E94', 'utr' => '660887078908'],

            // Page 9
            ['type' => 'income', 'datetime' => '2026-08-30 08:59:00', 'party' => 'Afzal Hussain Kader', 'amount' => 21.00, 'category' => 'Family Inflow', 'upi' => 'afzal.9056@waaxis', 'txn_id' => 'FMPIB6499492324', 'utr' => '660800007121'],
            ['type' => 'income', 'datetime' => '2026-08-30 08:58:00', 'party' => 'Afzal Hussain Kader', 'amount' => 100.00, 'category' => 'Family Inflow', 'upi' => 'afzal.9056@waaxis', 'txn_id' => 'FMPIB6499490542', 'utr' => '660802004921'],
            ['type' => 'expense', 'datetime' => '2026-08-30 08:53:00', 'party' => 'Amazon', 'amount' => 2.00, 'category' => 'Shopping & Groceries', 'upi' => 'amazonaws@rapl', 'txn_id' => 'FMPIB6499469555'],
            ['type' => 'expense', 'datetime' => '2026-08-29 15:44:00', 'party' => 'Caspian Host', 'amount' => 1170.00, 'category' => 'Utilities & Bills', 'upi' => 'caspianhost.cf@axisbank', 'txn_id' => 'TOTud2608291014395FC9603B86194B1A8F', 'utr' => '660782605857'],
            ['type' => 'income', 'datetime' => '2026-08-29 15:07:00', 'party' => 'J Nilamadhab Behera', 'amount' => 10.00, 'category' => 'Transfers & Friends', 'upi' => 'jnilamadhab2004@axl', 'txn_id' => 'FMPIB6495266641', 'utr' => '976846576586'],
            ['type' => 'income', 'datetime' => '2026-08-29 15:00:00', 'party' => 'J Nilamadhab Behera', 'amount' => 100.00, 'category' => 'Transfers & Friends', 'upi' => 'jnilamadhab2004@axl', 'txn_id' => 'FMPIB6495223366', 'utr' => '030522581705'],
            ['type' => 'income', 'datetime' => '2026-08-29 14:02:00', 'party' => 'Pavan Ramdas Kolhe', 'amount' => 500.00, 'category' => 'Transfers & Friends', 'txn_id' => 'FMPIB6494885151'],
            ['type' => 'income', 'datetime' => '2026-08-29 13:49:00', 'party' => 'Komal Verma', 'amount' => 10.00, 'category' => 'Transfers & Friends', 'txn_id' => 'FMPIB6494805472'],
            ['type' => 'income', 'datetime' => '2026-08-29 13:46:00', 'party' => 'Komal Verma', 'amount' => 10.00, 'category' => 'Transfers & Friends', 'txn_id' => 'FMPIB6494786842'],
            ['type' => 'income', 'datetime' => '2026-08-29 12:03:00', 'party' => 'Vikesh Kumar', 'amount' => 200.00, 'category' => 'Transfers & Friends', 'upi' => '9572945531@nyes', 'txn_id' => 'FMPIB6494190833', 'utr' => '004491452237'],
            ['type' => 'expense', 'datetime' => '2026-08-29 11:31:00', 'party' => 'Nandkumar And Brothers', 'amount' => 121.00, 'category' => 'Shopping & Groceries', 'upi' => 'paytmqr6ratib@ptys', 'txn_id' => 'TOTud260829060147F0827FE775F747D298', 'utr' => '660780771188'],

            // Page 10
            ['type' => 'income', 'datetime' => '2026-08-29 11:29:00', 'party' => 'Afzal Hussain Kader', 'amount' => 100.00, 'category' => 'Family Inflow', 'upi' => 'afzal.9056@waaxis', 'txn_id' => 'FMPIB6494017567', 'utr' => '660701541654'],
            ['type' => 'income', 'datetime' => '2026-08-28 23:28:00', 'party' => 'Paytm-83855917@Ptybl', 'amount' => 1.00, 'category' => 'Cashback & Refunds', 'upi' => 'paytm-83855917@ptybl', 'txn_id' => 'FMPIB6492361429', 'utr' => '660600787036'],
            ['type' => 'expense', 'datetime' => '2026-08-28 23:26:00', 'party' => 'Paytm-83855917@Ptybl', 'amount' => 1.00, 'category' => 'Transfers & Payments', 'upi' => 'paytm-83855917@ptybl', 'txn_id' => 'FMPIB6492355105'],
            ['type' => 'income', 'datetime' => '2026-08-28 22:55:00', 'party' => 'Neel Kalpeshbhai Patel', 'amount' => 60.00, 'category' => 'Transfers & Friends', 'txn_id' => 'FMPIB6492254804'],
            ['type' => 'expense', 'datetime' => '2026-08-28 22:21:00', 'party' => 'Raghunath Pandit Tambile', 'amount' => 120.00, 'category' => 'Transfers & Payments', 'upi' => '9921942088-5@axl', 'txn_id' => 'TOTud26082816512714C50950F55A436192', 'utr' => '660679098877'],
            ['type' => 'income', 'datetime' => '2026-08-28 22:18:00', 'party' => 'Rohan Kumar Ray', 'amount' => 300.00, 'category' => 'Transfers & Friends', 'upi' => '9599496734@axl', 'txn_id' => 'FMPIB6492100306', 'utr' => '075728722614'],
            ['type' => 'income', 'datetime' => '2026-08-28 19:17:00', 'party' => 'Raghunath Pandit Tambile', 'amount' => 120.00, 'category' => 'Transfers & Friends', 'upi' => '9921942088-5@axl', 'txn_id' => 'FMPIB6490802278', 'utr' => '013924448292'],
            ['type' => 'expense', 'datetime' => '2026-08-28 08:25:00', 'party' => 'Bharati Arjun Jadhav', 'amount' => 74.00, 'category' => 'Food & Dining', 'upi' => 'paytm.s1qrtae@pty', 'txn_id' => 'TOTud260828025517042E6ED68C4B4367A8', 'utr' => '660672406754'],
            ['type' => 'expense', 'datetime' => '2026-08-26 16:55:00', 'party' => 'Bharati Arjun Jadhav', 'amount' => 74.00, 'category' => 'Food & Dining', 'upi' => 'paytm.s1y0btz@pty', 'txn_id' => 'TOTud26082611252467BC87F9154B48E1AC', 'utr' => '660460511386'],
            ['type' => 'income', 'datetime' => '2026-08-26 13:59:00', 'party' => 'Anmol Singh', 'amount' => 74.00, 'category' => 'Transfers & Friends', 'txn_id' => 'FMPIB6475340285'],

            // Page 11
            ['type' => 'income', 'datetime' => '2026-08-25 15:51:00', 'party' => 'J Nilamadhab Behera', 'amount' => 10.00, 'category' => 'Transfers & Friends', 'upi' => 'jnilamadhab2004@axl', 'txn_id' => 'FMPIB6469576279', 'utr' => '109072946291'],
            ['type' => 'income', 'datetime' => '2026-08-24 15:25:00', 'party' => 'Pavan Ramdas Kolhe', 'amount' => 50.00, 'category' => 'Transfers & Friends', 'txn_id' => 'FMPIB6463300753'],
            ['type' => 'expense', 'datetime' => '2026-08-24 07:48:00', 'party' => 'Jio Prepaid', 'amount' => 199.99, 'category' => 'Utilities & Bills', 'txn_id' => 'FMPIB01a03190-5081-70be-b40f-473f7e0c9c01'],
            ['type' => 'income', 'datetime' => '2026-08-24 07:48:00', 'party' => 'Afzal Hussain Kader', 'amount' => 199.00, 'category' => 'Family Inflow', 'upi' => 'afzal.9056@waaxis', 'txn_id' => 'FMPIB6461107539', 'utr' => '660202610371'],
            ['type' => 'expense', 'datetime' => '2026-08-23 08:36:00', 'party' => 'Jio Prepaid', 'amount' => 29.99, 'category' => 'Utilities & Bills', 'txn_id' => 'FMPIB01a02c95-53ac-7e45-8002-c13fefdf94a2'],
            ['type' => 'income', 'datetime' => '2026-08-22 11:25:00', 'party' => 'Suryoday Shukla', 'amount' => 50.00, 'category' => 'Transfers & Friends', 'txn_id' => 'FMPIB6449042893'],
            ['type' => 'expense', 'datetime' => '2026-08-21 21:25:00', 'party' => 'Darakshaan Afzal Hussain', 'amount' => 13.00, 'category' => 'Transfers & Payments', 'txn_id' => 'FMPIB6446730300'],
            ['type' => 'expense', 'datetime' => '2026-08-20 10:29:00', 'party' => 'Nandkumar And Brothers', 'amount' => 110.00, 'category' => 'Shopping & Groceries', 'upi' => 'paytmqr6ratib@ptys', 'txn_id' => 'TOTud260820045932B10EAB676F7C4539A5', 'utr' => '659812634319'],
            ['type' => 'income', 'datetime' => '2026-08-20 10:27:00', 'party' => 'Afzal Hussain Kader', 'amount' => 50.00, 'category' => 'Family Inflow', 'upi' => 'afzal.9056@waaxis', 'txn_id' => 'FMPIB6436073085', 'utr' => '659800690109'],
            ['type' => 'expense', 'datetime' => '2026-08-20 08:39:00', 'party' => 'Bharati Arjun Jadhav', 'amount' => 51.00, 'category' => 'Food & Dining', 'upi' => 'paytm.s1y0btz@pty', 'txn_id' => 'TOTud260820030951E62B3A2AE4984EB7A3', 'utr' => '659812105648'],
            ['type' => 'income', 'datetime' => '2026-08-20 08:18:00', 'party' => 'Uffra Afzal Hussain', 'amount' => 68.00, 'category' => 'Family Inflow', 'upi' => 'hussainuffra88-1@okaxis', 'txn_id' => 'FMPIB6435552547', 'utr' => '623213708021'],

            // Page 12
            ['type' => 'expense', 'datetime' => '2026-08-19 15:47:00', 'party' => 'Anmol Singh', 'amount' => 300.00, 'category' => 'Transfers & Payments', 'txn_id' => 'FMPIB6431566454'],
            ['type' => 'income', 'datetime' => '2026-08-19 10:49:00', 'party' => 'Tanisha Afzal Husain', 'amount' => 40.00, 'category' => 'Family Inflow', 'txn_id' => 'FMPIB6429939179'],
            ['type' => 'expense', 'datetime' => '2026-08-19 08:11:00', 'party' => 'Tuljabhavani Amrutulya', 'amount' => 27.00, 'category' => 'Food & Dining', 'upi' => '7219763536@okbizaxis', 'txn_id' => 'TOTud260819024120BA977A4C77F6433EBB', 'utr' => '659704586566'],
            ['type' => 'income', 'datetime' => '2026-08-18 18:39:00', 'party' => 'Google Play', 'amount' => 2.00, 'category' => 'Cashback & Refunds', 'upi' => 'playstore1.bd@axisbank', 'txn_id' => 'FMPIB6426542523', 'utr' => '788413972306'],
            ['type' => 'income', 'datetime' => '2026-08-17 20:43:00', 'party' => 'Krishan Murari S O Sarau Prasad', 'amount' => 300.00, 'category' => 'Transfers & Friends', 'upi' => 'krishanmurari25283@okaxis', 'txn_id' => 'FMPIB6421560011', 'utr' => '659503985582'],
            ['type' => 'income', 'datetime' => '2026-08-17 15:38:00', 'party' => 'Master Sohel Imran Mujawar', 'amount' => 20.00, 'category' => 'Transfers & Friends', 'upi' => 'mujawarsohel849@okhdfcbank', 'txn_id' => 'FMPIB6419358563', 'utr' => '128063578783'],
            ['type' => 'expense', 'datetime' => '2026-08-16 08:18:00', 'party' => 'Lovishai.Cfp@Cashfreensdlpb', 'amount' => 1.00, 'category' => 'Transfers & Payments', 'upi' => 'lovishai.cfp@cashfreensdlpb', 'txn_id' => 'FMPIB6410589139'],
            ['type' => 'expense', 'datetime' => '2026-08-13 23:11:00', 'party' => 'Google Play Gift Card', 'amount' => 70.00, 'category' => 'Entertainment & Leisure', 'txn_id' => 'FMPIB019ffc36-b8f1-79c8-a352-acf2b7aa3f4a'],
            ['type' => 'expense', 'datetime' => '2026-08-13 09:14:00', 'party' => 'Blinkit', 'amount' => 908.00, 'category' => 'Shopping & Groceries', 'upi' => 'blinkit3.payu@hdfcbank', 'txn_id' => 'PPPL300909061961308260914316a7d3d9f', 'utr' => '622560408311'],
            ['type' => 'expense', 'datetime' => '2026-08-12 14:05:00', 'party' => 'Sirvi Bandhu Mithaiw', 'amount' => 40.00, 'category' => 'Food & Dining', 'upi' => 'q636128150@ybl', 'txn_id' => 'TOTud260812083536DBFA89212E234F4BB3', 'utr' => '622454725575'],
            ['type' => 'expense', 'datetime' => '2026-08-12 06:28:00', 'party' => 'Krishan Murari S O Sarau Prasad', 'amount' => 500.00, 'category' => 'Transfers & Payments', 'upi' => 'krishanmurari25283@okaxis', 'txn_id' => 'TOTud260812005840EF3FD327589C4B4B8F', 'utr' => '622452311759'],

            // Page 13
            ['type' => 'income', 'datetime' => '2026-08-12 06:26:00', 'party' => 'Krishan Murari S O Sarau Prasad', 'amount' => 500.00, 'category' => 'Transfers & Friends', 'upi' => 'krishanmurari25283@okaxis', 'txn_id' => 'FMPIB6384528175', 'utr' => '659021760649'],
            ['type' => 'expense', 'datetime' => '2026-08-11 18:39:00', 'party' => 'Google Play', 'amount' => 2.00, 'category' => 'Entertainment & Leisure', 'upi' => 'playstore1.bd@axisbank', 'txn_id' => 'FMPIB6381977602'],
            ['type' => 'expense', 'datetime' => '2026-08-11 18:38:00', 'party' => 'Google Play Gift Card', 'amount' => 99.00, 'category' => 'Entertainment & Leisure', 'txn_id' => 'FMPIB019ff0ef-f848-74e4-9573-b88e2977fe02'],
            ['type' => 'expense', 'datetime' => '2026-08-11 16:10:00', 'party' => 'Anmol Singh', 'amount' => 160.00, 'category' => 'Transfers & Payments', 'txn_id' => 'FMPIB6380947343'],
            ['type' => 'income', 'datetime' => '2026-08-11 11:34:00', 'party' => 'Nivkumar Kalpeshbhai Panchal', 'amount' => 300.00, 'category' => 'Transfers & Friends', 'txn_id' => 'FMPIB6379391857'],
            ['type' => 'income', 'datetime' => '2026-08-10 17:54:00', 'party' => 'Uffra Afzal Hussain', 'amount' => 1000.00, 'category' => 'Family Inflow', 'upi' => 'hussainuffra88@oksbi', 'txn_id' => 'FMPIB6375413739', 'utr' => '622255677349'],
            ['type' => 'expense', 'datetime' => '2026-08-10 07:39:00', 'party' => 'Mhavashe Auto Service', 'amount' => 100.00, 'category' => 'Transport & Fuel', 'upi' => 'paytmqr5d91gs@ptys', 'txn_id' => 'TOTud26081002093302F9AF066ED4483484', 'utr' => '622237663685'],
            ['type' => 'expense', 'datetime' => '2026-08-10 06:45:00', 'party' => 'Anmol Singh', 'amount' => 300.00, 'category' => 'Transfers & Payments', 'txn_id' => 'FMPIB6372016267'],
            ['type' => 'income', 'datetime' => '2026-08-10 04:50:00', 'party' => 'Havaldar Sandip Kumar Singh', 'amount' => 400.00, 'category' => 'Transfers & Friends', 'upi' => '1ek6v1rprzk1hsd4j2cp7pdttwnqfyn7uo@drsbi', 'txn_id' => 'FMPIB6371874328', 'utr' => '100677148544'],
            ['type' => 'expense', 'datetime' => '2026-08-08 12:04:00', 'party' => 'Sai Petro Hub', 'amount' => 210.00, 'category' => 'Transport & Fuel', 'upi' => 'paytm-162471@ptys', 'txn_id' => 'TOTud26080806340569AEFC7D03DB44788D', 'utr' => '622023724280'],
            ['type' => 'income', 'datetime' => '2026-08-08 12:02:00', 'party' => 'Uffra Afzal Hussain', 'amount' => 200.00, 'category' => 'Family Inflow', 'upi' => 'hussainuffra88-1@okaxis', 'txn_id' => 'FMPIB6359779946', 'utr' => '658635753308'],

            // Page 14
            ['type' => 'expense', 'datetime' => '2026-08-08 11:49:00', 'party' => 'Mbs Chai And Smoke', 'amount' => 30.00, 'category' => 'Food & Dining', 'upi' => 'vyapar.169834868937@hdfcbank', 'txn_id' => 'TOTud2608080619290C12FA1F40164D379F', 'utr' => '622023619461'],
            ['type' => 'expense', 'datetime' => '2026-08-08 11:17:00', 'party' => 'Bhagyalaxmi Chemist', 'amount' => 30.00, 'category' => 'Health & Medical', 'upi' => 'paytmqr701cgi@ptys', 'txn_id' => 'TOTud26080805472889F6E00DA2D64D23AE', 'utr' => '622023396412'],
            ['type' => 'income', 'datetime' => '2026-08-08 11:05:00', 'party' => 'Pinki Kumari', 'amount' => 50.00, 'category' => 'Transfers & Friends', 'upi' => '8209509186@ybl', 'txn_id' => 'FMPIB6359458074', 'utr' => '529635517623'],
            ['type' => 'expense', 'datetime' => '2026-08-08 06:59:00', 'party' => 'Jio Prepaid', 'amount' => 199.99, 'category' => 'Utilities & Bills', 'txn_id' => 'FMPIB019fdefd-7b25-7d62-9cb4-1e4f83d9577d'],
            ['type' => 'income', 'datetime' => '2026-08-08 06:59:00', 'party' => 'Afzal Hussain Kader', 'amount' => 99.00, 'category' => 'Family Inflow', 'upi' => 'afzal.9056@waaxis', 'txn_id' => 'FMPIB6358511945', 'utr' => '658600079302'],
            ['type' => 'income', 'datetime' => '2026-08-07 19:56:00', 'party' => 'Anish Kumar Tiwari', 'amount' => 100.00, 'category' => 'Transfers & Friends', 'txn_id' => 'FMPIB6356589909'],
            ['type' => 'expense', 'datetime' => '2026-08-05 21:19:00', 'party' => 'Google Play Gift Card', 'amount' => 79.00, 'category' => 'Entertainment & Leisure', 'txn_id' => 'FMPIB019fd29d-677d-7835-8229-fff108078ee0'],
            ['type' => 'expense', 'datetime' => '2026-08-05 12:22:00', 'party' => 'Nandkumar And Brothers', 'amount' => 60.00, 'category' => 'Shopping & Groceries', 'upi' => 'paytmqr28100505010111nj4d4lahfg@paytm', 'txn_id' => 'TOTud26080506522685F63A688D6F46BB9C', 'utr' => '621702254090'],
            ['type' => 'expense', 'datetime' => '2026-08-05 11:39:00', 'party' => 'Nandkumar And Brothers', 'amount' => 220.00, 'category' => 'Shopping & Groceries', 'upi' => 'paytmqrobev2amja6@paytm', 'txn_id' => 'TOTud260805060908E1022462EFA14939B4', 'utr' => '621701980244'],
            ['type' => 'income', 'datetime' => '2026-08-05 11:34:00', 'party' => 'Afzal Hussain Kader', 'amount' => 310.00, 'category' => 'Family Inflow', 'upi' => 'afzal.9056@waaxis', 'txn_id' => 'FMPIB6340862472', 'utr' => '658302243257'],
            ['type' => 'expense', 'datetime' => '2026-08-03 16:28:00', 'party' => 'Mr Vikram Dyendev Ghodke', 'amount' => 10.00, 'category' => 'Transfers & Payments', 'upi' => 'paytmqr6t2gf5@ptys', 'txn_id' => 'TOTud26080310583026C2C0C60FAA429C85', 'utr' => '658189931078'],

            // Page 15
            ['type' => 'income', 'datetime' => '2026-08-03 09:28:00', 'party' => 'Aswad Ur Rahman', 'amount' => 80.00, 'category' => 'Transfers & Friends', 'txn_id' => 'FMPIB6328082756'],
            ['type' => 'expense', 'datetime' => '2026-08-02 21:00:00', 'party' => 'Anmol Singh', 'amount' => 300.00, 'category' => 'Transfers & Payments', 'txn_id' => 'FMPIB6326175817'],
            ['type' => 'income', 'datetime' => '2026-08-02 18:41:00', 'party' => 'Tanisha Afzal Husain', 'amount' => 50.00, 'category' => 'Family Inflow', 'txn_id' => 'FMPIB6324898885'],
            ['type' => 'income', 'datetime' => '2026-08-02 18:21:00', 'party' => 'Krishan Murari S O Sarau Prasad', 'amount' => 250.00, 'category' => 'Transfers & Friends', 'upi' => 'krishanmurari25283@okaxis', 'txn_id' => 'FMPIB6324724361', 'utr' => '658062070554'],
            ['type' => 'income', 'datetime' => '2026-08-02 12:37:00', 'party' => 'Mr Abdus Sami Rashid Kazi', 'amount' => 1.00, 'category' => 'Cashback & Refunds', 'upi' => 'kazisami024@okicici', 'txn_id' => 'FMPIB6322309577', 'utr' => '621445686556'],
            ['type' => 'expense', 'datetime' => '2026-08-01 18:11:00', 'party' => 'Google Play Gift Card', 'amount' => 28.00, 'category' => 'Entertainment & Leisure', 'txn_id' => 'FMPIB019fbd58-40cf-70a3-bdd6-f71d8453be9d'],
        ];
    }
}
