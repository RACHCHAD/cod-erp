<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ad_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('platform_id')->constrained('platforms')->cascadeOnDelete();
            $table->string('external_id')->nullable();
            $table->foreignId('currency_id')->nullable()->constrained('currencies')->nullOnDelete();
            $table->decimal('daily_limit', 14, 2)->nullable();
            $table->enum('status', ['active', 'paused', 'banned', 'review'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['platform_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ad_accounts');
    }
};
