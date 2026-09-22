<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Split Groups Table
        Schema::create('split_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('name');
            $table->string('type')->default('other'); // apartment, trip, couple, event, other
            $table->string('currency')->default('INR');
            $table->string('icon')->default('👥');
            $table->string('invite_code')->nullable()->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 2. Group Members Table (Supports both registered users and manual/offline friends)
        Schema::create('split_group_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('split_groups')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('upi_id')->nullable();
            $table->boolean('is_admin')->default(false);
            $table->timestamps();
        });

        // 3. Split Expenses Table
        Schema::create('split_expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('split_groups')->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('paid_by_member_id')->constrained('split_group_members')->onDelete('cascade');
            $table->string('title');
            $table->decimal('amount', 12, 2);
            $table->string('currency')->default('INR');
            $table->string('category')->default('General'); // Food, Transport, Rent, Tickets, Utilities, Shopping, General
            $table->string('split_type')->default('equal'); // equal, exact, percentage
            $table->date('expense_date');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 4. Split Expense Participants Table (Who owes how much for each expense)
        Schema::create('split_expense_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('split_expense_id')->constrained('split_expenses')->onDelete('cascade');
            $table->foreignId('group_member_id')->constrained('split_group_members')->onDelete('cascade');
            $table->decimal('share_amount', 12, 2)->default(0.00);
            $table->decimal('percentage', 6, 2)->nullable();
            $table->timestamps();
        });

        // 5. Settlements Table (Record payments made to settle balances)
        Schema::create('split_settlements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('split_groups')->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('from_member_id')->constrained('split_group_members')->onDelete('cascade');
            $table->foreignId('to_member_id')->constrained('split_group_members')->onDelete('cascade');
            $table->decimal('amount', 12, 2);
            $table->string('payment_method')->default('UPI'); // UPI, Cash, Bank Transfer, Other
            $table->string('transaction_ref')->nullable();
            $table->text('notes')->nullable();
            $table->date('settled_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('split_settlements');
        Schema::dropIfExists('split_expense_participants');
        Schema::dropIfExists('split_expenses');
        Schema::dropIfExists('split_group_members');
        Schema::dropIfExists('split_groups');
    }
};
