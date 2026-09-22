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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'monthly_budget')) {
                $table->decimal('monthly_budget', 12, 2)->default(25000.00)->after('plan');
            }
            if (!Schema::hasColumn('users', 'currency')) {
                $table->string('currency', 10)->default('₹')->after('monthly_budget');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['monthly_budget', 'currency']);
        });
    }
};
