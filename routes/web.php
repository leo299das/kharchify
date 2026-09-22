<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\SplitController;
use App\Http\Controllers\IncomeController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    if (auth()->check()) {
        if (!auth()->user()->plan) {
            return redirect()->route('plans.show');
        }
        return redirect()->route('dashboard');
    }
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| LEGAL & PUBLIC SUPPORT ROUTES
|--------------------------------------------------------------------------
*/
Route::view('/terms', 'legal.terms')->name('terms');
Route::view('/privacy', 'legal.privacy')->name('privacy');
Route::get('/contact', function () {
    return view('legal.contact');
})->name('contact');

Route::post('/contact', function (\Illuminate\Http\Request $request) {
    $request->validate([
        'name' => 'required|string|max:100',
        'email' => 'required|email|max:150',
        'subject' => 'nullable|string|max:150',
        'message' => 'required|string|max:2000',
    ]);

    return back()->with('success', 'Thank you! Your message has been received. Our team will review and reply directly to ' . $request->input('email') . ' shortly (you can also email us directly at darakshaanhussain77@gmail.com).');
})->name('contact.send');



Route::middleware('auth')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | PLAN SELECTION & BUDGET
    |--------------------------------------------------------------------------
    */

    Route::get('/choose-plan', [PlanController::class, 'show'])->name('plans.show');
    Route::post('/choose-plan', [PlanController::class, 'store'])->name('plans.store');
    Route::post('/budget/update', [PlanController::class, 'updateBudget'])->name('budget.update');


    /*
    |--------------------------------------------------------------------------
    | DASHBOARD
    |--------------------------------------------------------------------------
    */

    Route::get('/dashboard', function () {

        if (!auth()->user()->plan) {
            return redirect('/choose-plan');
        }

        return view('dashboard');

    })->name('dashboard');


    /*
    |--------------------------------------------------------------------------
    | CATEGORIES
    |--------------------------------------------------------------------------
    */

    Route::get('/categories', function () {

        if (!auth()->user()->plan) {
            return redirect('/choose-plan');
        }

        return app(CategoryController::class)->index();

    })->name('categories.index');

    Route::get('/categories/create', [CategoryController::class, 'create'])->name('categories.create');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::get('/categories/{id}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
    Route::put('/categories/{id}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy'])->name('categories.destroy');


    /*
    |--------------------------------------------------------------------------
    | EXPENSES
    |--------------------------------------------------------------------------
    */

    Route::get('/expenses', function (\Illuminate\Http\Request $request) {

        if (!auth()->user()->plan) {
            return redirect('/choose-plan');
        }

        return app(ExpenseController::class)->index($request);

    })->name('expenses.index');

    Route::get('/expenses/create', [ExpenseController::class, 'create'])->name('expenses.create');
    Route::post('/expenses', [ExpenseController::class, 'store'])->name('expenses.store');
    Route::get('/expenses/{id}/edit', [ExpenseController::class, 'edit'])->name('expenses.edit');
    Route::put('/expenses/{id}', [ExpenseController::class, 'update'])->name('expenses.update');
    Route::delete('/expenses/{id}', [ExpenseController::class, 'destroy'])->name('expenses.destroy');
    Route::get('/expenses/pdf', [ExpenseController::class, 'downloadPDF'])->name('expenses.pdf');
    Route::get('/expenses/export-csv', [ExpenseController::class, 'exportCsv'])->name('expenses.export-csv');

    /*
    |--------------------------------------------------------------------------
    | INCOMES & EARNINGS (CASH FLOW INFLOW)
    |--------------------------------------------------------------------------
    */
    Route::get('/incomes', [IncomeController::class, 'index'])->name('incomes.index');
    Route::get('/incomes/create', [IncomeController::class, 'create'])->name('incomes.create');
    Route::post('/incomes', [IncomeController::class, 'store'])->name('incomes.store');
    Route::get('/incomes/{id}/edit', [IncomeController::class, 'edit'])->name('incomes.edit');
    Route::put('/incomes/{id}', [IncomeController::class, 'update'])->name('incomes.update');
    Route::delete('/incomes/{id}', [IncomeController::class, 'destroy'])->name('incomes.destroy');

    /*
    |--------------------------------------------------------------------------
    | SPLITWISE / SHARED GROUP EXPENSES
    |--------------------------------------------------------------------------
    */
    Route::get('/splits', [SplitController::class, 'index'])->name('splits.index');
    Route::get('/splits/groups/create', [SplitController::class, 'createGroup'])->name('splits.groups.create');
    Route::post('/splits/groups', [SplitController::class, 'storeGroup'])->name('splits.groups.store');
    Route::get('/splits/join/{code}', [SplitController::class, 'joinByCode'])->name('splits.join');
    Route::post('/splits/join', [SplitController::class, 'joinGroup'])->name('splits.join.store');
    Route::get('/splits/groups/{id}', [SplitController::class, 'show'])->name('splits.show');
    Route::post('/splits/groups/{id}/members', [SplitController::class, 'addMember'])->name('splits.groups.members.store');
    Route::post('/splits/groups/{id}/expenses', [SplitController::class, 'storeExpense'])->name('splits.expenses.store');
    Route::delete('/splits/groups/{id}/expenses/{expenseId}', [SplitController::class, 'destroyExpense'])->name('splits.expenses.destroy');
    Route::post('/splits/groups/{id}/settlements', [SplitController::class, 'storeSettlement'])->name('splits.settlements.store');
    Route::delete('/splits/groups/{id}/settlements/{settlementId}', [SplitController::class, 'destroySettlement'])->name('splits.settlements.destroy');
    Route::get('/splits/groups/{id}/whatsapp', [SplitController::class, 'shareWhatsApp'])->name('splits.groups.whatsapp');
    Route::delete('/splits/groups/{id}', [SplitController::class, 'destroyGroup'])->name('splits.groups.destroy');

    /*
    |--------------------------------------------------------------------------
    | PROFILE
    |--------------------------------------------------------------------------
    */

    Route::get('/profile', [ProfileController::class,'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class,'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

});

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminAnalyticsController;
use App\Http\Controllers\Admin\AdminSystemController;

/*
|--------------------------------------------------------------------------
| ADMIN CONTROL PANEL
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
    
    // User Management
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [AdminUserController::class, 'create'])->name('users.create');
    Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
    Route::get('/users/{id}', [AdminUserController::class, 'show'])->name('users.show');
    Route::post('/users/{id}/plan', [AdminUserController::class, 'updatePlan'])->name('users.update-plan');
    Route::post('/users/{id}/toggle-admin', [AdminUserController::class, 'toggleAdmin'])->name('users.toggle-admin');
    Route::delete('/users/{id}', [AdminUserController::class, 'destroy'])->name('users.destroy');

    // Analytics & Telemetry
    Route::get('/analytics', [AdminAnalyticsController::class, 'index'])->name('analytics');

    // System Status & Maintenance
    Route::get('/system', [AdminSystemController::class, 'index'])->name('system');
    Route::post('/system/clear-cache', [AdminSystemController::class, 'clearCache'])->name('system.clear-cache');
});

require __DIR__.'/auth.php';