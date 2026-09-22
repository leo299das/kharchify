<?php

namespace Tests\Feature;

use App\Models\SplitExpense;
use App\Models\SplitGroup;
use App\Models\SplitGroupMember;
use App\Models\SplitSettlement;
use App\Models\User;
use App\Services\SplitBalanceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SplitModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_from_splits(): void
    {
        $response = $this->get('/splits');
        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_split_dashboard(): void
    {
        $user = User::factory()->create(['plan' => 'basic']);

        $response = $this->actingAs($user)->get('/splits');

        $response->assertStatus(200);
        $response->assertSee('Kharchify Split & Settle', false);
        $response->assertSee('You Are Owed');
        $response->assertSee('You Owe');
    }

    public function test_user_can_create_split_group_with_members(): void
    {
        $user = User::factory()->create(['name' => 'Darakshaan', 'plan' => 'pro']);

        $response = $this->actingAs($user)->post('/splits/groups', [
            'name' => 'Goa Vacation 2026',
            'type' => 'trip',
            'currency' => 'INR',
            'description' => 'Splitting beach villa, cabs, and meals',
            'members' => [
                ['name' => 'Rahul Sharma', 'email' => 'rahul@example.com'],
                ['name' => 'Aman Verma', 'email' => 'aman@example.com'],
            ],
        ]);

        $group = SplitGroup::where('name', 'Goa Vacation 2026')->first();
        $this->assertNotNull($group);
        $response->assertRedirect('/splits/groups/' . $group->id);

        // Group has creator + 2 friends = 3 members
        $this->assertEquals(3, $group->members()->count());
        $this->assertTrue($group->members()->where('user_id', $user->id)->first()->is_admin);
    }

    public function test_equal_split_expense_and_balance_calculation(): void
    {
        $user = User::factory()->create(['name' => 'Darakshaan', 'plan' => 'pro']);

        $group = SplitGroup::create([
            'user_id' => $user->id,
            'name' => 'Flat 402 Roommates',
            'type' => 'apartment',
            'currency' => 'INR',
        ]);

        $m1 = SplitGroupMember::create([
            'group_id' => $group->id,
            'user_id' => $user->id,
            'name' => 'Darakshaan',
            'is_admin' => true,
        ]);

        $m2 = SplitGroupMember::create([
            'group_id' => $group->id,
            'name' => 'Rahul',
        ]);

        $m3 = SplitGroupMember::create([
            'group_id' => $group->id,
            'name' => 'Aman',
        ]);

        // Darakshaan pays ₹900 for dinner, split equally among all 3 (₹300 each)
        $response = $this->actingAs($user)->post('/splits/groups/' . $group->id . '/expenses', [
            'title' => 'Dinner at Social',
            'amount' => 900.00,
            'paid_by_member_id' => $m1->id,
            'category' => 'Food & Drinks',
            'expense_date' => '2026-09-22',
            'split_type' => 'equal',
            'participants' => [$m1->id, $m2->id, $m3->id],
        ]);

        $response->assertSessionHas('success');

        $expense = SplitExpense::where('group_id', $group->id)->first();
        $this->assertEquals(900.00, $expense->amount);
        $this->assertEquals(3, $expense->participants()->count());

        // Test Balance Calculations via Service
        $service = app(SplitBalanceService::class);
        $balances = $service->calculateGroupBalances($group);

        // Darakshaan paid ₹900, owes ₹300 -> net +₹600
        $this->assertEquals(600.00, $balances['members'][$m1->id]['balance']['net_balance']);
        
        // Rahul owes ₹300 -> net -₹300
        $this->assertEquals(-300.00, $balances['members'][$m2->id]['balance']['net_balance']);

        // Aman owes ₹300 -> net -₹300
        $this->assertEquals(-300.00, $balances['members'][$m3->id]['balance']['net_balance']);

        // Simplified debts should have 2 transactions: Rahul -> Darakshaan ₹300, Aman -> Darakshaan ₹300
        $this->assertCount(2, $balances['simplified_debts']);
    }

    public function test_recording_settlement_payment_clears_debt(): void
    {
        $user = User::factory()->create(['name' => 'Darakshaan', 'plan' => 'pro']);

        $group = SplitGroup::create([
            'user_id' => $user->id,
            'name' => 'Road Trip',
            'type' => 'trip',
        ]);

        $m1 = SplitGroupMember::create(['group_id' => $group->id, 'user_id' => $user->id, 'name' => 'Darakshaan']);
        $m2 = SplitGroupMember::create(['group_id' => $group->id, 'name' => 'Rahul']);

        // Darakshaan pays ₹500 cab, split equally (₹250 each)
        $this->actingAs($user)->post('/splits/groups/' . $group->id . '/expenses', [
            'title' => 'Fuel & Toll',
            'amount' => 500.00,
            'paid_by_member_id' => $m1->id,
            'expense_date' => '2026-09-22',
            'split_type' => 'equal',
            'participants' => [$m1->id, $m2->id],
        ]);

        // Rahul settles up and pays Darakshaan ₹250 via UPI
        $response = $this->actingAs($user)->post('/splits/groups/' . $group->id . '/settlements', [
            'from_member_id' => $m2->id,
            'to_member_id' => $m1->id,
            'amount' => 250.00,
            'payment_method' => 'UPI',
            'settled_at' => '2026-09-22',
            'notes' => 'GPay UPI ref 12345',
        ]);

        $response->assertSessionHas('success');

        $service = app(SplitBalanceService::class);
        $balances = $service->calculateGroupBalances($group);

        // After settlement, both are ₹0.00 (all settled)
        $this->assertEquals(0.00, $balances['members'][$m1->id]['balance']['net_balance']);
        $this->assertEquals(0.00, $balances['members'][$m2->id]['balance']['net_balance']);
        $this->assertEmpty($balances['simplified_debts']);
    }

    public function test_whatsapp_summary_redirect_url(): void
    {
        $user = User::factory()->create(['plan' => 'basic']);

        $group = SplitGroup::create([
            'user_id' => $user->id,
            'name' => 'Office Lunch Group',
        ]);

        $m1 = SplitGroupMember::create(['group_id' => $group->id, 'user_id' => $user->id, 'name' => 'Darakshaan']);
        $m2 = SplitGroupMember::create(['group_id' => $group->id, 'name' => 'Priya']);

        $response = $this->actingAs($user)->get('/splits/groups/' . $group->id . '/whatsapp');

        $response->assertRedirect();
        $this->assertStringContainsString('api.whatsapp.com/send', $response->headers->get('Location'));
    }

    public function test_dashboard_displays_split_balances_widget(): void
    {
        $user = User::factory()->create(['plan' => 'pro']);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Split & Settle Balances', false);
        $response->assertSee('You Are Owed');
        $response->assertSee('You Owe');
        $response->assertSee('Open Split Hub', false);
    }

    public function test_user_can_join_group_with_invite_code(): void
    {
        $creator = User::factory()->create(['name' => 'Creator', 'plan' => 'pro']);
        $friend = User::factory()->create(['name' => 'Friend', 'email' => 'friend@example.com', 'plan' => 'free']);

        $group = SplitGroup::create([
            'user_id' => $creator->id,
            'name' => 'Goa Trip 2026',
            'type' => 'trip',
        ]);

        // Friend joins using the group invite code
        $response = $this->actingAs($friend)->get('/splits/join/' . $group->invite_code);

        $response->assertRedirect('/splits/groups/' . $group->id);
        $response->assertSessionHas('success');

        // Verify friend is now a member of the group
        $this->assertDatabaseHas('split_group_members', [
            'group_id' => $group->id,
            'user_id' => $friend->id,
            'email' => 'friend@example.com',
        ]);
    }

    public function test_user_auto_links_and_sees_group_when_added_by_email(): void
    {
        $creator = User::factory()->create(['name' => 'Creator', 'plan' => 'pro']);
        $friend = User::factory()->create(['name' => 'Friend', 'email' => 'friend@example.com', 'plan' => 'free']);

        $group = SplitGroup::create([
            'user_id' => $creator->id,
            'name' => 'Flat Roommates',
            'type' => 'apartment',
        ]);

        // Creator added member by email before friend joined
        $member = SplitGroupMember::create([
            'group_id' => $group->id,
            'user_id' => null, // initially unlinked
            'name' => 'Friend',
            'email' => 'friend@example.com',
        ]);

        // Friend visits splits dashboard
        $response = $this->actingAs($friend)->get('/splits');

        $response->assertStatus(200);
        $response->assertSee('Flat Roommates');

        // Member record should now be linked to friend's user_id
        $this->assertDatabaseHas('split_group_members', [
            'id' => $member->id,
            'user_id' => $friend->id,
        ]);
    }

    public function test_group_creator_can_delete_group_when_work_is_done(): void
    {
        $creator = User::factory()->create(['name' => 'Creator', 'plan' => 'pro']);
        $friend = User::factory()->create(['name' => 'Friend', 'plan' => 'free']);

        $group = SplitGroup::create([
            'user_id' => $creator->id,
            'name' => 'Old Trip 2025',
            'type' => 'trip',
        ]);

        $m1 = SplitGroupMember::create([
            'group_id' => $group->id,
            'user_id' => $creator->id,
            'name' => 'Creator',
            'is_admin' => true,
        ]);

        $m2 = SplitGroupMember::create([
            'group_id' => $group->id,
            'user_id' => $friend->id,
            'name' => 'Friend',
            'is_admin' => false,
        ]);

        // Add an expense
        $expense = SplitExpense::create([
            'group_id' => $group->id,
            'created_by' => $creator->id,
            'paid_by_member_id' => $m1->id,
            'title' => 'Resort Booking',
            'amount' => 5000.00,
            'category' => 'Hotel',
            'expense_date' => '2026-09-20',
            'split_type' => 'equal',
        ]);

        // Creator deletes the group when finished
        $response = $this->actingAs($creator)->delete('/splits/groups/' . $group->id);

        $response->assertRedirect('/splits');
        $response->assertSessionHas('success');

        // Group and cascading records should be purged
        $this->assertDatabaseMissing('split_groups', ['id' => $group->id]);
        $this->assertDatabaseMissing('split_group_members', ['group_id' => $group->id]);
        $this->assertDatabaseMissing('split_expenses', ['group_id' => $group->id]);
    }

    public function test_non_admin_member_cannot_delete_group(): void
    {
        $creator = User::factory()->create(['name' => 'Creator', 'plan' => 'pro']);
        $memberUser = User::factory()->create(['name' => 'Member User', 'plan' => 'free']);

        $group = SplitGroup::create([
            'user_id' => $creator->id,
            'name' => 'Active House Rent',
            'type' => 'apartment',
        ]);

        SplitGroupMember::create([
            'group_id' => $group->id,
            'user_id' => $creator->id,
            'name' => 'Creator',
            'is_admin' => true,
        ]);

        SplitGroupMember::create([
            'group_id' => $group->id,
            'user_id' => $memberUser->id,
            'name' => 'Member User',
            'is_admin' => false,
        ]);

        // Regular member tries to delete the group
        $response = $this->actingAs($memberUser)->delete('/splits/groups/' . $group->id);

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('split_groups', ['id' => $group->id]);
    }
}
