<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Expense;
use App\Models\Category;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class AdminUserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::withCount(['expenses', 'categories'])
            ->withSum('expenses', 'amount');

        // Search Filter
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('id', $search);
            });
        }

        // Plan Filter
        if ($request->filled('plan')) {
            if ($request->plan === 'free') {
                $query->where(function($q) {
                    $q->where('plan', 'free')->orWhereNull('plan');
                });
            } else {
                $query->where('plan', $request->plan);
            }
        }

        // Role / Admin Filter
        if ($request->filled('role')) {
            if ($request->role === 'admin') {
                $query->where('is_admin', true);
            } elseif ($request->role === 'user') {
                $query->where('is_admin', false);
            }
        }

        // Sorting
        $sort = $request->get('sort', 'created_at');
        $direction = $request->get('direction', 'desc');

        if (in_array($sort, ['id', 'name', 'email', 'plan', 'created_at', 'expenses_sum_amount', 'expenses_count'])) {
            $query->orderBy($sort, $direction === 'asc' ? 'asc' : 'desc');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $users = $query->paginate(15)->withQueryString();
        $availablePlans = User::getAvailablePlans();

        $stats = [
            'total' => User::count(),
            'admins' => User::where('is_admin', true)->count(),
            'free' => User::where('plan', User::PLAN_FREE)->orWhereNull('plan')->count(),
            'paid' => User::whereIn('plan', [User::PLAN_BASIC, User::PLAN_MEDIUM, User::PLAN_PRO])->count(),
        ];

        return view('admin.users.index', compact('users', 'availablePlans', 'stats'));
    }

    public function show($id)
    {
        $user = User::withCount(['expenses', 'categories'])
            ->withSum('expenses', 'amount')
            ->findOrFail($id);

        $expenses = Expense::with('category')
            ->where('user_id', $user->id)
            ->orderBy('expense_date', 'desc')
            ->paginate(20);

        $categories = Category::withCount('expenses')
            ->withSum('expenses', 'amount')
            ->where('user_id', $user->id)
            ->get();

        $availablePlans = User::getAvailablePlans();

        return view('admin.users.show', compact('user', 'expenses', 'categories', 'availablePlans'));
    }

    public function create()
    {
        $availablePlans = User::getAvailablePlans();
        return view('admin.users.create', compact('availablePlans'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'string', Rules\Password::defaults()],
            'plan' => ['required', 'in:free,basic,medium,pro'],
            'is_admin' => ['nullable', 'boolean'],
            'monthly_budget' => ['nullable', 'numeric', 'min:0'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'plan' => $request->plan,
            'is_admin' => $request->boolean('is_admin'),
            'monthly_budget' => $request->monthly_budget ?? 25000,
            'email_verified_at' => now(),
        ]);

        return redirect()->route('admin.users.show', $user->id)
            ->with('success', "User '{$user->name}' created successfully with '{$user->plan}' plan.");
    }

    public function updatePlan(Request $request, $id)
    {
        $request->validate([
            'plan' => 'required|in:free,basic,medium,pro',
        ]);

        $user = User::findOrFail($id);
        $oldPlan = $user->plan ?? 'free';
        $user->plan = $request->plan;
        $user->save();

        return back()->with('success', "User '{$user->name}' plan changed from {$oldPlan} to {$user->plan}.");
    }

    public function toggleAdmin($id)
    {
        $user = User::findOrFail($id);

        if ($user->id === auth()->id() && $user->is_admin) {
            return back()->with('warning', 'You cannot revoke your own administrator privileges.');
        }

        $user->is_admin = !$user->is_admin;
        $user->save();

        $status = $user->is_admin ? 'granted Administrator privileges' : 'demoted to Regular User';
        return back()->with('success', "User '{$user->name}' was {$status}.");
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);

        if ($user->id === auth()->id()) {
            return back()->with('warning', 'You cannot delete your own account from the Admin Panel.');
        }

        $name = $user->name;
        $user->expenses()->delete();
        $user->categories()->delete();
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', "User '{$name}' and all associated records were permanently deleted.");
    }
}
