<?php

namespace App\Services;

use App\Models\SplitGroup;
use App\Models\SplitGroupMember;
use App\Models\User;

class SplitBalanceService
{
    /**
     * Calculate comprehensive balance summary for all members in a group.
     */
    public function calculateGroupBalances(SplitGroup $group): array
    {
        $members = $group->members()->with([
            'paidExpenses',
            'expenseParticipations',
            'sentSettlements',
            'receivedSettlements',
            'user'
        ])->get();

        $memberBalances = [];
        $totalSpend = 0;

        foreach ($members as $member) {
            $balance = $member->balance_details;
            $memberBalances[$member->id] = [
                'member' => $member,
                'balance' => $balance,
            ];
            $totalSpend += $balance['expenses_paid'];
        }

        $simplifiedDebts = $this->calculateSimplifiedDebts($memberBalances);

        return [
            'total_spend' => $totalSpend,
            'members' => $memberBalances,
            'simplified_debts' => $simplifiedDebts,
        ];
    }

    /**
     * Minimal transaction debt simplification algorithm.
     * Matches largest debtors with largest creditors to minimize total transactions.
     */
    public function calculateSimplifiedDebts(array $memberBalances): array
    {
        $creditors = []; // members who are owed money (positive net balance)
        $debtors = [];   // members who owe money (negative net balance)

        foreach ($memberBalances as $memberId => $data) {
            $net = $data['balance']['net_balance'];
            if ($net > 0.01) {
                $creditors[] = [
                    'member' => $data['member'],
                    'amount' => $net,
                ];
            } elseif ($net < -0.01) {
                $debtors[] = [
                    'member' => $data['member'],
                    'amount' => abs($net),
                ];
            }
        }

        // Sort descending by amount
        usort($creditors, fn($a, $b) => $b['amount'] <=> $a['amount']);
        usort($debtors, fn($a, $b) => $b['amount'] <=> $a['amount']);

        $debts = [];
        $i = 0; // debtor pointer
        $j = 0; // creditor pointer

        while ($i < count($debtors) && $j < count($creditors)) {
            $debtor = &$debtors[$i];
            $creditor = &$creditors[$j];

            $amount = min($debtor['amount'], $creditor['amount']);
            $amount = round($amount, 2);

            if ($amount > 0.01) {
                $debts[] = [
                    'from' => $debtor['member'],
                    'to' => $creditor['member'],
                    'amount' => $amount,
                ];
            }

            $debtor['amount'] = round($debtor['amount'] - $amount, 2);
            $creditor['amount'] = round($creditor['amount'] - $amount, 2);

            if ($debtor['amount'] <= 0.01) {
                $i++;
            }
            if ($creditor['amount'] <= 0.01) {
                $j++;
            }
        }

        return $debts;
    }

    /**
     * Calculate aggregate balance summary across all groups for a given user.
     */
    public function getUserOverallSummary(User $user): array
    {
        // Auto-link any unlinked members matching user's email
        if (!empty($user->email)) {
            SplitGroupMember::whereNull('user_id')
                ->whereRaw('LOWER(email) = ?', [strtolower(trim($user->email))])
                ->update(['user_id' => $user->id]);
        }

        // Get all group memberships for this user or created by this user
        $createdGroupIds = SplitGroup::where('user_id', $user->id)->pluck('id');
        $memberGroupIds = SplitGroupMember::where('user_id', $user->id)
            ->orWhere(function ($q) use ($user) {
                if (!empty($user->email)) {
                    $q->whereRaw('LOWER(email) = ?', [strtolower(trim($user->email))]);
                }
            })
            ->pluck('group_id');

        $allGroupIds = $createdGroupIds->merge($memberGroupIds)->unique();

        $groups = SplitGroup::whereIn('id', $allGroupIds)->with('members')->latest()->get();

        $totalOwedToUser = 0.0;
        $totalUserOwes = 0.0;
        $groupsSummary = [];

        foreach ($groups as $group) {
            $userMember = $group->getMemberForUser($user->id, $user->email);
            if (!$userMember) {
                // If user is creator but no member record matched by user_id/email, find by name
                $userMember = $group->members()->where('name', $user->name)->first();
            }

            if ($userMember) {
                $balance = $userMember->balance_details;
                $net = $balance['net_balance'];

                if ($net > 0.01) {
                    $totalOwedToUser += $net;
                } elseif ($net < -0.01) {
                    $totalUserOwes += abs($net);
                }

                $groupsSummary[] = [
                    'group' => $group,
                    'user_member' => $userMember,
                    'net_balance' => $net,
                    'status' => $balance['status'],
                ];
            } else {
                $groupsSummary[] = [
                    'group' => $group,
                    'user_member' => null,
                    'net_balance' => 0.0,
                    'status' => 'settled',
                ];
            }
        }

        $overallNet = round($totalOwedToUser - $totalUserOwes, 2);

        return [
            'total_groups' => $groups->count(),
            'total_owed_to_user' => round($totalOwedToUser, 2),
            'total_user_owes' => round($totalUserOwes, 2),
            'overall_net' => $overallNet,
            'groups' => $groupsSummary,
        ];
    }

    /**
     * Generate a formatted WhatsApp message for a group settlement.
     */
    public function generateWhatsAppSummary(SplitGroup $group): string
    {
        $data = $this->calculateGroupBalances($group);
        $currencySymbol = $group->currency === 'INR' ? '₹' : ($group->currency ?? '₹');

        $text = "👥 *Kharchify Split Summary*\n";
        $text .= "📂 *Group:* " . $group->name . "\n";
        $text .= "🔑 *Group Code:* " . $group->invite_code . "\n";
        $text .= "💰 *Total Group Spend:* " . $currencySymbol . number_format($data['total_spend'], 2) . "\n\n";

        $text .= "📊 *Member Balances:*\n";
        foreach ($data['members'] as $item) {
            $member = $item['member'];
            $net = $item['balance']['net_balance'];
            if ($net > 0.01) {
                $text .= "• " . $member->name . ": Gets back " . $currencySymbol . number_format($net, 2) . " ✅\n";
            } elseif ($net < -0.01) {
                $text .= "• " . $member->name . ": Owes " . $currencySymbol . number_format(abs($net), 2) . " ⚠️\n";
            } else {
                $text .= "• " . $member->name . ": Settled up 👍\n";
            }
        }

        if (!empty($data['simplified_debts'])) {
            $text .= "\n⚡ *Who Owes Whom (Simplified):*\n";
            foreach ($data['simplified_debts'] as $debt) {
                $text .= "👉 *" . $debt['from']->name . "* pays *" . $debt['to']->name . "*: " . $currencySymbol . number_format($debt['amount'], 2) . "\n";
            }
        }

        $joinUrl = url('/splits/join/' . $group->invite_code);
        $text .= "\n🔗 *Join & View Live Group:* " . $joinUrl . "\n";
        $text .= "\nTrack & Settle your group expenses easily with *Kharchify*! 🚀";

        return $text;
    }
}
