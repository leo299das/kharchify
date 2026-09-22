<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();

            // Which user owns the expense
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Category of expense
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();

            // Amount spent
            $table->decimal('amount', 10, 2);

            // Payment method
            $table->string('payment_method');

            // Optional transaction ID (UPI / Bank / Card)
            $table->string('transaction_id')->nullable();

            // Optional note
            $table->text('note')->nullable();

            // Date and time of expense
            $table->timestamp('expense_date');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};