<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'plan',
        'monthly_budget',
        'currency',
        'is_admin',
    ];

    protected $attributes = [
        'plan' => self::PLAN_FREE,
    ];

    public const PLAN_FREE = 'free';
    public const PLAN_BASIC = 'basic';
    public const PLAN_MEDIUM = 'medium';
    public const PLAN_PRO = 'pro';

    public static function getAvailablePlans(): array
    {
        return [
            self::PLAN_FREE => [
                'id' => self::PLAN_FREE,
                'name' => 'Free Plan',
                'price' => 0,
                'price_formatted' => '₹0',
                'period' => 'Free Forever',
                'badge' => 'Active Plan ✨',
                'badge_color' => 'bg-emerald-100 text-emerald-800 border border-emerald-200',
                'card_bg' => 'bg-white',
                'tagline' => 'Basic personal expense logging for everyday use',
                'is_coming_soon' => false,
                'status_label' => 'Available Now',
                'features' => [
                    'Daily Expense Logging (Up to 30/month)',
                    'Standard Category Management',
                    'Monthly Expense Overview & Totals',
                    'Recent Transaction History',
                    'Responsive Mobile & Desktop Web App',
                ],
                'unavailable' => [
                    'Instant Formatted PDF Statements (Basic ₹49+)',
                    'Higher Transaction Limits (150+/mo)',
                    'Interactive Visual Charts & Trends',
                    'Monthly Spending Budget Alert System',
                    'One-Click CSV Data Export',
                ],
                'can_charts' => false,
                'can_budget' => false,
                'can_csv' => false,
                'can_pdf' => false,
            ],
            self::PLAN_BASIC => [
                'id' => self::PLAN_BASIC,
                'name' => 'Basic Plan',
                'price' => 49,
                'price_formatted' => '₹49',
                'period' => '/month',
                'badge' => 'Starter ⭐',
                'badge_color' => 'bg-indigo-100 text-indigo-800 border border-indigo-200',
                'card_bg' => 'bg-white',
                'tagline' => 'Expanded tracking & instant PDF statement downloads',
                'is_coming_soon' => false,
                'status_label' => 'Available Now',
                'features' => [
                    'Everything in Free Plan',
                    'Instant Formatted PDF Statements Export',
                    'Higher Limits (Up to 150/month)',
                    'Custom Categories & Color Tags',
                    'Advanced Search & Multi-Filters',
                    'Expense Notes & Transaction IDs',
                    'Responsive Web & Mobile App',
                ],
                'unavailable' => [
                    'Interactive Visual Charts & Trends',
                    'Monthly Budget Spending Alert System',
                    'One-Click CSV Data Export',
                ],
                'can_charts' => false,
                'can_budget' => false,
                'can_csv' => false,
                'can_pdf' => true,
            ],
            self::PLAN_MEDIUM => [
                'id' => self::PLAN_MEDIUM,
                'name' => 'Medium Plan',
                'price' => 94,
                'price_formatted' => '₹94',
                'period' => '/month',
                'badge' => '🔥 Most Popular',
                'badge_color' => 'bg-emerald-100 text-emerald-800 border border-emerald-200',
                'card_bg' => 'bg-gradient-to-b from-white to-emerald-50/40 border-emerald-300',
                'tagline' => 'Smart budgeting, PDF exports & dynamic visual analytics',
                'is_coming_soon' => false,
                'status_label' => 'Available Now',
                'features' => [
                    'Everything in Basic Plan',
                    'Instant Formatted PDF Statements Export',
                    'Unlimited Expenses & Custom Categories',
                    'Interactive Monthly Trends & Category Charts',
                    'Monthly Budget Limit & Live Progress Meter',
                    'Spending Threshold Alerts (80% & 100% warnings)',
                    'Payment Mode & Recipient Breakdown',
                    'Instant CSV / Excel Data Export',
                ],
                'unavailable' => [
                    'Custom Date-Range Export Filters',
                    'Financial Health Score & Spending Insights',
                    'Recurring Bills & Due Payment Tracker',
                ],
                'can_charts' => true,
                'can_budget' => true,
                'can_csv' => true,
                'can_pdf' => true,
            ],
            self::PLAN_PRO => [
                'id' => self::PLAN_PRO,
                'name' => 'Full Features Plan',
                'price' => 150,
                'price_formatted' => '₹150',
                'period' => '/month',
                'badge' => '👑 Best Value',
                'badge_color' => 'bg-amber-300 text-amber-950 font-black',
                'card_bg' => 'bg-gradient-to-b from-indigo-900 to-slate-900 text-white border-indigo-600',
                'tagline' => 'The ultimate personal financial command center',
                'is_coming_soon' => false,
                'status_label' => 'Available Now',
                'features' => [
                    'Everything in Medium Plan',
                    'Instant Formatted PDF Expense Statements',
                    'Custom Date-Range Export Filters',
                    'Smart Financial Health Score & Spending Insights',
                    'Recurring Bills & Upcoming Due Payment Tracker',
                    'Category Budget vs Actual Variance Analyzer',
                    'Top Expense Highlights & Average Daily Spend',
                    'Priority Support & Unlimited Cloud Backup',
                ],
                'unavailable' => [],
                'can_charts' => true,
                'can_budget' => true,
                'can_csv' => true,
                'can_pdf' => true,
            ],
        ];
    }

    public static function getWhatsAppUrl(string $planKey, ?string $userEmail = null): string
    {
        $plans = self::getAvailablePlans();
        $plan = $plans[$planKey] ?? ['name' => 'Premium Plan', 'price_formatted' => '₹49', 'period' => '/mo'];
        $userIdentifier = $userEmail ? " for my account ({$userEmail})" : "";
        $phone = config('services.whatsapp.number', env('WHATSAPP_NUMBER', '+919209571683'));
        
        $message = "Hello Kharchify! I would like to buy/activate the " . $plan['name'] . " (" . $plan['price_formatted'] . ($plan['period'] ?? '') . ")" . $userIdentifier . ". Please share payment details.";
        
        if (!empty($phone)) {
            $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
            return "https://wa.me/{$cleanPhone}?text=" . urlencode($message);
        }
        
        return "https://api.whatsapp.com/send?text=" . urlencode($message);
    }

    public function getPlanDetailsAttribute(): array
    {
        $plans = self::getAvailablePlans();
        $userPlan = $this->plan ?? self::PLAN_FREE;
        return $plans[$userPlan] ?? $plans[self::PLAN_FREE];
    }

    public function isFree(): bool
    {
        return empty($this->plan) || $this->plan === self::PLAN_FREE;
    }

    public function isBasic(): bool
    {
        return $this->plan === self::PLAN_BASIC;
    }

    public function isMedium(): bool
    {
        return $this->plan === self::PLAN_MEDIUM;
    }

    public function isPro(): bool
    {
        return $this->plan === self::PLAN_PRO;
    }

    public function canExportPdf(): bool
    {
        return $this->isAdmin() || in_array($this->plan, [self::PLAN_BASIC, self::PLAN_MEDIUM, self::PLAN_PRO]);
    }

    public function canExportCsv(): bool
    {
        return in_array($this->plan, [self::PLAN_MEDIUM, self::PLAN_PRO]);
    }

    public function canUseCharts(): bool
    {
        return in_array($this->plan, [self::PLAN_MEDIUM, self::PLAN_PRO]);
    }

    public function canSetBudget(): bool
    {
        return in_array($this->plan, [self::PLAN_MEDIUM, self::PLAN_PRO]);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function categories()
    {
        return $this->hasMany(Category::class);
    }

    public function incomes()
    {
        return $this->hasMany(Income::class);
    }

    public function incomeCategories()
    {
        return $this->hasMany(IncomeCategory::class);
    }

    public function totalIncome(): float
    {
        return (float) $this->incomes()->sum('amount');
    }

    public function totalExpense(): float
    {
        return (float) $this->expenses()->sum('amount');
    }

    public function thisMonthIncome(): float
    {
        return (float) $this->incomes()
            ->whereMonth('income_date', now()->month)
            ->whereYear('income_date', now()->year)
            ->sum('amount');
    }

    public function thisMonthExpense(): float
    {
        return (float) $this->expenses()
            ->whereMonth('expense_date', now()->month)
            ->whereYear('expense_date', now()->year)
            ->sum('amount');
    }

    public function thisMonthNetSavings(): float
    {
        return round($this->thisMonthIncome() - $this->thisMonthExpense(), 2);
    }

    public function allTimeNetSavings(): float
    {
        return round($this->totalIncome() - $this->totalExpense(), 2);
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function isAdmin(): bool
    {
        return (bool) ($this->is_admin ?? false);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
        ];
    }
}
