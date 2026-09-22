<?php

namespace App\Http\Controllers;

use App\Models\SplitExpense;
use App\Models\SplitExpenseParticipant;
use App\Models\SplitGroup;
use App\Models\SplitGroupMember;
use App\Models\SplitSettlement;
use App\Models\User;
use App\Services\SplitBalanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SplitController extends Controller
{
    protected SplitBalanceService $balanceService;

    public function __construct(SplitBalanceService $balanceService)
    {
        $this->balanceService = $balanceService;
    }

    /**
     * Split Dashboard: Overview of user's groups, net balances, and quick actions.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $summary = $this->balanceService->getUserOverallSummary($user);

        return view('splits.index', [
            'summary' => $summary,
            'user' => $user,
        ]);
    }

    /**
     * Show form to create a new split group.
     */
    public function createGroup()
    {
        $user = Auth::user();
        $registeredUsers = User::where('id', '!=', $user->id)->select('id', 'name', 'email')->take(10)->get();

        return view('splits.create-group', [
            'registeredUsers' => $registeredUsers,
        ]);
    }

    /**
     * Join a group using an 8-character invite code.
     */
    public function joinByCode(string $code)
    {
        $user = Auth::user();
        $group = SplitGroup::where('invite_code', strtoupper(trim($code)))->first();

        if (!$group) {
            return redirect()->route('splits.index')->with('error', 'Group invite code "' . $code . '" was not found. Please check with your group admin.');
        }

        $this->linkUserToGroup($user, $group);

        return redirect()->route('splits.show', $group->id)->with('success', 'You have joined the group "' . $group->name . '" successfully!');
    }

    /**
     * Form submission handler to join a group by code or link.
     */
    public function joinGroup(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:100',
        ]);

        $rawInput = trim($request->input('code'));
        
        // Extract 8-character code if user pasted a full URL
        if (preg_match('/([A-Za-z0-9]{8})/i', $rawInput, $matches)) {
            $code = strtoupper($matches[1]);
        } else {
            $code = strtoupper($rawInput);
        }

        return $this->joinByCode($code);
    }

    /**
     * Link or add an authenticated user into a split group.
     */
    protected function linkUserToGroup(User $user, SplitGroup $group): SplitGroupMember
    {
        // 1. If user already linked by user_id
        $member = $group->members()->where('user_id', $user->id)->first();
        if ($member) {
            return $member;
        }

        // 2. If a member was added earlier with user's email
        if (!empty($user->email)) {
            $member = $group->members()
                ->whereNull('user_id')
                ->whereRaw('LOWER(email) = ?', [strtolower(trim($user->email))])
                ->first();

            if ($member) {
                $member->update([
                    'user_id' => $user->id,
                    'name' => $user->name,
                ]);
                return $member;
            }
        }

        // 3. If a member exists with the exact same name (and no other user assigned)
        $member = $group->members()
            ->whereNull('user_id')
            ->whereRaw('LOWER(name) = ?', [strtolower(trim($user->name))])
            ->first();

        if ($member) {
            $member->update([
                'user_id' => $user->id,
                'email' => $user->email,
            ]);
            return $member;
        }

        // 4. Create new member entry for this user
        return SplitGroupMember::create([
            'group_id' => $group->id,
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'is_admin' => false,
        ]);
    }

    /**
     * Store a newly created group and its initial members.
     */
    public function storeGroup(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'type' => 'required|string|in:trip,apartment,couple,event,other',
            'currency' => 'nullable|string|max:10',
            'description' => 'nullable|string|max:500',
            'members' => 'nullable|array',
            'members.*.name' => 'required|string|max:100',
            'members.*.email' => 'nullable|email|max:150',
            'members.*.phone' => 'nullable|string|max:20',
        ]);

        $user = Auth::user();

        $group = DB::transaction(function () use ($request, $user) {
            $group = SplitGroup::create([
                'user_id' => $user->id,
                'name' => $request->input('name'),
                'type' => $request->input('type', 'other'),
                'currency' => $request->input('currency', 'INR'),
                'description' => $request->input('description'),
            ]);

            // Add creator as the first member & admin
            SplitGroupMember::create([
                'group_id' => $group->id,
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'is_admin' => true,
            ]);

            // Add additional initial members
            if ($request->has('members') && is_array($request->input('members'))) {
                foreach ($request->input('members') as $memberData) {
                    if (empty(trim($memberData['name'] ?? ''))) continue;

                    // If user is already added (e.g. self), skip
                    if (strtolower(trim($memberData['name'])) === strtolower(trim($user->name)) || 
                        (!empty($memberData['email']) && strtolower(trim($memberData['email'])) === strtolower(trim($user->email)))) {
                        continue;
                    }

                    // Check if email matches an existing registered user
                    $registeredUser = null;
                    if (!empty($memberData['email'])) {
                        $registeredUser = User::whereRaw('LOWER(email) = ?', [strtolower(trim($memberData['email']))])->first();
                    }

                    SplitGroupMember::create([
                        'group_id' => $group->id,
                        'user_id' => $registeredUser?->id,
                        'name' => trim($memberData['name']),
                        'email' => $memberData['email'] ?? null,
                        'phone' => $memberData['phone'] ?? null,
                        'is_admin' => false,
                    ]);
                }
            }

            return $group;
        });

        return redirect()->route('splits.show', $group->id)->with('success', 'Group "' . $group->name . '" created successfully! Now you can add expenses or invite friends.');
    }

    /**
     * Show group details: expenses feed, member balances, simplified settlements, and WhatsApp share.
     */
    public function show(int $id)
    {
        $user = Auth::user();
        $group = SplitGroup::with(['members.user', 'expenses.payer', 'expenses.participants.member', 'settlements.fromMember', 'settlements.toMember'])
            ->findOrFail($id);

        $balances = $this->balanceService->calculateGroupBalances($group);
        $userMember = $group->getMemberForUser($user->id, $user->email) ?? $group->members()->first();
        $whatsAppSummary = $this->balanceService->generateWhatsAppSummary($group);

        // Fetch other registered users to allow 1-click adding
        $existingMemberUserIds = $group->members()->whereNotNull('user_id')->pluck('user_id')->toArray();
        $existingMemberEmails = $group->members()->whereNotNull('email')->pluck('email')->map(fn($e) => strtolower(trim($e)))->toArray();

        $registeredUsers = User::where('id', '!=', $user->id)
            ->whereNotIn('id', $existingMemberUserIds)
            ->whereNotIn(DB::raw('LOWER(email)'), $existingMemberEmails)
            ->select('id', 'name', 'email')
            ->take(20)
            ->get();

        return view('splits.show', [
            'group' => $group,
            'balances' => $balances,
            'userMember' => $userMember,
            'whatsAppSummary' => $whatsAppSummary,
            'registeredUsers' => $registeredUsers,
        ]);
    }

    /**
     * Add a new member to an existing group.
     */
    public function addMember(Request $request, int $groupId)
    {
        $group = SplitGroup::findOrFail($groupId);

        $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'nullable|email|max:150',
            'phone' => 'nullable|string|max:20',
            'upi_id' => 'nullable|string|max:50',
            'registered_user_id' => 'nullable|exists:users,id',
        ]);

        $registeredUser = null;
        if ($request->filled('registered_user_id')) {
            $registeredUser = User::find($request->input('registered_user_id'));
        } elseif (!empty($request->input('email'))) {
            $registeredUser = User::whereRaw('LOWER(email) = ?', [strtolower(trim($request->input('email')))])->first();
        }

        $memberName = $registeredUser ? $registeredUser->name : trim($request->input('name'));
        $memberEmail = $registeredUser ? $registeredUser->email : $request->input('email');

        // Check if member already in group
        $alreadyInGroup = $group->members()->where(function ($q) use ($registeredUser, $memberName, $memberEmail) {
            if ($registeredUser) {
                $q->where('user_id', $registeredUser->id);
            }
            if ($memberEmail) {
                $q->orWhereRaw('LOWER(email) = ?', [strtolower(trim($memberEmail))]);
            }
            $q->orWhereRaw('LOWER(name) = ?', [strtolower(trim($memberName))]);
        })->exists();

        if ($alreadyInGroup) {
            return back()->with('error', 'This member is already in the group!');
        }

        SplitGroupMember::create([
            'group_id' => $group->id,
            'user_id' => $registeredUser?->id,
            'name' => $memberName,
            'email' => $memberEmail,
            'phone' => $request->input('phone'),
            'upi_id' => $request->input('upi_id'),
            'is_admin' => false,
        ]);

        return back()->with('success', 'Member "' . $memberName . '" added to the group!');
    }

    /**
     * Store a new split expense inside a group.
     */
    public function storeExpense(Request $request, int $groupId)
    {
        $group = SplitGroup::findOrFail($groupId);

        $request->validate([
            'title' => 'required|string|max:150',
            'amount' => 'required|numeric|min:0.01|max:10000000',
            'paid_by_member_id' => 'required|exists:split_group_members,id',
            'category' => 'nullable|string|max:50',
            'expense_date' => 'required|date',
            'split_type' => 'required|string|in:equal,exact,percentage',
            'participants' => 'required|array|min:1',
            'shares' => 'nullable|array',
            'notes' => 'nullable|string|max:500',
        ]);

        $amount = (float) $request->input('amount');
        $splitType = $request->input('split_type', 'equal');
        $participantMemberIds = $request->input('participants', []);
        $sharesInput = $request->input('shares', []);

        DB::transaction(function () use ($request, $group, $amount, $splitType, $participantMemberIds, $sharesInput) {
            $expense = SplitExpense::create([
                'group_id' => $group->id,
                'created_by' => Auth::id(),
                'paid_by_member_id' => $request->input('paid_by_member_id'),
                'title' => trim($request->input('title')),
                'amount' => $amount,
                'currency' => $group->currency ?? 'INR',
                'category' => $request->input('category', 'General'),
                'split_type' => $splitType,
                'expense_date' => $request->input('expense_date'),
                'notes' => $request->input('notes'),
            ]);

            $totalParticipants = count($participantMemberIds);

            if ($splitType === 'equal') {
                $baseShare = round($amount / $totalParticipants, 2);
                $remainingDiff = round($amount - ($baseShare * $totalParticipants), 2);

                foreach ($participantMemberIds as $index => $memberId) {
                    $shareAmount = $baseShare;
                    // Adjust penny rounding on first participant if needed
                    if ($index === 0) {
                        $shareAmount += $remainingDiff;
                    }

                    SplitExpenseParticipant::create([
                        'split_expense_id' => $expense->id,
                        'group_member_id' => $memberId,
                        'share_amount' => $shareAmount,
                        'percentage' => round(100 / $totalParticipants, 2),
                    ]);
                }
            } elseif ($splitType === 'exact') {
                foreach ($participantMemberIds as $memberId) {
                    $share = (float) ($sharesInput[$memberId] ?? 0);
                    SplitExpenseParticipant::create([
                        'split_expense_id' => $expense->id,
                        'group_member_id' => $memberId,
                        'share_amount' => $share,
                        'percentage' => $amount > 0 ? round(($share / $amount) * 100, 2) : 0,
                    ]);
                }
            } elseif ($splitType === 'percentage') {
                foreach ($participantMemberIds as $memberId) {
                    $pct = (float) ($sharesInput[$memberId] ?? 0);
                    $share = round(($pct / 100) * $amount, 2);
                    SplitExpenseParticipant::create([
                        'split_expense_id' => $expense->id,
                        'group_member_id' => $memberId,
                        'share_amount' => $share,
                        'percentage' => $pct,
                    ]);
                }
            }
        });

        return back()->with('success', 'Expense "' . $request->input('title') . '" of ₹' . number_format($amount, 2) . ' recorded and split!');
    }

    /**
     * Delete an expense from the group.
     */
    public function destroyExpense(int $groupId, int $expenseId)
    {
        $group = SplitGroup::findOrFail($groupId);
        $expense = SplitExpense::where('group_id', $group->id)->findOrFail($expenseId);
        $title = $expense->title;
        $expense->delete();

        return back()->with('success', 'Expense "' . $title . '" deleted successfully.');
    }

    /**
     * Record a settlement payment between two members.
     */
    public function storeSettlement(Request $request, int $groupId)
    {
        $group = SplitGroup::findOrFail($groupId);

        $request->validate([
            'from_member_id' => 'required|exists:split_group_members,id',
            'to_member_id' => 'required|exists:split_group_members,id|different:from_member_id',
            'amount' => 'required|numeric|min:0.01|max:10000000',
            'payment_method' => 'required|string|in:UPI,Cash,Bank Transfer,Other',
            'settled_at' => 'required|date',
            'transaction_ref' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:300',
        ]);

        $settlement = SplitSettlement::create([
            'group_id' => $group->id,
            'created_by' => Auth::id(),
            'from_member_id' => $request->input('from_member_id'),
            'to_member_id' => $request->input('to_member_id'),
            'amount' => (float) $request->input('amount'),
            'payment_method' => $request->input('payment_method'),
            'transaction_ref' => $request->input('transaction_ref'),
            'notes' => $request->input('notes'),
            'settled_at' => $request->input('settled_at'),
        ]);

        $fromName = $settlement->fromMember->name;
        $toName = $settlement->toMember->name;

        return back()->with('success', 'Settlement of ₹' . number_format($settlement->amount, 2) . ' from ' . $fromName . ' to ' . $toName . ' recorded successfully!');
    }

    /**
     * Delete a settlement record.
     */
    public function destroySettlement(int $groupId, int $settlementId)
    {
        $group = SplitGroup::findOrFail($groupId);
        $settlement = SplitSettlement::where('group_id', $group->id)->findOrFail($settlementId);
        $settlement->delete();

        return back()->with('success', 'Settlement record deleted.');
    }

    /**
     * WhatsApp Share Helper: Redirects to WhatsApp with prefilled message.
     */
    public function shareWhatsApp(int $groupId)
    {
        $group = SplitGroup::findOrFail($groupId);
        $message = $this->balanceService->generateWhatsAppSummary($group);
        $url = "https://api.whatsapp.com/send?text=" . urlencode($message);

        return redirect()->away($url);
    }

    /**
     * Delete a split group when group work is finished or settled.
     */
    public function destroyGroup(int $id)
    {
        $user = Auth::user();
        $group = SplitGroup::findOrFail($id);

        $isCreator = ($group->user_id === $user->id);
        $isAdminMember = $group->members()->where('user_id', $user->id)->where('is_admin', true)->exists();
        $isSuperAdmin = $user->isAdmin();

        if (!$isCreator && !$isAdminMember && !$isSuperAdmin) {
            return back()->with('error', 'Only the group creator or group admin can delete this group.');
        }

        $groupName = $group->name;

        DB::transaction(function () use ($group) {
            $group->delete();
        });

        return redirect()->route('splits.index')->with('success', 'Group "' . $groupName . '" and all its records have been deleted successfully.');
    }
}
