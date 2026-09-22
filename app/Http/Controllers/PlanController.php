<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class PlanController extends Controller
{
    public function show()
    {
        $plans = User::getAvailablePlans();
        $currentPlan = auth()->user()->plan ?? 'free';

        return view('choose-plan', compact('plans', 'currentPlan'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'plan' => 'required|in:free,basic,medium,pro',
        ]);

        $plans = User::getAvailablePlans();
        $selectedPlan = $plans[$request->plan] ?? null;

        if (!$selectedPlan) {
            return redirect()->route('plans.show')->with('error', 'Invalid plan selected.');
        }

        // Restrict access if the plan is marked as coming soon
        if ($selectedPlan['is_coming_soon'] ?? false) {
            return redirect()->route('plans.show')->with('warning', "🚀 The {$selectedPlan['name']} ({$selectedPlan['price_formatted']}) is coming soon! Access to paid plans is currently locked while we prepare launch. You have full access to the Free Plan.");
        }

        $user = auth()->user();
        $user->plan = User::PLAN_FREE;
        $user->save();

        return redirect('/dashboard')->with('success', 'You are on the Free Plan with all starter features active!');
    }

    public function updateBudget(Request $request)
    {
        $request->validate([
            'monthly_budget' => 'required|numeric|min:0',
        ]);

        $user = auth()->user();
        $user->monthly_budget = $request->monthly_budget;
        $user->save();

        return back()->with('success', 'Monthly budget goal updated successfully!');
    }
}