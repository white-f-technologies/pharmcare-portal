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
            $table->string('expense_number')->unique();
            $table->foreignId('expense_category_id')->constrained('expense_categories')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('title');
            $table->decimal('amount', 12, 2);
            $table->string('payment_method')->default('cash'); // cash, mobile_money, bank_transfer
            $table->date('expense_date');
            $table->string('reference_no')->nullable(); // Voucher / receipt number
            $table->text('notes')->nullable();
            $table->string('attachment')->nullable(); // Receipt photo/document
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
