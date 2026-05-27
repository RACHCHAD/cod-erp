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
            $table->foreignId('category_id')->constrained('expense_categories')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('country_id')->nullable()->constrained('countries')->nullOnDelete();
            $table->foreignId('currency_id')->nullable()->constrained('currencies')->nullOnDelete();
            $table->date('date');
            $table->decimal('amount', 14, 2)->default(0);
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('invoice_path')->nullable();
            $table->boolean('is_recurring')->default(false);
            $table->enum('recurring_interval', ['daily', 'weekly', 'monthly', 'yearly'])->nullable();
            $table->date('next_due_at')->nullable();
            $table->timestamps();

            $table->index(['date', 'category_id']);
            $table->index('is_recurring');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
