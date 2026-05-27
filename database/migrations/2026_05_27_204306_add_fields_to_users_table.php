<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            $table->string('avatar_url')->nullable()->after('phone');
            $table->string('locale', 8)->default('en')->after('avatar_url');
            $table->boolean('is_active')->default(true)->after('locale');
            $table->foreignId('country_id')->nullable()->after('is_active')->constrained('countries')->nullOnDelete();
            $table->timestamp('last_active_at')->nullable()->after('country_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('country_id');
            $table->dropColumn(['phone', 'avatar_url', 'locale', 'is_active', 'last_active_at']);
        });
    }
};
